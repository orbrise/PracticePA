<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $primaryKey = 'noti_id';
    protected $fillable = [
        'noti_id','module_id','section','noti_type','noti_title','user_by','read_status','status','created_at','updated_at'
    ];
}
