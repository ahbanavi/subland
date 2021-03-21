<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Longman\TelegramBot\Commands;

use Carbon\Carbon;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use SubLand\Exceptions\NoResultException;
use SubLand\Exceptions\SubNotFoundException;
use SubLand\Models\User;
use SubLand\Utilities\Subscene;


abstract class UserCommand extends Command
{


    /**
     * @var User
     */
    protected $user;

    protected $response;

    protected function setUser(\Longman\TelegramBot\Entities\User $telegramUser): User
    {
        // save or update user in database
        $user = User::firstOrNew(['user_id' => $telegramUser->getId()]);
        Subscene::setLanguage($user->language);
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
            $this->execute();
        } catch (NoResultException $exception){
            $this->response = $this->inline_query->answer([$exception->getResponse()],['cache_time' => 0]);
        } catch (SubNotFoundException $exception){
            if (isset($this->callback_query)){
                $this->callback_query->answer(['text' => $exception->getMessage() ?? 'متاسفانه زیرنویس مورد نظر در دیتابیس یافت نشد. لطفا مجددا جستجو نمایید.','show_alert' => true]);
            }
            $this->response = null;
        }

        $this->afterExecute();
    }

    public function afterExecute()
    {
        $this->user->touch();

        if ($this->user->isDirty()){
            $this->user->save();
        }
        return $this->response;
    }

}
