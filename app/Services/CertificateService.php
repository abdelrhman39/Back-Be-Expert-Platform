<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\User;
use App\Support\CertificateAccessPolicy;
use App\Support\CertificateAccessSettings;
use App\Support\CertificateVariables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CertificateService
{
    /** @return Collection<int, Certificate> */
    public function forUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return Certificate::query()
            ->where('user_id', $user->id)
            ->with('academicStudent:id,academic_status,section_id')
            ->orderByDesc('issued_at')
            ->get()
            ->filter(fn (Certificate $certificate) => CertificateAccessPolicy::isVisible($certificate))
            ->values();
    }

    public function findForUser(User $user, int $certificateId): ?Certificate
    {
        $certificate = Certificate::query()
            ->where('user_id', $user->id)
            ->whereKey($certificateId)
            ->with('academicStudent:id,academic_status,section_id')
            ->first();

        return $certificate && CertificateAccessPolicy::isVisible($certificate) ? $certificate : null;
    }

    public function issueForStudent(
        AcademicStudent $student,
        ?string $programName = null,
        ?User $issuer = null,
        ?CertificateTemplate $template = null,
        array $overrides = [],
    ): Certificate {
        $student->loadMissing(['user', 'batch.program']);

        $code = $this->generateCode($student);
        $verifyToken = Str::random(32);
        $template ??= CertificateTemplate::query()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        $visibilityMode = $overrides['visibility_mode'] ?? CertificateAccessSettings::defaultVisibilityMode();

        return DB::transaction(function () use ($student, $programName, $issuer, $template, $overrides, $code, $verifyToken, $visibilityMode): Certificate {
            $certificate = Certificate::query()->updateOrCreate(
                ['code' => $code],
                [
                    'user_id' => $student->user_id,
                    'academic_student_id' => $student->id,
                    'certificate_template_id' => $template?->id,
                    'source_type' => 'platform',
                    'template_version' => $template?->version,
                    'holder_name' => $student->name_ar,
                    'program_name' => $programName
                        ?? $student->batch?->program?->name_on_certificate
                        ?? $student->batch?->program?->name_ar
                        ?? 'برنامج أكاديمي',
                    'program_started_at' => $overrides['program_started_at'] ?? $student->batch?->start_date,
                    'program_ended_at' => $overrides['program_ended_at'] ?? $student->batch?->end_date ?? $student->graduated_at,
                    'issued_at' => $overrides['issued_at'] ?? now()->toDateString(),
                    'expires_at' => $overrides['expires_at'] ?? null,
                    'status' => 'active',
                    'visibility_mode' => $visibilityMode,
                    'student_visible' => (bool) ($overrides['student_visible'] ?? $visibilityMode !== 'manual'),
                    'visible_from' => $visibilityMode === 'scheduled' ? ($overrides['visible_from'] ?? null) : null,
                    'allow_download' => (bool) ($overrides['allow_download'] ?? true),
                    'allow_print' => (bool) ($overrides['allow_print'] ?? true),
                    'show_details' => (bool) ($overrides['show_details'] ?? true),
                    'student_note' => $overrides['student_note'] ?? null,
                    'metadata' => $overrides['metadata'] ?? null,
                    'verify_token' => $verifyToken,
                    'credential_hash' => null,
                    'issued_by' => $issuer?->id,
                    'revoked_at' => null,
                    'revocation_reason' => null,
                ],
            );

            $certificate->loadMissing(['template', 'academicStudent.batch.program', 'issuer']);
            $certificate->update([
                'template_snapshot' => $template
                    ? app(CertificateRenderService::class)->snapshot($template)
                    : null,
                'data_snapshot' => CertificateVariables::resolve($certificate, $student),
                'pdf_path' => null,
                'pdf_generated_at' => null,
            ]);
            $certificate->refresh();
            $certificate->update(['credential_hash' => $certificate->calculateCredentialHash()]);

            $certificate = app(CertificateRenderService::class)->generateAndStore($certificate->fresh());
            app(AuditLogService::class)->log(
                'certificate.issued',
                'إصدار شهادة للطالب '.$certificate->holder_name,
                'certificates',
                $issuer,
                $certificate,
                $certificate->code,
                null,
                [
                    'student_id' => $student->id,
                    'template_id' => $template?->id,
                    'program_name' => $certificate->program_name,
                ],
            );

            return $certificate;
        });
    }

    public function revoke(Certificate $certificate, ?string $reason = null): Certificate
    {
        $certificate->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);
        app(AuditLogService::class)->log(
            'certificate.revoked',
            'إلغاء الشهادة '.$certificate->code,
            'certificates',
            auth()->user(),
            $certificate,
            $certificate->code,
            ['status' => 'active'],
            ['status' => 'revoked', 'reason' => $reason],
        );

        return $certificate->fresh();
    }

    protected function generateCode(AcademicStudent $student): string
    {
        if (filled($student->academic_id)) {
            return 'BE-'.$student->academic_id;
        }

        return 'BE-'.now()->format('y').str_pad((string) $student->id, 4, '0', STR_PAD_LEFT);
    }
}
