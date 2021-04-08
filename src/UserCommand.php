<?php

namespace Longman\TelegramBot\Commands;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use SubLand\Exceptions\NoResultException;
use SubLand\Exceptions\SubNotFoundException;
use SubLand\Models\User;
use SubLand\Utilities\Subscene;


abstract class UserCommand extends Command
{

    /**
     * @var User
     */
    protected User $user;

    protected function setUser(\Longman\TelegramBot\Entities\User $telegramUser): User
    {
        // save or update user in database
        global $local_lang;
        $user = User::firstOrCreate(['user_id' => $telegramUser->getId()]);
        Subscene::setLanguage($user->language);
        $local_lang = $user->local_language;
        return $this->user = $user;
    }

    /**
     * Pre-execute command
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function preExecute() :ServerResponse
    {

        try {
            return $this->execute();
        }
        catch (NoResultException $exception){
            return $this->inline_query->answer([$exception->getResponse()],['cache_time' => $_ENV['NO_RESULTS_CACHE_TIME']]);
        }
        catch (SubNotFoundException $exception){
            if (isset($this->callback_query)){
                $this->callback_query->answer(['text' => $exception->getMessage() ?? trans('no_results_found'),'show_alert' => true]);
                return Request::emptyResponse();
            } elseif (isset($this->inline_query)){
                $lang = Subscene::LANGUAGES[$this->user->language];
                $data = [
                    'text' => trans('no_sub_found', ['%lang' => $lang['title'] . ' ' . $lang['flag']]),
                    'inline_message_id' => $this->inline_message_id,
                    'parse_mode' => 'html',
                    'reply_markup' => [
                        'inline_keyboard' =>[[
                            ['text' => trans('try_again'),'switch_inline_query_current_chat' => $this->query]
                        ]]
                    ]
                ];

                return Request::editMessageText($data);
            }
        }
        finally {
            if (isset($this->user)){
                $this->user->touch();
            }
        }
    }

}
