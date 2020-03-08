<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class Company extends Authenticatable
{
    use Notifiable,HasApiTokens;
    protected $table = 'company';
    protected $primaryKey = 'company_id';
    const ACTIVE = 'Active';
    const UNVERIFIED = 'Unverified';

    protected $fillable = [
        'company_id','module_id','user_id','company_name','company_status','created_at', 'updated_at','billing_status',
        'mandate','gocardless_customer',
    ];

    public function CompanyModule()
    {
    	return $this->belongsTo(Module::class,'module_id','id');;
    }


}
