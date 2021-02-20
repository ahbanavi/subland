<?php


namespace SubLand\Models;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Result extends Pivot
{
    protected $table = 'film_query';
    protected $primaryKey = 'result_id';
    protected $foreignKey = 'query_id';
    protected $relatedKey = 'film_id';
    public $incrementing = true;

}
