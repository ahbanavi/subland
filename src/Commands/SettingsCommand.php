<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SettingsCommand extends UserCommand
{
    /**
     * Execute command
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute() :ServerResponse
    {
        $message = $this->getMessage();
        $this->setUser($message->getFrom());
        $chat_id = $message->getChat()->getId();

        $data = [
            'chat_id' => $chat_id,
            'text' => "🌎 Select Robot language | زبان ربات را انتخاب کنید",
            'reply_markup' => [
                'inline_keyboard' => [[
                    ['text' => 'English🇬🇧','callback_data' => json_encode(['local_language' => 'en'])],
                    ['text' => 'فارسی🇮🇷','callback_data' => json_encode(['local_language' => 'fa'])],
                ]]
            ]
        ];

        return Request::sendMessage($data);
    }


}
