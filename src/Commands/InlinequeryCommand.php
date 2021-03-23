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
use SubLand\Models\Query;
use SubLand\Utilities\Subscene;
use Longman\TelegramBot\Request;

class InlinequeryCommand extends UserCommand
{
    protected InlineQuery $inline_query;
    protected string $query;
    protected $offset;
    protected int $cache_time = 0;

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

        return $this->response = $this->inline_query->answer($results,$options);
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
                    'message_text' => trans('loading'),
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

        if ($this->query != ''){
            usleep(1.5 * 1000000);
            $this->user->refresh();
            if ($this->user->updated_at->addSeconds(1)->gte(Carbon::now())){
                return Request::emptyResponse();
            }
        }

        $search = $this->getFilms()->toArray();

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
                    'message_text' => trans('loading'),
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
     * Get New Films based on cache or source
     *
     * @return Film[]
     */
    public function getFilms()
    {
        $searchQuery = Str::lower($this->query);

        /** @var Query $query */
        /** @var Film $films */
        /** @var Carbon $updated_at */
        $query = Query::firstOrCreate(['query' => $searchQuery]);
        $films = $query->films;

        if ($searchQuery == ''){
            // home page
            $searchMethod = 'getHome';
            $cacheTime = $_ENV['HOME_CACHE_TIME'];

        } else {
            $searchMethod = 'search';
            $cacheTime = $_ENV['SEARCH_CACHE_TIME'];
        }

        if ($query->updated_at->addSeconds($cacheTime)->gt(Carbon::now()) && count($films)){
            // Use Cache
            return $films;
        } else {
            // Update Cache
            if ($searchQuery == ''){
                $fresh = Subscene::$searchMethod();
            } else {
                $fresh = Subscene::$searchMethod($searchQuery);
            }

            $ids = self::getFilmIds($fresh);
            $this->cache_time = $cacheTime;
            return $query->syncFilms($ids)->films;
        }
    }

    /**
     * Save new items to database and update old ones if required
     *
     * @param   array $films
     * @return  int[]
     */
    private static function getFilmIds(array $films)
    {
        $ids = [];
        foreach ($films as ['title' => $title, 'poster' => $poster, 'url' => $url, 'imdb' => $imdb]){

            /** @var Film $new_film */
            $new_film         = Film::firstOrNew(['url' => $url]);
            $new_film->url    = $url;
            $new_film->title  = $title;

            if (isset($poster)) {
                $new_film->poster = $poster;
            }

            if (isset($imdb)) {
                $new_film->imdb = $imdb;
            }

            $new_film->save();
            $ids[] = $new_film->film_id;
        }

        return $ids;
    }


}
