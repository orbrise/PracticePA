<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table ='staffs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id','company_id','role_id','module_id','report_to','status','created_at','updated_at'
    ];

    public static $UserResourceFields = [
        'EditFor' => 0
    ];


    public function user()
    {
    	return $this->hasOne(User::class, 'user_id','user_id');
    }


}
