<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Carbon\Carbon;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class TestCommand extends UserCommand
{
    protected $name = 'test';                      // Your command's name
    protected $description = 'A command for test'; // Your command description
    protected $usage = '/test';                    // Usage of your command
    protected $version = '1.0.1';                  // Version of your command

    /**
     * Execute command
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute() :ServerResponse
    {
        $message = $this->getMessage();            // Get Message object
        $this->setUser($message->getFrom());
        $chat_id = $message->getChat()->getId();   // Get the current Chat ID


        $data = [                                  // Set up the new message data
            'chat_id' => $chat_id,                 // Set Chat ID to send the message to
            'text'    => 'ssdsdsdsds',
        ];

        return $this->response = Request::sendMessage($data);        // Send message!
    }
}
