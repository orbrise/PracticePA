<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $table ='service_types';
    protected $primaryKey = 'service_type_id';
    protected $fillable = [
        'service_type_name','client_type_id','created_at', 'updated_at',
    ];

    public function client_type()
    {
    	return $this->belongsTo('App\ClientType', 'client_type_id','id');
    }

}
