<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Client extends Model
{
    protected $table ='client';
    protected $primaryKey = 'client_id';
    public const GC_ACCESS_TOKEN = "sandbox_V2dQiSmfwC0D3Wr-iSl_m8kL-jwCRpKFz0qq40BC" ;
    protected $fillable = [
        'company_id','client_name','client_code','client_code_prefix','trade_id','user_id','manager_id',
        'staff_id','payroll_id','registration_no','status','client_acquired','utr','paye_ref',
        'paye_account_office_ref','vat','prepare_letter','client_type','service_type','company_auth_code',
        'created_at','updated_at','module_id'
    ];

    public function code()
    {
        return $this->belongsTo(ClientCode::class, 'client_id', 'client_id');
    }


    public function get_service()
    {
        return $this->hasMany(ClientService::class, 'client_id', 'client_id')->where('service_status','Active');
    }


}
