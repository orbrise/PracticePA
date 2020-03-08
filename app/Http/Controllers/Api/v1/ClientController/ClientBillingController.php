<?php

namespace App\Http\Controllers\Api\v1\ClientController;

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
use DB;
use App\CompanyConfig;
use App\Company;
use App\Http\StaticFunctions\StaticFunctions;
use App\Client;

class ClientBillingController extends Controller
{
    public function __construct()
    {
        $this->client = new \GoCardlessPro\Client([
            'access_token' => Client::GC_ACCESS_TOKEN,
            'environment' => \GoCardlessPro\Environment::SANDBOX
        ]);
    }

	public function GetBillingData(Request $req)
	{
		$FilterRequest = ['company_id'];
		$data = (object) $req->only($FilterRequest);
		$attributes = [
			'company_id' => 'Company Id'
		];
		$validator = Validator::make($req->only($FilterRequest) ,[
			'company_id' => 'required|numeric',
		])->setAttributeNames($attributes);
		if($validator->fails()) {
			return Response::json(['status' => 'error', 'data' => $validator->errors()]);
		}
        $company = CompanyConfig::where(['company_id'=>$req->company_id])->get();
		if(count($company)>0)
		{
            foreach ($company as  $key => $value) {
                if($value->config_name == 'billing_company_name')
                {
                    $getBillingData['BillingCompanyName'] = $value->config_value;
                }
                if($value->config_name == 'billing_first_name')
                {
                    $getBillingData['BillingFirstName'] = $value->config_value;
                }
                if($value->config_name == 'billing_last_name')
                {
                    $getBillingData['BillingLastName'] = $value->config_value;
                }
                if($value->config_name == 'billing_email')
                {
                    $getBillingData['BillingEmail'] = $value->config_value;
                }
                if($value->config_name == 'billing_address_1')
                {
                    $getBillingData['BillingAddress1'] = $value->config_value;
                }
                if($value->config_name == 'billing_address_2')
                {
                    $getBillingData['BillingAddress2'] = $value->config_value;
                }
                if($value->config_name == 'billing_city')
                {
                    $getBillingData['BillingCity'] = $value->config_value;
                }
                if($value->config_name == 'billing_postal_code')
                {
                    $getBillingData['BillingPostalCode'] = $value->config_value;
                }
                if($value->config_name == 'billing_country')
                {
                    $getBillingData['BillingCountry'] = $value->config_value;
                }
            }
            StaticFunctions::ClientNotifications('','','ClientBilling','desktop','You has been login successfully','1');
            return Response::json(['status' => 'success', 'data' => $getBillingData]);

		} else {
				$errors['ErrorMessage'] = ['No Company Data Found !!'];
				return Response::json(['status' => 'error', 'data' => $errors]);
			}
	}

	public function BillingAdd(Request $req) {

		$FilterRequest = ['company_id','billing_company_name','billing_first_name','billing_last_name',
            'billing_email','billing_address_1','billing_address_2','billing_city',
            'billing_postal_code','billing_country', 'module_id'];
		$data = (object) $req->only($FilterRequest);
		$attributes = [
			'company_id' => 'Company Id',
			'billing_company_name' => 'Billing Company Name',
			'billing_first_name' => 'Billing First Name',
			'billing_last_name' => 'Billing Last Name',
			'billing_email' => 'Billing Email', 
			'billing_address_1' => 'Billing Address1', 
			'billing_address_2'=> 'Billing Address2',
			'billing_city'=> 'Billing City',
			'billing_postal_code' => 'Billing Postal Code',
			'billing_country' => 'Billing Country',
            'module_id' => 'Module ID'
		];
		$validator = Validator::make($req->only($FilterRequest) ,[
			'company_id' => 'required|numeric',
			'billing_company_name' => 'required',
			'billing_first_name' => 'required',
			'billing_last_name' => 'required',
			'billing_email' => 'required|email',
			'billing_address_1' => 'required',
			'billing_city' => 'required',
			'billing_postal_code' => 'required',
			'billing_country' => 'required',
            'module_id' => 'required'
		])->setAttributeNames($attributes);
		if($validator->fails()) {
			return Response::json(['status' => 'error', 'data' => $validator->errors()]);
		}
        $session_token = session()->getId();
		$company_id=$data->company_id;
		$module_id = StaticFunctions::getModuleSlugByID($data->module_id);
		unset($data->company_id);
		unset($data->module_id);
        $company_data = Company::where(['company_id' => $company_id, 'module_id' => $module_id])->first();
        $billing_Status = $company_data->billing_status;
        if($billing_Status == 0){
            try
            {

                foreach ($data as $key => $value) {
                    $data_insert=CompanyConfig::create(['company_id'=>$company_id,'config_name'=>$key,'config_value'=>$value]);
                }
                Company::where(['company_id' => $company_id, 'module_id' => $module_id])->update(['billing_status' => 1]);
                $redirectFlow = $this->client->redirectFlows()->create([
                    "params" => [
                        // This will be shown on the payment pages
                        "description" => "Bizpad",
                        // Not the access token
                        "session_token" => "samplesessionid",
                        "success_redirect_url" => "http://dev.myppa.com/complete_flow",
                        // Optionally, prefill customer details on the payment page
                        "prefilled_customer" => [
                            "given_name" => $data->billing_first_name,
                            "family_name" => $data->billing_last_name,
                            "email" => $data->billing_email,
                            "address_line1" => $data->billing_address_1,
                            "city" => $data->billing_city,
                            "postal_code" => $data->billing_postal_code
                        ]
                    ]
                ]);
                $getConfig['RedirectID'] = $redirectFlow->id;
                $getConfig['RedirectURL'] = $redirectFlow->redirect_url;
                $getConfig['SessionID'] = $session_token;
                return Response::json(['status'=>'success', 'data' => $getConfig]);
                //return Response::json(['status' => 'success', 'data' => $data_insert]);
            } catch (\Exception $e){
                $errors['ErrorMessage'] = $e->getMessage();
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $pre_data = CompanyConfig::where(['company_id' => $company_id])->get();
            try {
                if (count($pre_data) > 0) {
                    foreach ($pre_data as $key => $value) {
                        if ($value->config_name == 'billing_company_name') {
                            CompanyConfig::where(['company_id' => $company_id, 'config_name' => $value->config_name])
                                ->update(['config_value' => $data->billing_company_name]);
                        }
                        if ($value->config_name == 'billing_first_name') {
                            CompanyConfig::where(['company_id' => $company_id, 'config_name' => $value->config_name])
                                ->update(['config_value' => $data->billing_first_name]);
                        }
                        if ($value->config_name == 'billing_last_name') {
                            CompanyConfig::where(['company_id' => $company_id, 'config_name' => $value->config_name])
                                ->update(['config_value' => $data->billing_last_name]);
                        }

                        if ($value->config_name == 'billing_address_line1') {
                            CompanyConfig::where(['company_id' => $company_id, 'config_name' => $value->config_name])
                                ->update(['config_value' => $data->billing_address_1]);
                        }
                        if ($value->config_name == 'billing_address_2') {
                            CompanyConfig::where(['company_id' => $company_id, 'config_name' => $value->config_name])
                                ->update(['config_value' => $data->billing_address_2]);
                        }
                        if ($value->config_name == 'billing_city') {
                            CompanyConfig::where(['company_id' => $company_id, 'config_name' => $value->config_name])
                                ->update(['config_value' => $data->billing_city]);
                        }
                        /* if ($value->config_name == 'county') {
                             CompanyConfig::where(['company_id'=> $company_id, 'config_name' => $value->config_name ])
                                 ->update(['config_value'=>$data->billing_county]);
                         }*/
                        if ($value->config_name == 'billing_postal_code') {
                            CompanyConfig::where(['company_id' => $company_id, 'config_name' => $value->config_name])
                                ->update(['config_value' => $data->billing_postal_code]);
                        }
                        if ($value->config_name == 'billing_country') {
                            CompanyConfig::where(['company_id' => $company_id, 'config_name' => $value->config_name])
                                ->update(['config_value' => $data->billing_country]);
                        }
                    }
                    $redirectFlow = $this->client->redirectFlows()->create([
                        "params" => [
                            // This will be shown on the payment pages
                            "description" => "Bizpad",
                            // Not the access token
                            "session_token" => "samplesessionid",
                            "success_redirect_url" => "http://dev.myppa.com/complete_flow",
                            // Optionally, prefill customer details on the payment page
                            "prefilled_customer" => [
                                "given_name" => $data->billing_first_name,
                                "family_name" => $data->billing_last_name,
                                "email" => $data->billing_email,
                                "address_line1" => $data->billing_address_1,
                                "city" => $data->billing_city,
                                "postal_code" => $data->billing_postal_code
                            ]
                        ]
                    ]);
                    $message['RedirectID'] = $redirectFlow->id;
                    $message['RedirectURL'] = $redirectFlow->redirect_url;
                    $message['SessionID'] = $session_token;
                    return Response::json(['status' => 'success', 'data' => $message]);
                } else {
                    $errors['ErrorMessage'] = ['Company Not Found !!'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } catch (\Exception $e) {
                return Response::json(['status' => 'error', 'data' => $e->getMessage()]);
            }
        }

	}

	public function goCardlesComplete(Request $req)
    {
        $FilterRequest = ['company_id','redirect_id','module_id'];
        $data = (object) $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'Company Id',
            'session_id' => 'Redirect ID',
            'module_id' => 'Module ID'

        ];
        $validator = Validator::make($req->only($FilterRequest) ,[
            'company_id' => 'required|numeric',
            'redirect_id' => 'required',
            'module_id' => 'required'

        ])->setAttributeNames($attributes);
        if($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        try
        {
            $module_id = StaticFunctions::getModuleSlugByID($data->module_id);
            $redirectFlow = $this->client->redirectFlows()->complete(
            $data->redirect_id, //The redirect flow ID from above.
            ["params" => ["session_token" => "samplesessionid"]]
            );

    $toBeInsert['mandate'] = $redirectFlow->links->mandate;
    $toBeInsert['customer_id'] = $redirectFlow->links->customer;
    //$toBeInsert['confirmation_url'] = $redirectFlow->links->confirmation_url;
    /*print("Mandate: " . $redirectFlow->links->mandate . "<br />");
    print("Customer: " . $redirectFlow->links->customer . "<br />");
    print("Confirmation URL: " . $redirectFlow->confirmation_url . "<br />");*/
    if(!empty($redirectFlow->links->mandate) && !empty($redirectFlow->links->customer))
    {
        Company::where(['company_id'=>$data->company_id,'module_id' => $module_id])
            ->update(['mandate' => $redirectFlow->links->mandate, 'gocardless_customer' => $redirectFlow->links->customer]);

        /*foreach ($toBeInsert as $key => $value) {
            $data_insert=CompanyConfig::create(['company_id'=>$data->company_id,'config_name'=>$key,'config_value'=>$value]);
        }*/
            $successMessage['SuccessMessage'] = ['Billing Update Successfully'];
            return Response::json(['status' => 'success', 'data' => $successMessage]);

    }
        } catch (\Exception $e)
        {
            $errorMessage['errorMessage'] = [$e->getMessage() ];
            return Response::json(['status' => 'error', 'data' => $errorMessage]);
        }
}
	public function BillingList(Request $req) {
		$FilterRequest = ['company_id','module_id'];
		$data = $req->only($FilterRequest);
		$attributes = [  
			'company_id' => 'Company ID',   
		];
		$validator = Validator::make($data ,[
			'company_id' => 'required',
            'module_id' => 'required'
		])->setAttributeNames($attributes);

		if($validator->fails())
		{
			return Response::json(['status' => 'error', 'data' => $validator->errors()]);
		} else {
		    $BillingList = Company::where('company_id',$req->company_id)->first();
		    $config_data = CompanyConfig::where('company_id',$req->company_id)->get();
		    $status = $BillingList->billing_status;
		    if ($status==0) {
				foreach ($config_data as $key => $value) {
		 	    	$data_new[$value->config_name]=$value->config_value; 
		    	}
		    	$get_data['CompanyDatabase'] = $data_new['company_database'];
		    	$get_data['CompanyName'] = $data_new['company_name'];
		    	$get_data['UserID'] = $data_new['user_id'];
				$get_data['FirstName'] = $data_new['first_name'];
				$get_data['LastName'] = $data_new['last_name'];
				$get_data['Email'] = $data_new['email'];
				$get_data['City'] = $data_new['city'];
				$get_data['Phone'] = $data_new['phone'];
				$get_data['PostCode'] = $data_new['post_code'];
				$get_data['Country'] = $data_new['country'];
				$get_data['County'] = $data_new['county'];
		     } else {
		}
			 $BillingList = CompanyConfig::where('company_id',$req->company_id)->get();
			foreach ($BillingList as $key => $value) {
		 	 $data_new[$value->config_name]=$value->config_value; 
		    }
		    // return $data_new['company_id'];
			$get_data['CompanyName'] = $data_new['billing_company_name'];
			$get_data['FirstName'] = $data_new['billing_first_name'];
			$get_data['LastName'] = $data_new['billing_last_name'];
			$get_data['Email'] = $data_new['billing_email'];
			$get_data['Address'] = $data_new['billing_address_1'];
			$get_data['Address1'] = $data_new['billing_address_2'];
			$get_data['City'] = $data_new['billing_city'];
			$get_data['PostCode'] = $data_new['billing_postal_code'];
			$get_data['Country'] = $data_new['billing_country'];
			}
			if($get_data) {
				return Response::json(['status' => 'success', 'data' => $get_data]);
			} else {
				$errors['ErrorMessage'] = ['Billing Record Not Found !!'];
				return Response::json(['status' => 'error', 'data' => $errors]);
			}

	}
 //    public function EditBilling(Request $req) {
 //    	$FilterRequest = ['company_id','user_id','module_id'];
	// 	$data = $req->only($FilterRequest);
	// 	$attributes = [
	// 		'company_id' => 'Company ID',
	// 		'user_id' => 'User ID',
	// 		'module_id' => 'Module ID'
	// 	];
	// 	$validator = Validator::make($data ,[
	// 		'company_id' => 'required',
	// 		'user_id' => 'required',
	// 		'module_id' => 'required',
	// 	])->setAttributeNames($attributes);
	// 	if($validator->fails()) {
	// 		return Response::json(['status' => 'error', 'data' => $validator->errors()]);
	// 	}
	// 	$data_get = CompanyConfig::where('company_id',$req->company_id)->get();
	// 	foreach ($data_get as $key => $value) {
	// 	 	$data_new[$value->config_name]=$value->config_value; 
	// 	 }
	// 	 // return $data_new;
	// 	  $get_data['CompanyName'] = $data_new['billing_company_name'];
	// 	  $get_data['FirstName'] = $data_new['billing_first_name'];
	// 	  $get_data['LastName'] = $data_new['billing_last_name'];
	// 	  $get_data['Email'] = $data_new['billing_email'];
	// 	  $get_data['Address'] = $data_new['billing_address_1'];
	// 	  $get_data['Address1'] = $data_new['billing_address_2'];
	// 	  $get_data['City'] = $data_new['billing_city'];
	// 	  $get_data['PostCode'] = $data_new['billing_postal_code'];
	// 	  $get_data['Country'] = $data_new['billing_country'];
	// 	if(!empty($get_data)) {
	// 		return Response::json(['status' => 'success', 'data' => $get_data]);
	// 	} else {
	// 		$error['ErrorMessage'] = ['Billing Record Not Found'];
	// 		return Response::json(['status' => 'error', 'data' => $error]);
	// 	}
	// } 
	public function BillingUpdate(Request $req) {
		$FilterRequest = ['company_id','billing_company_name','billing_first_name','billing_last_name','billing_address_1','billing_address_2','billing_city','billing_postal_code','billing_country'];
		$data = $req->only($FilterRequest);
		$attributes = [
			'company_id' => 'Company Id',
			'billing_company_name' => 'Billing Company Name',
			'billing_first_name' => 'Billing First Name',
			'billing_last_name' => 'Billing Last Name',
			'billing_address_1' => 'Billing Address1', 
			'billing_address_2'=> 'Billing Address2',
			'billing_city'=> 'Billing City',
			'billing_postal_code' => 'Billing Postal Code',
			'billing_country' => 'Billing Country',
		];
		$validator = Validator::make($data ,[
			'billing_company_name' => 'required',
			'billing_first_name' => 'required',
			'billing_last_name' => 'required',
			'billing_address_1' => 'required',
			'billing_city' => 'required',
			'billing_postal_code' => 'required',
			'billing_country' => 'required',
		])->setAttributeNames($attributes);
		if($validator->fails()) {
			return Response::json(['status' => 'error', 'data' => $validator->errors()]);
		}
      $id = $req->company_id;
      // return $id;
      //$data_delete = CompanyConfig::where('company_id',$id)->delete();

		foreach ($data as $key => $value) {
		 $billing_update =CompanyConfig::create(['company_id'=>$id,'config_name'=>$key,'config_value'=>$value]);
		}
		if(!$billing_update) {
			$error['ErrorMessage'] = ['Billing Not Update'];
			return Response::json(['status' => 'error', 'data' => $error]);
		} else {
			$message['Message'] = ['Billing updated'];
			return Response::json(['status' => 'success', 'data' => $message]);	
		}	
	}
}
