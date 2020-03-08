<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeSheet extends Model
{
    protected $table ='timesheet';
    protected $primaryKey = 'sheet_id';
    const WIP = 'WIP';
    const REPOST = 'Repost';
    const POSTED = 'Posted';
    const INVOICED = 'Invoiced';
    protected $fillable = [
        'employee_id','client_id','job_id','module_id','work_date','work_desc','work_unit','work_unit_minutes','charge_out_rate','time_start','time_end','work_type','post_status','post_date','repost_allowed_by','repost_datetime',
    ];

    public function client()
    {
    	return $this->belongsTo(Client::class,'client_id','client_id');
	}

	public function get_job()
    {
    	return $this->belongsTo(ClientJob::class,'job_id','job_id');
	}
     public function get_service()
    {
        return $this->hasOne(ClientService::class, 'cs_id', 'job_id');
    }
}
