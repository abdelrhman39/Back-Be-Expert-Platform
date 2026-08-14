<?php

namespace App\Services;

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Models\AcademicRequest;
use App\Models\AcademicStudent;
use App\Models\User;
use App\Support\AcademicRequestOptions;
use App\Support\AcademicStudentOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicRequestService
{
    public function __construct(
        protected AuditLogService $audit,
    ) {}

    public function resolveStudent(User $user): ?AcademicStudent
    {
        return AcademicStudent::query()
            ->with(['batch.program', 'section'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);

                if (filled($user->email)) {
                    $q->orWhere('email', $user->email);
                }

                if (filled($user->national_id)) {
                    $q->orWhere('national_id', $user->national_id);
                }
            })
            ->first();
    }

    /** @return Collection<int, AcademicRequest> */
    public function forUser(User $user): Collection
    {
        $student = $this->resolveStudent($user);

        if (! $student) {
            return collect();
        }

        return AcademicRequest::query()
            ->where('student_id', $student->id)
            ->latest('submitted_at')
            ->get();
    }

    public function openForStudent(AcademicStudent $student, string $type): ?AcademicRequest
    {
        return AcademicRequest::query()
            ->where('student_id', $student->id)
            ->where('type', $type)
            ->whereIn('status', ['pending', 'processing'])
            ->latest('submitted_at')
            ->first();
    }

    public function submit(User $user, string $type, array $data): AcademicRequest
    {
        if (! array_key_exists($type, AcademicRequestOptions::types())) {
            throw ValidationException::withMessages(['type' => 'نوع الطلب غير صالح.']);
        }

        $student = $this->resolveStudent($user);

        if (! $student) {
            throw ValidationException::withMessages([
                'student' => 'لا يوجد سجل أكاديمي مرتبط بحسابك. تواصل مع شؤون الطلاب.',
            ]);
        }

        if ($this->openForStudent($student, $type)) {
            throw ValidationException::withMessages([
                'type' => 'لديك طلب من نفس النوع قيد المراجعة بالفعل.',
            ]);
        }

        $program = $student->batch?->program;
        $payload = [];
        $semesterKey = null;
        $semesterLabel = null;
        $reason = $data['reason'] ?? null;

        if ($type === 'deferral') {
            $semester = collect(AcademicRequestOptions::semesters())->firstWhere('key', $data['semester_key'] ?? '');
            $target = collect(AcademicRequestOptions::semesters())->firstWhere('key', $data['target_semester_key'] ?? '');
            $semesterKey = $semester['key'] ?? null;
            $semesterLabel = $semester['label'] ?? null;
            $payload['target_semester'] = $target['label'] ?? ($data['target_semester_key'] ?? null);
        } elseif ($type === 'semester_excuse') {
            $semester = collect(AcademicRequestOptions::semesters())->firstWhere('key', $data['semester_key'] ?? '');
            $semesterKey = $semester['key'] ?? null;
            $semesterLabel = $semester['label'] ?? null;
            $payload['program_full'] = ($program?->name_ar ?? '').($program?->duration_label ? ' ('.$program->duration_label.')' : '');
            $payload['added_by'] = $student->name_ar;
        } elseif ($type === 'program_change') {
            $newProgram = AcademicProgram::query()->findOrFail($data['new_program_id'] ?? 0);
            $payload = [
                'current_program' => $program?->name_ar,
                'current_program_full' => ($program?->name_ar ?? '').($program?->duration_label ? ' ('.$program->duration_label.')' : ''),
                'current_duration' => $program?->duration_label,
                'new_program' => $newProgram->name_ar,
                'new_program_full' => $newProgram->name_ar.($newProgram->duration_label ? ' ('.$newProgram->duration_label.')' : ''),
                'new_duration' => $newProgram->duration_label,
                'new_program_id' => $newProgram->id,
            ];
        } elseif ($type === 'withdrawal') {
            $payload['payment_method'] = $data['payment_method'] ?? 'دفع إلكتروني';
        }

        $request = AcademicRequest::query()->create([
            'request_no' => AcademicRequestOptions::generateRequestNo(),
            'type' => $type,
            'student_id' => $student->id,
            'student_name' => $student->name_ar,
            'student_national_id' => $student->national_id,
            'program_id' => $program?->id,
            'program_name' => $program?->name_ar,
            'semester_key' => $semesterKey,
            'semester_label' => $semesterLabel,
            'status' => 'pending',
            'review_status' => 'pending',
            'reason' => $reason,
            'payload' => $payload ?: null,
            'submitted_at' => now(),
        ]);

        $this->audit->log(
            action: 'academic_request.submitted',
            descriptionAr: 'تقديم '.AcademicRequestOptions::studentSingularLabel($type).' — '.$request->request_no,
            group: 'requests',
            actor: $user,
            subject: $request,
            subjectLabel: $request->request_no,
            newValues: [
                'type' => $type,
                'program' => $program?->name_ar,
                'reason' => $reason,
            ],
        );

        return $request;
    }

    /**
     * Approve / reject / start processing with academic side-effects.
     *
     * @return array{request: AcademicRequest, effects: array<int, string>}
     */
    public function decide(AcademicRequest $request, string $action, User $reviewer, ?string $notes = null): array
    {
        if (! in_array($action, ['approved', 'rejected', 'processing'], true)) {
            throw ValidationException::withMessages(['action' => 'إجراء غير صالح.']);
        }

        if ($action !== 'processing' && ! $request->canReview()) {
            throw ValidationException::withMessages(['status' => 'لا يمكن مراجعة هذا الطلب في حالته الحالية.']);
        }

        $effects = [];

        DB::transaction(function () use ($request, $action, $reviewer, $notes, &$effects) {
            $oldStatus = $request->status;

            if ($action === 'processing') {
                if ($request->status === 'pending') {
                    $request->update([
                        'status' => 'processing',
                        'reviewer_id' => $reviewer->id,
                    ]);
                    $effects[] = 'تم تحويل الطلب إلى «جاري العمل عليه».';
                }
            } elseif ($action === 'approved') {
                $request->update([
                    'status' => 'approved',
                    'review_status' => 'reviewed',
                    'reviewer_id' => $reviewer->id,
                    'reviewed_at' => now(),
                    'admin_notes' => $notes ?: $request->admin_notes,
                ]);
                $effects = array_merge($effects, $this->applyApprovalEffects($request->fresh(['student.batch.program'])));
            } else {
                $request->update([
                    'status' => 'rejected',
                    'review_status' => 'reviewed',
                    'reviewer_id' => $reviewer->id,
                    'reviewed_at' => now(),
                    'admin_notes' => $notes,
                ]);
                $effects[] = 'تم رفض الطلب دون تغيير الحالة الأكاديمية للطالب.';
            }

            $request->refresh();

            $this->audit->log(
                action: 'academic_request.'.$action,
                descriptionAr: AcademicRequestOptions::reviewActionLabel($action).' — '.$request->request_no,
                group: 'requests',
                actor: $reviewer,
                subject: $request,
                subjectLabel: $request->request_no,
                oldValues: ['status' => $oldStatus],
                newValues: array_filter([
                    'status' => $request->status,
                    'admin_notes' => $notes,
                    'effects' => $effects,
                ]),
            );
        });

        if (in_array($action, ['processing', 'approved', 'rejected'], true)) {
            app(NotificationService::class)->notifyAcademicRequestStatus($request->fresh(['student.user']), $action);
        }

        return [
            'request' => $request->fresh(['student', 'program', 'reviewer']),
            'effects' => $effects,
        ];
    }

    /** @return array<int, string> */
    public function applyApprovalEffects(AcademicRequest $request): array
    {
        $effects = [];
        $student = $request->student;

        if (! $student) {
            return ['لم يُعثر على سجل طالب مرتبط — تم اعتماد الطلب فقط.'];
        }

        $payload = $request->payload ?? [];

        $effects = match ($request->type) {
            'semester_excuse' => $this->markStudentDeferred(
                $student,
                'معتذر عن الفصل'.($request->semester_label ? ' — '.$request->semester_label : ''),
                $request,
                'اعتذار عن الفصل',
            ),
            'deferral' => $this->markStudentDeferred(
                $student,
                'مؤجل'.($request->payloadValue('target_semester') ? ' إلى '.$request->payloadValue('target_semester') : ''),
                $request,
                'تأجيل دراسي',
            ),
            'withdrawal' => $this->markStudentWithdrawn($student, $request),
            'program_change' => $this->applyProgramChange($student, $request, $payload),
            default => ['تم اعتماد الطلب.'],
        };

        $payload['decision_effects'] = $effects;
        $payload['decision_applied_at'] = now()->toIso8601String();
        $request->update(['payload' => $payload]);

        return $effects;
    }

    /** @return array<int, string> */
    protected function markStudentDeferred(AcademicStudent $student, string $studyStatus, AcademicRequest $request, string $label): array
    {
        $old = $student->academic_status;

        $student->update([
            'academic_status' => 'deferred',
            'study_status' => $studyStatus,
        ]);

        return [
            'تم تحديث حالة الطالب إلى «'.AcademicStudentOptions::academicStatusLabel('deferred').'» ('.$label.').',
            $old && $old !== 'deferred'
                ? 'الحالة السابقة: '.AcademicStudentOptions::academicStatusLabel($old).'.'
                : 'رقم الطلب: '.$request->request_no.'.',
        ];
    }

    /** @return array<int, string> */
    protected function markStudentWithdrawn(AcademicStudent $student, AcademicRequest $request): array
    {
        $old = $student->academic_status;

        $student->update([
            'academic_status' => 'withdrawn',
            'study_status' => AcademicStudentOptions::academicStatusLabel('withdrawn'),
            'login_allowed' => false,
        ]);

        return [
            'تم تحديث حالة الطالب إلى «منسحب» وإيقاف الدخول الأكاديمي.',
            $old && $old !== 'withdrawn'
                ? 'الحالة السابقة: '.AcademicStudentOptions::academicStatusLabel($old).'.'
                : 'رقم الطلب: '.$request->request_no.'.',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    protected function applyProgramChange(AcademicStudent $student, AcademicRequest $request, array $payload): array
    {
        $newProgramId = (int) ($payload['new_program_id'] ?? 0);

        if ($newProgramId <= 0) {
            return ['تم اعتماد طلب تغيير البرنامج دون تحديد برنامج جديد في البيانات.'];
        }

        $newProgram = AcademicProgram::query()->find($newProgramId);

        if (! $newProgram) {
            return ['البرنامج الجديد غير موجود — تم اعتماد الطلب للمراجعة اليدوية.'];
        }

        $batch = AcademicBatch::query()
            ->where('program_id', $newProgram->id)
            ->orderByDesc('id')
            ->first();

        $oldProgram = $student->batch?->program?->name_ar ?? $request->program_name;
        $effects = [];

        if ($batch) {
            $student->update([
                'batch_id' => $batch->id,
                'section_id' => null,
                'study_status' => 'تم النقل إلى: '.$newProgram->name_ar,
            ]);
            $effects[] = 'تم نقل الطالب من «'.($oldProgram ?: '—').'» إلى برنامج «'.$newProgram->name_ar.'».';
            $effects[] = 'أُسند إلى الدفعة #'.$batch->id.' — يلزم مراجعة الشعبة يدوياً إن لزم.';
        } else {
            $student->update([
                'study_status' => 'بانتظار إسناد دفعة: '.$newProgram->name_ar,
            ]);
            $effects[] = 'تم اعتماد النقل إلى «'.$newProgram->name_ar.'» لكن لا توجد دفعة لهذا البرنامج — أكمل الإسناد من شاشة الدفعات.';
        }

        return $effects;
    }

    public function logReview(AcademicRequest $request, string $action, User $reviewer, ?string $notes = null, ?string $oldStatus = null): void
    {
        $this->audit->log(
            action: 'academic_request.'.$action,
            descriptionAr: AcademicRequestOptions::reviewActionLabel($action).' — '.$request->request_no,
            group: 'requests',
            actor: $reviewer,
            subject: $request,
            subjectLabel: $request->request_no,
            oldValues: ['status' => $oldStatus ?? $request->status],
            newValues: array_filter([
                'status' => $request->status,
                'admin_notes' => $notes,
            ]),
        );

        if (in_array($action, ['processing', 'approved', 'rejected'], true)) {
            app(NotificationService::class)->notifyAcademicRequestStatus($request->fresh(), $action);
        }
    }
}
