<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use SubLand\Utilities\Subscene;
use SubLand\Traits\Language;

class SettingsCommand extends UserCommand
{
    use Language;
    protected $name = 'settings';
    protected $description = 'A command for settings';
    protected $usage = '/settings';
    protected $version = '1.0';

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
            'text' => $this->getLanguageMessage(),
            'reply_markup' => [
                'inline_keyboard' => $this->getLanguageKeys()
            ]
        ];

        return $this->response = Request::sendMessage($data);
    }


}
