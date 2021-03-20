<?php

namespace SubLand\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Search Query
 *
 * @mixin Builder
 */
class Query extends Model
{
    protected $table = 'search_queries';
    protected $primaryKey = 'query_id';

    protected $guarded = [];

    public function films()
    {
        return $this->belongsToMany('SubLand\Models\Film',null,'query_id','film_id')
            ->using('SubLand\Models\Result')->withTimestamps();
    }

    /**
     * @param int[] $ids
     * @return Query
     */
    public function syncFilms(array $ids)
    {
        $this->films()->sync($ids);
        $this->touch();
        $this->refresh();

        return $this;
    }

}
