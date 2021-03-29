<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Carbon\Carbon;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class PingCommand extends UserCommand
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

        return Request::sendMessage(['chat_id' => $chat_id, 'text' => 'Pong']);
    }
}
