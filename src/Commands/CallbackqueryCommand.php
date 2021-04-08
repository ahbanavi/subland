<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\CallbackQuery;
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
     * @var CallbackQuery
     */
    protected $callback_query;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws SubNotFoundException
     */
    public function execute(): ServerResponse
    {
        $this->callback_query = $this->getCallbackQuery();
        $this->setUser($this->callback_query->getFrom());
        $inline_message_id = $this->callback_query->getInlineMessageId();
        $callback_data = json_decode($this->callback_query->getData());

        $data = [];
        if (property_exists($callback_data, 'subtitle_id')){
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

        } elseif (array_key_exists('language',$callback_data)){
            if (in_array($callback_data->language, array_keys(Subscene::LANGUAGES))){
                $this->user->language = $callback_data->language;
                $this->user->save();
                $this->user->refresh();

                $data = [
                    'text' => $this->getLanguageMessage(),
                    'chat_id' => $this->user->user_id,
                    'message_id' => $this->callback_query->getMessage()->getMessageId(),
                    'reply_markup' => [
                        'inline_keyboard' => $this->getLanguageKeys()
                    ]
                ];
            }

        } elseif (array_key_exists('local_language',$callback_data)){
            if (in_array($callback_data->local_language, ['en', 'fa'])){
                global $local_lang;
                $this->user->local_language = $local_lang = $callback_data->local_language;
                $this->user->save();
                $this->user->refresh();

                $data = [
                    'text' => trans('success_change_local_language'),
                    'chat_id' => $this->user->user_id,
                    'message_id' => $this->callback_query->getMessage()->getMessageId()
                ];
            }

        } elseif (property_exists($callback_data, 'just_one')){
            return $this->callback_query->answer(['text' => trans('just_one_callback'), 'show_alert' => true, 'cache_time' => PHP_INT_MAX]);
        } else {
            return $this->callback_query->answer(['text' => trans('dont_understand'), 'show_alert' => true, 'cache_time' => PHP_INT_MAX]);
        }


        $response = Request::editMessageText($data);
        $this->callback_query->answer(['text' => 'Done.', 'show_alert' => false]);
        return $response;
    }
}
