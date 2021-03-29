<?php
use Longman\TelegramBot\TelegramLog;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/** @var array $config */

require_once 'bootstrap/boot.php';

try {
    // Create Telegram API object
    TelegramLog::initialize(
    // Main logger that handles all 'debug' and 'error' logs.
        new Logger('telegram_bot', [
            (new StreamHandler('debug_log.log', Logger::DEBUG))->setFormatter(new LineFormatter(null, null, true)),
            (new StreamHandler('error_log.log', Logger::ERROR))->setFormatter(new LineFormatter(null, null, true)),
        ]),
//        // Updates logger for raw updates.
//        new Logger('telegram_bot_updates', [
//            (new StreamHandler('updates_log.log', Logger::INFO))->setFormatter(new LineFormatter('%message%' . PHP_EOL)),
//        ])
    );

    $telegram = new Longman\TelegramBot\Telegram($config['api_key'], $config['bot_username']);

    $telegram->addCommandsPaths($config['commands']['paths']);
    $telegram->handle();

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    // echo $e->getMessage();
}
