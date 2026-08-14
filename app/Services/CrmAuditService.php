<?php

namespace App\Services;

use App\Models\CrmActivity;
use App\Models\CrmAssignmentRule;
use App\Models\CrmContact;
use App\Models\CrmImport;
use App\Models\CrmSource;
use App\Models\CrmStatus;
use App\Models\User;
use App\Support\CrmOptions;

class CrmAuditService
{
    public function __construct(private AuditLogService $audit) {}

    public function contactCreated(CrmContact $contact, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.contact.created',
            'إنشاء عميل CRM: '.$contact->name,
            'crm',
            $actor,
            $contact,
            $contact->name,
            null,
            $this->contactSnapshot($contact),
        );
    }

    public function contactUpdated(CrmContact $contact, array $old, array $new, ?User $actor = null): void
    {
        if ($old === $new) {
            return;
        }

        $this->audit->log(
            'crm.contact.updated',
            'تحديث بيانات عميل CRM: '.$contact->name,
            'crm',
            $actor,
            $contact,
            $contact->name,
            $old,
            $new,
        );
    }

    public function contactDeleted(CrmContact $contact, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.contact.deleted',
            'حذف عميل CRM: '.$contact->name,
            'crm',
            $actor,
            $contact,
            $contact->name,
            $this->contactSnapshot($contact),
            null,
        );
    }

    public function statusChanged(CrmContact $contact, string $from, string $to, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.contact.status_changed',
            'تغيير حالة العميل '.$contact->name.' من '.CrmOptions::statusLabel($from).' إلى '.CrmOptions::statusLabel($to),
            'crm',
            $actor,
            $contact,
            $contact->name,
            ['status' => $from, 'status_label' => CrmOptions::statusLabel($from)],
            ['status' => $to, 'status_label' => CrmOptions::statusLabel($to)],
        );
    }

    public function activityLogged(CrmActivity $activity, CrmContact $contact, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.activity.logged',
            'تسجيل تواصل ('.CrmOptions::label(CrmOptions::activityTypes(), $activity->type).') مع العميل '.$contact->name,
            'crm',
            $actor,
            $contact,
            $contact->name,
            null,
            [
                'type' => $activity->type,
                'outcome' => $activity->outcome,
                'subject' => $activity->subject,
                'content' => $activity->content,
                'next_follow_up_at' => $contact->next_follow_up_at?->toDateTimeString(),
            ],
        );
    }

    public function assigned(CrmContact $contact, ?int $fromOwnerId, int $toOwnerId, ?string $reason = null, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.contact.assigned',
            'توزيع العميل '.$contact->name.($reason ? ' — '.$reason : ''),
            'crm',
            $actor,
            $contact,
            $contact->name,
            ['owner_id' => $fromOwnerId],
            ['owner_id' => $toOwnerId, 'reason' => $reason],
        );
    }

    public function bulkAssigned(int $count, int $ownerId, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.contact.bulk_assigned',
            "توزيع جماعي لـ {$count} عميل إلى الموظف #{$ownerId}",
            'crm',
            $actor,
            null,
            'توزيع جماعي',
            null,
            ['count' => $count, 'owner_id' => $ownerId],
        );
    }

    public function imported(CrmImport $import, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.import.completed',
            'استيراد ملف CRM: '.$import->original_filename.' ('.$import->status.')',
            'crm',
            $actor,
            $import,
            $import->original_filename,
            null,
            [
                'status' => $import->status,
                'total_rows' => $import->total_rows,
                'created_rows' => $import->created_rows,
                'updated_rows' => $import->updated_rows,
                'failed_rows' => $import->failed_rows,
                'options' => $import->options,
            ],
        );
    }

    /** @param array{users: int, applications: int} $result */
    public function synced(array $result, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.sync.completed',
            "مزامنة CRM: {$result['users']} حساب و{$result['applications']} طلب",
            'crm',
            $actor,
            null,
            'مزامنة المسجلين',
            null,
            $result,
        );
    }

    public function exported(int $count, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.contacts.exported',
            "تصدير {$count} عميل من CRM",
            'crm',
            $actor,
            null,
            'تصدير العملاء',
            null,
            ['count' => $count],
        );
    }

    public function ruleSaved(CrmAssignmentRule $rule, bool $created, ?User $actor = null): void
    {
        $this->audit->log(
            $created ? 'crm.rule.created' : 'crm.rule.updated',
            ($created ? 'إنشاء' : 'تحديث').' قاعدة توزيع CRM #'.$rule->id,
            'crm',
            $actor,
            $rule,
            'قاعدة توزيع #'.$rule->id,
            null,
            [
                'program_id' => $rule->program_id,
                'sales_user_id' => $rule->sales_user_id,
                'priority' => $rule->priority,
                'is_active' => $rule->is_active,
            ],
        );
    }

    public function ruleToggled(CrmAssignmentRule $rule, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.rule.toggled',
            ($rule->is_active ? 'تفعيل' : 'تعطيل').' قاعدة توزيع CRM #'.$rule->id,
            'crm',
            $actor,
            $rule,
            'قاعدة توزيع #'.$rule->id,
            null,
            ['is_active' => $rule->is_active],
        );
    }

    public function ruleDeleted(CrmAssignmentRule $rule, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.rule.deleted',
            'حذف قاعدة توزيع CRM #'.$rule->id,
            'crm',
            $actor,
            $rule,
            'قاعدة توزيع #'.$rule->id,
            [
                'program_id' => $rule->program_id,
                'sales_user_id' => $rule->sales_user_id,
                'priority' => $rule->priority,
            ],
            null,
        );
    }

    public function statusOptionChanged(CrmStatus $status, string $action, ?array $old = null, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.status.'.$action,
            $this->optionActionLabel($action).' حالة CRM: '.$status->name_ar,
            'crm',
            $actor,
            $status,
            $status->name_ar,
            $old,
            [
                'code' => $status->key,
                'name_ar' => $status->name_ar,
                'color' => $status->color,
                'sort_order' => $status->sort_order,
                'is_active' => $status->is_active,
                'is_default' => $status->is_default,
                'is_won' => $status->is_won,
                'is_lost' => $status->is_lost,
                'is_closed' => $status->is_closed,
            ],
        );
    }

    public function sourceOptionChanged(CrmSource $source, string $action, ?array $old = null, ?User $actor = null): void
    {
        $this->audit->log(
            'crm.source.'.$action,
            $this->optionActionLabel($action).' مصدر CRM: '.$source->name_ar,
            'crm',
            $actor,
            $source,
            $source->name_ar,
            $old,
            [
                'code' => $source->key,
                'name_ar' => $source->name_ar,
                'sort_order' => $source->sort_order,
                'is_active' => $source->is_active,
            ],
        );
    }

    /** @return array<string, mixed> */
    public function contactSnapshot(CrmContact $contact): array
    {
        return [
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'status' => $contact->status,
            'status_label' => CrmOptions::statusLabel($contact->status),
            'source' => $contact->source,
            'source_label' => CrmOptions::sourceLabel($contact->source),
            'priority' => $contact->priority,
            'program_id' => $contact->program_id,
            'owner_id' => $contact->owner_id,
            'company' => $contact->company,
            'city' => $contact->city,
            'do_not_contact' => $contact->do_not_contact,
            'next_follow_up_at' => $contact->next_follow_up_at?->toDateTimeString(),
        ];
    }

    private function optionActionLabel(string $action): string
    {
        return match ($action) {
            'created' => 'إنشاء',
            'updated' => 'تحديث',
            'deleted' => 'حذف',
            'toggled' => 'تغيير تفعيل',
            default => $action,
        };
    }
}
