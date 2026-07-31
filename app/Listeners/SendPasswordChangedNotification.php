<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PasswordChanged;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Security alert dispatched whenever a password is replaced
 * (17_Authentication_Module.md §25).
 */
final class SendPasswordChangedNotification implements ShouldQueue
{
    public function handle(PasswordChanged $event): void
    {
        $event->user->notify(new PasswordChangedNotification);
    }
}
