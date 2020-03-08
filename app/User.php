<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,HasApiTokens;
    protected $table = 'login_users';
    protected $primaryKey = 'user_id';
    const ACTIVE = 'Active';
    const UNVERIFIED = 'Unverified';
    const PAIDACCOUNT = 'Paid';
    const FREEACCOUNT = 'Free';
    const Owner =1;
    const Partner =2;
    const Manager = 3;
    const Senior = 4;
    const Junior = 5;
    const Consultant = 6;
    const PayrollManager = 7;


    protected $fillable = [
        'first_name','last_name', 'user_email', 'user_password','phone', 'city', 'county', 'postal_code','country','account_type', 'user_status', 'expiry_date', 'verification_code','verification_code_expiry','forget_code','forget_code_created_at', 'last_login_ip', 'last_login_time', 'remember_token', 'subscribe_newsletter',
        'created_at', 'updated_at','charge_out_rate','company_id'
    ];

    public function getAuthPassword(){
        return $this->user_password;
    }

    public static $UserResourceFields = [
        'UserId' => 1,
        'FirstName' => 1,
        'LastName' => 1,
        'UserEmail' => 1,
        'UserStatus' => 1,
        'VerificationCode' => 0
        ];

    public function loginRolePartner()
    {
        return $this->hasMany(LoginUserRole::class, 'company_id', 'company_id')->where('role_id',2);
    }

    public function loginRoleManager()
    {
        return $this->hasMany(LoginUserRole::class, 'company_id', 'company_id')->where('role_id',3);
    }
    public function loginPayRoleManager()
    {
        return $this->hasMany(LoginUserRole::class, 'company_id', 'company_id')->where('role_id',7);
    }

    

}
