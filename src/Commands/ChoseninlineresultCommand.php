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
 * Chosen inline result command
 *
 * Gets executed when an item from an inline query is selected.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use SubLand\Models\Film;
use SubLand\Models\Subtitle;
use SubLand\Traits\HasSubtitle;
use SubLand\Utilities\Subscene;

class ChoseninlineresultCommand extends UserCommand
{
    use HasSubtitle;
    /**
     * @var string
     */
    protected $name = 'choseninlineresult';

    /**
     * @var string
     */
    protected $description = 'Handle the chosen inline result';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    protected $inline_query;

    protected $query;

    protected $result_id;

    /**
     * Main command execution
     *
     * @return ServerResponse
     */
    public function execute(): ServerResponse
    {
        // Information about the chosen result is returned.
        $this->inline_query = $this->getChosenInlineResult();
        $this->query        = $this->inline_query->getQuery();
        $this->result_id = $this->inline_query->getResultId();

        if($this->result_id != -1) {
            $film = Film::firstWhere('film_id',$this->result_id);

            $subtitle = self::getFirstSubtitle($film);
            $film->htmlEscape();

            $data = [
                'text' => $this->getSubtitleText($subtitle,$film),
                'inline_message_id' => $this->inline_query->getInlineMessageId(),
                'parse_mode' => 'html',
                'reply_markup' => $this->getSubtitleKeyboard($subtitle)
            ];

            $this->response = Request::editMessageText($data);
        }

        return $this->response;
    }

}