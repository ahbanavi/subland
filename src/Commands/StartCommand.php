<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class StartCommand extends UserCommand
{
    /**
     * Execute command
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute() :ServerResponse
    {
        return $this->getTelegram()->executeCommand("help");
    }

}
