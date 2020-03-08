<?php

namespace App\Http\Controllers\Api\v1\ClientController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ClientOfficer;
use App\CompanyConfig;
use DB;
use App\Http\StaticFunctions\StaticFunctions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Http\Resources\ClientOfficer\ClientOfficerResource;
class ClientOfficerController extends Controller
{
    public function addClientOfficer(Request $req)
    {
        $FilterRequest = [ 'company_id','client_id','contact_type','officer_type','ceased_on','appointed_on','resigned_on','contact_title','contact_other_title','first_name','last_name','contact_designation','contact_phone_no','contact_email','contact_address_line1','contact_city','contact_county','contact_country','nationality','contact_postal_code','notes'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'client_id' => 'client id',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'contact_type' => 'contact_type',
            'officer_type' => 'officer_type',
            'ceased_on' => 'ceased_on',
            'appointed_on' => 'appointed_on',
            'resigned_on' => 'resigned_on',
            'contact_title' => 'contact_title',
            'contact_other_title' => 'contact_other_title',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'contact_designation' => 'contact_designation',
            'contact_phone_no' => 'contact_phone_no',
            'contact_postal_code' => 'contact_postal_code',
            'notes' => 'notes'
        ];
        $validator = Validator::make($data ,[
            'client_id'=> 'required|numeric',
            'company_id'=>'required|numeric',
            'first_name'=>'required|string',
            'last_name'=>'required|string',
            'officer_type'=>'required|string'
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
                    // return $data;
                    $addClientOfficer  = ClientOfficer::create([
                        'client_id'=> $data['client_id'],
                        'company_id'=> $data['company_id'],
                        'contact_title' => $data['contact_title'],
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'contact_designation' => $data['contact_designation'],
                        'contact_phone_no' => $data['contact_phone_no'],
                        'contact_email' => $data['contact_email'],
                        'contact_address_line1' => $data['contact_address_line1'],
                        'contact_city' => $data['contact_city'],
                        'contact_county' => $data['contact_county'],
                        'contact_postal_code' => $data['contact_postal_code'],
                        'contact_country' => $data['contact_country'],
                        'notes' => $data['notes'],
                        'officer_type' => $data['officer_type'],
                        'status'=>ClientOfficer::ACTICESTATUS
                    ]);
                    if(!empty($addClientOfficer)) {
                        $success['SuccessMessage'] = ['Client Officer Add Successfully !!'];
                        return Response::json(['status' => 'success', 'data' => $success]);
                    } else {
                        $errors['ErrorMessage'] = ['clientOfficer Failed to Add !!'];
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

    public function editClientOfficer(Request $req)
    {
        $FilterRequest = ['company_id','contact_id',];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'contact_id' => 'contact id',
        ];
        $validator = Validator::make($data ,[
            'contact_id' => 'required|numeric',
            'company_id'=>'required|numeric',
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
                    $editClientOfficer  = ClientOfficer::where(['company_id'=>$data['company_id'],'contact_id'=>$data['contact_id'],'status'=>ClientOfficer::ACTICESTATUS])->first();
                    if(!empty($editClientOfficer)){
                        $clientOfficer = new ClientOfficerResource($editClientOfficer);
                        return Response::json(['status' => 'success', 'data' => $clientOfficer]);
                    } else {
                        $errors['ErrorMessage'] = ['clientOfficer Not Found !!'];
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
            $errors['ErrorMessage'] = ['company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function updateClientOfficer(Request $req)
    {
        $FilterRequest = ['contact_id', 'company_id','contact_type','officer_type','ceased_on','appointed_on','resigned_on','contact_title','contact_other_title','first_name','last_name','contact_designation','contact_phone_no','contact_email','contact_address_line1','contact_city','contact_county','contact_country','nationality','contact_postal_code','notes'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'contact_id' => 'contact id',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'contact_type' => 'contact_type',
            'officer_type' => 'officer_type',
            'ceased_on' => 'ceased_on',
            'appointed_on' => 'appointed_on',
            'resigned_on' => 'resigned_on',
            'contact_title' => 'contact_title',
            'contact_other_title' => 'contact_other_title',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'contact_designation' => 'contact_designation',
            'contact_phone_no' => 'contact_phone_no',
            'contact_postal_code' => 'contact_postal_code',
            'notes' => 'notes'

        ];
        $validator = Validator::make($data ,[
            'contact_id'=> 'required|numeric',
            'company_id'=>'required|numeric',
            'first_name'=>'required|string',
            'last_name'=>'required|string'
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
                    // return $data;
                    $updateClientOfficer  = ClientOfficer::where([
                        'company_id'=>$data['company_id'],
                        'contact_id'=>$data['contact_id']
                    ])->update([
                        'contact_title' => $data['contact_title'],
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'contact_designation' => $data['contact_designation'],
                        'contact_phone_no' => $data['contact_phone_no'],
                        'contact_email' => $data['contact_email'],
                        'contact_address_line1' => $data['contact_address_line1'],
                        'contact_city' => $data['contact_city'],
                        'contact_county' => $data['contact_county'],
                        'contact_postal_code' => $data['contact_postal_code'],
                        'contact_country' => $data['contact_country'],
                        'notes' => $data['notes']
                    ]);
                    if($updateClientOfficer) {
                        $editClientOfficer  = ClientOfficer::where(['company_id'=>$data['company_id'],'contact_id'=>$data['contact_id'],'status'=>ClientOfficer::ACTICESTATUS])->first();
                        $clientOfficer = new ClientOfficerResource($editClientOfficer);
                        return Response::json(['status' => 'success', 'data' => $clientOfficer]);
                    } else {
                        $errors['ErrorMessage'] = ['clientOfficer Failed to update !!'];
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

    public function getClientOfficer(Request $req)
    {
        $FilterRequest = [ 'company_id','client_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'client_id' => 'client id',
        ];
        $validator = Validator::make($data ,[
            'company_id'=>'required|numeric',
            'client_id'=>'required|numeric',
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
                    $getClientOfficer  = ClientOfficer::where([
                        'company_id'=>$data['company_id'],
                        'client_id'=>$data['client_id'],
                        'status' => ClientOfficer::ACTICESTATUS
                    ])->get();
                    if(!empty($getClientOfficer)) {
                        $clientOfficer = ClientOfficerResource::collection($getClientOfficer);
                        return Response::json(['status' => 'success', 'data' => $clientOfficer]);
                    } else {
                        $client_type = StaticFunctions::getClientTypeByID($req->client_id);
                        //$errors['ErrorMessage'] = ['Record Not Found !!'];
                        $errors['ClientType'] = $client_type;
                        $errors['ClientID'] = $req->client_id;
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

    public function deleteClientOfficer(Request $req)
    {
        $FilterRequest = [ 'company_id','client_id','contact_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'contact_id'=>'contact id',
        ];
        $validator = Validator::make($data ,[
            'company_id'=>'required|numeric',
            'contact_id'=>'required|numeric',
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
                    $deleteClientOfficer  = ClientOfficer::where([
                        'company_id'=>$data['company_id'],
                        'contact_id'=>$data['contact_id']
                    ])->select('status')->first();
                    if($deleteClientOfficer) {
                        $status = $deleteClientOfficer->status;
                        if($status == ClientOfficer::ACTICESTATUS) {
                            ClientOfficer::where([
                                'company_id'=>$data['company_id'],
                                'contact_id'=>$data['contact_id']
                            ])->update(['status'=> ClientOfficer::DEACTIVESTATUS]);

                            $message['SuccessMessage'] = ['clientOfficer delete successfully !!'];
                            return Response::json(['status' => 'success', 'data' => $message]);
                        } else {
                            $message['ErrorMessage'] = ['clientOfficer Deactivated !!'];
                            return Response::json(['status' => 'success', 'data' => $message]);
                        }

                    } else {
                        $errors['ErrorMessage'] = ['clientOfficer Failed to update !!'];
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

    public function getClientActiveOfficer(Request $req)
    {
        $FilterRequest = [ 'company_id','client_id','module_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'client_id' => 'client id',
            'module_id' => 'Module id'
        ];
        $validator = Validator::make($data ,[
            'company_id'=>'required|numeric',
            'client_id'=>'required|numeric',
            'module_id' => 'required'
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
                    $getClientOfficer  = ClientOfficer::where([
                        'company_id'=>$data['company_id'],
                        'client_id'=>$data['client_id'],
                        'officer_type' => ClientOfficer::OFFICERACTIVE,
                        'module_id' => StaticFunctions::getModuleSlugByID($data['module_id'])
                    ])->get();
                    if(!empty($getClientOfficer)) {
                        $clientOfficer = ClientOfficerResource::collection($getClientOfficer);
                        return Response::json(['status' => 'success', 'data' => $clientOfficer]);
                    } else {
                        $client_type = StaticFunctions::getClientTypeByID($req->client_id);
                        //$errors['ErrorMessage'] = ['Record Not Found !!'];
                        $errors['ClientType'] = $client_type;
                        $errors['ClientID'] = $req->client_id;
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
