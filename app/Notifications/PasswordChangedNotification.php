<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirms a password change so the owner can react to unexpected activity.
 */
final class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('mail.password_changed.subject'))
            ->greeting(__('mail.password_changed.greeting', ['name' => $notifiable->first_name]))
            ->line(__('mail.password_changed.body'))
            ->line(__('mail.password_changed.warning'))
            ->action(__('mail.password_changed.action'), route('profile.security'));
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('mail.password_changed.subject'),
            'message' => __('mail.password_changed.body'),
            'url' => route('profile.security'),
        ];
    }
}
