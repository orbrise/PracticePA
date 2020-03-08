<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'country_code','country_name', 'created_at', 'updated_at',
    ];
}
