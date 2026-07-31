<?php

declare(strict_types=1);

return [

    'title' => 'Profile',
    'heading' => 'Your profile',
    'subheading' => 'Manage your personal information and preferences.',

    'security_title' => 'Security',
    'security_heading' => 'Security settings',
    'security_subheading' => 'Protect your account and review where you are signed in.',

    'login_history_title' => 'Login history',
    'login_history_heading' => 'Login history',
    'login_history_subheading' => 'A complete record of authentication attempts on your account.',

    'sections' => [
        'personal' => 'Personal information',
        'personal_caption' => 'This information appears across the application.',
        'avatar' => 'Profile picture',
        'avatar_caption' => 'A square image of at least 64×64 pixels works best.',
        'preferences' => 'Preferences',
        'preferences_caption' => 'Control how dates and the interface are presented to you.',
        'password' => 'Change password',
        'password_caption' => 'Use a long, unique password to keep your account secure.',
        'sessions' => 'Active sessions',
        'sessions_caption' => 'Devices currently signed in to your account.',
        'recent_logins' => 'Recent sign-ins',
        'recent_logins_caption' => 'The five most recent authentication attempts.',
    ],

    'fields' => [
        'avatar' => 'Profile picture',
        'timezone' => 'Timezone',
        'locale' => 'Language',
        'current_password' => 'Current password',
        'new_password' => 'New password',
        'confirm_password' => 'Confirm new password',
    ],

    'actions' => [
        'save' => 'Save changes',
        'upload_avatar' => 'Upload picture',
        'remove_avatar' => 'Remove',
        'change_password' => 'Update password',
        'revoke_session' => 'Revoke',
        'revoke_others' => 'Sign out other devices',
        'view_full_history' => 'View full history',
    ],

    'updated' => 'Your profile has been updated.',
    'avatar_updated' => 'Your profile picture has been updated.',
    'avatar_removed' => 'Your profile picture has been removed.',
    'password_updated' => 'Your password has been changed.',
    'session_revoked' => 'The session has been revoked.',
    'other_sessions_revoked' => 'All other sessions have been signed out.',

    'current_session' => 'This device',
    'last_active' => 'Last active :time',
    'never_signed_in' => 'Never signed in',

    'sessions_empty_title' => 'No other sessions',
    'sessions_empty_message' => 'You are only signed in on this device.',
    'history_empty_title' => 'No sign-in history',
    'history_empty_message' => 'Authentication attempts will be recorded here.',

    'revoke_others_confirm_title' => 'Sign out other devices?',
    'revoke_others_confirm_message' => 'Enter your password to end every other active session. This cannot be undone.',
    'revoke_session_confirm_title' => 'Revoke this session?',
    'revoke_session_confirm_message' => 'The device will be signed out immediately.',

    'errors' => [
        'current_password' => 'The current password you entered is incorrect.',
        'password_reuse' => 'The new password must differ from your current password.',
        'avatar_type' => 'The picture must be a JPG, PNG or WebP image.',
        'avatar_dimensions' => 'The picture must be at least 64×64 pixels.',
    ],

    'api' => [
        'retrieved' => 'Profile retrieved successfully.',
        'updated' => 'Profile updated successfully.',
        'login_history_retrieved' => 'Login history retrieved successfully.',
    ],

];
