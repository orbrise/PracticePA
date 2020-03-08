<?php

namespace App\Http\Controllers\Api\v1\OrganizationController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyConfig;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Arr;
use App\Http\Resources\Organization\OrganizationResource;
use DB;
use App\Http\StaticFunctions\StaticFunctions;
use App\Organization;


class OrganizationController extends Controller
{
	public function OrganizationAdd(Request $req)
	{
		$FilterRequest = ['org_name','title','first_name','last_name','designation', 'phone','email','address','city', 'county','country','postal_code','company_id','module_id'];
		$data = $req->only($FilterRequest);
		$attributes = [
			'company_id' => 'Company ID',
			'org_name' => 'Organization Name',
			'title' => 'Title',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'designation' => 'Designation', 
			'phone' => 'Phone Number', 
			'email'=> 'Email',
			'address'=> 'Address',
			'city' => 'City',
			'county' => 'Country',
			'country' => 'Country',
			'postal_code' => 'Contact Postal Code',
		];
		$validator = Validator::make($data ,[
			'org_name' => 'required|string|max:50',
			'title' => 'required|string|max:50',
			// 'email' => 'required|email|unique:organizations,email',
			'first_name' => 'required|string|max:50',
			'last_name' => 'required|string|max:50',
			'company_id' => 'required|numeric',
		])->setAttributeNames($attributes);

		if($validator->fails())
		{
			return Response::json(['status' => 'error', 'data' => $validator->errors()]);
		}

		$database = CompanyConfig::where('company_id',$data['company_id'])->get();
	   	if($database) {
	         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
	         if($company_db) {
	            $db_con = StaticFunctions::db_connection(strtolower($company_db));
	            if($db_con) {
						$organization = Organization::create([
							'company_id'=>$data['company_id'],
							'org_name'=>$data['org_name'],
							'title'=>$data['title'],
							'first_name'=>$data['first_name'],
							'last_name'=>$data['last_name'],
							'designation'=>$data['designation'],
							'phone'=>$data['phone'],
							'email'=>$data['email'],
							'address' => $data['address'],
							'city' => $data['city'],
							'county' => $data['county'],
							'country' => $data['country'],
							'postal_code' => $data['postal_code'],
						]); 
						if(!empty($organization)) {
							$succes['SuccessMessage'] = ['Organization add succesfully.'];
							return Response::json(['status' => 'success', 'data' => $succes]);
						} else {
							$errors['ErrorMessage'] = ['Fail to add the Organization, please try again.'];
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

	public function OrganizationList(Request $req)
	{
		$FilterRequest = ['company_id'];
		$data = $req->only($FilterRequest);
		$attributes = [
			'company_id' => 'company id',   
		];
		$validator = Validator::make($data ,[
			'company_id' => 'required',
		])->setAttributeNames($attributes);

		if($validator->fails())
		{
			return Response::json(['status' => 'error', 'data' => $validator->errors()]);
		}

		$database = CompanyConfig::where('company_id',$data['company_id'])->get();
   	if($database){
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
            	$Organization = Organization::get();
					$allOrganizationt = OrganizationResource::collection($Organization);
					if($allOrganizationt) {
						return Response::json(['status' => 'success', 'data' => $allOrganizationt]);
					} else {
						$errors['ErrorMessage'] = ['Organization Not Found !!'];
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

	public function OrganizationEdit(Request $req)
	{
		// return $req;
		$FilterRequest = ['company_id','id'];
		$data = $req->only($FilterRequest);
		$attributes = [
			'company_id' => 'Company ID',
			'id'=> 'Organization ID',
		];
		$validator = Validator::make($data ,[
			'company_id' => 'required',
			'id' => 'required',
		])->setAttributeNames($attributes);

		if($validator->fails())
		{
			return Response::json(['status' => 'error', 'data' => $validator->errors()]);
		}

		$database = CompanyConfig::where('company_id',$data['company_id'])->get();
   	if($database){
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
					 $get_organization = Organization::where('id',$data['id'])->first();
					 if(!empty($get_organization)){
					 	$edit = Arr::set(Organization::$OrganizationResourceFields,'EditFor','1');
						$get_data = new OrganizationResource($get_organization,$edit);
					 	return Response::json(['status' => 'success', 'data' => $get_data]);
					} else {
						$error['ErrorMessage'] = ['Organization Not Found'];
						return Response::json(['status' => 'error', 'data' => $error]);
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

	public function OrganizationUpdate(Request $req)
	{
		$FilterRequest = ['company_id','id','org_name','title','first_name','last_name','designation', 'phone','email','address','city', 'county','country','postal_code','company_id'];
		$data = $req->only($FilterRequest);
		$attributes = [
			'company_id' => 'Company ID',
			'org_name' => 'Organization Name',
			'title' => 'Title',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'designation' => 'Designation', 
			'phone' => 'Phone Number', 
			'email'=> 'Email',
			'address'=> 'Address',
			'city' => 'City',
			'county' => 'Country',
			'country' => 'Country',
			'postal_code' => 'Contact Postal Code',
		];
		$validator = Validator::make($data ,[
			'org_name' => 'required|string|max:50',
			'title' => 'required|string|max:50',
			// 'email' => 'required|email|unique:organizations,email',
			'first_name' => 'required|string|max:50',
			'last_name' => 'required|string|max:50',
			'company_id' => 'required|numeric',
		])->setAttributeNames($attributes);

		if($validator->fails())
		{
			return Response::json(['status' => 'error', 'data' => $validator->errors()]);
		}

		$database = CompanyConfig::where('company_id',$data['company_id'])->get();
   	if($database){
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
            	$return_data = Organization::where('id',$data['id'])->update([
						'company_id'=>$data['company_id'],
						'org_name'=>$data['org_name'],
						'title'=>$data['title'],
						'first_name'=>$data['first_name'],
						'last_name'=>$data['last_name'],
						'designation'=>$data['designation'],
						'phone'=>$data['phone'],
						'email'=>$data['email'],
						'address' => $data['address'],
						'city' => $data['city'],
						'county' => $data['county'],
						'country' => $data['country'],
						'postal_code' => $data['postal_code'],
					]);
					if($return_data) {
						$message['Message'] = ['Organization Update SuccesFully'];
						return Response::json(['status' => 'success', 'data' => $message]);
					} else {
						$error['ErrorMessage'] = ['DataBase Not Found'];
						return Response::json(['status' => 'success', 'data' => $error]);
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

	public function OrganizationDelete(Request $req)
	{
		$FilterRequest = ['company_id','id'];
		$data = $req->only($FilterRequest);
		$attributes = [
			'company_id' => 'Company ID',
			'id'=> 'Organization ID',
		];
		$validator = Validator::make($data ,[
			'company_id' => 'required|numeric',
			'id' => 'required|numeric',
		])->setAttributeNames($attributes);

		if($validator->fails()){
			return Response::json(['status' => 'error', 'data' => $validator->errors()]);
		}

		$database = CompanyConfig::where('company_id',$data['company_id'])->get();
   	if($database){
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
					$delete_data=Organization::where('id',$data['id'])->delete();
					if($delete_data) {
						$success['Message'] = ['Organization Deleted Succesfuly'];
						return Response::json(['status' => 'success', 'data' => $success]);
					} else {
						$errors['ErrorMessage'] = ['Contact failed to delete , try again !!'];
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

}
