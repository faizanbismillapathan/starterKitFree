<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Delivers the welcome message once an account has been created.
 *
 * Queued so registration responses stay fast (38_Performance_Guide.md §13).
 */
final class SendWelcomeNotification implements ShouldQueue
{
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new WelcomeNotification);
    }
}
