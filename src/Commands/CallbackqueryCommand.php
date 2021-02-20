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

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use SubLand\Exceptions\SubNotFoundException;
use SubLand\Models\Subtitle;
use SubLand\Traits\HasSubtitle;
use SubLand\Utilities\Subscene;
use function Composer\Autoload\includeFile;

class CallbackqueryCommand extends UserCommand
{
    use HasSubtitle;
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
        $callback_data  = json_decode($this->callback_query->getData());


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
                'inline_message_id' => $this->callback_query->getInlineMessageId(),
                'parse_mode' => 'html',
                'reply_markup' => $this->getSubtitleKeyboard($subtitle)
            ];

            $this->response = Request::editMessageText($data);
        }

        $data = [
            'text'       => 'loaded.',
            'show_alert' =>  false
        ];
        $this->callback_query->answer($data);

        return $this->response;
    }
}
