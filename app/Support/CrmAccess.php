<?php

namespace App\Support;

use App\Models\User;

class CrmAccess
{
    public static function canViewAll(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.contacts.view_all'));
    }

    public static function canCreate(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.contacts.create'));
    }

    public static function canUpdate(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.contacts.update'));
    }

    public static function canDelete(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.contacts.delete'));
    }

    public static function canChangeStatus(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.contacts.change_status'));
    }

    public static function canLogActivity(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.activities.log'));
    }

    public static function canAssign(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.assign'));
    }

    public static function canBulkAssign(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.assign.bulk'));
    }

    public static function canExport(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.contacts.export'));
    }

    public static function canImport(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.import'));
    }

    public static function canSync(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.sync'));
    }

    public static function canViewRules(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.rules.view') || $user?->canAdmin('crm.rules.manage'));
    }

    public static function canManageRules(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.rules.manage'));
    }

    public static function canViewSettings(?User $user): bool
    {
        return (bool) (
            $user?->canAdmin('crm.settings.view')
            || $user?->canAdmin('crm.statuses.manage')
            || $user?->canAdmin('crm.sources.manage')
        );
    }

    public static function canManageStatuses(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.statuses.manage'));
    }

    public static function canManageSources(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.sources.manage'));
    }

    public static function canViewAudit(?User $user): bool
    {
        return (bool) ($user?->canAdmin('crm.audit.view') || $user?->canAdmin('audit-log.view'));
    }

    public static function canAccessContact(?User $user, $contact): bool
    {
        if (! $user?->canAdmin('crm.view')) {
            return false;
        }

        return static::canViewAll($user) || (int) $contact->owner_id === (int) $user->id;
    }
}
