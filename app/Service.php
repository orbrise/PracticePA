<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table ='services';
    protected $primaryKey = 'service_id';
    const OPTIONAL = 'Optional';
    const TRACKED = 'Tracked';
}
