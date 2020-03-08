<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientNotification extends Model
{
    protected $table ='client_notifications';
    protected $primaryKey = 'noti_id';
    protected $fillable = [
    	'client_id','noti_type','noti_title','noti_desc','client_id','status','created_at','updated_at','module_id'
    ];
}
