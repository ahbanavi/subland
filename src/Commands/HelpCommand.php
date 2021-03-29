<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class HelpCommand extends UserCommand
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
        $keys = [
            ['text' => trans('key_try_here'),'switch_inline_query_current_chat' => ''],
            ['text' => trans('key_try_else'),'switch_inline_query' => '']
        ];
        if ($this->user->local_language == 'fa'){
            $keys = array_reverse($keys);
        }
        $data = [
            'chat_id' => $chat_id,
            'text' => trans('welcome', ['%name' =>$message->getFrom()->getFirstName()]),
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => true,
            'reply_markup' => [
                'inline_keyboard' => [$keys],
            ]
        ];

        return Request::sendMessage($data);
    }


}
