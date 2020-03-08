<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientService extends Model
{
    protected $table ='client_services';
    protected $primaryKey = 'cs_id';
    protected $fillable = [
        'client_id','service_name','service_id','initial_date','duration_month','duration_day','service_type','service_track','repeat_type','repeat_number','require_confirm','created_at','updated_at'
    ];

    const INACTIVE = 'Inactive';
    const ACTIVE = 'Active';
  
    public function get_job()
    {
    	return $this->belongsTo(ClientJob::class, 'cs_id', 'cs_id')->select('start_date','year_end','due_date');
    }

    
}
