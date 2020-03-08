<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientJob extends Model
{
	const JOB_NEW = 'New'; 
	const JOB_ASSIGNED = 'Assigned'; 
	const JOB_SUBMITTED = 'Submitted'; 
	const JOB_COMPLETED = 'Completed'; 
	const JOB_DELAYEA = 'Delayed'; 
	const JOB_EXPIRED = 'Expired'; 
	const JOB_CANCELLED = 'Cancelled'; 

    protected $table ='client_jobs';
    protected $primaryKey = 'job_id';
    protected $fillable = [
        'cs_id','start_date','year_end','due_date','assigned_to','assigned_date','assigned_due_date','job_status','completed_by','completed_on','confirmed_on','user_id','client_id','can_contact_client','is_documents_received','last_checked','created_at','updated_at'
    ];

       public function client()
       {
       		return $this->belongsTo(Client::class,'client_id','client_id');
       }

	   public function get_service()
    {
    	return $this->hasOne(ClientService::class, 'cs_id', 'cs_id')->where('service_track','Tracked');
    }
}
