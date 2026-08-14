<?php

namespace App\Services;

use App\Models\AcademicStaff;
use App\Models\AccessRole;
use App\Models\RegistrationApplication;
use App\Models\User;
use App\Support\AccessControl;
use App\Support\AcademicStaffOptions;
use App\Support\PhoneNormalizer;
use App\Support\RegistrationApplicationOptions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegistrationApplicationService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly NotificationService $notifications,
    ) {}

    /** @param  array<string, mixed>  $formData
     * @param  array<string, UploadedFile|null>  $files
     */
    public function submit(
        string $type,
        array $formData,
        array $files = [],
        ?User $user = null,
        ?string $courseName = null,
        ?int $courseId = null,
        ?int $fellowshipId = null,
    ): RegistrationApplication {
        $fields = RegistrationApplicationOptions::fieldsFor($type);

        $contact = [
            'name' => null,
            'email' => null,
            'phone' => null,
        ];

        if ($type === 'fellowship') {
            $contact['name'] = $formData['name'] ?? null;
            $contact['email'] = $formData['email'] ?? null;
            $contact['phone'] = $formData['phone'] ?? null;
        }

        foreach ($fields as $field) {
            $contactKey = $field['contact'] ?? null;

            if ($contactKey && filled($formData[$field['key']] ?? null)) {
                $contact[$contactKey] = $formData[$field['key']];
            }
        }

        if ($type === 'client' && filled($formData['name'] ?? null)) {
            $contact['name'] = $formData['name'];
        }

        if ($type === 'company') {
            $contact['name'] = $formData['company_name'] ?? $formData['responsible_name'] ?? 'شركة';
        }

        if (in_array($type, ['instructor', 'marketer', 'employee'], true)) {
            $contact['name'] = trim(($formData['f_name'] ?? '').' '.($formData['l_name'] ?? ''));
        }

        $attachments = $this->storeAttachments($type, $files);
        $payload = collect($formData)
            ->except(array_keys($attachments))
            ->all();

        foreach (array_keys($attachments) as $fileKey) {
            unset($payload[$fileKey]);
        }

        $application = RegistrationApplication::query()->create([
            'application_no' => $this->generateNumber(),
            'type' => $type,
            'status' => 'pending',
            'user_id' => $user?->id,
            'applicant_name' => $contact['name'] ?? '—',
            'applicant_email' => strtolower(trim($contact['email'] ?? '')),
            'applicant_phone' => filled($contact['phone'] ?? null)
                ? PhoneNormalizer::toE164((string) $contact['phone'])
                : null,
            'approved_role' => RegistrationApplicationOptions::approvedRoleForType($type),
            'course_name' => $courseName,
            'course_id' => $courseId,
            'fellowship_id' => $fellowshipId,
            'payload' => $payload,
            'attachments' => $attachments,
            'submitted_at' => now(),
        ]);

        $this->audit->log(
            action: 'registration_application.submitted',
            descriptionAr: 'تقديم طلب تسجيل '.$application->application_no.' ('.$application->typeLabel().')',
            group: 'applications',
            actor: $user,
            subject: $application,
            subjectLabel: $application->application_no,
        );

        $this->notifyAdminsNewApplication($application);

        if ($user) {
            $this->notifications->send(
                $user,
                'application.submitted',
                'تم استلام طلبك',
                'تم استلام طلب «'.$application->typeLabel().'» برقم '.$application->application_no.'.',
                route('apply.track', ['locale' => app()->getLocale(), 'application' => $application->application_no]),
                'fa-clipboard-check',
                $application,
            );
        }

        return $application;
    }

    public function markUnderReview(RegistrationApplication $application, User $reviewer): void
    {
        if (! $application->canReview()) {
            return;
        }

        $application->update([
            'status' => 'under_review',
            'reviewer_id' => $reviewer->id,
        ]);

        $this->audit->log(
            action: 'registration_application.under_review',
            descriptionAr: 'طلب '.$application->application_no.' — قيد المراجعة',
            group: 'applications',
            actor: $reviewer,
            subject: $application,
            subjectLabel: $application->application_no,
        );
        $this->notifyApplicant($application, 'under_review');
    }

    public function approve(RegistrationApplication $application, User $reviewer, ?string $notes = null): void
    {
        if (! $application->canReview()) {
            return;
        }

        DB::transaction(function () use ($application, $reviewer, $notes) {
            $application->update([
                'status' => 'approved',
                'reviewer_id' => $reviewer->id,
                'reviewed_at' => now(),
                'admin_notes' => $notes ?: $application->admin_notes,
            ]);

            $this->applyApprovedRole($application);
        });

        $this->audit->log('registration_application.approved', $application, user: $reviewer);
        $this->notifyApplicant($application, 'approved');
    }

    public function reject(RegistrationApplication $application, User $reviewer, ?string $notes = null): void
    {
        if (! $application->canReview()) {
            return;
        }

        $application->update([
            'status' => 'rejected',
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);

        $this->audit->log(
            action: 'registration_application.rejected',
            descriptionAr: 'رفض طلب '.$application->application_no,
            group: 'applications',
            actor: $reviewer,
            subject: $application,
            subjectLabel: $application->application_no,
        );
        $this->notifyApplicant($application, 'rejected');
    }

    protected function applyApprovedRole(RegistrationApplication $application): void
    {
        $role = $application->approved_role;

        if (! $role) {
            return;
        }

        $user = $application->user;

        if (! $user && filled($application->applicant_email)) {
            $user = User::query()->where('email', $application->applicant_email)->first();
        }

        if ($role === 'instructor') {
            $user = $this->provisionInstructorAccount($application, $user);

            if ($user && ! $application->user_id) {
                $application->update(['user_id' => $user->id]);
            }

            return;
        }

        if (! $user) {
            return;
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($role === 'student' && $user->role !== 'instructor') {
            $user->update(['role' => $role]);
        }

        if (! $application->user_id) {
            $application->update(['user_id' => $user->id]);
        }
    }

    /**
     * Create / update the instructor portal user and AcademicStaff record on approval.
     */
    protected function provisionInstructorAccount(RegistrationApplication $application, ?User $user): ?User
    {
        $email = strtolower(trim((string) $application->applicant_email));

        if ($email === '') {
            return $user;
        }

        $payload = $application->payload ?? [];
        $nameAr = trim((string) $application->applicant_name) ?: 'مدرب';
        $nameEn = trim(($payload['f_name'] ?? '').' '.($payload['l_name'] ?? '')) ?: $nameAr;
        $specialty = filled($payload['specialization'] ?? null)
            ? (string) $payload['specialization']
            : null;
        $gender = match ($payload['gender'] ?? null) {
            'male' => 'ذكر',
            'female' => 'أنثى',
            default => null,
        };
        $isNewUser = false;

        if (! $user) {
            $user = User::query()->create([
                'name' => $nameEn,
                'name_ar' => $nameAr,
                'email' => $email,
                'phone' => $application->applicant_phone,
                'national_id' => filled($payload['ssn'] ?? null) ? (string) $payload['ssn'] : null,
                'password' => Str::password(32),
                'role' => 'instructor',
                'status' => 'active',
                'locale' => 'ar',
            ]);
            $isNewUser = true;
        } elseif ($user->role !== 'admin') {
            $user->fill([
                'name' => $user->name ?: $nameEn,
                'name_ar' => $user->name_ar ?: $nameAr,
                'phone' => $user->phone ?: $application->applicant_phone,
                'national_id' => $user->national_id ?: (filled($payload['ssn'] ?? null) ? (string) $payload['ssn'] : null),
                'role' => 'instructor',
                'status' => $user->status === 'suspended' ? 'active' : $user->status,
            ]);
            $user->save();
        }

        $staff = AcademicStaff::query()->firstOrNew(['user_id' => $user->id]);

        if (! $staff->exists) {
            $staff->fill([
                'name_ar' => $nameAr,
                'name_en' => $nameEn !== $nameAr ? $nameEn : null,
                'role' => 'instructor',
                'permission_preset' => 'instructor.trainer',
                'specialty' => $specialty,
                'gender' => $gender && array_key_exists($gender, AcademicStaffOptions::genders()) ? $gender : null,
                'courses_count' => 0,
                'hours_per_week' => 0,
                'compensation_total' => 0,
                'status' => 'active',
            ]);
            $staff->user_id = $user->id;
            $staff->save();
        } else {
            $staff->fill(array_filter([
                'name_ar' => $staff->name_ar ?: $nameAr,
                'specialty' => $staff->specialty ?: $specialty,
                'gender' => $staff->gender ?: $gender,
                'permission_preset' => $staff->permission_preset ?: 'instructor.trainer',
                'status' => $staff->status === 'inactive' ? 'active' : $staff->status,
            ], fn ($value) => $value !== null && $value !== ''));
            $staff->save();
        }

        if (AccessControl::available()) {
            $bundleKeys = array_keys(\App\Support\InstructorPermissions::presetLabels());
            $bundleRoleIds = AccessRole::query()->whereIn('key', $bundleKeys)->pluck('id');
            $selectedBundleId = AccessRole::query()->where('key', $staff->permission_preset ?: 'instructor.trainer')->value('id');
            $preservedRoleIds = $user->accessRoles()
                ->whereNotIn('access_roles.id', $bundleRoleIds)
                ->pluck('access_roles.id')
                ->all();

            AccessControl::syncUserRoles(
                $user,
                array_values(array_filter([...$preservedRoleIds, $selectedBundleId])),
            );
        }

        if ($isNewUser) {
            Password::broker()->sendResetLink(['email' => $user->email]);
        }

        return $user;
    }

    /** @param  array<string, UploadedFile|null>  $files */
    protected function storeAttachments(string $type, array $files): array
    {
        $stored = [];

        foreach ($files as $key => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store("applications/{$type}/".date('Y/m'), 'local');
            $stored[$key] = [
                'path' => $path,
                'original' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $stored;
    }

    protected function generateNumber(): string
    {
        do {
            $no = 'APP-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (RegistrationApplication::query()->where('application_no', $no)->exists());

        return $no;
    }

    protected function notifyAdminsNewApplication(RegistrationApplication $application): void
    {
        $admins = User::query()->where('role', 'admin')->get();

        foreach ($admins as $admin) {
            if (! $admin->canAdmin('applications.view')) {
                continue;
            }

            $this->notifications->send(
                $admin,
                'application.new',
                'طلب تسجيل جديد',
                $application->typeLabel().' — '.$application->applicant_name.' ('.$application->application_no.')',
                route('admin.applications.show', ['application' => $application->id]),
                'fa-inbox',
                $application,
            );
        }
    }

    protected function notifyApplicant(RegistrationApplication $application, string $event): void
    {
        $user = $application->user;

        if (! $user && filled($application->applicant_email)) {
            $user = User::query()->where('email', $application->applicant_email)->first();
        }

        if (! $user) {
            return;
        }

        [$title, $body] = match ($event) {
            'under_review' => ['طلبك قيد المراجعة', 'جاري مراجعة طلب «'.$application->typeLabel().'» رقم '.$application->application_no.'.'],
            'approved' => ['تم قبول طلبك', 'تهانينا! تم قبول طلب «'.$application->typeLabel().'» رقم '.$application->application_no.'.'],
            'rejected' => ['تم رفض طلبك', 'نعتذر، لم يُقبل طلب «'.$application->typeLabel().'» رقم '.$application->application_no.'.'],
            default => ['تحديث على طلبك', 'تم تحديث حالة طلبك '.$application->application_no.'.'],
        };

        $this->notifications->send(
            $user,
            'application.'.$event,
            $title,
            $body,
            route('apply.track', ['locale' => app()->getLocale(), 'application' => $application->application_no]),
            'fa-clipboard-list',
            $application,
        );
    }

    public function attachmentDownloadPath(RegistrationApplication $application, string $key): ?string
    {
        $meta = data_get($application->attachments, $key);

        if (! is_array($meta) || empty($meta['path'])) {
            return null;
        }

        $path = $meta['path'];

        return Storage::disk('local')->exists($path) ? $path : null;
    }
}
