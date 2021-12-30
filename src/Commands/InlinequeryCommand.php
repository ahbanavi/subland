<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineQuery;
use Longman\TelegramBot\Entities\InlineQuery\InlineQueryResultArticle;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Entities\ServerResponse;
use SubLand\Exceptions\NoResultException;
use SubLand\Models\Film;
use SubLand\Utilities\Subscene;
use Longman\TelegramBot\Request;

class InlinequeryCommand extends UserCommand
{
    protected InlineQuery $inline_query;
    protected string $query;
    protected $offset;
    protected int $cache_time = 5;

    /**
     * Execute command
     *
     * @return ServerResponse
     * @throws NoResultException
     */
    public function execute() :ServerResponse
    {
        $this->inline_query = $this->getInlineQuery();
        $this->setUser($this->inline_query->getFrom());
        $this->query = trim(strtolower($this->inline_query->getQuery()));
        $this->offset = (int) $this->inline_query->getOffset() ?? 0;


        if (preg_match('/list:(\d*)\-([a-z\_]*)\-(.*)/s', $this->query, $matches)){
            $results = $this->listMode($matches[1], $matches[2]);
        } elseif (!Str::startsWith($this->query, 'list:')) {
            $results = $this->searchMode();
        } else {
            throw new NoResultException($this->query);
        }

        $options = [
            'cache_time' => $this->cache_time,
            'next_offset' => $this->offset == 0 ? '' : $this->offset
        ];

        if ($switch ?? false)
            $options[] = ['switch_pm_text' => urlencode($switch)];
        if ($switchPM ?? false)
            $options[] = ['switch_pm_parameter' => urlencode($switchPM)];

        return $this->inline_query->answer($results,$options);
    }


    public function listMode($film_id, $language): array
    {
        $results = [];
        /** @var Film $film */
        $film = Film::find($film_id);
        $subtitles = $film->subtitles->where('language', $language)->toArray();
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
                    'message_text' => 'loading...',
                ]),
                'reply_markup' => new InlineKeyboard([
                    new InlineKeyboardButton(['text' => 'Subscene URL','url' => Subscene::BASE_URL . $subtitle['url']])
                ]),
            ]);
        }
        $this->cache_time = 600;
        return $results;
    }

    public function searchMode()
    {
        $results = [];

        ## I comment these lines because there are some problems with this approach for debouncing till I find a better solution.
//        if ($this->query != ''){
//            usleep(1.5 * 1000000);
//            $this->user->refresh();
//            if ($this->user->updated_at->addSeconds(1)->gte(Carbon::now())){
//                return Request::emptyResponse();
//            }
//        }


        # Check if there's any results for the query multiple times to avoid any false positives.
        $counter = 0;
        do {
            $counter++;
            if ($counter > 1) {
                usleep($counter / 2 * 1000000);
            }
            $search = $this->getFilms();
        } while (count($search) === 0 && $counter < 5);

        if (count($search) === 0) {
            # No results found. Return an empty response.
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
                    'message_text' => 'loading...',
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


    /**
     * Get New Films based on search query or home
     *
     * @return Film[]
     */
    public function getFilms()
    {
        /** @var Film $films */

        $new_films = [];
        foreach ($gen = Subscene::searchOrHome($this->query) as ['title' => $title, 'poster' => $poster, 'url' => $url, 'imdb' => $imdb]){

            /** @var Film $film */
            $film = Film::firstOrNew(['url' => $url]);
            $film->url = $url;
            $film->title = $title;

            if (isset($poster) && $poster != '') {
                $film->poster = $poster;
            }

            if (isset($imdb) && $imdb != '') {
                $film->imdb = $imdb;
            }

            $film->save();
            $new_films[] = $film;
        }

        $this->cache_time = $gen->getReturn();
        return $new_films;

    }
}
