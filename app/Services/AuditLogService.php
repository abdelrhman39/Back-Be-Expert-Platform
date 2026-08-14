<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AuditLogService
{
    public function log(
        string $action,
        string $descriptionAr,
        string $group = 'system',
        ?User $actor = null,
        ?Model $subject = null,
        ?string $subjectLabel = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'user_id' => $actor?->id ?? auth()->id(),
            'action' => $action,
            'log_group' => $group,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subjectLabel,
            'description_ar' => $descriptionAr,
            'old_values' => $this->sanitizeValues($oldValues),
            'new_values' => $this->sanitizeValues($newValues),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /** @return LengthAwarePaginator<int, ActivityLog> */
    public function paginate(?string $group = null, ?string $search = null, int $perPage = 25)
    {
        return ActivityLog::query()
            ->with('user')
            ->when($group && $group !== 'all', fn ($q) => $q->where('log_group', $group))
            ->when(filled($search), function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('description_ar', 'like', "%{$search}%")
                        ->orWhere('subject_label', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%");
                });
            })
            ->latest('created_at')
            ->paginate($perPage);
    }

    /** @return array<string, string> */
    public function groups(): array
    {
        return [
            'all' => 'الكل',
            'settings' => 'الإعدادات',
            'notifications' => 'الإشعارات',
            'refunds' => 'الاسترداد',
            'finance' => 'التقسيط والمالية',
            'requests' => 'طلبات الطلاب',
            'exams' => 'الاختبارات',
            'certificates' => 'الشهادات',
            'crm' => 'إدارة علاقات العملاء CRM',
            'users' => 'المستخدمون',
            'system' => 'النظام',
        ];
    }

    /** @return Collection<int, ActivityLog> */
    public function recentForSubject(Model $subject, int $limit = 10): Collection
    {
        return ActivityLog::query()
            ->where('subject_type', $subject::class)
            ->where('subject_id', $subject->getKey())
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    protected function sanitizeValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $sanitized = [];

        foreach ($values as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $sanitized[$key] = filled($value) ? '●●●●' : null;

                continue;
            }

            $sanitized[$key] = is_scalar($value) || $value === null ? $value : json_encode($value);
        }

        return $sanitized;
    }

    protected function isSensitiveKey(string $key): bool
    {
        $lower = strtolower($key);

        return preg_match('/(^|_)(secret|password|token|api_key|private_key|hash_salt)(_|$)/i', $key) === 1
            || in_array($lower, ['password', 'secret', 'token', 'remember_token', 'api_token'], true);
    }
}
