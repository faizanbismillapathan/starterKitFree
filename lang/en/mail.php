<?php

declare(strict_types=1);

return [

    'welcome' => [
        'subject' => 'Welcome to :app',
        'greeting' => 'Hello :name,',
        'intro' => 'Your :app account is ready to use. You can sign in at any time to review your dashboard.',
        'action' => 'Open dashboard',
        'outro' => 'If you did not create this account, please contact our support team.',
    ],

    'password_changed' => [
        'subject' => 'Your password was changed',
        'greeting' => 'Hello :name,',
        'body' => 'The password for your account was changed successfully.',
        'warning' => 'If you did not make this change, secure your account immediately.',
        'action' => 'Review security settings',
    ],

];
