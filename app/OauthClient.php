<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OauthClient extends Model
{
    protected $table = 'oauth_clients';

    protected $fillable = [
        'user_id','name','secret', 'redirect','personal_access_client' ,'password_client','revoked',
        'created_at', 'updated_at',
    ];
}
