<?php

namespace SubLand\Models;
use Illuminate\Database\Eloquent\Model;
use SubLand\Traits\Escapable;

class User extends Model
{
    use Escapable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $guarded = [];

    protected $attributes = [
        'language' => 'farsi_persian'
    ];

    public function setLanguageAttribute($value)
    {
        $this->attributes['language'] = trim(strtolower($value));
    }

}