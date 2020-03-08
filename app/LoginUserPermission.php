<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginUserPermission extends Model
{

	public function loginPermissions()
	{
		return $this->hasOne(LoginPermission::class,'id', 'permission_id');
	}

	

    public static function hasPemrission($user_id ='', $company_id ='', $module_id= '', $route = '')
    {
    $data = [];
    $permission = LoginUserPermission::with('loginPermissions')->where(['user_id' => $user_id, 'company_id' => $company_id, 'module_id' => $module_id])->get();
        foreach($permission as $key => $val) {
        	if($route == $val->loginPermissions->route):
        	$data = $val->loginPermissions->route;
        endif;
        }
        return $data;
    }
}
