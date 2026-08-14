<?php

namespace App\Services\Zoom;

use App\Models\AcademicStaff;
use App\Models\ZoomHost;

class ZoomHostSyncService
{
    public function __construct(private readonly ZoomApiClient $api) {}

    /** @return array<string, mixed> */
    public function testConnection(): array
    {
        $user = $this->api->get('/users/me');

        return [
            'ok' => true,
            'account_id' => $user['account_id'] ?? null,
            'user_id' => $user['id'] ?? null,
            'email' => $user['email'] ?? null,
            'license' => $this->licenseLabel($user['type'] ?? null),
            'status' => $user['status'] ?? null,
        ];
    }

    public function syncHosts(): int
    {
        $users = $this->api->paginate('/users', ['status' => 'active'], 'users');
        $staffByEmail = AcademicStaff::query()
            ->with('user:id,email')
            ->get()
            ->filter(fn (AcademicStaff $staff) => filled($staff->user?->email))
            ->keyBy(fn (AcademicStaff $staff) => strtolower($staff->user->email));

        foreach ($users as $user) {
            $id = (string) ($user['id'] ?? '');
            $email = strtolower((string) ($user['email'] ?? ''));

            if ($id === '' || $email === '') {
                continue;
            }

            $host = ZoomHost::query()->firstOrNew(['zoom_user_id' => $id]);
            $host->email = $email;
            $host->license_type = $this->licenseLabel($user['type'] ?? null);
            $host->is_active = ($user['status'] ?? 'active') === 'active';
            $host->last_synced_at = now();
            $host->metadata = [
                'display_name' => $user['display_name'] ?? trim(($user['first_name'] ?? '').' '.($user['last_name'] ?? '')),
                'timezone' => $user['timezone'] ?? null,
                'pmi' => $user['pmi'] ?? null,
                'verified' => $user['verified'] ?? null,
            ];

            if (
                ! $host->academic_staff_id
                && ($staff = $staffByEmail->get($email))
                && ! ZoomHost::query()
                    ->where('academic_staff_id', $staff->id)
                    ->whereKeyNot($host->getKey())
                    ->exists()
            ) {
                $host->academic_staff_id = $staff->id;
            }

            $host->save();
        }

        return count($users);
    }

    public function sync(): int
    {
        return $this->syncHosts();
    }

    private function licenseLabel(mixed $type): string
    {
        return match ((int) $type) {
            2 => 'licensed',
            3 => 'on_prem',
            default => 'basic',
        };
    }
}
