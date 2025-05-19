<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cooldown Time Between Alerts (in minutes)
    |--------------------------------------------------------------------------
    |
    | Minimum amount of time that must pass before the same error is notified again.
    |
    */

    'cooldown_minutes' => env('ECHO_LOG_COOLDOWN_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Log Scan Window (in minutes)
    |--------------------------------------------------------------------------
    |
    | Number of minutes to look back in the logs to count repeated errors.
    |
    */

    'scan_window_minutes' => env('ECHO_LOG_SCAN_WINDOW_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Email Recipients
    |--------------------------------------------------------------------------
    |
    | List of email addresses that will receive notifications about log errors.
    |
    */

    'email_recipients' => explode(',', env('ECHO_LOG_EMAIL_RECIPIENTS', '')),

    /*
    |--------------------------------------------------------------------------
    | Application Name and URL
    |--------------------------------------------------------------------------
    |
    | These values are used in the alerts to identify which app is sending the notification.
    |
    */

    'app_name' => env('ECHO_LOG_APP_NAME', env('APP_NAME', 'Laravel')),
    'app_url' => env('ECHO_LOG_APP_URL', env('APP_URL', 'https://example.com')),


    'levels' => [
        'EMERGENCY' => [
            'count' => env('ECHO_LOG_EMERGENCY_COUNT', 1), 
        ],
        'ALERT'     => [
            'count' => env('ECHO_LOG_EMERGENCY_COUNT', 1), 
        ],
        'CRITICAL'  => [
            'count' => env('ECHO_LOG_EMERGENCY_COUNT', 2), 
        ],
        'ERROR'     => [
            'count' => env('ECHO_LOG_EMERGENCY_COUNT', 3),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Services
    |--------------------------------------------------------------------------
    |
    | Settings for external services used to send notifications, such as Discord.
    |
    */

    'services' => [
        'discord' => [
            'webhook_url' => env('DISCORD_WEBHOOK_URL'),
            'mention_user_ids' => explode(',', env('DISCORD_MENTION_USER_IDS', '')),
            'app_name' => env('APP_NAME', 'Laravel'),
        ],
    ]
];
