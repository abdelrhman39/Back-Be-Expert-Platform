<?php

namespace App\Services;

use App\Models\CrmActivity;
use App\Models\CrmContact;
use App\Models\RegistrationApplication;
use App\Models\User;
use App\Support\CrmOptions;

class CrmContactSyncService
{
    public function __construct(private CrmAssignmentService $assignment) {}

    public function syncUser(User $user, ?User $actor = null, bool $autoAssign = true): CrmContact
    {
        $user->loadMissing('academicStudent.batch');
        $student = $user->academicStudent;
        $contact = CrmContact::query()->firstOrNew(['user_id' => $user->id]);
        $isNew = ! $contact->exists;

        $contact->fill([
            'program_id' => $student?->batch?->program_id,
            'source' => CrmOptions::resolveSourceKey('registration'),
            'source_type' => User::class,
            'source_id' => $user->id,
            'status' => $contact->status ?: CrmOptions::defaultStatusKey(),
            'priority' => $contact->priority ?: 'medium',
            'name' => $user->displayName(),
            'email' => $user->email,
            'phone' => $user->phone ?: $student?->mobile,
            'city' => $student?->city,
            'country' => $student?->nationality,
            'created_by' => $contact->created_by ?: $actor?->id,
        ])->save();

        if ($isNew) {
            CrmActivity::query()->create([
                'contact_id' => $contact->id,
                'user_id' => $actor?->id,
                'type' => 'system',
                'subject' => 'إضافة من المسجلين',
                'content' => 'تمت مزامنة العميل من حساب مسجل بالمنصة.',
                'completed_at' => now(),
            ]);
        }
        if ($autoAssign && ! $contact->owner_id) {
            $this->assignment->autoAssign($contact, $actor);
        }

        return $contact->refresh();
    }

    public function syncApplication(RegistrationApplication $application, ?User $actor = null, bool $autoAssign = true): CrmContact
    {
        if ($application->user_id) {
            $contact = $this->syncUser($application->user, $actor, false);
        } else {
            $contact = CrmContact::query()->firstOrNew([
                'source_type' => RegistrationApplication::class,
                'source_id' => $application->id,
            ]);
        }

        $isNew = ! $contact->exists;
        $programId = $application->payloadValue('program_id')
            ?: $application->payloadValue('academic_program_id');
        $contact->fill([
            'program_id' => $contact->program_id ?: ($programId ? (int) $programId : null),
            'source' => CrmOptions::resolveSourceKey('application'),
            'status' => $contact->status ?: CrmOptions::defaultStatusKey(),
            'priority' => $contact->priority ?: 'medium',
            'name' => $application->applicant_name,
            'email' => $application->applicant_email,
            'phone' => $application->applicant_phone,
            'created_by' => $contact->created_by ?: $actor?->id,
        ])->save();

        if ($isNew) {
            CrmActivity::query()->create([
                'contact_id' => $contact->id,
                'user_id' => $actor?->id,
                'type' => 'system',
                'subject' => 'إضافة من طلبات التسجيل',
                'content' => 'تمت مزامنة العميل من طلب تسجيل بالمنصة.',
                'completed_at' => now(),
            ]);
        }
        if ($autoAssign && ! $contact->owner_id) {
            $this->assignment->autoAssign($contact, $actor);
        }

        return $contact->refresh();
    }

    /** @return array{users: int, applications: int} */
    public function syncAll(?User $actor = null): array
    {
        $users = 0;
        User::query()->where('role', 'student')->with('academicStudent.batch')->chunkById(100, function ($items) use ($actor, &$users) {
            foreach ($items as $user) {
                $this->syncUser($user, $actor);
                $users++;
            }
        });

        $applications = 0;
        RegistrationApplication::query()->with('user.academicStudent.batch')->chunkById(100, function ($items) use ($actor, &$applications) {
            foreach ($items as $application) {
                $this->syncApplication($application, $actor);
                $applications++;
            }
        });

        return compact('users', 'applications');
    }
}
