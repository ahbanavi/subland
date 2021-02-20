<?php

/** @var array $config */
require_once 'bootstrap/boot.php';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($config['api_key'], $config['bot_username']);

    // Set webhook
    $result = $telegram->setWebhook($config['webhook']['url']);
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    // echo $e->getMessage();
}
