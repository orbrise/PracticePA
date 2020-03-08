<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginPermission extends Model
{
    public static function getPermissionNameByID($id='')
	{
		$obj = LoginPermission::find($id);
		return $obj->route;
	}
}
