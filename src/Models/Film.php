<?php


namespace SubLand\Models;
use Illuminate\Database\Eloquent\Model;
use SubLand\Traits\Escapable;
use SubLand\Utilities\Subscene;

class Film extends Model
{
    use Escapable;

    protected $table = 'films';
    protected $primaryKey = 'film_id';

    protected $guarded = [];

    public function results()
    {
        return $this->belongsToMany('SubLand\Models\Result');
    }

    public function subtitles()
    {
        return $this->hasMany('SubLand\Models\Subtitle','film_id','film_id');
    }
}
