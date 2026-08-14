<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExternalCertificateService
{
    public function upload(
        AcademicStudent $student,
        UploadedFile $file,
        array $data,
        ?User $uploader = null,
    ): Certificate {
        $student->loadMissing(['user', 'batch.program']);

        abort_unless($student->user_id, 422, 'يجب ربط الطالب بحساب مستخدم قبل إضافة الشهادة.');

        $disk = 'local';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');
        $storedPath = $file->storeAs(
            'certificates/external/'.$student->id,
            Str::uuid().'.'.$extension,
            $disk,
        );

        if (! $storedPath) {
            throw new \RuntimeException('تعذّر حفظ ملف الشهادة الخارجية.');
        }

        try {
            return DB::transaction(function () use ($student, $file, $data, $uploader, $disk, $storedPath): Certificate {
                $certificate = Certificate::query()->create([
                    'code' => $this->generateCode(),
                    'verify_token' => Str::random(40),
                    'user_id' => $student->user_id,
                    'academic_student_id' => $student->id,
                    'certificate_template_id' => null,
                    'source_type' => 'external',
                    'holder_name' => $student->name_ar,
                    'program_name' => trim($data['title']),
                    'external_issuer' => trim($data['issuer']),
                    'external_credential_id' => filled($data['credential_id'] ?? null)
                        ? trim($data['credential_id'])
                        : null,
                    'external_verification_url' => filled($data['verification_url'] ?? null)
                        ? trim($data['verification_url'])
                        : null,
                    'related_program_type' => $data['related_program_type'],
                    'related_program_id' => $data['related_program_id'],
                    'related_program_name' => $data['related_program_name'],
                    'issued_at' => $data['issued_at'],
                    'expires_at' => $data['expires_at'] ?? null,
                    'status' => 'active',
                    'visibility_mode' => $data['visibility_mode'],
                    'student_visible' => $data['visibility_mode'] !== 'hidden',
                    'visible_from' => $data['visibility_mode'] === 'scheduled'
                        ? $data['visible_from']
                        : null,
                    'allow_download' => true,
                    'allow_print' => true,
                    'show_details' => true,
                    'student_note' => filled($data['student_note'] ?? null)
                        ? trim($data['student_note'])
                        : null,
                    'notes' => filled($data['notes'] ?? null) ? trim($data['notes']) : null,
                    'pdf_disk' => $disk,
                    'pdf_path' => $storedPath,
                    'pdf_generated_at' => now(),
                    'external_file_name' => $file->getClientOriginalName(),
                    'external_file_mime' => $file->getMimeType() ?: 'application/octet-stream',
                    'external_file_hash' => hash_file('sha256', $file->getRealPath()),
                    'metadata' => [
                        'source' => 'external_upload',
                        'uploaded_by' => $uploader?->id,
                    ],
                    'issued_by' => $uploader?->id,
                ]);

                $certificate->update([
                    'data_snapshot' => [
                        'holder_name' => $certificate->holder_name,
                        'title' => $certificate->program_name,
                        'issuer' => $certificate->external_issuer,
                        'credential_id' => $certificate->external_credential_id,
                        'related_program_type' => $certificate->related_program_type,
                        'related_program_id' => $certificate->related_program_id,
                        'related_program_name' => $certificate->related_program_name,
                        'issued_at' => $certificate->issued_at?->toDateString(),
                        'expires_at' => $certificate->expires_at?->toDateString(),
                        'visibility_mode' => $certificate->visibility_mode,
                        'visible_from' => $certificate->visible_from?->toIso8601String(),
                        'file_hash' => $certificate->external_file_hash,
                    ],
                ]);
                $certificate->refresh();
                $certificate->update(['credential_hash' => $certificate->calculateCredentialHash()]);

                app(AuditLogService::class)->log(
                    'certificate.external_uploaded',
                    'رفع شهادة خارجية للطالب '.$certificate->holder_name,
                    'certificates',
                    $uploader,
                    $certificate,
                    $certificate->code,
                    null,
                    [
                        'student_id' => $student->id,
                        'issuer' => $certificate->external_issuer,
                        'title' => $certificate->program_name,
                        'file_mime' => $certificate->external_file_mime,
                    ],
                );

                return $certificate->fresh();
            });
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($storedPath);

            throw $exception;
        }
    }

    private function generateCode(): string
    {
        do {
            $code = 'EXT-'.now()->format('y').'-'.Str::upper(Str::random(8));
        } while (Certificate::query()->where('code', $code)->exists());

        return $code;
    }
}
