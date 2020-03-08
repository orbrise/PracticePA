<?php

namespace App\Http\Controllers\Api\v1;

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
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\CompanyConfig\CompanyConfigResource;
use App\Http\Resources\Profile\ProfileResource;

class AuthController extends Controller
{

    public function Register(Request $req)
    {
        //filter request
        $FilterRequest = ['first_name', 'last_name','user_email', 'phone','user_password','c_password'];
        $data = $req->only($FilterRequest);

        // changing the input fields name
        $attributes = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'user_email' => 'Email Address',
            'phone' => 'Phone',
            'user_password' => 'Password',
            'c_password' => 'Password Confirmation ',
        ];
        $validator = Validator::make($data ,[
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'user_email' => 'required|email|unique:login_users,user_email',
            'phone' => 'required|numeric|digits_between:10,16',
            'user_password' => 'required|min:6',
            'c_password' => 'required|same:user_password',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $data['user_password'] = bcrypt($data['user_password']);
        unset($data['c_password']);
        $data['user_status'] = User::UNVERIFIED;
        $data['verification_code'] = str_random(30);
        $data['verification_code_expiry'] = now()->addDays(2);
        $user = User::create($data);

        $oauth_client = OauthClient::create([
            'user_id'                => $user->user_id,
            'id'                     => $user->user_email,
            'name'                   => $user->first_name,
            'secret'                 => base64_encode(hash_hmac('sha256',$data['user_password'], 'secret', true)),
            'password_client'        => 1,
            'personal_access_client' => 0,
            'redirect'               => '',
            'revoked'                => 0,
        ]);
        Arr::set(User::$UserResourceFields,'VerificationCode','1');
        $userdata = new UserResource(User::find($user->user_id), User::$UserResourceFields);
        return Response::json(['status' => 'success', 'data' => $userdata]);
    }

    public function Login(Request $req)
   {
       $attributes = [
           'email' => 'Email Address ',
           'password' => 'Password'
       ];
       $validator = Validator::make($req->only(['email', 'password']) ,[
           'email' => 'required|email|exists:login_users,user_email',
           'password' => 'required'
       ])->setAttributeNames($attributes);
       if($validator->fails())
       {
           return Response::json(['status' => 'error', 'data' => $validator->errors()]);
       }
       $email = filter_var($req->email, FILTER_SANITIZE_EMAIL);
       if(Auth::attempt(['user_email' => $email, 'password' => $req->password])){
           $user = Auth::user();
           if($user->user_status != "Active"){
           if ( $user->user_status == 'Unverified' && $user->verification_code_expiry < now()) {
                $verification_new_code = str_random(30);
                $verification_new_code_expiry = now()->addDays(2);
                $success_update = User::where(['user_id'=>$user->user_id])->update(['verification_code'=>$verification_new_code,'verification_code_expiry'=>$verification_new_code_expiry]);
                if($success_update){
                    $data['Email'] = $user->user_email;
                    $data['verificationCode'] =$verification_new_code;
                    $data['verificationExpiryDate'] =$verification_new_code_expiry;
                    $data['Name'] = $user->first_name;

                    return Response::json(['status' => 'ExpireCode' ,'data' =>  $data]);
                }else{
                   $errors['ErrorMessage'] = ['Error while updating Verification Code!!'];
                   return Response::json(['status' => 'error', 'data' => $errors]);
                }
           }else{
                if ($user->user_status == 'Expired') {
                    $message['ErrorMessage'] = ['Your account Expired.'];
                    return Response::json(['status' => 'error' ,'data' =>  $message]);
                }
                else if($user->user_status == 'Suspended'){
                    $message['ErrorMessage'] = ['Your account Suspended.'];
                    return Response::json(['status' => 'error' ,'data' =>  $message]);
                }else{
                    $message['ErrorMessage'] = ['We sent you a verification email, please verify your email for login.'];
                    return Response::json(['status' => 'error' ,'data' =>  $message]);
                }
           }
       }
          $success['UserID'] = $user->user_id;
          $success['FirstName'] = $user->first_name;
          $success["UserEmail"] = $user->user_email;
          $success["UserEmail"] = $user->user_email;
          $success['CompanyId'] = $user->company_id;
          $success['Token'] = $user->createToken($email)->accessToken;
           return Response::json(['status' => 'success', 'data' => $success]);
       } else {
           $errors['ErrorMessage'] = ['Password is Incorrect! Try Again'];
           return Response::json(['status' => 'error', 'data' => $errors]);
       }
   }



public function getUser(Request $req)
{
    return new UserResource(User::find(Auth::user()->user_id), User::$UserResourceFields);
}

public function Activation($code = '')
{
         // check code empty 
    if(!empty($code) ) {
           //verification code and user status is Unverified
        $user_varify = User::where(['verification_code'=>$code])->first();
        if($user_varify || $user_varify != null)
        {
           if($user_varify->user_status=='Active')
           {
            $errors['ErrorMessage'] = ['You Already Active!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
        else
        {
            $expiry_date = $user_varify->verification_code_expiry;
                // check expiry verification code date greater then from current date otherwise regenerate verification code
            if( $expiry_date > now())
            {
                $success = User::where(['user_id'=>$user_varify->user_id,'verification_code'=>$code])->update(['user_status' => 'Active']);
                Arr::set(User::$UserResourceFields,'VerificationCode','1');
                $data = new UserResource(User::find($user_varify->user_id),User::$UserResourceFields );
                return Response::json(['status' => 'success', 'data' => $data]);
            }
            else 
            {
                $new_code = str_random(30);
                $data['verification_code']=$new_code;
                $data['verification_code_expiry']=now()->addDays(2);
                $success = User::where(['user_id'=>$user_varify->user_id])->update($data);
                $message['message'] = 'Verification code is expired New Code is sent to you by email, please verify.';
                $message['NewCode'] = $new_code;
                $message['valid_till'] = now()->addDays(2);

                return Response::json(['status' => 'error', 'data' => $message]);
            }
        } 
    }
    else 
    {
        $errors['ErrorMessage'] = ['Verification Code Not Found!!'];
        return Response::json(['status' => 'error', 'data' => $errors]);
    } 
}
else 
{
    $errors['ErrorMessage'] = ['Verification Code Not Found!!'];
    return Response::json(['status' => 'error', 'data' => $errors]);
} 
}

public function ForgotPassword(Request $req)
{
    $attributes = [
        'user_email' => 'Email Address',
    ];

    $validator = Validator::make($req->all() ,[
        'user_email' => 'required',
    ])->setAttributeNames($attributes);

    if($validator->fails())
    {
        return Response::json(['status' => 'error', 'data' => $validator->errors()]);
    }

    $verify_email = User::where('user_email',$req->user_email)->first();
    if($verify_email || $verify_email != null) {
        $new_code = str_random(30);
        $update_code = User::where('user_email',$req->user_email)->update(['forget_code'=>$new_code,'forget_code_created_at'=>now()]);
        if($update_code) {
            $updated_data = User::where('user_email', $req->user_email)->first();
            $data['Email'] = $updated_data->user_email;
            $data['ForgetCode'] = $updated_data->forget_code;
            $data['ForgetCodeCreatedAt'] = $updated_data->forget_code_created_at;
            return Response::json(['status' => 'success', 'data' => $data]);
        } else {
            $errors['ErrorMessage'] = ['Network error try again!!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    } else {
        $errors['ErrorMessage'] = ['Email Not Found!!'];
        return Response::json(['status' => 'error', 'data' => $errors]);
    }
}

public function NewPassword(Request $req)
   {
       $data = $req->all();
       $attributes = [
           'user_password' => 'Password',
           'c_password' => 'Password Confirmation ',
       ];
       $validator = Validator::make($data ,[
           'user_password' => 'required|min:6',
           'c_password' => 'required|same:user_password',
       ])->setAttributeNames($attributes);

       if($validator->fails())
       {
           return Response::json(['status' => 'error', 'data' => $validator->errors()]);
       }
       $verify_user = User::where('forget_code',$req->forget_code)->first();
       if($verify_user  && !empty($verify_user->forget_code)){
           if(!empty($req->user_password)){
               $update_password = User::where('forget_code',$verify_user->forget_code)->update(['user_password'=>bcrypt($req->user_password)]);
               if($update_password){
                   $update = User::where('user_id',$verify_user->user_id)->update(['forget_code' => '']);
                   $data = User::where('user_id',$verify_user->user_id)->first();
                   $user['Email']= $data->user_email;
                   $user['Name']= $data->first_name;
                   $user['LastName']= $data->last_name;
                   return Response::json(['status' => 'success', 'data' => $user]);
               } else {
                   $errors['ErrorMessage'] = ['Network error try again!!'];
                   return Response::json(['status' => 'error', 'data' => $errors]);
               }
           } else {
               $errors['ErrorMessage'] = ['Link Expired!!'];
               return Response::json(['status' => 'error', 'data' => $errors]);
           }
       } else {
           $errors['ErrorMessage'] = ['Link Expired!!'];
           return Response::json(['status' => 'error', 'data' => $errors]);
       }
   }

    public function CheckStatus(Request $req)
   {
       $errors=[];
       $data=[];

       if( !empty($req->user_id) || !empty($req->user_email) ){

           $user = User::orWhere(['user_id'=>$req->user_id,'user_email'=>$req->user_email])->first();

           if(empty($user)){
               $errors['ErrorMessage'] = 'User Does Not Exist!!';
           } else {

               if(!empty($req->account_type) && isset($req->account_type) ){
                   if($user->account_type){
                       $data['AccountType'] = $user->account_type;
                   } else {
                       $errors['ErrorMessage'] = 'Acount Not Found!!';
                   }
               }

               if(!empty($req->verification_code) && isset($req->verification_code)){
                   if(!empty($user->verification_code)){
                       $data['EmailVerificationCode'] = $user->verification_code;
                       $data['EmailVerificationCodeExpiredDate'] = $user->verification_code_expiry;
                   } else {
                       $errors['ErrorMessage'] = 'VerificationCode Expired!!';
                   }
               }

               if(!empty($req->forget_code) && isset($req->forget_code)) {
                   if(!empty($user->forget_code)){
                       $data['ForgetCode'] = $user->forget_code;
                   } else {
                       $errors['ErrorMessage'] = 'ForgetCode Expired!!';
                   }
               }

               if(!empty($req->user_status) && isset($req->user_status) ){
                   if(!empty($user->user_status)){
                       $data['UserStatus'] = $user->user_status;
                   } else {
                       $errors['ErrorMessage'] = 'UserStatus Not Found!!';
                   }
               }

               $alldata = array_merge($data,$errors);
               return Response::json(['status' => 'success', 'data' => $alldata]);
           }

       } else {
           $errors['ErrorMessage'] = ['Email or Id Connot be Empty!!'];
           return Response::json(['status' => 'error', 'data' => $errors]);
       }
   }

public function getmodules(){
    return ModuleResource::collection(Module::where('status', 'Active')->get());
        //return new ModuleCollection(Module::all());
}

public function Logout(Request $request)
{
    $request->user()->token()->revoke();
    $success['SuccessMessage'] = ['Logout Successfully'];
    return Response::json(['status' => 'success', 'data' => $success]);
}



public function clientType(Request $req)
{
    $attributes = ['type' => 'Client Type'];
    $validator = Validator::make($req->all() ,[
        'type' => 'required|max:50|unique:client_type,type',
    ])->setAttributeNames($attributes);

    if($validator->fails())
    {
        return Response::json(['status' => 'error', 'data' => $validator->errors()]);
    }

    $data = ClientType::create(['type'=>$req->type]);
    if($data){
        $Client['ClientType'] = $data->type;
        return Response::json(['status' => 'success', 'data' => $Client]);
    } else {
        $errors['ErrorMessage'] = ['Unable to process your request plz try again !'];
        return Response::json(['status' => 'error', 'data' => $errors]);
    }

}

public function serviceType(Request $req)
{
    $FilterRequest = ['service_type_name', 'client_type_id'];
    $data = $req->only($FilterRequest);

        // changing the input fields name
    $attributes = [
        'service_type_name' => 'Service Type',
        'client_type_id' => 'Client Type',
    ];
    $validator = Validator::make($data ,[
        'service_type_name' => 'required|max:50',
        'client_type_id' => 'required|numeric|max:11',
    ])->setAttributeNames($attributes);

    if($validator->fails())
    {
        return Response::json(['status' => 'error', 'data' => $validator->errors()]);
    }

    $record  = ServiceType::where(['service_type_name'=>$req->service_type_name,'client_type_id'=>$req->client_type_id])->first();
    if($record){
        $errors['ErrorMessage'] = ['Already Service Type Exist !!'];
        return Response::json(['status' => 'error', 'data' => $errors]);
    } else {

        $data = ServiceType::create(['service_type_name'=>$req->service_type_name,'client_type_id'=>$req->client_type_id]);
        if($data){
            $new_data = ServiceType::with('client_type')->where('service_type_id',$data->service_type_id)->first();
            if($new_data){
                $service['ClientType'] = $new_data->client_type->type;
                $service['ServiceType'] = $new_data->service_type_name;
                $service['ClientId'] = $new_data->client_type->id;
                return Response::json(['status' => 'success', 'data' => $service]);
            } else {
                $errors['ErrorMessage'] = ['Record Not Found !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }

        } else {
            $errors['ErrorMessage'] = ['Unable to process your request plz try again !'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

}

public function clientAdd(Request $req)
{
                //filter request
    $FilterRequest = ['company_id','trade_id','client_name','client_type','service_type','user_id','registration_no','partner_id','manager_id','pay_role','client_code_prefix','code_digit','client_acquired','utr'];
    $data = $req->only($FilterRequest);

        // changing the input fields name
    $attributes = [
        'company_id' => 'company id',
        'trade_id' => 'trade id',
        'client_name' => 'client name',
        'client_type' => 'client type',
        'service_type' => 'service type',
        'registration_no' => 'registration no',
        'partner_id' => 'partner id',
        'manager_id' => 'manager id',
        'pay_role' => 'pay role',
        'client_code_prefix' => 'client code prefix',
        'code_digit' => 'code digit',
        'client_acquired'=> 'client acquired',
        'utr'=> 'utr'

    ];
    $validator = Validator::make($data ,[
        'trade_id' => 'required|max:11',
        'client_name' => 'required|max:50',
        'client_type' => 'required',
        'service_type' => 'required',
        'registration_no' => 'required|max:11',
        'partner_id' => 'required|max:11',
        'client_code_prefix' => 'required|alpha|max:1',
        'code_digit' => 'required',
        'client_acquired'=> 'required|date',

    ])->setAttributeNames($attributes);

    if($validator->fails())
    {
        return Response::json(['status' => 'error', 'data' => $validator->errors()]);
    }

    $data = Client::create($data);
    if($data){

                // $service['ClientType'] = $data->client_type;
                // $service['ServiceType'] = $data->service_type;
                // $service['ClientId'] = $data->client_id;
        return Response::json(['status' => 'success', 'data' => $data]);

    } else {
        $errors['ErrorMessage'] = ['Record Not Found !!'];
        return Response::json(['status' => 'error', 'data' => $errors]);
    }
}
    public function getCompanyProfile($company_id = '')
    {
        if(!empty($company_id) ) {
            $company = CompanyConfig::where(['company_id'=>$company_id])->get();
            if(count($company)>0){
              $pre_data = CompanyConfigResource::collection($company);
              if(count($pre_data) > 0) {
                  foreach ($pre_data as  $key => $value) {
                    $newarray[$value->config_name] = $value->config_value;
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
           'company_id'=>'company id'

        ];

        $validator = Validator::make($data ,[
           'company_name' => 'required|max:60',
           'first_name' => 'required|alpha|max:50',
           'last_name'=>'required|alpha|max:50',
           'email' => 'required|email',
           'address' => 'required',
           'city' => 'alpha|max:50',
           'county' => 'required|alpha|max:50',
           'post_code' => 'required',
           'country' => 'required|alpha|max:50',
           'phone' => 'required|numeric|digits_between:10,16',
           'website' => 'required',
           'minutesperunit' => 'required|numeric',
           'company_id' => 'required|numeric',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
           return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $id = $request->company_id;
         $data_get = CompanyConfig::where('company_id',$id)->get();
        
        if($request->hasFile('choose_logo')) {
          $images = $request->file('choose_logo');
          $ext = $request->file('choose_logo')->getClientOriginalExtension();
          $imagename = 'profile'.$request->company_id.'_'.date('YmdHis').'.'.$ext;
          foreach ($data_get as $key => $value) {
            if($value->config_name == 'choose_logo') {
              $old_image = $value->config_value;
              $path ='images/company/profiles/'.$request->company_id.'/'.$old_image;
              if(file_exists($path)) {
                unlink($path);
              }
            }
          }
          
          $images->move('images/company/profiles/'.$request->company_id.'/', $imagename);
        }
        
        foreach($data_get as $key => $value) {
          if($value->config_name == 'choose_logo') {
            $data['choose_logo'] = $value->config_value;
            $old_img = $value->config_value;
          }
        }
         
        $data_delete = CompanyConfig::where('company_id',$id)->delete();
        
        foreach ($data as $key => $value) {
          if($key == 'choose_logo') {
            $data_update = CompanyConfig::where('company_id',$id)
            ->create(['company_id'=>$id,'config_name'=>$key,'config_value'=>isset($imagename)?$imagename:$old_img]);
          } else {
            $data_update =  CompanyConfig::where('company_id',$id)
            ->create(['company_id'=>$id,'config_name'=>$key,'config_value'=>$value]);
          }
        }
        
        if($data_update){
          $company = CompanyConfig::where(['company_id'=>$id])->get();
          $pre_data = CompanyConfigResource::collection($company);
          if(count($pre_data) > 0) {
            foreach ($pre_data as  $key => $value) {
              $newarray[$value->config_name] = $value->config_value;
            }
          }
            return Response::json(['status' => 'success', 'data' => $newarray]);
        } else {
            $errors['ErrorMessage'] = ['CompanyConfig fial to update'];
            return Response::json(['status' => 'error', 'data' => $errors]); 
        }
    }


public function CompanyConfig(Request $request)
{
          // changing the input fields name
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

   ]; 
   $validator = Validator::make($data ,[
       'company_name' => 'required|max:60',
       'first_name' => 'required|alpha|max:50',
       'last_name'=>'required|alpha|max:50',
       'email' => 'required|email',
       'address' => 'required',
       'city' => 'alpha|max:50',
       'county' => 'required|alpha|max:50',
       'post_code' => 'required|numeric',
       'country' => 'required|alpha|max:50',
       'phone' => 'required|numeric|digits_between:10,16',
       'website' => 'required',
       'minutesperunit' => 'required|numeric',
   ])->setAttributeNames($attributes);

   if($validator->fails())
   {
       return Response::json(['status' => 'error', 'data' => $validator->errors()]);
   }

   $id = CompanyConfig::max('company_id');
   $id = $id+1;

   foreach ($data as $key => $value) {
    $data_insert=CompanyConfig::create(['company_id'=>$id,'config_name'=>$key,'config_value'=>$value]);
}

if($data_insert) {
   $return_data['CompanyId'] = $data_insert->company_id;
   $return_data['CompanyName'] = $request->company_name;
   $return_data['ConfigValue'] = $data_insert->config_value;
   return Response::json(['status' => 'success', 'data' => $return_data]);
}
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

public function EditProfile()
{
    $id=Auth::user()->user_id;
    $user_data  = User::where(['user_id'=>$id])->first();
    if($user_data)
    {
        $return_data['FirstName'] = $user_data->first_name;
        $return_data['LastName'] = $user_data->last_name;
        $return_data['Email'] = $user_data->user_email;
        $return_data['Phone'] = $user_data->phone;
        $return_data['City'] = $user_data->city;
        $return_data['County'] = $user_data->county;
        $return_data['PostalCode'] = $user_data->postal_code;
        $return_data['Country'] = $user_data->country;
        $return_data['MinutesPerunit'] = CompanyConfig::company_data(Auth::user()->company_id);
        return Response::json(['status' => 'success', 'data' => $return_data]);
    } else 
    {
       $errors['ErrorMessage'] = ['User Does Not Exist'];
       return Response::json(['status' => 'error', 'data' => $errors]);
    }
   return Response::json(['status' => 'success', 'data' => $data]);
}




public function UpdateProfile(Request $request)
{
    $FilterRequest = ['first_name', 'last_name', 'phone','city','county','postal_code','country','minutesperunit'];
    $data = $request->only($FilterRequest);
        // changing the input fields name
    $attributes = [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'phone' => 'Phone',
        'city' => 'City Name',
        'county' => 'County Name',
        'postal_code' => 'Postal code',
        'country' => 'Country',
        'minutesperunit' => 'Rate Per Unit',
    ];
    $validator = Validator::make($data ,[
        'first_name' => 'required|alpha|max:50',
        'last_name' => 'required|alpha|max:50',
        'phone' => 'required|numeric|digits_between:10,16',
        'city' => 'required|alpha|max:50',
        'county' => 'required|min:6',
        'postal_code' => 'required|numeric',
        'country' => 'required|alpha|max:50',
        'minutesperunit' => 'required|numeric',
    ])->setAttributeNames($attributes);
    if($validator->fails())
    {
        return Response::json(['status' => 'error', 'data' => $validator->errors()]);
    }

    $user_data['first_name'] = $request->first_name;
    $user_data['last_name'] = $request->last_name;
    $user_data['phone'] = $request->phone;
    $user_data['city'] = $request->city;
    $user_data['county'] = $request->county;
    $user_data['postal_code'] = $request->postal_code;
    $user_data['country'] = $request->country;
    $updated_data = User::where(['user_id'=>Auth::user()->user_id])->update($user_data);
    $company_data = CompanyConfig::where(['company_id'=>Auth::user()->company_id])->get();
    foreach ($company_data as $key => $value)
     {

         if($value->config_name == 'minutesperunit') 
        {  
             $config_value['config_value']=$request->minutesperunit;
             $updated_data = CompanyConfig::where(['config_id'=>$value->config_id])->update($config_value);
        }
     }
    if($updated_data>0 )
    {
        $success['SuccessMessage'] = ['Record Updated Successfully'];
        return Response::json(['status' => 'success', 'data' => $success]);

    }
    else
    {
        $errors['ErrorMessage'] = ['Record Not Updated'];
        return Response::json(['status' => 'error', 'data' => $errors]);

    }
    
}


}
