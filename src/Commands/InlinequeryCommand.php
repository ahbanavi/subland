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
 * Inline query command
 *
 * Command that handles inline queries and returns a list of results.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Illuminate\Support\Str;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineQuery;
use Longman\TelegramBot\Entities\InlineQuery\InlineQueryResultArticle;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use SubLand\Exceptions\NoResultException;
use SubLand\Models\Film;
use SubLand\Utilities\SubCache;
use SubLand\Utilities\Subscene;

class InlinequeryCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'inlinequery';

    /**
     * @var string
     */
    protected $description = 'Handle inline query';

    /**
     * @var string
     */
    protected $version = '0.1';

    protected InlineQuery $inline_query;

    protected string $query;

    protected $offset;

    public function execute()
    {
        $this->inline_query = $this->getInlineQuery();
        $this->setUser($this->inline_query->getFrom());
        $this->query = trim(strtolower($this->inline_query->getQuery()));
        $this->offset = (int) $this->inline_query->getOffset() ?? 0;


        if (($film_id = Str::of($this->query)->match('/list:(\d*)/')) != ''){
            $results = $this->listMode(Str::before($film_id,'-'));
        } else {
            $results = $this->searchMode();
        }

        $options = [
            'cache_time' => 0,
            'next_offset' => $this->offset == 0 ? '' : $this->offset
        ];

        if ($switch ?? false)
            $options[] = ['switch_pm_text' => urlencode($switch)];
        if ($switchPM ?? false)
            $options[] = ['switch_pm_parameter' => urlencode($switchPM)];

        return $this->response = $this->inline_query->answer($results,$options);
    }


    public function listMode($film_id): array
    {
        $results = [];
        /** @var Film $film */
        $film = Film::find($film_id);
        $subtitles = $film->subtitles->where('language', $this->user->language)->toArray();
        if (count($subtitles) === 0) {
            throw new NoResultException($this->query);
        }
        $this->normalizeResults($subtitles);
        foreach ($subtitles as $subtitle) {
            $results[] = new InlineQueryResultArticle([
                'id' => 'sub' . $subtitle['subtitle_id'],
                'title' => $subtitle['author_name'] . ($subtitle['extra'] != '' ? ' |' . $subtitle['extra'] : ''),
                'description' =>  $subtitle['comment'] . $subtitle['info'],
                'input_message_content' => new InputTextMessageContent([
                    'message_text' => 'در حال بارگذاری...',
                ]),
                'reply_markup' => new InlineKeyboard([
                    new InlineKeyboardButton(['text' => 'Subscene URL','url' => Subscene::BASE_URL . $subtitle['url']])
                ]),
            ]);
        }

        return $results;
    }

    public function searchMode(): array
    {
        $results = [];

        $search = SubCache::getFilms($this->query)->toArray();

        if (count($search) === 0) {
            throw new NoResultException($this->query);
        }


        $total_count = $this->normalizeResults($search);
        foreach ($search as $key => $item) {
            $results[] = new InlineQueryResultArticle([
                'id' => $item['film_id'],
                'title' => $item['title'],
                'thumb_url' => $item['poster'] ?? '',
                'description' =>  $key + 1 . '/' . $total_count,
                'input_message_content' => new InputTextMessageContent([
                    'message_text' => 'در حال بارگذاری...',
                ]),
                'reply_markup' => new InlineKeyboard([
                    new InlineKeyboardButton(['text' => 'Subscene URL','url' => Subscene::BASE_URL . $item['url']])
                ]),
            ]);
        }

        return $results;
    }

    public function normalizeResults(&$results): int
    {
        $total_last_key = array_key_last($results);
        $total_count = count($results);
        $results = array_slice($results,$this->offset,50,true);
        $current_last_key = (int) array_key_last($results);
        $this->offset = $current_last_key + 1;

        if ($this->offset >= $total_last_key){
            $this->offset = 0;
        }

        return $total_count;
    }


}
