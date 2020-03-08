<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginUserRole extends Model
{
	const OWNER = 1;
    const PARTNER = 2;
    protected $fillable = [
        'user_id','role_id','company_id', 'created_at', 'updated_at',
    ];
    public function getusername()
    {
        return $this->hasOne(User::class, 'user_id','user_id');
    }
}
