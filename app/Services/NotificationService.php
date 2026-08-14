<?php

namespace App\Services;

use App\Models\AcademicRequest;
use App\Models\AcademicStudent;
use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\NotificationDelivery;
use App\Models\NotificationRule;
use App\Models\SessionRecording;
use App\Models\User;
use App\Notifications\PlatformAlertNotification;
use App\Support\NotificationPreferences;
use App\Support\NotificationTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class NotificationService
{
    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /** @return Collection<int, DatabaseNotification> */
    public function recentFor(User $user, int $limit = 8): Collection
    {
        return $user->notifications()->limit($limit)->get();
    }

    public function markRead(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->update(['read_at' => now()]);
    }

    public function markAllRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function send(
        User $user,
        string $type,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $icon = null,
        ?Model $subject = null,
        ?NotificationRule $rule = null,
        ?array $channelsOverride = null,
    ): void {
        if (! $user->exists) {
            return;
        }

        $channels = $channelsOverride ?? $rule?->channelList() ?? ['database', 'mail'];

        if (! filled($user->email)) {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        if ($channels === []) {
            return;
        }

        if (! NotificationPreferences::allowsChannel($user, $type, 'mail')) {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        if ($channels === []) {
            return;
        }

        $user->notify(new PlatformAlertNotification(
            alertType: $type,
            title: $title,
            body: $body,
            actionUrl: $actionUrl,
            icon: $icon,
            subjectType: $subject ? $subject::class : null,
            subjectId: $subject?->getKey(),
            channels: $channels,
        ));

        foreach ($channels as $channel) {
            $this->logDelivery($user, $channel, $subject, $rule);
        }
    }

    /** @return Collection<int, User> */
    public function usersForSection(int $sectionId): Collection
    {
        return AcademicStudent::query()
            ->where('section_id', $sectionId)
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();
    }

    public function notifySectionStudents(
        int $sectionId,
        string $type,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $icon = null,
        ?Model $subject = null,
    ): int {
        $rule = NotificationRule::query()
            ->where('type', $type)
            ->where('trigger_kind', 'immediate')
            ->first();

        if ($rule && ! $rule->is_enabled) {
            return 0;
        }

        $count = 0;

        foreach ($this->usersForSection($sectionId) as $user) {
            $this->send($user, $type, $title, $body, $actionUrl, $icon, $subject, $rule);
            $count++;
        }

        return $count;
    }

    /** @return Collection<int, User> */
    public function usersForAudience(string $audience): Collection
    {
        $query = User::query()->where('status', 'active');

        return match ($audience) {
            'students' => $query->where('role', 'student')->get(),
            'instructors' => $query->where('role', 'instructor')->get(),
            'admins' => $query->where('role', 'admin')->get(),
            'staff' => $query->whereIn('role', ['admin', 'instructor', 'sales'])->get(),
            'all' => $query->get(),
            default => collect(),
        };
    }

    /**
     * Broadcast a notification to an audience group.
     *
     * @param  array<int, string>  $channels
     */
    public function notifyAudience(
        string $audience,
        string $type,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $icon = null,
        array $channels = ['database'],
    ): int {
        $users = $this->usersForAudience($audience);
        $count = 0;

        foreach ($users as $user) {
            $this->sendWithChannels($user, $type, $title, $body, $actionUrl, $icon, $channels);
            $count++;
        }

        return $count;
    }

    /**
     * @param  iterable<int, User>  $users
     * @param  array<int, string>  $channels
     */
    public function notifyMany(
        iterable $users,
        string $type,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $icon = null,
        array $channels = ['database'],
    ): int {
        $count = 0;

        foreach ($users as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $this->sendWithChannels($user, $type, $title, $body, $actionUrl, $icon, $channels);
            $count++;
        }

        return $count;
    }

    /** @param  array<int, string>  $channels */
    public function sendWithChannels(
        User $user,
        string $type,
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $icon = null,
        array $channels = ['database'],
    ): void {
        if (! $user->exists) {
            return;
        }

        $channels = array_values(array_intersect($channels, ['database', 'mail']));

        if ($channels === []) {
            $channels = ['database'];
        }

        if (! filled($user->email)) {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        if (! NotificationPreferences::allowsChannel($user, $type, 'mail')) {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        if ($channels === []) {
            $channels = ['database'];
        }

        $user->notify(new PlatformAlertNotification(
            alertType: $type,
            title: $title,
            body: $body,
            actionUrl: $actionUrl,
            icon: $icon,
            channels: $channels,
        ));
    }

    public function notifyAssignmentPublished(Assignment $assignment): int
    {
        $assignment->loadMissing('section');

        $locale = app()->getLocale();
        $url = route('assignments.show', ['locale' => $locale, 'assignment' => $assignment->id]);

        return $this->notifySectionStudents(
            sectionId: (int) $assignment->section_id,
            type: NotificationTypes::ASSIGNMENT_PUBLISHED,
            title: 'واجب جديد: '.$assignment->title,
            body: 'تم نشر واجب جديد في شعبتك. '.$this->dueHint($assignment),
            actionUrl: $url,
            icon: 'fa-file-pen',
            subject: $assignment,
        );
    }

    public function notifyExamPublished(Exam $exam): int
    {
        $url = route('exams.show', ['locale' => 'ar', 'exam' => $exam->id]);

        return $this->notifyExamCandidates(
            exam: $exam,
            type: NotificationTypes::EXAM_PUBLISHED,
            title: 'اختبار جديد: '.$exam->title,
            body: 'تم نشر اختبار جديد. '.($exam->opens_at
                ? 'موعد الفتح: '.$exam->opens_at->translatedFormat('d M Y — H:i').'.'
                : 'الاختبار متاح الآن.'),
            actionUrl: $url,
            icon: 'fa-file-circle-check',
        );
    }

    public function notifyExamSubmitted(ExamAttempt $attempt): void
    {
        $attempt->loadMissing(['exam', 'student.user']);
        $user = $attempt->student?->user;

        if (! $user) {
            return;
        }

        $this->send(
            user: $user,
            type: NotificationTypes::EXAM_SUBMITTED,
            title: 'تم تسليم الاختبار بنجاح',
            body: 'تم استلام محاولتك في اختبار «'.$attempt->effectiveExamTitle().'» رقم '.$attempt->attempt_number.'.',
            actionUrl: route('exams.show', ['locale' => $user->locale ?: 'ar', 'exam' => $attempt->exam_id]),
            icon: 'fa-circle-check',
            subject: $attempt,
        );
    }

    public function notifyExamResultsReleased(Exam $exam): int
    {
        return $this->notifyExamCandidates(
            exam: $exam,
            type: NotificationTypes::EXAM_RESULT_RELEASED,
            title: 'نتيجة اختبار متاحة: '.$exam->title,
            body: 'تم اعتماد نتيجة الاختبار ويمكنك الاطلاع عليها الآن.',
            actionUrl: route('exams.show', ['locale' => 'ar', 'exam' => $exam->id]),
            icon: 'fa-square-poll-vertical',
        );
    }

    public function notifyCertificateIssued(Certificate $certificate): void
    {
        $certificate->loadMissing('user');
        $user = $certificate->user;

        if (! $user) {
            return;
        }

        $rule = NotificationRule::query()
            ->where('type', NotificationTypes::CERTIFICATE_ISSUED)
            ->where('trigger_kind', 'immediate')
            ->first();

        if ($rule && ! $rule->is_enabled) {
            return;
        }

        $this->send(
            user: $user,
            type: NotificationTypes::CERTIFICATE_ISSUED,
            title: 'شهادتك أصبحت جاهزة',
            body: 'تم إصدار شهادة «'.$certificate->program_name.'» وإتاحتها في حسابك.',
            actionUrl: route('certificates.show', [
                'locale' => $user->locale ?: 'ar',
                'certificate' => $certificate->id,
            ]),
            icon: 'fa-award',
            subject: $certificate,
            rule: $rule,
        );
    }

    private function notifyExamCandidates(
        Exam $exam,
        string $type,
        string $title,
        string $body,
        string $actionUrl,
        string $icon,
    ): int {
        $rule = NotificationRule::query()
            ->where('type', $type)
            ->where('trigger_kind', 'immediate')
            ->first();

        if ($rule && ! $rule->is_enabled) {
            return 0;
        }

        $users = $exam->candidates()
            ->where('status', 'eligible')
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        foreach ($users as $user) {
            $localizedUrl = route('exams.show', [
                'locale' => $user->locale ?: 'ar',
                'exam' => $exam->id,
            ]);
            $this->send($user, $type, $title, $body, $localizedUrl ?: $actionUrl, $icon, $exam, $rule);
        }

        return $users->count();
    }

    public function notifyAcademicRequestStatus(AcademicRequest $request, string $action): void
    {
        $request->loadMissing('student.user');
        $user = $request->student?->user;

        if (! $user) {
            return;
        }

        $locale = $user->locale ?: 'ar';
        $url = route('user-requests.show', ['locale' => $locale, 'academicRequest' => $request->id]);

        $title = match ($action) {
            'approved' => 'تمت الموافقة على طلبك الأكاديمي',
            'rejected' => 'تم رفض طلبك الأكاديمي',
            'processing' => 'طلبك الأكاديمي قيد المعالجة',
            default => 'تحديث على طلبك الأكاديمي',
        };

        $body = $request->typeLabel().' — رقم '.$request->request_no.'. الحالة الحالية: '.$request->statusLabel().'.';

        if (filled($request->admin_notes) && in_array($action, ['approved', 'rejected'], true)) {
            $body .= ' ملاحظة الإدارة: '.$request->admin_notes;
        }

        $this->send(
            user: $user,
            type: NotificationTypes::ACADEMIC_REQUEST_STATUS,
            title: $title,
            body: $body,
            actionUrl: $url,
            icon: 'fa-clipboard-check',
            subject: $request,
        );
    }

    public function notifyRecordingPublished(SessionRecording $recording): int
    {
        $recording->loadMissing('session.section');
        $session = $recording->session;

        if (! $session) {
            return 0;
        }

        $locale = app()->getLocale();
        $url = route('sessions.show', ['locale' => $locale, 'session' => $session->id]);

        return $this->notifySectionStudents(
            sectionId: (int) $session->section_id,
            type: NotificationTypes::RECORDING_PUBLISHED,
            title: 'تسجيل محاضرة متاح',
            body: 'تسجيل محاضرة «'.$session->displayTitle().'» أصبح متاحاً للمشاهدة.',
            actionUrl: $url,
            icon: 'fa-circle-play',
            subject: $recording,
        );
    }

    public function wasDelivered(
        NotificationRule $rule,
        User $user,
        ?Model $subject,
        string $channel,
    ): bool {
        return NotificationDelivery::query()
            ->where('notification_rule_id', $rule->id)
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('subject_type', $subject ? $subject::class : null)
            ->where('subject_id', $subject?->getKey())
            ->where('channel', $channel)
            ->exists();
    }

    protected function logDelivery(
        User $user,
        string $channel,
        ?Model $subject,
        ?NotificationRule $rule,
    ): void {
        if (! $rule) {
            return;
        }

        NotificationDelivery::query()->firstOrCreate(
            [
                'notification_rule_id' => $rule->id,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'channel' => $channel,
            ],
            ['sent_at' => now()],
        );
    }

    protected function dueHint(Assignment $assignment): string
    {
        if (! $assignment->due_at) {
            return 'راجع التفاصيل وسلّم إجابتك.';
        }

        return 'الموعد النهائي: '.$assignment->due_at->translatedFormat('d M Y — H:i').'.';
    }
}
