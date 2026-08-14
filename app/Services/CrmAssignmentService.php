<?php

namespace App\Services;

use App\Models\CrmActivity;
use App\Models\CrmAssignmentRule;
use App\Models\CrmContact;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CrmAssignmentService
{
    public function salesUsers(): Collection
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('accessRoles', fn ($query) => $query
                ->where('access_roles.key', 'crm-sales')
                ->where('access_roles.is_active', true))
            ->orderByRaw('COALESCE(name_ar, name)')
            ->get(['users.id', 'users.name', 'users.name_ar', 'users.email']);
    }

    public function autoAssign(CrmContact $contact, ?User $actor = null): ?User
    {
        if ($contact->owner_id) {
            return $contact->owner;
        }

        return DB::transaction(function () use ($contact, $actor) {
            $rules = CrmAssignmentRule::query()
                ->where('is_active', true)
                ->whereHas('salesUser', fn ($query) => $query->where('status', 'active'))
                ->where(function ($query) use ($contact) {
                    if ($contact->program_id) {
                        $query->where('program_id', $contact->program_id)
                            ->orWhereNull('program_id');
                    } else {
                        $query->whereNull('program_id');
                    }
                })
                ->orderByRaw('CASE WHEN program_id IS NULL THEN 1 ELSE 0 END')
                ->orderBy('priority')
                ->orderBy('assigned_count')
                ->orderByRaw('CASE WHEN last_assigned_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('last_assigned_at')
                ->lockForUpdate()
                ->get();

            $rule = $rules->first();
            if (! $rule) {
                return null;
            }

            $this->assign($contact, $rule->sales_user_id, $actor, 'توزيع تلقائي حسب قواعد البرنامج');
            $rule->increment('assigned_count');
            $rule->update(['last_assigned_at' => now()]);

            return $rule->salesUser;
        });
    }

    public function assign(CrmContact $contact, int $ownerId, ?User $actor = null, ?string $reason = null): void
    {
        $previousOwner = $contact->owner_id;
        $contact->update(['owner_id' => $ownerId, 'assigned_at' => now(), 'last_activity_at' => now()]);

        CrmActivity::query()->create([
            'contact_id' => $contact->id,
            'user_id' => $actor?->id,
            'type' => 'assignment',
            'subject' => 'توزيع العميل',
            'content' => $reason,
            'completed_at' => now(),
            'metadata' => ['from_owner_id' => $previousOwner, 'to_owner_id' => $ownerId],
        ]);

        app(CrmAuditService::class)->assigned($contact, $previousOwner ? (int) $previousOwner : null, $ownerId, $reason, $actor);
    }

    /** @param array<int, int|string> $contactIds */
    public function bulkAssign(array $contactIds, int $ownerId, User $actor): int
    {
        $count = 0;
        CrmContact::query()->whereKey($contactIds)->each(function (CrmContact $contact) use ($ownerId, $actor, &$count) {
            $this->assign($contact, $ownerId, $actor, 'توزيع جماعي من مدير CRM');
            $count++;
        });

        if ($count > 0) {
            app(CrmAuditService::class)->bulkAssigned($count, $ownerId, $actor);
        }

        return $count;
    }
}
