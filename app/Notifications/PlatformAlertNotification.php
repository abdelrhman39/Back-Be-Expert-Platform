<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param  array<int, string>  $channels */
    public function __construct(
        public string $alertType,
        public string $title,
        public string $body,
        public ?string $actionUrl = null,
        public ?string $icon = null,
        public ?string $subjectType = null,
        public ?int $subjectId = null,
        public array $channels = ['database', 'mail'],
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        $allowed = array_values(array_intersect($this->channels, ['database', 'mail']));

        return $allowed ?: ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->alertType,
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->actionUrl,
            'icon' => $this->icon ?? 'fa-bell',
            'subject_type' => $this->subjectType,
            'subject_id' => $this->subjectId,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('مرحباً '.$notifiable->displayName())
            ->line($this->body);

        if ($this->actionUrl) {
            $mail->action('عرض التفاصيل', $this->actionUrl);
        }

        return $mail->salutation(platform_name('ar'));
    }
}
