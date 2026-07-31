<?php

declare(strict_types=1);

return [

    'title' => 'Dashboard',
    'welcome' => 'Welcome back, :name',
    'subtitle' => 'Here is what has happened across your workspace.',

    'statistics' => [
        'total_users' => 'Total users',
        'active_users' => 'Active users',
        'verified_users' => 'Verified accounts',
        'successful_logins' => 'Sign-ins this week',
        'compared_to_previous_period' => 'vs previous 30 days',
        'active_caption' => 'Currently able to sign in',
        'verified_caption' => 'Completed email verification',
        'last_seven_days' => 'Across the last 7 days',
    ],

    'charts' => [
        'registrations' => 'New registrations',
        'registrations_caption' => 'Accounts created over the last 14 days',
        'authentication' => 'Authentication activity',
        'authentication_caption' => 'Sign-in outcomes over the last 7 days',
        'successful' => 'Successful',
        'failed' => 'Failed',
    ],

    'recent_activity' => [
        'title' => 'Recent sign-in activity',
        'caption' => 'The latest authentication attempts.',
        'empty_title' => 'No activity yet',
        'empty_message' => 'Sign-in attempts will appear here once they occur.',
    ],

    'recent_users' => [
        'title' => 'Newest members',
        'caption' => 'The most recently created accounts.',
        'empty_title' => 'No members yet',
        'empty_message' => 'New accounts will be listed here as they register.',
    ],

    'system' => [
        'title' => 'System status',
        'caption' => 'Runtime information for this deployment.',
        'edition' => 'Edition',
        'version' => 'Version',
        'environment' => 'Environment',
        'stored_media' => 'Stored media',
    ],

    'api' => [
        'retrieved' => 'Dashboard retrieved successfully.',
        'statistics_retrieved' => 'Statistics retrieved successfully.',
        'charts_retrieved' => 'Charts retrieved successfully.',
    ],

];
