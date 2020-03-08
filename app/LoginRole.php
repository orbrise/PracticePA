<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginRole extends Model
{
    protected $primaryKey = 'role_id';
    const OWNER = 1;
    const PARTNER = 2;
    protected $fillable = [
        'role_id','role_name',
    ];
    // const PARTNER = 2;
    // const PARTNER = 2;
    // const PARTNER = 2;
    // const PARTNER = 2;
    // const PARTNER = 2;
    // const PARTNER = 2;
}
