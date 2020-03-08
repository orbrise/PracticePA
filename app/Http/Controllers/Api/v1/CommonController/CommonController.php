<?php

namespace App\Http\Controllers\Api\v1\CommonController;

use App\Http\StaticFunctions\StaticFunctions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\CompanyConfig;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\UserRequest;
use App\Http\Resources\User as UserResource;
use App\OauthClient;
use App\Module;
use App\Http\Resources\Module\ModuleCollection;
use App\Http\Resources\Module\ModuleResource;
use App\ClientType;
use App\ServiceType;
use App\Client;
use App\Country;
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\CompanyConfig\CompanyConfigResource;
use App\Http\Resources\Profile\ProfileResource;
use DB;
use App\LoginRole;
use App\Models\Route;
use App\Http\Resources\Route as RouteResource;
use App\Permission;
use App\Models\PermissionName;
use App\Http\Resources\Permission as PermissionResource;
use App\Permission as PermissionModel;

class CommonController extends Controller
{
   public function getUser(Request $req)
   {
      return new UserResource(User::find(Auth::user()->user_id), User::$UserResourceFields);
   }

   public function getmodules()
   {
      return ModuleResource::collection(Module::where('status', 'Active')->get());
           //return new ModuleCollection(Module::all());
   }

   public function CountryList()
   {
      $countries=Country::get();
      if(count($countries)>0){
         $data = CountryResource::collection($countries);
         return Response::json(['status' => 'success', 'data' => $data]);
      } else {
         $errors['ErrorMessage'] = ['Countrys Does Not Exist'];
         return Response::json(['status' => 'error', 'data' => $errors]);
      }
   }

   public function getUserRoles()
   {
        $result =  LoginRole::select('role_id as RoleID','role_name as RoleName')->get();
        return Response::json(['status' => 'success', 'data' => $result]);
   }

   public function getRoutes(Request $req)
   {
       $data = (object) $req->only('module');
       $routes = Route::where('module', 'none')->orWhere('module', $data->module)->get();
       $RouteResource = RouteResource::collection($routes);
       return Response::json(['status' => 'success', 'data' => $RouteResource]);
   }
    public function AddNewPermission(Request $req)
    {
         $fillterRequest =  $req->only(['permission_name_id','company_id','module_id','target_id','permission_level']);
         $data = (object) $fillterRequest;

        $attributes = [
            'permission_name_id' => 'Permission Name',
            'company_id' => 'Company ID',
            'module_id' => 'Module ID',
            'target_id' => 'Target ID',
            'permission_level' => 'Role Level'
        ];
        $validator = Validator::make($fillterRequest ,[
            'company_id' => 'required',
            'module_id' => 'required',
            'target_id' => 'required',
            'permission_level' => 'required'
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        try{
            $fillterRequest['module_id'] = StaticFunctions::getModuleSlugByID($data->module_id);
            $conditions = [
                'company_id' => $data->company_id,
                'module_id' => StaticFunctions::getModuleSlugByID($data->module_id),
                'target_id' => $data->target_id,
                'permission_level' => $data->permission_level
            ];
             $check  = PermissionModel::where($conditions)->first();
            if(!empty($check))
            {
                PermissionModel::where('id', $check->id)->update(['permission_name_id' => $data->permission_name_id]);
            } else {
                PermissionModel::create($fillterRequest);
            }
            return Response::json(['status' => 'success', 'data' => ['Permissions Applied Successffully']]);
        } catch (\Exception $e) {return Response::json(['status' => 'error', 'data' => $e->getMessage()]);}


    }

    public function getPermissionNames()
    {
        return PermissionName::get();
    }

    public function getPermissions(Request $req)
    {
        $fillterRequest =  $req->only(['company_id','module_id','target_id','permission_level']);
         $data = (object) $fillterRequest;
        $attributes = [
            'company_id' => 'Company ID',
            'module_id' => 'Module ID',
            'target_id' => 'Target ID',
            'permission_level' => 'Role Level'
        ];
        $validator = Validator::make($fillterRequest ,[
            'company_id' => 'required',
            'module_id' => 'required',
            'target_id' => 'required',
            'permission_level' => 'required'
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        try {
            $conditions = [
                'company_id' => $data->company_id,
                'module_id' => StaticFunctions::getModuleSlugByID($data->module_id),
                'target_id' => $data->target_id,
                'permission_level' => $data->permission_level
            ];
            $check  = PermissionModel::where($conditions)->first();

            if(!empty($check->permission_name_id))
            {
                $explodedata = explode(',',$check->permission_name_id);
                 $permissionnames = PermissionName::whereIn('id', $explodedata)->get();

            }
              /*$permissions = Permission::where(['company_id' => $data->company_id,
                                        'module_id' => StaticFunctions::getModuleSlugByID($data->module_id),
                                        'target_id' => $data->target_id,
                                        'permission_level' => $data->permission_level])->get();*/
            if(count($permissionnames)< 0)
            {
                $error['ErrorMessage'] = ['permissions not found'];
                return Response::json(['status' => 'error', 'data' => $error]);
            } else {

                return Response::json(['status' => 'success', 'data' => $permissionnames]);
            }

        } catch(\Exception $e) {return Response::json(['status' => 'error', 'data' => $e->getMessage()]); }
    }


}
