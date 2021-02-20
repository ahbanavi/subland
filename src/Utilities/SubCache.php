<?php


namespace SubLand\Utilities;


use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SubLand\Models\Film;
use SubLand\Models\Query;
use SubLand\Models\Result;


class SubCache
{


    /**
     * Get New Films based on cache or source
     *
     * @param string $searchQuery
     * @return Film[]
     */
    public static function getFilms($searchQuery)
    {
        $searchQuery = Str::lower($searchQuery);

        /** @var Query $Query */
        /** @var Film $films */
        /** @var Carbon $updated_at */
        $Query = Query::firstOrCreate(['query' => $searchQuery]);
        $films = $Query->films;
        $updated_at = $Query->updated_at;

        if ($searchQuery == ''){
            // home page
            $searchMethod = 'getHome';
            $cacheTimeKey = 'HOME_CACHE_TIME';
        } else {
            $searchMethod = 'search';
            $cacheTimeKey = 'SEARCH_CACHE_TIME';
        }

        if ($updated_at->addSeconds($_ENV[$cacheTimeKey])->gt(Carbon::now()) && count($films)){
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

            return $Query->syncFilms($ids)->films;
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
