<?php

return [
    'api_key'      => $_ENV['BOT_API_KEY'],
    'bot_username' => $_ENV['BOT_USER_NAME'],
    'webhook'      => [
        'url' => $_ENV['HOOK_URL'],
        'blank_page' => $_ENV['BLANK_URL'],
        'max_connections' => 100
    ],
    'admins' => [],
    'validate_request' => false,
    'commands'         => [
        'paths'   => [
            dirname(__DIR__) . '/src/Commands',
        ],
    ]
];
