<?php

return [
    'api_key'      => $_ENV['BOT_API_KEY'],
    'bot_username' => $_ENV['BOT_USER_NAME'],
    'webhook'      => [
        'url' => $_ENV['HOOK_URL'],
        'max_connections' => 100
    ],
    'admins' => [],
    'validate_request' => false,
    'commands'         => [
        // (array) A list of custom commands paths.
        'paths'   => [
            'src/Commands',
        ],
    ],
//         (array) Paths where the log files should be put.
    'logging'          => [
        // (string) Log file for all incoming update requests.
        'update' =>  'php-telegram-bot-update.log',
        // (string) Log file for debug purposes.
        'debug'  => 'php-telegram-bot-debug.log',
        // (string) Log file for all errors.
        'error'  =>  'php-telegram-bot-error.log',
    ],
];
