<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Notes extends Model
{
    protected $table ='client_notes';
    protected $primaryKey = 'note_id';
    protected $fillable = [
        'client_id','note_date','note_time','telephone_conversation','user_id','service_id','due_date',
        'note_data','created_at','updated_at','module_id'
    ];

    public static $UserResourceFields = [
        'EditFor' => 0
    ];

    public function clientType()
    {
        return $this->hasOne(Client::class,'client_id','client_id');
    }
}
