<?php

namespace App\Models;

use App\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyInvite extends Model
{
    protected $fillable = [
        'id','company_id','user_id','invitation_code','first_name','invitation_email','signup_email','invitation_role',
        'module_slug','expiry_date','invite_type','invitation_status','module_status'
    ];

    public function getCompanyName()
    {
        return $this->hasOne(Company::class,'company_id','company_id');
    }
}
