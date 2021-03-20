<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use SubLand\Exceptions\SubNotFoundException;
use SubLand\Models\Subtitle;
use SubLand\Traits\HasSubtitle;
use SubLand\Traits\Language;
use SubLand\Utilities\Subscene;

class CallbackqueryCommand extends UserCommand
{
    use HasSubtitle;
    use Language;

    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Handle the callback query';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    protected $callback_query;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws \Exception
     */
    public function execute(): ServerResponse
    {
        $this->callback_query = $this->getCallbackQuery();
        $this->setUser($this->callback_query->getFrom());
        $inline_message_id = $this->callback_query->getInlineMessageId();
        $callback_data = json_decode($this->callback_query->getData());

        $answer_data = $data = [];
        if (array_key_exists('subtitle_id',$callback_data)){
            $subtitle = Subtitle::find($callback_data->subtitle_id);
            if (!$subtitle){
                throw new SubNotFoundException();
            }
            $film = $subtitle->film;

            $subtitle->checkDownload($film->title);
            $film->htmlEscape();

            $data = [
                'text' => $this->getSubtitleText($subtitle,$film),
                'inline_message_id' => $inline_message_id,
                'parse_mode' => 'html',
                'reply_markup' => $this->getSubtitleKeyboard($subtitle, $inline_message_id)
            ];

            $answer_data = [
                'text'       => 'loaded.',
                'show_alert' =>  false
            ];

        } elseif (array_key_exists('language',$callback_data)){
            if (in_array($callback_data->language, array_keys(Subscene::LANGUAGES))){
                $this->user->language = $callback_data->language;
                $this->user->save();

                $data = [
                    'text' => $this->getLanguageMessage(),
                    'chat_id' => $this->user->user_id,
                    'message_id' => $this->callback_query->getMessage()->getMessageId(),
                    'reply_markup' => [
                        'inline_keyboard' => $this->getLanguageKeys()
                    ]
                ];

                $answer_data = [
                    'text'       => 'زبان ' . Subscene::LANGUAGES[$callback_data->language]['flag'] . ' با موفقیت ثبت شد.',
                    'show_alert' =>  false
                ];
            }

        }


        $this->response = Request::editMessageText($data);
        $this->callback_query->answer($answer_data);

        return $this->response;
    }
}
