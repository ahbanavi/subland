<?php

namespace SubLand\Exceptions;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineQuery\InlineQueryResultArticle;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;


class NoResultException extends \Exception
{
    public function getResponse(): InlineQueryResultArticle
    {
        return new InlineQueryResultArticle([
            'id' => -1,
            'title' => 'نتیجه ای یافت نشد!',
            'input_message_content' => new InputTextMessageContent([
                'message_text' => 'نتیجه ای یافت نشد!'
            ]),
            'reply_markup' => new InlineKeyboard([
                new InlineKeyboardButton([
                    'text' => '🔍 جستجو مجدد...',
                    'switch_inline_query_current_chat' => $this->getMessage()
                ])
            ])
        ]);
    }
}
