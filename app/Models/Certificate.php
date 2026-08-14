<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'code',
        'verify_token',
        'user_id',
        'academic_student_id',
        'certificate_template_id',
        'source_type',
        'template_version',
        'holder_name',
        'program_name',
        'external_issuer',
        'external_credential_id',
        'external_verification_url',
        'related_program_type',
        'related_program_id',
        'related_program_name',
        'program_started_at',
        'program_ended_at',
        'issued_at',
        'expires_at',
        'status',
        'visibility_mode',
        'student_visible',
        'visible_from',
        'allow_download',
        'allow_print',
        'show_details',
        'student_note',
        'notes',
        'template_snapshot',
        'data_snapshot',
        'credential_hash',
        'pdf_disk',
        'pdf_path',
        'pdf_generated_at',
        'external_file_name',
        'external_file_mime',
        'external_file_hash',
        'revoked_at',
        'revocation_reason',
        'metadata',
        'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'student_visible' => 'boolean',
            'visible_from' => 'datetime',
            'allow_download' => 'boolean',
            'allow_print' => 'boolean',
            'show_details' => 'boolean',
            'program_started_at' => 'date',
            'program_ended_at' => 'date',
            'expires_at' => 'datetime',
            'pdf_generated_at' => 'datetime',
            'revoked_at' => 'datetime',
            'template_snapshot' => 'array',
            'data_snapshot' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicStudent(): BelongsTo
    {
        return $this->belongsTo(AcademicStudent::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isExternal(): bool
    {
        return $this->source_type === 'external';
    }

    public function verifyUrl(): string
    {
        return route('certificate-verify', [
            'locale' => app()->getLocale(),
            'code' => $this->verify_token ?: $this->code,
        ]);
    }

    public function isValid(): bool
    {
        return $this->status === 'active'
            && (! $this->expires_at || $this->expires_at->isFuture())
            && $this->hasValidIntegrity();
    }

    public function hasValidIntegrity(): bool
    {
        if (! $this->credential_hash) {
            return true;
        }

        return hash_equals($this->credential_hash, $this->calculateCredentialHash());
    }

    public function calculateCredentialHash(): string
    {
        $payload = implode('|', [
            $this->code,
            $this->verify_token,
            json_encode($this->data_snapshot ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($this->template_snapshot ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }
}
