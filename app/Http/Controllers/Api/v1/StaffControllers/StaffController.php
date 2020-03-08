<?php

namespace App\Http\Controllers\Api\v1\StaffControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\UserRequest;
use App\Http\Resources\User as UserResource;
use Arr;
use App\OauthClient;
use App\Staff;
use DB;
use App\CompanyConfig;
use App\Http\Resources\StaffResource\StaffResource;
use App\Http\StaticFunctions\StaticFunctions;
use App\DatasbeseConnection;
use App\Http\Controllers\Api\v1\EmailController\EmailController;
use App\Client;

class StaffController extends Controller
{

    public function __construct()
    {
        $this->client = new \GoCardlessPro\Client([
            'access_token' => Client::GC_ACCESS_TOKEN,
            'environment' => \GoCardlessPro\Environment::SANDBOX
        ]);
    }

    public function StaffAdd(Request $req)
    {
        $FilterRequest = ['company_id','user_id','first_name','last_name','role_id','report_to','user_email','charge_out_rate','phone','city','county','postal_code','country','module_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'Company Id',
            'user_id' => 'User Id',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'role_id' => 'User Role',
            'report_to' => 'Report',
            'user_email'=> 'User Email',
            'charge_out_rate'=> 'Charge Rate',
            'phone' => 'Phone',
            'city' => 'City',
            'county' => 'Country',
            'postal_code' => 'Postal Code',
            'country' => 'Country',
            'module_id' => 'Module Id',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|numeric',
            'first_name' => 'required',
            'last_name' => 'required',
            'role_id' => 'required',
            'user_email' => 'required|email|unique:login_users,user_email',
            'charge_out_rate' => 'required|numeric',
            'module_id' => 'required',
            'report_to' => 'required'
        ])->setAttributeNames($attributes);
        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $password = str_random(30);
        $verification_code= str_random(30);
        $forget_code= str_random(30);
        $verification_code_expiry = now()->addDays(2);
        $user = User::create([
            'company_id'=>$req->company_id,
            'first_name'=>$req->first_name,
            'last_name'=>$req->last_name,
            'user_roles'=>$req->role_id,
            'forget_code'=>$forget_code,
            'user_email'=>$req->user_email,
            'user_password'=>$password,
            'verification_code'=> $verification_code,
            'verification_code_expiry'=>$verification_code_expiry,
            'charge_out_rate'=>$req->charge_out_rate,
            'phone' => $req->phone,
            'city' => $req->city,
            'county' => $req->county,
            'postal_code' => $req->postal_code,
            'country' => $req->country,
            'user_status' => User::ACTIVE,
            'account_type' => User::PAIDACCOUNT,
        ]);
        if(!empty($user)) {
            $staff = Staff::create([
                'company_id'=>$user->company_id,
                'user_id'=>$user->user_id,
                'role_id'=>$req->role_id,
                'module_id'=> StaticFunctions::getModuleSlugByID($data['module_id']),
                'report_to'=>$req->report_to,
            ]);
            if(!empty($staff)) {
                $succes['Email'] = $user->user_email;
                $succes['FirstName'] = $user->first_name;
                $succes['LastName'] = $user->last_name;
                $data['UserStatus'] = $user->user_status;
                $data['ForgetCode'] = $user->forget_code;
                $data['verification_code'] = $verification_code;
                $layout = \View::make('emailslayout.staffmaillayout', ['data' => $data]);
                EmailController::send_default_email($user->user_email, "Please Verify Your Email",(string)$layout);
                return Response::json(['status' => 'success', 'data' => $succes]);
            } else {
                $errors['ErrorMessage'] = ['Fail to add the Staff, please try again.'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['Fail to add Staff in User login, please try again.'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function ListStaff(Request $req)
    {
        $FilterRequest = ['company_id','module_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'Company ID',
            'module_id' => 'Module ID',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required',
            'module_id' => 'required',
        ])->setAttributeNames($attributes);
        if($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        } else {
            $staff = Staff::where(['company_id'=>$data['company_id'],'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id'])])->get();
            $staff = StaffResource::collection($staff);
            if($staff) {
                return Response::json(['status' => 'success', 'data' => $staff]);
            } else {
                $errors['ErrorMessage'] = ['Record Not Found !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        }
    }

    public function StaffEdit(Request $req)
    {
        $FilterRequest = ['id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'id' => 'Staff ID'
        ];
        $validator = Validator::make($data ,[
            'id' => 'required',
        ])->setAttributeNames($attributes);
        if($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $staff = Staff::where('id',$data['id'])->first();
        if(!empty($staff)) {
            $edit = Arr::set(Staff::$UserResourceFields,'EditFor','1');
            $get_data = new StaffResource($staff,$edit);
            return Response::json(['status' => 'success', 'data' => $get_data]);
        } else {
            $error['ErrorMessage'] = ['Notes Not Found'];
            return Response::json(['status' => 'error', 'data' => $error]);
        }
    }

    public function StaffUpdate(Request $req)
    {
        $FilterRequest = ['staff_id','first_name','last_name','report_to','charge_out_rate','phone','city','county','postal_code','country','module_id','role_id','user_email'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'report_to' => 'Report',
            'charge_out_rate'=> 'Charge Rate',
            'phone' => 'Phone',
            'city' => 'City',
            'county' => 'Country',
            'postal_code' => 'Postal Code',
            'country' => 'Country',
            'module_id' => 'Module ID',
            'staff_id' => 'Staff ID',
            'user_email' => 'User Email',
        ];
        $validator = Validator::make($data ,[
            'first_name' => 'required',
            'last_name' => 'required',
            'charge_out_rate' => 'required|numeric',
            'module_id' => 'required',
            'staff_id' => 'required|numeric',
        ])->setAttributeNames($attributes);
        if($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $staff = Staff::where('id',$data['staff_id'])->first();
        if($staff->user->account_type == User::PAIDACCOUNT)
        {
            $check = User::where('user_id',$staff->user_id)->first();
            $checkEmail = User::where('user_email', '!=' , $check->user_email)->get();
            foreach ($checkEmail as $key) {
                if($key->user_email==$data['user_email']) {
                    $error['ErrorMessage'] = 'User Email Name Already Exit';
                    return Response::json(['status' => 'error', 'data' => $error]);
                }
            }
            DB::beginTransaction();
            $updateStaff = Staff::where('id',$data['staff_id'])->update([
                'role_id' => $data['role_id'],
                'report_to' => $data['report_to'],
                'module_id' => StaticFunctions::getModuleSlugByID($data['module_id'])
            ]);
            if($updateStaff) {
                $return_data = User::where('user_id',$staff->user_id)->update([
                    'first_name'=> $data['first_name'],
                    'last_name'=> $data['last_name'],
                    'charge_out_rate'=> $data['charge_out_rate'],
                    'phone' =>  $data['phone'],
                    'city' =>  $data['city'],
                    'county' =>  $data['county'],
                    'postal_code' =>  $data['postal_code'],
                    'country' =>  $data['country'],
                ]);
                if($return_data) {
                    DB::commit();
                    $message['Message'] = ['Staff Updated Successfully'];
                    return Response::json(['status' => 'success', 'data' => $message]);
                } else {
                    DB::rollBack();
                    $errors['ErrorMessage'] = ['Fail to update the Staff, please try again.'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } else {
                DB::rollBack();
                $errors['ErrorMessage'] = ['Fail to update the Staff, please try again.'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $return_data = User::where('user_id',$staff->user_id)->update([
                'first_name'=> $data['first_name'],
                'last_name'=> $data['last_name'],
                'charge_out_rate'=> $data['charge_out_rate'],
                'phone' =>  $data['phone'],
                'city' =>  $data['city'],
                'county' =>  $data['county'],
                'postal_code' =>  $data['postal_code'],
                'country' =>  $data['country'],
            ]);
            if($return_data) {
                DB::commit();
                $message['Message'] = ['Staff Updated Successfully'];
                return Response::json(['status' => 'success', 'data' => $message]);
            } else {
                DB::rollBack();
                $errors['ErrorMessage'] = ['Fail to update the Staff, please try again.'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        }
    }

    public function updateBilling()
    {
        //$customers = $this->client->customers()->list()->records;
        //print_r($customers);
        $redirectFlow = $this->client->redirectFlows()->create([
            "params" => [
                // This will be shown on the payment pages
                "description" => "Wine boxes",
                // Not the access token
                "session_token" => "dummy_session_token",
                "success_redirect_url" => "https://dev.practicepa.co.uk/newpa",
                // Optionally, prefill customer details on the payment page
                "prefilled_customer" => [
                    "given_name" => "Tim",
                    "family_name" => "Rogers",
                    "email" => "tim@gocardless.com",
                    "address_line1" => "338-346 Goswell Road",
                    "city" => "London",
                    "postal_code" => "EC1V 7LQ"
                ]
            ]
        ]);
        $data['RedirectID'] = $redirectFlow->id;
        $data['RedirectURL'] = $redirectFlow->redirect_url;
        return Response::json(['status'=>'success', 'data' => $data]);

    }

    public function goCardlesComplete(Request $req)
    {
        $redirectFlow = $this->client->redirectFlows()->complete(
            $req->redirectID, //The redirect flow ID from above.
            ["params" => ["session_token" => "dummy_session_token"]]
        );

        print("Mandate: " . $redirectFlow->links->mandate . "<br />");
        print("Customer: " . $redirectFlow->links->customer . "<br />");
        print("Confirmation URL: " . $redirectFlow->confirmation_url . "<br />");
    }
}
