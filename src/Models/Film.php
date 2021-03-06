<?php

namespace SubLand\Models;

use Illuminate\Database\Eloquent\Model;
use SubLand\Traits\Escapable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Film
 *
 * @mixin Builder
 */
class Film extends Model
{
    use Escapable;

    protected $table = 'films';
    protected $primaryKey = 'film_id';

    protected $guarded = [];

    public function subtitles()
    {
        return $this->hasMany(Subtitle::class,'film_id','film_id');
    }
}
