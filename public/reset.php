<?php

/** @var array $config */

require_once '../bootstrap/boot.php';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($config['api_key'], $config['bot_username']);

    // Set webhook to blank page
    $telegram->setWebhook($config['webhook']['blank_page']);

    sleep(1);

    // Set webhook to real hook
    $result = $telegram->setWebhook($config['webhook']['url'], $config['webhook']);
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    // echo $e->getMessage();
}
