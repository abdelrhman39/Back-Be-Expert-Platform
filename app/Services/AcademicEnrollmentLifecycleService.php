<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\CrmActivity;
use App\Models\CrmContact;
use App\Models\InstallmentContract;
use App\Models\InstallmentSchedule;
use App\Models\User;
use App\Support\AcademicStudentOptions;
use App\Support\CrmOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AcademicEnrollmentLifecycleService
{
    public function __construct(
        private readonly CrmContactSyncService $crmSync,
        private readonly InstallmentPaymentService $payments,
        private readonly AuditLogService $audit,
    ) {}

    /**
     * After academic registration: keep student pending and push CRM to awaiting payment.
     */
    public function markRegistrationAwaitingPayment(User $user, ?User $actor = null): ?CrmContact
    {
        if (! Schema::hasTable('crm_contacts')) {
            return null;
        }

        $user->loadMissing('academicStudent.batch.program');
        $student = $user->academicStudent;

        $contact = $this->crmSync->syncUser($user, $actor, true);

        $awaiting = $this->awaitingPaymentStatusKey();
        $payload = [
            'program_id' => $student?->batch?->program_id ?: $contact->program_id,
            'last_activity_at' => now(),
        ];

        if (! CrmOptions::isWon($contact->status) && $contact->status !== 'paid') {
            $payload['status'] = $awaiting;
            $payload['paid_at'] = null;
        }

        $oldStatus = $contact->status;
        $contact->update($payload);

        if (($payload['status'] ?? $oldStatus) !== $oldStatus) {
            CrmActivity::query()->create([
                'contact_id' => $contact->id,
                'user_id' => $actor?->id,
                'type' => 'system',
                'subject' => 'تسجيل أكاديمي بانتظار السداد',
                'content' => sprintf(
                    'سجّل الطالب في «%s» وما زال بانتظار إكمال السداد الإلكتروني أو تأكيد التحويل البنكي.',
                    $student?->batch?->program?->name_ar
                        ?: $student?->batch?->name
                        ?: 'البرنامج'
                ),
                'completed_at' => now(),
                'metadata' => [
                    'from' => $oldStatus,
                    'to' => $payload['status'] ?? $oldStatus,
                    'academic_student_id' => $student?->id,
                    'batch_id' => $student?->batch_id,
                ],
            ]);
        }

        return $contact->fresh();
    }

    /**
     * CRM status «تم السداد»: activate student in program and confirm first installment.
     */
    public function activateFromCrmPaid(CrmContact $contact, User $actor): void
    {
        DB::transaction(function () use ($contact, $actor): void {
            $contact->loadMissing('user.academicStudent');
            $user = $contact->user;

            if (! $user) {
                CrmActivity::query()->create([
                    'contact_id' => $contact->id,
                    'user_id' => $actor->id,
                    'type' => 'system',
                    'subject' => 'تعذر تفعيل البرنامج تلقائياً',
                    'content' => 'لا يوجد حساب مستخدم مرتبط بهذا العميل لتفعيله في البرنامج.',
                    'completed_at' => now(),
                ]);

                return;
            }

            $student = $this->activateStudentRecord($user->academicStudent);

            $schedule = $this->firstUnpaidScheduleFor($user, $student);

            if ($schedule) {
                if ($schedule->contract && $schedule->contract->status === 'pending_signature') {
                    $schedule->contract->update([
                        'status' => 'active',
                        'student_signed_at' => $schedule->contract->student_signed_at ?: now(),
                        'admin_notes' => trim(($schedule->contract->admin_notes ? $schedule->contract->admin_notes."\n" : '').'تم تفعيل العقد بعد تأكيد السداد من CRM.'),
                    ]);
                }

                if ($schedule->fresh()->isPayable()) {
                    $this->payments->recordManualPayment(
                        schedule: $schedule->fresh(['contract']),
                        admin: $actor,
                        notes: 'تأكيد سداد عبر CRM مع إيصال تحويل/سداد',
                        gatewayRef: 'CRM-PAID-'.$contact->id,
                    );
                } else {
                    $this->activateStudentRecord($student?->fresh() ?? $user->academicStudent);
                }
            }

            CrmActivity::query()->create([
                'contact_id' => $contact->id,
                'user_id' => $actor->id,
                'type' => 'system',
                'subject' => 'تفعيل البرنامج بعد تأكيد السداد',
                'content' => $student
                    ? 'تم تحديث حالة الطالب إلى «مستمر دراسياً» وإضافته للبرنامج'.($schedule ? ' مع تسجيل سداد القسط الأول.' : '.')
                    : 'تم تأكيد السداد، لكن لا يوجد سجل أكاديمي مرتبط بالحساب.',
                'completed_at' => now(),
                'metadata' => [
                    'academic_student_id' => $student?->id,
                    'schedule_id' => $schedule?->id,
                ],
            ]);
        });
    }

    /**
     * After online/gateway payment of the first installment: mark CRM as paid.
     */
    public function syncCrmPaidFromInstallment(InstallmentContract $contract, InstallmentSchedule $schedule, ?User $actor = null): void
    {
        if ($schedule->sequence !== 1 || ! Schema::hasTable('crm_contacts')) {
            return;
        }

        $user = $contract->user ?? User::query()->find($contract->user_id);

        if (! $user) {
            return;
        }

        $contact = CrmContact::query()->firstOrNew(['user_id' => $user->id]);
        $isNew = ! $contact->exists;
        $oldStatus = $contact->status;
        $paidKey = $this->paidStatusKey();

        $user->loadMissing('academicStudent.batch');
        $contact->fill([
            'program_id' => $user->academicStudent?->batch?->program_id ?: $contact->program_id ?: $contract->program_id,
            'source' => $contact->source ?: CrmOptions::resolveSourceKey('registration'),
            'source_type' => User::class,
            'source_id' => $user->id,
            'status' => $paidKey,
            'priority' => $contact->priority ?: 'medium',
            'name' => $contact->name ?: $user->displayName(),
            'email' => $contact->email ?: $user->email,
            'phone' => $contact->phone ?: $user->phone,
            'paid_at' => $contact->paid_at ?: now(),
            'converted_at' => $contact->converted_at ?: now(),
            'last_activity_at' => now(),
            'created_by' => $contact->created_by ?: $actor?->id,
        ])->save();

        if ($isNew || $oldStatus !== $paidKey) {
            CrmActivity::query()->create([
                'contact_id' => $contact->id,
                'user_id' => $actor?->id,
                'type' => 'system',
                'subject' => 'تم السداد إلكترونياً',
                'content' => 'تم سداد القسط الأول عبر المنصة وتفعيل الدخول للبرنامج.',
                'completed_at' => now(),
                'metadata' => [
                    'from' => $oldStatus,
                    'to' => $paidKey,
                    'contract_id' => $contract->id,
                    'schedule_id' => $schedule->id,
                ],
            ]);
        }
    }

    /**
     * Leaving «تم السداد» in CRM requires an explicit reason and does not auto-withdraw the student.
     */
    public function assertCrmPaidReversalAllowed(CrmContact $contact, string $newStatus, ?string $reason): void
    {
        if ($contact->status !== 'paid' && ! CrmOptions::isWon($contact->status)) {
            return;
        }

        if ($newStatus === 'paid' || CrmOptions::isWon($newStatus)) {
            return;
        }

        if (! filled(trim((string) $reason))) {
            throw ValidationException::withMessages([
                'lostReason' => 'بعد تأكيد السداد، يجب ذكر سبب واضح عند إرجاع/تغيير مرحلة العميل (مثل انسحاب أو خطأ في التأكيد). لن يُسحب الطالب من البرنامج تلقائياً.',
            ]);
        }
    }

    /**
     * Changing academic status after the student has paid requires a written reason.
     *
     * @return list<string>
     */
    public function sensitivePostPaymentStatuses(): array
    {
        return ['withdrawn', 'deferred', 'suspended', 'pending'];
    }

    public function studentHasConfirmedPayment(AcademicStudent $student): bool
    {
        if ($student->user_id && Schema::hasTable('crm_contacts')) {
            $crmPaid = CrmContact::query()
                ->where('user_id', $student->user_id)
                ->where(function ($query): void {
                    $query->where('status', 'paid')->orWhereNotNull('paid_at');
                })
                ->exists();

            if ($crmPaid) {
                return true;
            }
        }

        return InstallmentSchedule::query()
            ->where('status', 'paid')
            ->where('sequence', 1)
            ->whereHas('contract', function ($query) use ($student): void {
                $query->where('academic_student_id', $student->id)
                    ->orWhere(function ($inner) use ($student): void {
                        if ($student->user_id) {
                            $inner->where('user_id', $student->user_id);
                        }
                    });
            })
            ->exists();
    }

    public function assertAcademicStatusChangeAllowed(
        AcademicStudent $student,
        string $newStatus,
        ?string $reason,
    ): void {
        if ($student->academic_status === $newStatus) {
            return;
        }

        if (! in_array($newStatus, $this->sensitivePostPaymentStatuses(), true)) {
            return;
        }

        if (! $this->studentHasConfirmedPayment($student)) {
            return;
        }

        if (! filled(trim((string) $reason))) {
            throw ValidationException::withMessages([
                'statusChangeReason' => 'هذا الطالب مؤكد السداد. لتغيير حالته إلى «'.AcademicStudentOptions::academicStatusLabel($newStatus).'» يجب إدخال سبب الطلب (انسحاب / تأجيل / إيقاف…)، وسيُسجَّل في سجل التدقيق وCRM.',
            ]);
        }
    }

    public function recordPostPaymentStatusChange(
        AcademicStudent $student,
        string $oldStatus,
        string $newStatus,
        string $reason,
        User $actor,
    ): void {
        $this->audit->log(
            action: 'academic_student.status_change_after_payment',
            descriptionAr: sprintf(
                'طلب تغيير حالة طالب مؤكد السداد: %s ← %s — السبب: %s',
                AcademicStudentOptions::academicStatusLabel($oldStatus),
                AcademicStudentOptions::academicStatusLabel($newStatus),
                $reason
            ),
            group: 'requests',
            actor: $actor,
            subject: $student,
            subjectLabel: $student->name_ar,
            oldValues: ['academic_status' => $oldStatus],
            newValues: ['academic_status' => $newStatus, 'reason' => $reason],
        );

        if ($student->user_id && Schema::hasTable('crm_contacts')) {
            $contact = CrmContact::query()->where('user_id', $student->user_id)->first();

            if ($contact) {
                CrmActivity::query()->create([
                    'contact_id' => $contact->id,
                    'user_id' => $actor->id,
                    'type' => 'note',
                    'subject' => 'طلب تغيير حالة بعد تأكيد السداد',
                    'content' => sprintf(
                        '%s ← %s. السبب: %s',
                        AcademicStudentOptions::academicStatusLabel($oldStatus),
                        AcademicStudentOptions::academicStatusLabel($newStatus),
                        $reason
                    ),
                    'completed_at' => now(),
                    'metadata' => [
                        'academic_student_id' => $student->id,
                        'from' => $oldStatus,
                        'to' => $newStatus,
                    ],
                ]);
                $contact->update(['last_activity_at' => now()]);
            }
        }
    }

    protected function activateStudentRecord(?AcademicStudent $student): ?AcademicStudent
    {
        if (! $student) {
            return null;
        }

        if ($student->academic_status === 'pending' || $student->academic_status === 'suspended') {
            $student->update([
                'academic_status' => 'studying',
                'study_status' => AcademicStudentOptions::academicStatusLabel('studying'),
                'login_allowed' => true,
            ]);
        }

        return $student->fresh();
    }

    protected function firstUnpaidScheduleFor(User $user, ?AcademicStudent $student): ?InstallmentSchedule
    {
        $contractQuery = InstallmentContract::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderByDesc('id');

        if ($student) {
            $contractQuery->where(function ($query) use ($student): void {
                $query->where('academic_student_id', $student->id)
                    ->orWhereNull('academic_student_id');
            });
        }

        $contract = $contractQuery->first();

        if (! $contract) {
            return null;
        }

        return $contract->schedules()
            ->where('status', '!=', 'paid')
            ->orderBy('sequence')
            ->first();
    }

    protected function awaitingPaymentStatusKey(): string
    {
        return array_key_exists('awaiting_payment', CrmOptions::statuses(false))
            ? 'awaiting_payment'
            : CrmOptions::defaultStatusKey();
    }

    protected function paidStatusKey(): string
    {
        return array_key_exists('paid', CrmOptions::statuses(false))
            ? 'paid'
            : (CrmOptions::wonStatusKeys()[0] ?? 'paid');
    }
}
