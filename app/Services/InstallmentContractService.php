<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\InstallmentContract;
use App\Models\InstallmentPlanTemplate;
use App\Models\InstallmentSchedule;
use App\Models\Order;
use App\Models\User;
use App\Support\InstallmentSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InstallmentContractService
{
    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    /** @return Collection<int, InstallmentContract> */
    public function forUser(User $user): Collection
    {
        $this->refreshOverdueStatuses();

        return InstallmentContract::query()
            ->with(['schedules', 'student', 'program', 'batch', 'template'])
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function createFromTemplate(
        User $studentUser,
        InstallmentPlanTemplate $template,
        float $totalAmount,
        ?AcademicStudent $academicStudent,
        ?Carbon $startsAt,
        ?User $creator = null,
        ?string $title = null,
        ?string $adminNotes = null,
    ): InstallmentContract {
        if (! $template->is_active) {
            throw ValidationException::withMessages(['template' => 'خطة التقسيط غير نشطة.']);
        }

        if (abs($template->totalPercent() - 100) > 0.01) {
            throw ValidationException::withMessages(['template' => 'نسب أقساط الخطة لا تساوي 100%.']);
        }

        $items = $template->items()->orderBy('sequence')->get();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['template' => 'الخطة لا تحتوي على أقساط.']);
        }

        $startsAt ??= now()->startOfDay();

        $academicStudent?->loadMissing('batch');

        return DB::transaction(function () use (
            $studentUser,
            $template,
            $totalAmount,
            $academicStudent,
            $startsAt,
            $creator,
            $title,
            $adminNotes,
        ) {
            $requiresSignature = InstallmentSettings::requiresSignature();
            $initialStatus = $requiresSignature ? 'pending_signature' : 'active';

            $contract = InstallmentContract::query()->create([
                'contract_no' => $this->generateContractNo(),
                'user_id' => $studentUser->id,
                'academic_student_id' => $academicStudent?->id,
                'program_id' => $academicStudent?->batch?->program_id,
                'batch_id' => $academicStudent?->batch_id,
                'template_id' => $template->id,
                'title' => $title ?: ($academicStudent?->batch?->program?->name_ar ?? $template->name_ar),
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'remaining_balance' => $totalAmount,
                'status' => $initialStatus,
                'starts_at' => $startsAt,
                'signed_at' => now(),
                'requires_student_signature' => $requiresSignature,
                'admin_notes' => $adminNotes,
                'created_by' => $creator?->id,
            ]);

            $this->generateSchedules($contract, $template, $startsAt);

            $this->audit->log(
                action: 'installment_contract.created',
                descriptionAr: 'إنشاء عقد تقسيط '.$contract->contract_no,
                group: 'finance',
                actor: $creator ?? $studentUser,
                subject: $contract,
                subjectLabel: $contract->contract_no,
                newValues: [
                    'student' => $studentUser->displayName(),
                    'total' => $totalAmount,
                    'template' => $template->name_ar,
                ],
            );

            return $contract->fresh(['schedules']);
        });
    }

    public function generateSchedules(
        InstallmentContract $contract,
        InstallmentPlanTemplate $template,
        Carbon $startsAt,
    ): void {
        $items = $template->items()->orderBy('sequence')->get();
        $total = (float) $contract->total_amount;
        $allocated = 0.0;
        $lastIndex = $items->count() - 1;

        foreach ($items as $index => $item) {
            $amount = $index === $lastIndex
                ? round($total - $allocated, 2)
                : round($total * ((float) $item->percent / 100), 2);

            $allocated += $amount;

            $dueDate = match ($item->due_rule) {
                'at_enrollment' => $startsAt->copy(),
                'month_offset' => $startsAt->copy()->addMonths((int) ($item->month_offset ?? 0)),
                default => $startsAt->copy()->addMonths(max(0, $item->sequence - 1)),
            };

            InstallmentSchedule::query()->create([
                'contract_id' => $contract->id,
                'sequence' => $item->sequence,
                'label' => $item->displayLabel(),
                'amount' => $amount,
                'percent' => $item->percent,
                'due_date' => $dueDate->toDateString(),
                'status' => 'pending',
            ]);
        }
    }

    public function refreshContractBalances(InstallmentContract $contract): InstallmentContract
    {
        $paid = (float) InstallmentSchedule::query()
            ->where('contract_id', $contract->id)
            ->where('status', 'paid')
            ->sum('amount');
        $waived = (float) InstallmentSchedule::query()
            ->where('contract_id', $contract->id)
            ->where('status', 'waived')
            ->sum('amount');

        $remaining = max(0, round((float) $contract->total_amount - $paid - $waived, 2));

        $pendingCount = InstallmentSchedule::query()
            ->where('contract_id', $contract->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->count();

        $status = $contract->status;

        if ($remaining <= 0 && $pendingCount === 0) {
            $status = 'completed';
        } elseif ($contract->status === 'completed' && $remaining > 0) {
            $status = 'active';
        }

        $contract->update([
            'paid_amount' => $paid,
            'remaining_balance' => $remaining,
            'status' => $status,
            'completed_at' => $status === 'completed' ? ($contract->completed_at ?? now()) : null,
        ]);

        return $contract->fresh();
    }

    public function refreshOverdueStatuses(): void
    {
        $graceCutoff = now()->subDays(InstallmentSettings::graceDays())->toDateString();

        InstallmentSchedule::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', $graceCutoff)
            ->update(['status' => 'overdue']);
    }

    public function signByStudent(
        InstallmentContract $contract,
        User $student,
        string $signatureDataUrl,
        string $signerName,
        ?string $ip = null,
    ): InstallmentContract {
        abort_unless($contract->user_id === $student->id, 403);
        abort_unless($contract->needsStudentSignature(), 422);

        if (! preg_match('/^data:image\/png;base64,/', $signatureDataUrl)) {
            throw ValidationException::withMessages(['signature' => 'صيغة التوقيع غير صالحة.']);
        }

        $binary = base64_decode(substr($signatureDataUrl, strpos($signatureDataUrl, ',') + 1), true);

        if ($binary === false || strlen($binary) < 100) {
            throw ValidationException::withMessages(['signature' => 'التوقيع فارغ أو غير صالح.']);
        }

        $path = "contracts/signatures/{$contract->id}/".now()->format('YmdHis').'.png';
        Storage::disk('public')->put($path, $binary);

        $contract->update([
            'student_signature_path' => $path,
            'student_signature_name' => $signerName,
            'student_signature_ip' => $ip,
            'student_signed_at' => now(),
            'status' => $contract->status === 'pending_signature' ? 'active' : $contract->status,
        ]);

        $this->audit->log(
            action: 'installment_contract.student_signed',
            descriptionAr: 'توقيع الطالب على عقد '.$contract->contract_no,
            group: 'finance',
            actor: $student,
            subject: $contract,
            subjectLabel: $contract->contract_no,
        );

        return $contract->fresh();
    }

    public function waiveSchedule(InstallmentSchedule $schedule, User $admin, ?string $notes = null): InstallmentSchedule
    {
        abort_unless($schedule->isPayable() || $schedule->status === 'overdue', 422);

        DB::transaction(function () use ($schedule, $admin, $notes) {
            $schedule->update([
                'status' => 'waived',
                'waived_by' => $admin->id,
                'waived_at' => now(),
                'admin_notes' => $notes,
            ]);

            Order::query()
                ->where('installment_schedule_id', $schedule->id)
                ->where('status', 'pending_payment')
                ->update(['status' => 'cancelled']);
        });

        $this->refreshContractBalances($schedule->contract);

        $this->audit->log(
            action: 'installment_schedule.waived',
            descriptionAr: 'إعفاء قسط #'.$schedule->sequence.' — '.$schedule->contract?->contract_no,
            group: 'finance',
            actor: $admin,
            subject: $schedule,
            subjectLabel: $schedule->contract?->contract_no,
        );

        return $schedule->fresh();
    }

    public function updateScheduleDetails(
        InstallmentSchedule $schedule,
        User $admin,
        string $dueDate,
        float $lateFeeAmount,
        ?string $notes = null,
    ): InstallmentSchedule {
        if (! $schedule->isPayable()) {
            throw ValidationException::withMessages([
                'schedule' => 'لا يمكن تعديل قسط مدفوع أو معفى أو ملغى.',
            ]);
        }

        return DB::transaction(function () use ($schedule, $admin, $dueDate, $lateFeeAmount, $notes) {
            $lockedSchedule = InstallmentSchedule::query()->lockForUpdate()->findOrFail($schedule->id);
            $oldValues = $lockedSchedule->only(['due_date', 'late_fee_amount', 'admin_notes', 'status']);
            $date = Carbon::parse($dueDate)->startOfDay();
            $status = $date->lt(today()) ? 'overdue' : 'pending';

            $lockedSchedule->update([
                'due_date' => $date->toDateString(),
                'late_fee_amount' => max(0, round($lateFeeAmount, 2)),
                'admin_notes' => filled($notes) ? trim((string) $notes) : null,
                'status' => $status,
            ]);

            Order::query()
                ->where('installment_schedule_id', $lockedSchedule->id)
                ->where('status', 'pending_payment')
                ->update(['total' => $lockedSchedule->fresh()->totalDue()]);

            $this->audit->log(
                action: 'installment_schedule.updated',
                descriptionAr: 'تعديل بيانات القسط #'.$lockedSchedule->sequence,
                group: 'finance',
                actor: $admin,
                subject: $lockedSchedule,
                subjectLabel: $lockedSchedule->contract?->contract_no,
                oldValues: $oldValues,
                newValues: $lockedSchedule->fresh()->only(['due_date', 'late_fee_amount', 'admin_notes', 'status']),
            );

            return $lockedSchedule->fresh();
        });
    }

    public function cancelContract(InstallmentContract $contract, User $admin, ?string $notes = null): InstallmentContract
    {
        if ($contract->schedules()->where('status', 'paid')->exists()) {
            throw ValidationException::withMessages([
                'contract' => 'لا يمكن إلغاء عقد يحتوي أقساطاً مدفوعة قبل معالجة الاسترداد المالي.',
            ]);
        }

        DB::transaction(function () use ($contract, $notes) {
            $contract->update([
                'status' => 'cancelled',
                'admin_notes' => trim(($contract->admin_notes ?? '')."\n".$notes),
            ]);

            InstallmentSchedule::query()
                ->where('contract_id', $contract->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->update(['status' => 'cancelled']);

            Order::query()
                ->whereIn('installment_schedule_id', $contract->schedules()->pluck('id'))
                ->where('status', 'pending_payment')
                ->update(['status' => 'cancelled']);

            $this->audit->log(
                action: 'installment_contract.cancelled',
                descriptionAr: 'إلغاء عقد تقسيط '.$contract->contract_no,
                group: 'finance',
                actor: $admin,
                subject: $contract,
                subjectLabel: $contract->contract_no,
            );
        });

        return $contract->fresh();
    }

    protected function generateContractNo(): string
    {
        do {
            $no = 'IC-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (InstallmentContract::query()->where('contract_no', $no)->exists());

        return $no;
    }
}
