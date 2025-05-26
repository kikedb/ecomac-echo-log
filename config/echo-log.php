<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cooldown Time Between Alerts (in minutes)
    |--------------------------------------------------------------------------
    |
    | The minimum amount of time that must pass before the same error
    | notification is sent again. This prevents spamming alerts for the
    | same recurring issue within a short time frame.
    |
    */

    'cooldown_minutes' => env('ECHO_LOG_COOLDOWN_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Log Scan Window (in minutes)
    |--------------------------------------------------------------------------
    |
    | The number of minutes to look back in the log files when counting
    | repeated errors. This helps determine if an error has occurred
    | multiple times within this period.
    |
    */

    'scan_window_minutes' => env('ECHO_LOG_SCAN_WINDOW_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Email Recipients
    |--------------------------------------------------------------------------
    |
    | A list of email addresses that will receive notifications about log
    | errors. This is a comma-separated list configured via environment variable.
    |
    */

    'email_recipients' => explode(',', env('ECHO_LOG_EMAIL_RECIPIENTS', '')),

    /*
    |--------------------------------------------------------------------------
    | Application Name and URL
    |--------------------------------------------------------------------------
    |
    | These values are included in alert messages to identify which
    | application is sending the notification.
    |
    */

    'app_name' => env('ECHO_LOG_APP_NAME', env('APP_NAME', 'Laravel')),
    'app_url' => env('ECHO_LOG_APP_URL', env('APP_URL', 'https://example.com')),

    /*
    |--------------------------------------------------------------------------
    | Error Levels and Notification Thresholds
    |--------------------------------------------------------------------------
    |
    | Defines the error severity levels to monitor and the number of times
    | an error must occur within the scan window before a notification is sent.
    | For example, 'ERROR' level errors require 3 occurrences to trigger.
    |
    */

    'levels' => [
        'EMERGENCY' => [
            'count' => env('ECHO_LOG_EMERGENCY_COUNT', 1),
        ],
        'ALERT'     => [
            'count' => env('ECHO_LOG_ALERT_COUNT', 1),
        ],
        'CRITICAL'  => [
            'count' => env('ECHO_LOG_CRITICAL_COUNT', 2),
        ],
        'ERROR'     => [
            'count' => env('ECHO_LOG_ERROR_COUNT', 3),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Services Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for external services used to send notifications, such as Discord.
    | You can configure webhook URLs, users to mention, and other relevant details.
    |
    */

    'services' => [
        'discord' => [
            'webhook_url' => env('DISCORD_WEBHOOK_URL'),
            'mention_user_ids' => explode(',', env('DISCORD_MENTION_USER_IDS', '')),
        ],
    ],

];
