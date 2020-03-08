<?php

namespace App\Http\Controllers\Api\v1\ContactController;

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
use App\Contact;
use App\Country;
use App\Http\Resources\Country\CountryResource;
use App\Http\Resources\CompanyConfig\CompanyConfigResource;
use App\Http\Resources\Profile\ProfileResource;
use App\Http\Resources\Contact\ContactResource;
use DB;
use App\Http\StaticFunctions\StaticFunctions;
use App\Client;
use App\DatasbeseConnection;


class ContactController extends Controller
{
    public function ContactAdd(Request $req)
    {

        $FilterRequest = ['company_id','contact_title','first_name','last_name','contact_designation','contact_phone_no','contact_email','contact_address_line1','contact_city','contact_county','contact_country','contact_postal_code','notes','contact_other_title'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'Company ID',
            'contact_title' => 'Contact Title',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'contact_designation' => 'Contact Designation',
            'contact_phone_no' => 'Contact Phone Number',
            'contact_email'=> 'Contact Email',
            'contact_address_line1'=> 'Contact Address Line',
            'contact_city' => 'Contact City',
            'contact_county' => 'Contact Country',
            'contact_country' => 'Contact Country',
            'contact_postal_code' => 'Contact Postal Code',
            'notes' => 'notes',
            'contact_other_title'=>'Contact Other Title'
        ];
        $validator = Validator::make($data ,[
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
                    $contact = Contact::create([
                        'company_id'=>$data['company_id'],
                        'contact_title'=>$data['contact_title'],
                        'contact_other_title'=>$data['contact_other_title'],
                        'first_name'=>$data['first_name'],
                        'last_name'=>$data['last_name'],
                        'contact_designation'=>$data['contact_designation'],
                        'contact_phone_no'=>$data['contact_phone_no'],
                        'contact_email'=>$data['contact_email'],
                        'contact_address_line1' => $data['contact_address_line1'],
                        'contact_city' => $data['contact_city'],
                        'contact_county' => $data['contact_county'],
                        'contact_country' => $data['contact_country'],
                        'contact_postal_code' => $data['contact_postal_code'],
                        'notes' => $data['notes'],
                        'contact_type' => Contact::CONTACTYPECOMPANY
                    ]);
                    if(!empty($contact)) {
                        $succes['SuccessMessage'] = ['Contact add succesfully.'];
                        return Response::json(['status' => 'success', 'data' => $succes]);
                    } else {
                        $errors['ErrorMessage'] = ['Fail to add the contact, please try again.'];
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

    public function ContactList(Request $req)
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
                    $contacts = Contact::get();
                    $allContact = ContactResource::collection($contacts);
                    if($allContact) {
                        return Response::json(['status' => 'success', 'data' => $allContact]);
                    } else {
                        $errors['ErrorMessage'] = ['Record Not Found !!'];
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

    public function ContactEdit(Request $req)
    {
        // return $req;
        $FilterRequest = ['company_id','contact_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'contact_id'=> 'contact id',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required',
            'contact_id' => 'required',
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
                    $get_contact = Contact::where('contact_id',$data['contact_id'])->first();
                    if(!empty($get_contact)){
                        $get_data = new ContactResource($get_contact);
                        return Response::json(['status' => 'success', 'data' => $get_data]);
                    } else {
                        $error['ErrorMessage'] = ['Contact Not Found'];
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

    public function ContactUpdate(Request $req)
    {
        $FilterRequest = ['company_id','contact_id','contact_title','first_name','last_name','contact_designation','contact_phone_no','contact_email','contact_address_line1','contact_city','contact_county','contact_country','contact_postal_code','notes','contact_other_title'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'Company ID',
            'contact_id' => 'Contact ID',
            'contact_title' => 'Contact Title',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'contact_designation' => 'Contact Designation',
            'contact_phone_no' => 'Contact Phone Number',
            'contact_email'=> 'Contact Email',
            'contact_address_line1'=> 'Contact Address Line',
            'contact_city' => 'Contact City',
            'contact_county' => 'Contact Country',
            'contact_country' => 'Contact Country',
            'contact_postal_code' => 'Contact Postal Code',
            'notes' => 'Notes',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|numeric',
            'contact_id' => 'required|numeric',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',

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
                    $return_data = Contact::where('contact_id',$data['contact_id'])->update([
                        'company_id'=> $data['company_id'],
                        'contact_title'=> $data['contact_title'],
                        'contact_other_title'=> $data['contact_other_title'],
                        'first_name'=> $data['first_name'],
                        'last_name'=> $data['last_name'],
                        'contact_designation'=> $data['contact_designation'],
                        'contact_phone_no'=> $data['contact_phone_no'],
                        'contact_email'=> $data['contact_email'],
                        'contact_address_line1' =>  $data['contact_address_line1'],
                        'contact_city' =>  $data['contact_city'],
                        'contact_county' =>  $data['contact_county'],
                        'contact_country' =>  $data['contact_country'],
                        'contact_postal_code' =>  $data['contact_postal_code'],
                        'notes' =>  $data['notes'],
                    ]);
                    if($return_data) {
                        $message['Message'] = ['Contact Update SuccesFully'];
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

    public function ContactDelete(Request $req)
    {
        $FilterRequest = ['company_id','contact_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'contact_id'=> 'contact id',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|numeric',
            'contact_id' => 'required|numeric',
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
                    $delete_data=Contact::where('contact_id',$data['contact_id'])->delete();
                    if($delete_data) {
                        $success['Message'] = ['Contact Deleted Succesfuly'];
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
