<?php

namespace App\Http\Controllers\Api\v1\AuthController;

use App\Http\StaticFunctions\StaticFunctions;
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
use DB;
use App\Http\Controllers\Api\v1\EmailController\EmailController;
use App\Models\CompanyInvite;
use App\Http\Resources\CompanyConfig\CompanyInviteResource;

class AuthController extends Controller
{

    public function Register(Request $req)
    {
       
        //filter request
        $FilterRequest = ['first_name', 'last_name','user_email', 'phone','user_password','c_password','invitation_code'];
        $data = $req->only($FilterRequest);
        // changing the input fields name
        $attributes = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'user_email' => 'Email Address',
            'phone' => 'Phone',
            'user_password' => 'Password',
            'c_password' => 'Password Confirmation ',
            'invitation_code' => 'Invitation Code'
        ];
        $validator = Validator::make($data ,[
            'first_name' => 'required|alpha_dash|max:50',
            'last_name' => 'required|alpha_dash|max:50',
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
        $data['UrlPath'] = StaticFunctions::getFrontendPath(env('APP_ENV'));
        $data['role_id'] = 1;
        $user = User::create($data);
        $layout = \View::make('emailslayout.registeration', ['data' => $data]);
        EmailController::send_default_email($req->user_email, "Please Verify Your Email",(string)$layout);

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
        if(isset($data['invitation_code']) && !empty($data['invitation_code']))
        {
              $invitationData = CompanyInvite::where(['invitation_code' => $data['invitation_code'], 'invitation_status' => 'New'])->get();
            if(count($invitationData) >0 )
            {
                try
                {
                    foreach($invitationData as $invitupdate) {
                        CompanyInvite::where(['invitation_code' => $data['invitation_code'], 'invitation_status' => 'New'])->update(['signup_email' => $data['user_email']]);
                    }

                } catch (\Exception $e){return Response::json(['status' => 'error', 'data' => $e->getMessage()]);}
            }
        }
        return Response::json(['status' => 'success', 'data' => $userdata]);
    }

    public function Login(Request $req)
   {
       $modules = null;
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
           $verification_new_code = str_random(30);
           $verification_new_code_expiry = now()->addDays(2);
           if($user->user_status != "Active"){
           if ( $user->user_status == 'Unverified' && $user->verification_code_expiry < now()) {

                $success_update = User::where(['user_id'=>$user->user_id])->update(['verification_code'=>$verification_new_code,'verification_code_expiry'=>$verification_new_code_expiry]);

                if($success_update){
                    $data['Email'] = $user->user_email;
                    $data['verification_code'] =$verification_new_code;
                    $data['verification_code_expiry'] =$verification_new_code_expiry;
                    $data['Name'] = $user->first_name;
                    $layout = \View::make('emailslayout.registeration', ['data' => $data]);
                    EmailController::send_default_email($user->user_email, "Verify Your Email",(string)$layout);
                    // $data['CompanyId'] = $user->first_name;

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
                    $data['Email'] = $user->user_email;
                    $data['verification_code'] =$verification_new_code;
                    $data['verification_code_expiry'] =$verification_new_code_expiry;
                    $data['Name'] = $user->first_name;
                    $layout = \View::make('emailslayout.registeration', ['data' => $data]);
                    EmailController::send_default_email($req->user_email, "Please Verify Your Email",(string)$layout);
                    $message['ErrorMessage'] = ['We sent you a verification email, please verify your email for login.'];
                    return Response::json(['status' => 'error' ,'data' =>  $message]);
                }
           }
       }

           $invitationData = CompanyInvite::where(['signup_email' => $user->user_email, 'invitation_status' => 'Used','module_status' => 'Pending'])->get();
           if(count($invitationData) >0 )
           {
               try
               {
                   foreach($invitationData as $key => $invitupdate) {
                       $modules[] = [
                           'CompanyID' => $invitupdate->company_id,
                           'InvitationID' =>$invitupdate->id,
                           'ModuleName' => $invitupdate->module_slug,
                           'SentBy' => StaticFunctions::getCompanyNameByID($invitupdate->company_id),
                           'InvitationRole' => StaticFunctions::getRoleByID($invitupdate->invitation_role)
                       ];
                   }

               } catch (\Exception $e){return Response::json(['status' => 'error', 'data' => $e->getMessage()]);}
           }
          $success['UserID'] = $user->user_id;
          $success['FirstName'] = $user->first_name;
          $success["UserEmail"] = $user->user_email;
          $success["UserEmail"] = $user->user_email;
          $success['CompanyId'] = $user->company_id;
          $success['RoleID'] = $user->role_id;
          $success['BillingStatus'] = StaticFunctions::billingStatusByCompanyID($user->company_id);
          $success['Invitations'] = $modules;
          $success['Module'] = StaticFunctions::getCompanyModule($user->user_id,$user->company_id);
          $success['Token'] = $user->createToken($email)->accessToken;
           return Response::json(['status' => 'success', 'data' => $success]);
       } else {
           $errors['ErrorMessage'] = ['Password is Incorrect! Try Again'];
           return Response::json(['status' => 'error', 'data' => $errors]);
       }
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

            $message['SuccessMessage'] = ['You Already Active!'];
            return Response::json(['status' => 'success', 'data' => $message]);
        }
        else
        {
            $expiry_date = $user_varify->verification_code_expiry;
                // check expiry verification code date greater then from current date otherwise regenerate verification code
            if( $expiry_date > now())
            {
                $getInvieData = CompanyInvite::where('signup_email', $user_varify->user_email)->get();
                if(count($getInvieData) > 0)
                {
                        try
                        {
                            foreach($getInvieData as $updateinv)
                            {
                                CompanyInvite::where(['signup_email' => $user_varify->user_email, 'invitation_status' => 'New'])->update(['invitation_status' => 'Used']);
                            }
                        } catch (\Exception $e)
                        {
                            return Response::json(['status' => 'error', 'data' => $e->getMessage()]);
                        }
                }
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
            $data['first_name'] = $updated_data->first_name;
            $data['Email'] = $updated_data->user_email;
            $data['ForgetCode'] = $updated_data->forget_code;
            $data['ForgetCodeCreatedAt'] = $updated_data->forget_code_created_at;
            $data['UrlPath'] = StaticFunctions::getFrontendPath(env('APP_ENV'));
            $layout = \View::make('emailslayout.setnewpassword', ['data' => $data]);
            EmailController::send_default_email($req->user_email, "Reset Your Password",(string)$layout);
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
           'forget_code' => 'required'
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




    public function Logout(Request $request)
{
    $request->user()->token()->revoke();
    $success['SuccessMessage'] = ['Logout Successfully'];
    return Response::json(['status' => 'success', 'data' => $success]);
}


    public function getInvitationData(Request $req)
    {
        $data = (object) $req->only(['invitation_code']);
        $attributes = [
            'invitation_code' => 'Invitation Code'
        ];
        $validator = Validator::make((Array)$data ,[
            'invitation_code' => 'required|string',

        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        /*$fields = ['company_id as CompanyID','user_id as UserID', 'invitation_code as InvitationCode','first_name as FirstName',
            'invitation_email as InvitationEmail', 'invitation_role as InvitationRole', 'module_slug as ModuleSlug',
            'invite_type as InviteType', 'invitation_status as InvitationStatus'];*/
        $invitationData = CompanyInvite::where('invitation_code', $data->invitation_code)->get();
        $resourceData = CompanyInviteResource::collection($invitationData);
        if(count($invitationData)>0)
        {
            return Response::json(['status' => 'success', 'data' => $resourceData]);

        } else {
            $errorMessage['ErrorMessage'] = ['No invitation found against this code'];
            return Response::json(['status' => 'error', 'data' => $errorMessage]);
        }
    }


}
