<?php

return [

    // mails
    [
        'keywords' => ['smtp', 'mail', 'connection refused', 'swift', 'mailgun', 'sendmail', 'sendgrid', 'failed to authenticate', 'invalid credentials', 'mailtrap'],
        'icon' => '📧',
        'code' => 'Mail',
        'description' => 'Fallo en envío de correos',
    ],

    // Database
    [
        'keywords' => ['sql', 'pdo', 'database', 'mysql', 'pgsql', 'oracle', 'sqlsrv', 'deadlock', 'constraint', 'foreign key', 'sqlstate', 'query'],
        'icon' => '🛢️',
        'code' => 'DB',
        'description' => 'Error de base de datos',
    ],

    // Auth
    [
        'keywords' => ['unauthorized', 'unauthenticated', 'token', 'session expired', 'csrf', 'forbidden', '403', '401'],
        'icon' => '🔐',
        'code' => 'Auth',
        'description' => 'Error de autenticación',
    ],

    // Permissions and files
    [
        'keywords' => ['file', 'filesystem', 'permission', 'open stream', 'failed to open', 'no such file', 'not writable', 'read-only file system'],
        'icon' => '📁',
        'code' => 'FS',
        'description' => 'Error de archivos o permisos',
    ],

    // Cache/Redis
    [
        'keywords' => ['redis', 'cache', 'memcached', 'ttl expired', 'cache store'],
        'icon' => '🧠',
        'code' => 'Cache',
        'description' => 'Fallo en Redis/cache',
    ],

    // Network / HTTP / cURL
    [
        'keywords' => ['curl', 'timeout', 'http', 'request', 'dns', 'server not found', 'host unreachable', '503', 'proxy', 'ssl', 'certificate'],
        'icon' => '🌐',
        'code' => 'Network',
        'description' => 'Fallo de red o HTTP',
    ],

    // App Internal errores
    [
        'keywords' => ['exception', 'error', 'runtime', 'undefined', 'class not found', 'method not found'],
        'icon' => '🧩',
        'code' => 'App',
        'description' => 'Error interno de aplicación',
    ],
];