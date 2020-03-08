<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'permission_name_id','company_id','module_id','permission_level','target_id','created_at','updated_at',
    ];
}
