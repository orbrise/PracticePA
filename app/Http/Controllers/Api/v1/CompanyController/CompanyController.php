<?php

namespace App\Http\Controllers\Api\v1\CompanyController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\CompanyConfig;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\UserRequest;
use App\Http\Resources\User as UserResource;
use Arr;
use App\OauthClient;
use App\Module;
use App\Http\Resources\Module\ModuleCollection;
use App\Http\Resources\Module\ModuleResource;
use App\ClientType;
use App\ServiceType;
use App\Client;
use App\Country;
use App\Company;
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\CompanyConfig\CompanyConfigResource;
use App\Http\Resources\Profile\ProfileResource;
use DB;
use GuzzleHttp\Client as GuzzleClient;
use File;
use App\ClientContact;
use App\Http\StaticFunctions\StaticFunctions;
use App\LoginUserRole;
use App\Staff;
use App\LoginRole;
use finfo;
use App\Models\CompanyInvite;
use App\Http\Controllers\Api\v1\EmailController\EmailController;


class CompanyController extends Controller
{
    const NEW = 'New';
    const USED = 'Used';
    const EXPIRED = 'Expired';
    const PENDING = 'Pending';

    public function getCompanyActiveModules(Request $req)
    {
        $data = (object) $req->only(['company_id']);
        $attributes = [
            'company_id' => 'Company ID'
        ];
        $validator = Validator::make((Array)$data ,[
            'company_id' => 'required|numeric',

        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        try
        {
            $getrecords = Company::where('company_id',$data->company_id)->get();
            foreach($getrecords as $module)
            {
                $modules[] = ['ModuleID'=>$module->module_id, 'ModuleSlug' => StaticFunctions::getModuleSlugFromID($module->module_id)];
            }
            return Response::json(['status' => 'success', 'data' => $modules]);
        } catch (\Exception $e)
        {
            return Response::json(['status' => 'error', 'data' => $e->getMessage()]);
        }
    }

    public function getCompanyProfile(Request $req)
    {
        if(isset($req->company_id) ) {
            $company_id = $req->company_id;
            $company = CompanyConfig::where(['company_id'=>$company_id])->get();
            if(count($company)>0) {
                $pre_data = CompanyConfigResource::collection($company);
                if(count($pre_data) > 0) {
                    foreach ($pre_data as  $key => $value) {
                        if($value->config_name == 'choose_logo') {
                            $newarray[$value->config_name] =  env("IMAGE_PATH")."/$company_id/$value->config_value";
                        } else {
                            $newarray[$value->config_name] = $value->config_value;
                        }
                    }
                }
                return Response::json(['status' => 'success', 'data' => $newarray]);
            } else {
                $errors['ErrorMessage'] = ['Company Does Not Exist'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['Company Not Found!!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function updateCompanyProfile(Request $request)
    {
        $data = $request->all();
        $attributes = [
            'company_name' => 'company name',
            'address' => 'address',
            'address1' => 'address1',
            'city' => 'city',
            'county' => 'county',
            'post_code' => 'post_Code',
            'country' => 'country',
            'phone' => 'phone',
            'website' => 'website',
            'minutesperunit' => 'minutesperunit',
            'choose_logo'=>'Logo',
        ];
        $validator = Validator::make($data ,[
            'company_name' => 'required|max:60',
            'address' => 'string',
            'city' => 'string|max:50',
            'county' => 'string|max:50',
            'post_code' => 'string',
            'country' => 'string|max:50',
            'phone' => 'numeric|digits_between:10,16',
            'website' => 'url',
            'minutesperunit' => 'numeric',
        ])->setAttributeNames($attributes);
        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $id = $request->company_id;
        $data_get = CompanyConfig::where('company_id',$id)->get();
        if(!empty($request->choose_logo)) {
            $exts = ['jpg' => 'image/jpg', 'png' => 'image/png', 'jpeg' => 'image/jpeg'];
            $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->choose_logo));
            $tmpFilePath=sys_get_temp_dir().'/'.uniqid();
            file_put_contents($tmpFilePath, $image_data);
            $ext = array_search(mime_content_type($tmpFilePath),$exts);
            if(!array_key_exists($ext, $exts)) {
                $errors['ErrorMessage'] = ['Image formate is invalid'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
            if(!file_exists(StaticFunctions::getImagePath(env('APP_ENV')).$request->company_id)) {
                mkdir(StaticFunctions::getImagePath(env('APP_ENV')).$request->company_id);
            }
            $imagename = 'profile'.$request->company_id.'_'.date('YmdHis').'.'.$ext;
            foreach ($data_get as $key => $value) {
                if($value->config_name == 'choose_logo') {
                    $old_image = $value->config_value;
                    if(!empty($old_image)){
                        $path =StaticFunctions::getImagePath(env('APP_ENV')).'/'.$request->company_id.'/'.$old_image;
                        if(file_exists($path)) {
                            unlink($path);
                        }
                    }
                    CompanyConfig::where(['company_id'=> $id,'config_name' => 'choose_logo' ])
                        ->update(['config_value'=>$imagename]);
                }
            }
            File::move($tmpFilePath, StaticFunctions::getImagePath(env('APP_ENV'))."/$request->company_id/$imagename");
        }
        $pre_data = CompanyConfig::where(['company_id'=>$id])->get();
        if(count($pre_data) > 0) {
            foreach ($pre_data as  $key => $value) {
                if($value->config_name == 'company_name') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->company_name]);
                }
                if ($value->config_name == 'first_name') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->first_name]);
                }
                if ($value->config_name == 'last_name') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->last_name]);
                }

                if ($value->config_name == 'address') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->address]);
                }
                if ($value->config_name == 'address1') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->address1]);
                }
                if ($value->config_name == 'city') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->city]);
                }
                if ($value->config_name == 'county') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->county]);
                }
                if ($value->config_name == 'post_code') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->post_code]);
                }
                if ($value->config_name == 'country') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->country]);
                }
                if ($value->config_name == 'phone') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->phone]);
                }
                if ($value->config_name == 'website') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->website]);
                }
                if ($value->config_name == 'minutesperunit') {
                    CompanyConfig::where(['company_id'=> $id, 'config_name' => $value->config_name ])
                        ->update(['config_value'=>$request->minutesperunit]);
                }
            }
            $message['SuccessMessage'] = ['Company Information Added Successfully !!'];
            return Response::json(['status' => 'success', 'data' => $message]);
        } else {
            $errors['ErrorMessage'] = ['Company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }




    public function CompanyConfig(Request $request)
    {
        $data = $request->all();
        $attributes = [
            'company_name' => 'company name',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email',
            'address' => 'address',
            'address1' => 'address1',
            'city' => 'city',
            'county' => 'county',
            'post_code' => 'post_Code',
            'country' => 'country',
            'phone' => 'phone',
            'website' => 'website',
            'minutesperunit' => 'minutesperunit',
            'choose_logo'=>'Logo',
            'module_id'=>'Module id',
            'user_id'=>'User id',
        ];
        $validator = Validator::make($data ,[
            'company_name' => 'required|max:60',
            'first_name' => 'required|string|max:50',
            'last_name'=>'required|string|max:50',
            'email' => 'required|email',
            'address' => 'required',
            'city' => 'string|max:50',
            'county' => 'required|string|max:50',
            'post_code' => 'required|string',
            'country' => 'required|string|max:50',
            'phone' => 'required|numeric|digits_between:10,16',
            'website' => 'url',
            'minutesperunit' => 'numeric',
            'module_id'=>'required',
            'user_id' => 'required|numeric',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $id = CompanyConfig::max('company_id');
        $id = $id+1;

        // $return_data['ConfigValue'] = $data_insert->config_value;
        $company_db = str_replace([':', '\\', '/', '*', ' '], '_', strtolower('devppa_'.StaticFunctions::clean($request->company_name)));
        $check = DB::select(" CALL createDB('$company_db') ");
        if (!empty($check)) {
            $company_db = $company_db.'_'.rand(1, 100000);
        }

        $data['company_database'] = $company_db;
        if(!empty($request->choose_logo)) {
            $exts = ['jpg' => 'image/jpg', 'png' => 'image/png', 'jpeg' => 'image/jpeg'];
            $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->choose_logo));
            $tmpFilePath=sys_get_temp_dir().'/'.uniqid();
            file_put_contents($tmpFilePath, $image_data);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($finfo, $tmpFilePath);
            $ext = array_search($fileType,$exts);
            if(!array_key_exists($ext, $exts)) {
                $errors['ErrorMessage'] = ['Image formate is invalid'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
            if(!file_exists(StaticFunctions::getImagePath(env('APP_ENV')).$id)) {
                mkdir(StaticFunctions::getImagePath(env('APP_ENV')).$id);
            }
            $imagename = 'profile'.$id.'_'.date('YmdHis').'.'.$ext;
            $path = StaticFunctions::getImagePath(env('APP_ENV'))."/$id/$imagename";
            File::move($tmpFilePath, $path);
        }

        foreach ($data as $key => $value)
        {
            if($key == 'choose_logo')
            {
                $data_insert = CompanyConfig::create(['company_id' => $id, 'config_name' => $key, 'config_value' =>$imagename]);
            }else {
                $data_insert = CompanyConfig::create(['company_id' => $id, 'config_name' => $key, 'config_value' => $value]);
            }
        }

        if($data_insert) {
            $company_data['company_id']=$id;
            $company_data['company_name']=$request->company_name;
            $company_data['module_id']=StaticFunctions::getModuleSlugByID($request->module_id);
            $company_data['user_id']=$request->user_id;
            $company_data['company_status']=Company::ACTIVE;
            $data_insert=Company::create($company_data);
            $user_update=User::where('user_id',$request->user_id)->update(['company_id'=>$id]);

        }

        $return_data['CompanyID'] = $id;
        $return_data['CompanyName'] = $request->company_name;

        DB::statement(" create database ".$company_db);
        DB::connection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        DB::connection()->getPdo()->exec("use ".$company_db);

        $db_setup = CompanyConfig::setupDatabase();
        foreach($db_setup as $query) {
            DB::statement($query);
        }

        StaticFunctions::db_connection(StaticFunctions::NewPa);
        $database = CompanyConfig::where('company_id',$id)->get();
        if($database){
            $company_db = StaticFunctions::GetKeyValue($database,'company_database');
            if($company_db) {
                $db_con = StaticFunctions::db_connection(strtolower($company_db));
                if($db_con) {
                    $clientContact = ClientContact::create([
                        'first_name'=>$data['first_name'],
                        'last_name'=>$data['last_name'],
                        'contact_designation'=>'Company Owner',
                        'contact_email'=>$data['email'],
                        'contact_phone_no'=>$data['phone'],
                        'company_id'=>$id,
                        'contact_type'=>'company'
                    ]);
                    $module_id = StaticFunctions::getModuleSlugByID($data['module_id']);
                    if(!empty($clientContact)){
                        $main_con = StaticFunctions::db_connection(StaticFunctions::NewPa);
                        if($main_con){
                            Staff::create([
                                'user_id'=>$request->user_id,
                                'role_id'=>LoginRole::PARTNER,
                                'module_id'=> $module_id,
                                'company_id'=>$id,
                            ]);
                            $owner_contact = CompanyConfig::create(['company_id'=>$id, 'config_name'=>'owner_contact_id','config_value'=>$clientContact->contact_id]);
                            if(!empty($owner_contact)) {
                                User::where(['user_id'=>$request->user_id,'company_id'=>$id])->update(['company_contact_id'=>$clientContact->contact_id,'user_status'=>User::ACTIVE]);
                                LoginUserRole::create(['user_id'=>$request->user_id,'role_id'=>LoginUserRole::PARTNER,'company_id'=>$id]);
                                Company::where('company_id',$id)->update(['company_status'=>User::ACTIVE]);
                                if(isset($request->invitation_code) && !empty($request->invitation_code))
                                {
                                    CompanyInvite::where(['invitation_code' => $request->invitation_code, 'module_id' => $module_id])->update(['module_status' => 'Accepted']);
                                }
                                return Response::json(['status' => 'success', 'data' => $return_data]);
                            }

                        } else {
                            $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
                            return Response::json(['status' => 'error', 'data' => $errors]);
                        }
                    } else {
                        $errors['ErrorMessage'] = ['clientContact failed to add !!'];
                        return Response::json(['status' => 'error', 'data' => $errors]);
                    }
                } else {
                    $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } else {
                $errors['ErrorMessage'] = ['Datasbese Not Found !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['Company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }


    public function GetCompanyModule(Request $req)
    {
        $data = Company::select('company_name', 'module_id')->where('user_id', $req->user_id)->with(['CompanyModule' =>
            function($query) {
                $query->select('id', 'name','logo','slug');
            }
        ])->get();
        return $data;
    }

    public static function GetCompany($key = '', $value = '' , $per_page = '')
    {

        $output = array();
        if(!empty($key) && !empty($value)) {
            $my_Api_Key = "g_uuzpfRIy_eBTUm2alXX13XVC0nnHlH8EO1gi0Z";
            $url = "https://api.companieshouse.gov.uk/";

            if($key == 'companykeyword') {
                $IPP = 25;
                $start_index = (!empty($per_page)) ? ($per_page - 1) * $IPP : 0;
                $param = 'search/companies?q='.$value.'&items_per_page='.$IPP.'&start_index='.$start_index;
            } else if($key == "companynumber") {
                $param = 'company/'.$value;
            } else if($key == 'companyofficers') {
                $param = 'company/'.$value.'/officers';
            } else if($key == 'companypersons') {
                $param = 'company/'.$value.'/persons-with-significant-control';
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url.$param);
            curl_setopt($curl, CURLOPT_USERPWD,$my_Api_Key);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
            if($errno = curl_errno($curl))
            {
                $error_message = curl_strerror($errno);
                echo "cURL error ({$errno}):\n {$error_message}";
            }
            curl_close($curl);
            $output = json_decode($response, true);
            if(count($output)>0){
                return $output;
            } else {
                $return_data['ErrorMessage'] = ['Company Not Found !!'];
                return Response::json(['status' => 'error', 'data' => $return_data]);
            }

        } else {
            $return_data['ErrorMessage'] = ['Parameters Can Not be Empty !!'];
            return Response::json(['status' => 'error', 'data' => $return_data]);
        }
    }

    public function Invitation(Request $req)
    {
        $data = $req->only(['first_name','company_id','user_id','modules','send_to','role_id','invite_type']);
        $attributes = [
            'company_id' => 'Company ID',
            'user_id' => 'User ID',
            'modules' => 'Modules',
            'send_to' => 'Invitation Email',
            'role_id' => 'Role ID',
            'invite_type' => 'Invitation Type',
            'first_name' => 'First Name'
        ];
        $messages = [
          'unique' => 'We already sent invitation to this email '.$data['send_to'],
        ];
        $validator = Validator::make($data ,[
        'company_id' => 'required|numeric',
            'user_id' => 'required|numeric',
            'modules' => 'required',
            //'send_to' => 'required|email/*|unique:company_invites,invitation_email*/',
            'send_to' => 'required|email',
            'role_id' => 'required|numeric',
            'invite_type' => 'required|string',
            'first_name' => 'required|string'
        ], $messages)->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        try
        {
                $invitation_code = str_random(20);
                foreach($data['modules'] as $val) {
                    $inviteData['company_id'] = $data['company_id'];
                    $inviteData['user_id'] = $data['user_id'];
                    $inviteData['invitation_code'] = $invitation_code;
                    $inviteData['first_name'] = $data['first_name'];
                    $inviteData['invitation_email'] = $data['send_to'];
                    $inviteData['invitation_role'] = $data['role_id'];
                    $inviteData['module_slug'] = $val;
                    //$inviteData['expiry_date'] = now()->addDays(2);
                    $inviteData['invite_type'] = $data['invite_type'];
                    $inviteData['invitation_status'] = self::NEW;
                    $inviteData['module_status'] = self::PENDING;
                    CompanyInvite::create($inviteData);
                }

            $emailData['company_name'] = StaticFunctions::getCompanyNameByID($data['company_id']);
            $emailData['invitation_code'] = $invitation_code;
            $emailData['first_name'] = $data['first_name'];
            $emailData['UrlPath'] = StaticFunctions::getFrontendPath(env('APP_ENV'));
            $layout = \View::make('emailslayout.invitationsend', ['data' => $emailData]);
            try {
                EmailController::send_default_email($data['send_to'], "Invitation email to join Bizpad", (string)$layout);
            } catch (\Exception $e)
            {
                $errormessage = $e->getMessage();
                return Response::json(['status' => 'error', 'data' => $errormessage]);
            }
            $responseData['SentTo'] = $data['send_to'];
            $responseData['SuccessMessage'] = 'Invitation has been sent successfully ';
            return Response::json(['status' => 'success', 'data' => $responseData]);


        } catch (\Exception $e)
        {
            $errormessage = $e->getMessage();
            return Response::json(['status' => 'error', 'data' => $errormessage]);
        }
    }



    public function updateInvitationStatus(Request $req)
    {
        $data = (object) $req->only(['invitation_id','invitation_code']);
        $attributes = [
            'invitation_id' => 'Invitation ID',
            'invitation_code' => 'Invitation Code',

        ];
        $validator = Validator::make((Array)$data ,[
            'invitation_id' => 'numeric',
            'invitation_code' => 'string'

        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
            if(!empty($data->invitation_id) || !empty($data->invitation_code))
            {
                try
                {
                    if(!empty($data->invitation_id))
                    {
                        CompanyInvite::where(['id' => $data->invitation_id])->update(['module_status' => 'Rejected']);

                    } else {
                        CompanyInvite::where(['invitation_code' => $data->invitation_code])->update(['module_status' => 'Rejected']);
                    }

                    return Response::json(['status' => 'success', 'data' => ['Module has been Rejected successfully']]);
                } catch (\Exception $e)
                {
                    return Response::json(['status' => 'error', 'data' => $e->getMessage()]);
                }
            } else {
                return Response::json(['status' => 'error', 'data' => ['Parameters are missing']]);
            }
    }


}
