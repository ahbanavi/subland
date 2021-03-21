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

use Illuminate\Support\Str;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use SubLand\Exceptions\SubNotFoundException;
use SubLand\Models\Film;
use SubLand\Models\Subtitle;
use SubLand\Traits\HasSubtitle;

class ChoseninlineresultCommand extends UserCommand
{
    use HasSubtitle;

    protected $inline_query;
    protected $query;
    protected $result_id;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws SubNotFoundException
     */
    public function execute(): ServerResponse
    {
        // Information about the chosen result is returned.
        $this->inline_query = $this->getChosenInlineResult();
        $this->setUser($this->inline_query->getFrom());
        $this->query        = $this->inline_query->getQuery();
        $this->result_id = $this->inline_query->getResultId();

        $listMode = False;
        if (Str::contains($this->result_id,'sub')){
            // From List Mode
            $listMode = True;
            preg_match("/list:\d*\-[a-z\_]*\-(.*)/s", $this->query, $matches);
            $this->inline_message_id = $matches[1];
            $this->result_id = Str::after($this->result_id, 'sub');
            $subtitle = Subtitle::find($this->result_id);
            if (!$subtitle){
                throw new SubNotFoundException();
            }
            $film = $subtitle->film;
            $subtitle->checkDownload($film->title);
        } else {
            // From Search Mode
            $this->inline_message_id = $this->inline_query->getInlineMessageId();
            $film = Film::firstWhere('film_id',$this->result_id);
            $subtitle = $this->getFirstSubtitle($film);
        }

        $film->htmlEscape();


        $data = [
            'text' => $this->getSubtitleText($subtitle,$film),
            'inline_message_id' => $this->inline_message_id,
            'parse_mode' => 'html',
            'reply_markup' => $this->getSubtitleKeyboard($subtitle, $this->inline_message_id)
        ];

        $this->response = Request::editMessageText($data);

        if ($listMode) {
            // Update Searching Message...
            Request::editMessageText([
                'text' => trans('subtitle_loaded'),
                'inline_message_id' => $this->inline_query->getInlineMessageId()
            ]);
        }

        return $this->response;
    }

}
