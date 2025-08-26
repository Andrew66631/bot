<?php

return [
    'telegram_bot' => [
        'enabled' => env('TELEGRAM_BOT_SCHEDULER_ENABLED', true),
        'interval' => env('TELEGRAM_BOT_INTERVAL', 10),
        'log' => env('TELEGRAM_BOT_LOG', true),
        'failure_email' => env('TELEGRAM_BOT_FAILURE_EMAIL', 'admin@example.com'),
    ],
];
