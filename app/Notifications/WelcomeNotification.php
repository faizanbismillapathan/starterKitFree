<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

/**
 * Introduces the application to a newly registered user.
 */
#[DeleteWhenMissingModels]
final class WelcomeNotification extends Notification implements ShouldQueue
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
            ->subject(__('mail.welcome.subject', ['app' => config('starter_kit.name')]))
            ->greeting(__('mail.welcome.greeting', ['name' => $notifiable->first_name]))
            ->line(__('mail.welcome.intro', ['app' => config('starter_kit.name')]))
            ->action(__('mail.welcome.action'), route('dashboard'))
            ->line(__('mail.welcome.outro'));
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('mail.welcome.subject', ['app' => config('starter_kit.name')]),
            'message' => __('mail.welcome.intro', ['app' => config('starter_kit.name')]),
            'url' => route('dashboard'),
        ];
    }
}
