<?php

namespace App\Http\Controllers\Api\v1\ClientController;

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
use App\ClientContactinfo;
use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Client\ClientJobsResource;
use App\Http\Resources\Client\ClientServiceResource;
use DB;
use App\ClientCode;
use App\TradeCode;
use App\Service;
use App\ClientJob;
use App\ClientService;
use App\ClientDeadline;
use App\DatasbeseConnection;
use App\Http\StaticFunctions\StaticFunctions;
use App\ClientKeyService;
use App\Contact;
use App\Http\Controllers\Api\v1\CompanyController\CompanyController;
use App\ClientOfficer;
use App\LoginRole;
use App\ClientExtra;



class ClientController extends Controller
{
    public function addClientType(Request $req)
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

        $record  = ServiceType::where(['service_type_name'=>$data['service_type_name'],'client_type_id'=>$data['client_type_id']])->first();
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
                    $service['ClientID'] = $new_data->client_type->id;
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

    public function getServiceType(Request $req)
    {
        $FilterRequest = ['client_type_id','company_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'client_type_id' => 'Client Type id',
        ];
        $validator = Validator::make($data ,[
            // 'company_id' => 'digits_between:1,11',
            'client_type_id' => 'digits_between:1,11',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $new_data = ServiceType::where('client_type_id',$data['client_type_id'])->select('service_type_id as ServiceTypeID','service_type_name as ServiceTypeName')->get();
        if(!empty($new_data)) {
            return Response::json(['status' => 'success', 'data' => $new_data]);
        } else {
            $errors['ErrorMessage'] = ['Record Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function ClientList()
    {
        $data=ClientType::select('id','type')->get();

        if($data) {
            foreach ($data as $key => $value) {
                $client_id[$value->id] = $value->type;
            }
            return Response::json(['status' => 'success', 'data' => $client_id]);
        } else {
            $errors['ErrorMessage'] = ['ClientType Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function generatePrefixCode(Request $req)
    {
        $FilterRequest = ['company_id', 'client_code_prefix'];
        $data = $req->only($FilterRequest);
        // changing the input fields name
        $attributes = [
            'company_id' => 'Company ID',
            'client_code_prefix' => 'Client Code Prefix',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|max:50',
            'client_code_prefix' => 'required|',
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
                    $code = ClientCode::where('code_alpha',strtoupper($data['client_code_prefix']))->max('code_digit');
                    if($code > 0) {
                        $code = $code+1;
                        $client_code['CodeDigit'] = $code;
                        return Response::json(['status' => 'success', 'data' => $client_code]);
                    } else {
                        $code = 100;
                        $client_code['CodeDigit'] = $code;
                        return Response::json(['status' => 'success', 'data' => $client_code]);
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

    public function clientAdd(Request $req)
    {
        $FilterRequest = ['company_id','client_name','client_code','client_code_prefix','user_id','manager_id','partner_id','client_acquired','utr','client_type','service_type','company_auth_code','code_digit','registration_no','module_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'Company ID',
            'client_name' => 'Client Name',
            'client_code' => 'Client Code',
            'client_code_prefix' => 'Client Code Prefix',
            'user_id' => 'User ID',
            'manager_id' => 'Manager ID', //add optional*
            'partner_id' => 'Partner ID', //add optional*
            'client_acquired'=> 'Client Acquired',
            'utr'=> 'Utr',//add optional*
            'client_type' => 'Client Type',
            'service_type' => 'Service Type',
            'module_id' => 'Module ID',
            'registration_no','Registration Number'
        ];
        $validator = Validator::make($data ,[
            'client_name' => 'required|string|max:50',
            'client_type' => 'required|numeric|max:50',
            'service_type' => 'required|numeric|digits_between:1,11',
            'partner_id' => 'required|numeric|digits_between:1,11',
            'client_code_prefix' => 'required|string|max:1',
            'code_digit' => 'required|numeric',
            'client_acquired'=> 'required',
            'company_id'=>'required',
            'module_id'=>'required|string'
        ])->setAttributeNames($attributes);
        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $database = CompanyConfig::where('company_id',$data['company_id'])->get();
        if($database) {
            $client_type_data = ClientType::where('id',$data['client_type'])->first();

            $company_db = StaticFunctions::GetKeyValue($database,'company_database');

            if(!empty($client_type_data)) {
                StaticFunctions::db_connection($company_db);
                DB::beginTransaction();
                if($client_type_data->id == ClientType::Limted || $client_type_data->id == ClientType::LLP ) {
                    $validator = Validator::make($data ,[
                        'registration_no' => 'unique:client,registration_no'
                    ])->setAttributeNames($attributes);
                    if($validator->fails())
                    {
                        return Response::json(['status' => 'error', 'data' => $validator->errors()]);
                    }
                }

                $client = Client::create([
                    'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id']),
                    'company_id'=>$data['company_id'],
                    'user_id'=>$data['partner_id'],
                    'client_name'=>$data['client_name'],
                    'client_type'=>$client_type_data->id,
                    'service_type'=>$data['service_type'],
                    'client_acquired'=>StaticFunctions::dateRequets($data['client_acquired']),
                    'utr'=>$data['utr']?$data['utr']:'',
                    'manager_id'=>$data['manager_id']?$data['manager_id']:0,
                    'registration_no'=>($client_type_data->id == ClientType::Limted || $client_type_data->id == ClientType::LLP )?$data['registration_no']:'',
                ]);
                if(!$client) {
                    DB::rollBack();
                    $errors['ErrorMessage'] = ['Fail to add the Client, please try again.'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                } else {
                    $client_code = ClientCode::create([
                        'code_digit'=>$data['code_digit'],
                        'code_alpha'=>strtoupper($data['client_code_prefix']),
                        'client_id'=>$client->client_id,
                    ]);
                    ClientKeyService::create([
                        'client_id'=>$client->client_id
                    ]);
                    ClientExtra::create([
                        'client_id'=>$client->client_id
                    ]);
                    if(!$client_code) {
                        DB::rollBack();
                        $errors['ErrorMessage'] = ['Fail to add the Client Code, please try again.'];
                        return Response::json(['status' => 'error', 'data' => $errors]);
                    } else {
                        DB::commit();
                    }
                }

                if($client_type_data->id == ClientType::SoleTrader || $client_type_data->id == ClientType::Partnership ) {

                } else {

                    $company = CompanyController::GetCompany('companyofficers',$client->registration_no);
                    $company_officer = ClientOfficer::getClientOfficers($company,$client->client_id,$data['company_id']);

                    if(count($company_officer) > 0 ) {
                        foreach ($company_officer as  $officer) {
                            ClientOfficer::create($officer);
                        }
                    }

                    $psc_officers_data = CompanyController::GetCompany('companypersons',$client->registration_no);
                    $company_persons = ClientOfficer::getClientPersons($psc_officers_data,$client->client_id,$data['company_id']);

                    if(count($company_persons) > 0 ) {
                        foreach ($company_persons as $persons) {
                            ClientOfficer::create($persons);
                        }
                    }

                }
                $all_client['ClientID']= $client_code->client_id;
                $all_client['ClientName'] = $client->client_name;
                $all_client['ClientType'] = $client->client_type;
                $all_client['ServiceType'] = $client->service_type;
                $all_client['ClientAcquired'] = $client->client_acquired;
                $all_client['RegistrationNo'] = (!empty($client->registration_no))?$client->registration_no:null;
                $all_client['PartnerID'] = $client->user_id;
                $all_client['CompanyID'] = $client->company_id;
                $all_client['ManagerID'] = $client->manager_id;
                $all_client['Utr'] = (!empty($client->utr))?$client->utr:null;
                $all_client['ClientCodeRrefix'] = $client_code->code_alpha;
                $all_client['CodeDigit'] = $client_code->code_digit;
                return Response::json(['status' => 'success', 'data' => $all_client]);
            } else {
                $errors['ErrorMessage'] = ['Client Type Not Found'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }



    public function getAllClients(Request $req)
    {
        $FilterRequest = ['user_id','company_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|digits_between:1,11',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $database = CompanyConfig::where('company_id',$data['company_id'])->get();
        if($database) {
            $company_db = StaticFunctions::GetKeyValue($database,'company_database');

            if($company_db) {
                $clients = DB::select("Call getClients('$company_db')");
                if(count($clients) >0) {
                    return Response::json(['status' => 'success', 'data' => $clients]);
                } else {
                    $errors['ErrorMessage'] = ['Clients Not found !!'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }

            } else {
                $errors['ErrorMessage'] = ['DataBase Not Found !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }



    public function editClients(Request $req)
    {
        $FilterRequest = ['client_id','company_id'];
        $data = $req->only($FilterRequest);
        // changing the input fields name
        $attributes = [
            'client_id' => 'Client ID',
            'company_id' => 'Company ID',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|digits_between:1,11',
            'client_id' => 'required|digits_between:1,11',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $database = CompanyConfig::where('company_id',$data['company_id'])->get();
        if($database){
            $company_db = StaticFunctions::GetKeyValue($database,'company_database');

            if($company_db) {
                $db_con = StaticFunctions::db_connection($company_db);
                if($db_con) {
                    $client  = Client::with('code')->where(['client_id'=>$req->client_id,'company_id'=>$req->company_id])->first();
                    if($client){
                        $db_con2 = StaticFunctions::db_connection(StaticFunctions::NewPa);
                        if($db_con2){
                            $serviceType= ServiceType::where('service_type_id',$client->service_type)->first();
                            if($serviceType){
                                $get_client['ClientID']= $client->client_id;
                                $get_client['ClientName'] = $client->client_name;
                                $get_client['ClientType'] = $client->client_type;
                                $get_client['ServiceType'] = $client->service_type;
                                $get_client['ClientAcquired'] = $client->client_acquired;
                                $get_client['PartnerID'] = $client->user_id;
                                $get_client['PayRollManagerId'] = $client->payroll_id;
                                $get_client['CompanyID'] = $client->company_id;
                                $get_client['ManagerID'] = $client->manager_id;
                                $get_client['Utr'] = $client->utr;
                                $get_client['ClientCodePrefix'] = $client->code->code_alpha;
                                $get_client['CodeDigit'] = $client->code->code_digit;
                                return Response::json(['status' => 'success', 'data' => $get_client]);
                            } else {
                                $errors['ErrorMessage'] = ['Client No Found!!'];
                                return Response::json(['status' => 'error', 'data' => $errors]);
                            }
                        } else {
                            $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
                            return Response::json(['status' => 'error', 'data' => $errors]);
                        }
                    } else {
                        $errors['ErrorMessage'] = ['Client No Found!!'];
                        return Response::json(['status' => 'error', 'data' => $errors]);
                    }
                } else {
                    $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } else {
                $errors['ErrorMessage'] = ['DataBase Not Found !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function updateClients(Request $req)
    {
        // return $req->all();
        // only for Sole trader or Partnership
        $FilterRequest = ['company_id','client_id','client_name','client_code','client_code_prefix','user_id','manager_id','partner_id','client_acquired','utr','client_type','service_type','company_auth_code','code_digit','staff_id','module_id'];
        $data = $req->only($FilterRequest);
        // return $data['partner_id'];
        $attributes = [
            'company_id' => 'Company ID',
            'client_id' => 'Client ID',
            'client_name' => 'Client Name',
            'client_code' => 'Client Code',
            'client_code_prefix' => 'Client Code Prefix',
            'user_id' => 'User ID',
            'manager_id' => 'Manager ID', //add optional*
            'partner_id' => 'Partner ID', //add optional*
            'client_acquired'=> 'Client Acquired',
            'utr'=> 'Utr',//add optional*
            'client_type' => 'Client Type',
            'service_type' => 'Service Type',
            'staff_id'=>'Staff ID',
            'module_id'=>'Module ID',
        ];
        $validator = Validator::make($data ,[
            'client_name' => 'required|string|max:50',
            'client_type' => 'required|string|max:50',
            'service_type' => 'required|numeric|digits_between:1,11',
            'partner_id' => 'required|numeric|digits_between:1,11',
            'client_code_prefix' => 'required|string|max:1',
            'code_digit' => 'required|numeric',
            'client_acquired'=> 'required',
            'company_id'=>'required',
            'client_id'=>'required',
            'module_id'=>'required|string'
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $database = CompanyConfig::where('company_id',$data['company_id'])->get();
        $company_db = StaticFunctions::GetKeyValue($database,'company_database');

        if($company_db) {

            $db_con = StaticFunctions::db_connection($company_db);
            if($db_con) {

                $get_client = Client::where('client_id',$data['client_id'])->first();
                if(!empty($get_client)) {

                    $client = array(
                        'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id']),
                        'company_id'=>$data['company_id'],
                        'user_id'=>$data['partner_id'],
                        'client_name'=>$data['client_name'],
                        'client_type'=>$data['client_type'],
                        'service_type'=>$data['service_type'],
                        'client_acquired'=>StaticFunctions::dateRequets($data['client_acquired']),
                        'utr'=>$data['utr']?$data['utr']:'',
                        'manager_id'=>$data['manager_id']?$data['manager_id']:0,
                        'staff_id'=>$data['staff_id']?$data['staff_id']:0,
                    );

                    $client_update =Client::where(['client_id'=>$req->client_id,'company_id'=>$req->company_id])->update($client);
                    if(!$client_update) {
                        $errors['ErrorMessage'] = ['Fail to update the Client, please try again.'];
                        return Response::json(['status' => 'error', 'data' => $errors]);
                    } else {
                        $client_code = array(
                            'code_digit'=>$data['code_digit'],
                            'code_alpha'=>strtoupper($data['client_code_prefix']),
                            'client_id'=>$req->client_id,
                        );
                        try {
                            $client_code_update = ClientCode::where(['client_id'=>$req->client_id])->update($client_code);
                            $data['ClientID'] = $req->client_id;
                            return Response::json(['status' => 'success', 'data' => $data]);
                        } catch (\Exception $e) {return $e-getMessage();}
                    }
                } else {
                    $errors['ErrorMessage'] = ['Client Not Found'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } else {
                $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['DataBase Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function addTradeCodeClients(Request $req)
    {
        $FilterRequest = ['trade_code'];
        $data = $req->only($FilterRequest);
        // changing the input fields name
        $attributes = [
            'trade_code' => 'Trade Code',
        ];
        $validator = Validator::make($data ,[
            // 'user_id' => 'required|digits_between:1,11',
            'trade_code' => 'required|unique:trade_list,trade_code|digits_between:1,11',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        //only for llp and limited
        $trade = TradeCode::create(['trade_code'=>$req->trade_code]);

        if($trade){
            $Message['message'] = ['success fully add trade code'];
            return Response::json(['status' => 'success', 'data' => $Message]);
        } else {
            $errors['ErrorMessage'] = ['Fail to add the trade Code, please try again.'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }





    public function getPartners(Request $req)
    {
        $FilterRequest = ['company_id'];
        $data = $req->only($FilterRequest);
        // changing the input fields name
        $attributes = [
            'company_id' => 'company id',
        ];
        $validator = Validator::make($data ,[
            // 'user_id' => 'required|digits_between:1,11',
            'company_id' => 'required|digits_between:1,11',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $user = User::where('company_id',$req->company_id)->first();
        if(!empty($user)) {
            $new_array=[];
            if(isset($user->loginRolePartner) && !empty($user->loginRolePartner)){

                foreach($user->loginRolePartner as $key => $value) {
                    if(isset($value->getusername->first_name)){
                        $new_array[] = ['UserId' => $value->user_id , 'Name' => $value->getusername->first_name];
                    }
                }

                if($new_array) {
                    return Response::json(['status' => 'success', 'data' => $new_array]);
                } else {
                    $errors['ErrorMessage'] = ['Record Not Found !!'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } else {
                $errors['ErrorMessage'] = ['Partners Not Found'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['Company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function getManagers(Request $req)
    {
        $FilterRequest = ['user_id','company_id'];
        $data = $req->only($FilterRequest);
        // changing the input fields name
        $attributes = [
            // 'user_id' => 'user id',
            'company_id' => 'company id',
        ];
        $validator = Validator::make($data ,[
            // 'user_id' => 'digits_between:1,11',
            'company_id' => 'digits_between:1,11',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $user = User::where('company_id',$req->company_id)->first();
        if(!empty($user)) {
            $new_array=[];
            if(!empty($user->loginRoleManager)){
                // return $user->loginRoleManager;
                foreach ($user->loginRoleManager as $key => $value) {
                    if(isset($value->getusername->first_name)){
                        $new_array[] = ['UserId' => $value->user_id , 'Name' => $value->getusername->first_name];
                    }
                }

                if($new_array) {
                    return Response::json(['status' => 'success', 'data' => $new_array]);
                } else {
                    $errors['ErrorMessage'] = ['Record Not Found !!'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } else {
                $errors['ErrorMessage'] = ['Managers Not Found !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['Company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function getPayroleManagers(Request $req)
    {
        $FilterRequest = ['user_id','company_id'];
        $data = $req->only($FilterRequest);
        // changing the input fields name
        $attributes = [
            // 'user_id' => 'user id',
            'company_id' => 'company id',
        ];
        $validator = Validator::make($data ,[
            // 'user_id' => 'digits_between:1,11',
            'company_id' => 'digits_between:1,11',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $user = User::where('company_id',$req->company_id)->first();
        if(!empty($user)) {
            $new_array=[];
            if(count($user->loginPayRoleManager)>0){
                // return $user->loginRoleManager;
                foreach ($user->loginPayRoleManager as $key => $value) {
                    if(isset($value->getusername->first_name)){
                        $new_array[] = ['UserId' => $value->user_id , 'Name' => $value->getusername->first_name];
                    }
                }

                if($new_array) {
                    return Response::json(['status' => 'success', 'data' => $new_array]);
                } else {
                    $errors['ErrorMessage'] = ['Record Not Found !!'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } else {
                $errors['ErrorMessage'] = ['PayRole Managers Not Found !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['Company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function ClientAddressAdd(Request $req)
    {
        $FilterRequest = ['corres_check','address_type','city','county','country','postal_code','phone_no','fax_no','company_id','module_id','client_id','address_id','address_line1','address_type_other','mobile','email','website'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'address_line1' => 'Address',
            'city' => 'City',
            'county' => 'County',
            'country' => 'Country',
            'postal_code' => 'Postal Code',
            'phone_no' => 'Phone No',
            'fax_no' => 'Fax No',
            'module_id' => 'Module ID',
            'client_id' => 'Client ID',
            'company_id' => 'Company ID',
        ];
        $validator = Validator::make($data ,[
            'address_line1' => 'required|string',
            'city' => 'required|string',
            'county' => 'required',
            'country' => 'required|string|max:50',
            'postal_code' => 'required|string',
            'phone_no' => 'required|numeric|digits_between:10,16',
            'fax_no' => 'required|numeric|digits_between:10,16',
            'module_id' => 'required|string',
            'client_id' => 'required|numeric',
            'company_id' => 'required|numeric'
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

                    $addressData['client_id']=$data['client_id'];
                    $addressData['address_line1']=$data['address_line1'];
                    $addressData['address_type']=$data['address_type'];
                    $addressData['address_type_other']=$data['address_type_other'];
                    $addressData['city']=$data['city'];
                    $addressData['county']=$data['county'];
                    $addressData['postal_code']=$data['postal_code'];
                    $addressData['mobile']=$data['mobile'];
                    $addressData['country']=$data['country'];
                    $addressData['phone_no']=$data['phone_no'];
                    $addressData['fax']=$data['fax_no'];
                    $addressData['email']=$data['email'];
                    $addressData['website']=$data['website'];
                    $addressData['module_id']=StaticFunctions::getModuleSlugByID($data['module_id']);


                    if(!empty($data['corres_check']) && isset($data['corres_check'])) {
                        $insert_data=ClientContactinfo::create($addressData);
                        $addressData['address_type']= "Correspondence";
                        $insert_data_corres=ClientContactinfo::create($addressData);

                        if($insert_data && $insert_data_corres) {
                            $success['SuccessMessage'] = ['Successfully Add Correspondence Address'];
                            return Response::json(['status' => 'success', 'data' => $success]);
                        } else {
                            $errors['ErrorMessage'] = ['Failled to add  Correspondence Address !!'];
                            return Response::json(['status' => 'error', 'data' => $errors]);
                        }
                    } else {
                        $insert_data=ClientContactinfo::create($addressData);
                        if(!empty($insert_data))
                        {
                            $success['SuccessMessage'] = ['Successfully Add Address'];
                            return Response::json(['status' => 'success', 'data' => $success]);
                        } else {
                            $errors['ErrorMessage'] = ['Failled to add  Address !!'];
                            return Response::json(['status' => 'error', 'data' => $errors]);
                        }
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

    public function ClientAddressEdit(Request $req)
    {
        $FilterRequest = ['company_id','module_id','address_id'];
        $data = $req->only($FilterRequest);

        // changing the input fields name
        $attributes = [
            'company_id' => 'Company ID',
            'module_id' => 'Module ID',
            'address_id' => 'Address ID',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|numeric',
            'module_id' => 'required|string',
            'address_id' => 'required|numeric',
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
                    $ClientAddress = ClientContactInfo::where(['cci_id'=>$data['address_id'],'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id'])])->first();
                    if(!empty($ClientAddress)) {
                        $client_address = new ClientResource($ClientAddress);
                        return Response::json(['status' => 'success', 'data' => $client_address]);
                    } else {
                        $errors['ErrorMessage'] = ['Client Address Not Found !!'];
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

    public function ClientAddressUpdate(Request $req)
    {
        $FilterRequest = ['client_id','address_line1','address_type','city','county','country',
            'postal_code','phone_no','fax_no','module_id','address_id','company_id','website','email','mobile','address_type_other'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'address_line1' => 'Address',
            'city' => 'City',
            'county' => 'County',
            'country' => 'Country',
            'postal_code' => 'Postal Code',
            'phone_no' => 'Phone No',
            'fax_no' => 'Fax No',
            'module_id' => 'Module ID',
            'company_id' => 'Company ID',
        ];
        $validator = Validator::make($data ,[
            'client_id' => 'required',
            'address_line1' => 'required|string',
            'city' => 'required|string',
            'county' => 'required',
            'country' => 'required|string|max:50',
            'postal_code' => 'required|string',
            'phone_no' => 'required|numeric|digits_between:10,16',
            'fax_no' => 'required|numeric|digits_between:10,16',
            'module_id' => 'required|string',
            'company_id' => 'required|numeric'
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
                    $update_data['client_id']=$data['client_id'];
                    $update_data['address_line1']=$data['address_line1'];
                    $update_data['address_type']=$data['address_type'];
                    $update_data['address_type_other']=$data['address_type_other'];
                    $update_data['city']=$data['city'];
                    $update_data['county']=$data['county'];
                    $update_data['postal_code']=$data['postal_code'];
                    $update_data['country']=$data['country'];
                    $update_data['phone_no']=$data['phone_no'];
                    $update_data['mobile']=$data['mobile'];
                    $update_data['fax']=$data['fax_no'];
                    $update_data['email']=$data['email'];
                    $update_data['website']=$data['website'];
                    $update_data['module_id']=StaticFunctions::getModuleSlugByID($data['module_id']);

                    $return_data = ClientContactInfo::where('cci_id',$data['address_id'])->update($update_data);

                    if(!empty($data['corres_check'])) {
                        $update_data['address_type']=$data['corres_check'];
                        $insert_data = ClientContactinfo::create($update_data);
                    }

                    if($return_data) {
                        $success['SuccessMessage'] = ['Successfully Update Client Address'];
                        return Response::json(['status' => 'success', 'data' => $success]);
                    } else {
                        $error['ErrorMessage'] = ['Failled to Update Client Address'];
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

    public function ClientAddressDelete(Request $req)
    {
        $FilterRequest = ['module_id','address_id','company_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'address_id' => 'Address ID',
            'company_id' => 'Company ID',
            'module_id' => 'module ID',
        ];
        $validator = Validator::make($data ,[
            'address_id' => 'required|numeric',
            'company_id' => 'required|numeric',
            'module_id' => 'required|string'
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
                    $delete_data = ClientContactinfo::where('cci_id',$data['address_id'])->update(['status'=>'Previous']);
                    if($delete_data) {
                        $success['Message'] = ['Client Address Deleted Succesfuly'];
                        return Response::json(['status' => 'success', 'data' => $success]);
                    } else {
                        $errors['ErrorMessage'] = ['Failed to delete Client Address !!'];
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


    public function ClientAddressShow(Request $req)
    {
        $FilterRequest = ['module_id','company_id','client_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'Company ID',
            'module_id' => 'module ID',
            'client_id' => 'client ID',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|numeric',
            'module_id' => 'required|string',
            'client_id' => 'required|numeric'
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
                    $clientAddress = ClientContactInfo::where('client_id',$data['client_id'])->orderBy('cci_id','desc')->get();
                    if(count($clientAddress) > 0) {
                        $client_address = ClientResource::collection($clientAddress);
                    } else {
                        $client_address['ClientID'] = $req->client_id;
                        $client_address['ClientType'] = StaticFunctions::getClientTypeByID($req->client_id);
                    }

                    return Response::json(['status' => 'success', 'data' => $client_address]);

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
    
//     public function SaveOptionalService(Request $req)
//     {
//         $FilterRequest = ['client_id','vat_number','next_vat_return','vat_return_period','vat_return_date','payroll_start_date','payroll_type','payroll_due_date','service_name','duration_month','vat_registered','prepare_payroll','duration_day','service_type','service_track','repeat_type','repeat_number','require_confirm','cs_id','start_date','year_end','due_date','job_status','completed_by','completed_on','confirmed_on','user_id','initial_date','company_id','service_status','prepare_vat'];
//         $data = $req->only($FilterRequest);
//         if(!empty($data['prepare_vat'])) {
//             $attributes = [
//                 'next_vat_return'=>'Next Vate Return Date',
//             ];
//             $validator = Validator::make($data ,[
//                 'next_vat_return' => 'required',
//             ])->setAttributeNames($attributes);

//             if($validator->fails())
//             {
//                 return Response::json(['status' => 'error', 'data' => $validator->errors()]);
//             }
//         }
//         if( !empty($data['prepare_payroll'])) {
//             $attributes = [
//                 'payroll_start_date' => 'Payroll Start Date',
//             ];
//             $validator = Validator::make($data ,[
//                 'payroll_start_date' => 'required',
//             ])->setAttributeNames($attributes);

//             if($validator->fails())
//             {
//                 return Response::json(['status' => 'error', 'data' => $validator->errors()]);
//             }
//         }

//         $database = CompanyConfig::where('company_id',$data['company_id'])->get();
//         $company_db = StaticFunctions::GetKeyValue($database,'company_database');

//         if($company_db) {

//             $db_con = StaticFunctions::db_connection($company_db);
//             if($db_con) {
                
//                 $clinet_deadline_data = array(
//                     'client_id'          =>$data['client_id'],
//                     'vat_registered'     =>$data['vat_registered'],
//                     'vat_number'         =>$data['vat_number'],
//                     'next_vat_return'    =>StaticFunctions::dateRequets($data['next_vat_return']),
//                     'vat_return_period'  =>$data['vat_return_period'],
//                     'payroll_start_date' =>StaticFunctions::dateRequets($data['payroll_start_date']),
//                     'payroll_type'       =>$data['payroll_type'],
//                     'prepare_payroll'    =>$data['prepare_payroll'],
//                     'prepare_vat_return'       =>$data['prepare_vat'],
//                     'vat_return_date'    =>StaticFunctions::dateRequets($data['vat_return_date']),
//                 );
//                 foreach ($variable as $key => $value) {
//                         # code...
//                 }
//                 $client_deadline=
//                 $service_name=$data['service_name'];
//                 foreach ($service_name as $key => $value)  {
//                     $db_con = StaticFunctions::db_connection(StaticFunctions::NewPa);
//                     $service_data=Service::where('service_id',$value)->first();
//                     $db_con = StaticFunctions::db_connection($company_db);
//                     if($data['vat_return_period']=="quarterly" || $data['vat_return_period']=="monthly"){
//                     $duration_month = 1;
//                     $duration_day = 7;
//                 }else if($data['vat_return_period']=="annual"){
//                     $duration_month = 2;
//                     $duration_day = 0;           
//                 }
//                       $clinet_service_data = array(
//                         'client_id'       =>$data['client_id'],
//                         'initial_date'    =>StaticFunctions::dateRequets($data['next_vat_return']),
//                         'service_name'    =>$service_data->service_name,
//                         'service_id'      =>$service_data->service_id,
//                         'service_track'   =>$service_data->service_track,
//                         'duration_month'  =>$duration_month,
//                         'duration_day'    =>$duration_day,
//                         'service_type'    =>$service_data->service_category,
//                         'repeat_type'     =>$service_data->repeat_type,
//                         'repeat_number'   =>$service_data->repeat_number,
//                         'service_status'  =>'Active',
//                     );
//                     $client_service= ClientService::create($clinet_service_data);
//                     if($service_data->service_track=='Tracked' && isset($data['vat_return_period']) && !empty($data['vat_return_period']))
//                     {
//                      $start_date = $data['next_vat_return'];
//                      $due_date = date("Y-m-d");
//                      while (strtotime($due_date) >= strtotime($start_date)) {
//                         if($data['vat_return_period']=='monthly') {
//                             $time = strtotime($start_date);
//                             $end_date = date("Y-m-d", strtotime("+1 month", $time));
//                         } else if($data['vat_return_period']=='quarterly') {
//                             $time = strtotime($start_date);
//                             $end_date = date("Y-m-d", strtotime("+3 month", $time));
//                         } else {
//                             $time = strtotime($start_date);
//                             $end_date = date("Y-m-d", strtotime("+1 years", $time));
//                         }

//                         $clinet_jobs_data = array(
//                             'cs_id'                  =>$client_service->cs_id,
//                             'start_date'             =>StaticFunctions::dateRequets($start_date),
//                             'year_end'               =>StaticFunctions::dateRequets($end_date),
//                             'job_status'             =>'New',
//                             'user_id'                =>$data['user_id'],
//                             'client_id'              =>$data['client_id'],
//                         );
//                         $client_jobs=ClientJob::create($clinet_jobs_data);
//                         if($client_jobs) {
//                             $start_date=$end_date;
//                             $client_jobs='';
//                             $clinet_jobs_data='';
//                         }
//                     }  $client_service = '';
//                 }
//             }
//             $success['ClientID'] = $data['client_id'];
//             $success['ClientType'] = StaticFunctions::getClientTypeByID($data['client_id']);
//             return Response::json(['status' => 'success', 'data' => $success]);

//         }  else {
//             $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
//             return Response::json(['status' => 'error', 'data' => $errors]);
//         }
//     } else {
//         $errors['ErrorMessage'] = ['DataBase Not Found !!'];
//         return Response::json(['status' => 'error', 'data' => $errors]);
//     }
// }
    public function SaveOptionalService(Request $req)
    {
        $FilterRequest = ['client_id','vat_number','next_vat_return','vat_return_period','vat_return_date',
            'payroll_start_date','payroll_type','payroll_due_date',
            'vat_registered','prepare_payroll','duration_day','service_type','service_track','repeat_type',
            'repeat_number','require_confirm','cs_id','start_date','year_end','due_date','job_status',
            'completed_by','completed_on','confirmed_on','user_id','initial_date','company_id',
            'service_status','prepare_vat','last_vat_return','first_vat_return','prepare_vat_return'];

        $data = (object) $req->only($FilterRequest);
        // if(!empty($data['prepare_vat'])) {
        //     $attributes = [
        //         'next_vat_return'=>'Next Vate Return Date',
        //     ];
        //     $validator = Validator::make($req->only($FilterRequest) ,[
        //         'next_vat_return' => 'required',
        //     ])->setAttributeNames($attributes);

        //     if($validator->fails())
        //     {
        //         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        //     }
        // }
        // if( !empty($data['prepare_payroll'])) {
        //     $attributes = [
        //         'payroll_start_date' => 'Payroll Start Date',
        //     ];
        //     $validator = Validator::make($data ,[
        //         'payroll_start_date' => 'required',
        //     ])->setAttributeNames($attributes);

        //     if($validator->fails())
        //     {
        //         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        //     }
        // }

        $database = CompanyConfig::where('company_id',$data->company_id)->get();
        if($database) {
            $company_db = StaticFunctions::GetKeyValue($database,'company_database');
            if($company_db) {
                $db_con = StaticFunctions::db_connection(strtolower($company_db));
                if($db_con) {

                    $client_id = $data->client_id;
                    $user_id = $data->user_id;
                    
                    $vat_registered = !empty($data->vat_registered)?$data->vat_registered:0;
                    $vat_number = !empty($data->vat_number)?$data->vat_number:0;
                    $prepare_vat = !empty($data->prepare_vat)?$data->prepare_vat:0;
                    $vat_return_period = !empty($data->vat_return_period)?$data->vat_return_period:0;
                    $prepare_payroll = !empty($data->prepare_payroll)?$data->prepare_payroll:0;
                    $payroll_type = !empty($data->payroll_type)?$data->payroll_type:0;
                    
                    $next_vat_return = !empty($data->next_vat_return)?$data->next_vat_return:null;
                    $vat_return_date = !empty($data->vat_return_date)?$data->vat_return_date:null;
                    $payroll_start_date = !empty($data->payroll_start_date)?$data->payroll_start_date:null;
                    $last_vat_return = !empty($data->last_vat_return)?$data->last_vat_return:null;
                    $payroll_due_date = !empty($data->payroll_due_date)?$data->payroll_due_date:null;
                    
                    $payroll_year_end = "05-04-".date("Y");
                    if(strtotime($payroll_year_end)<strtotime(date('Y-m-d'))){
                        $payroll_year_end = date ( 'd-m-Y',strtotime ( '+1 years' , strtotime ( $payroll_year_end ) ) );
                    }

                    $client_deadlines['vat_registered'] = $vat_registered;
                    $client_deadlines['vat_number'] = preg_replace('/\s+/', ' ', trim($vat_number));
                    if($vat_registered==1 && $prepare_vat==1) {
                        $client_deadlines['first_vat_return'] = StaticFunctions::dateRequets($last_vat_return);
                        $client_deadlines['next_vat_return'] = StaticFunctions::dateRequets($next_vat_return);
                        $client_deadlines['prepare_vat_return'] = $prepare_vat;
                        $client_deadlines['vat_return_period'] = $vat_return_period;
                        $client_deadlines['vat_return_date'] = StaticFunctions::dateRequets($vat_return_date);
                    } else {
                        $client_deadlines['prepare_vat_return'] = 0;
                    }

                    $client_deadlines['prepare_payroll'] = $prepare_payroll;
                    if($prepare_payroll==1){
                        $client_deadlines['payroll_start_date'] = StaticFunctions::dateRequets($payroll_start_date);
                        $client_deadlines['payroll_type'] = $payroll_type;
                    }
                    
                    $savedeadline = ClientDeadline::where('client_id',$client_id)->update($client_deadlines); 
                    if($savedeadline) {      
                         $current_deadline = ClientDeadline::where('client_id',$client_id)->orderBy('client_id','DESC')->first();
                        $client_data = Client::where('client_id',$client_id)->first();
                        $payroll_manager = $client_data->staff_id;
                        $client_type = $client_data->client_type;
                        if($client_type==ClientType::SoleTrader){
                            $year_end = $current_deadline->tax_return_date;
                        } else {
                            $year_end = $current_deadline->accounting_reference;
                        }
                        if($prepare_vat==1){

                            $service_name[] = "VAT Return";
                            
                            if($vat_return_period=="quarterly" || $vat_return_period=="monthly") {
                                $duration_month = 1;
                                $duration_day = 7;
                                if($vat_return_period=="quarterly") {

                                    $repeat_type = 'Months';
                                    $repeat_number = 3;                     
                                }else if($vat_return_period=="monthly") {
                                    $repeat_type = 'Months';
                                    $repeat_number = 1;
                                }
                                $vat_return_date = date ( 'Y-m-d',strtotime ( '+1 months' , strtotime ( $next_vat_return ) ) );
                                $vat_return_date = date ( 'd-m-Y',strtotime ( '+7 days' , strtotime ( $vat_return_date ) ) );
                            }else if($vat_return_period=="annual") {
                                $duration_month = 2;
                                $duration_day = 0;
                                $repeat_type = 'Years';
                                $repeat_number = 1;                 
                                $vat_return_date = date ( 'Y-m-d',strtotime ( '+2 months' , strtotime ( $next_vat_return ) ) );
                            }
                            
                             $get_exist_service =ClientService::where(['service_name'=>'VAT Return','client_id'=>$client_id])->orderBy('client_id','DESC')->first();
                            if(!empty($get_exist_service) && !empty($vat_return_date)) {
                                $add_vat_service['client_id'] = $client_id;
                                $add_vat_service['service_name'] = "VAT Return";
                                $add_vat_service['initial_date'] =  StaticFunctions::dateRequets($next_vat_return);
                                $add_vat_service['duration_month'] = $duration_month;
                                $add_vat_service['duration_day'] = $duration_day;
                                $add_vat_service['repeat_type'] = $repeat_type;
                                $add_vat_service['repeat_number'] = $repeat_number;
                                $add_vat_service['service_type'] = 'Optional';                    
                                $last_service_id = ClientService::create($add_vat_service);

                                if(!empty($vat_return_date)) {
                                    $add_vat_job['cs_id'] = $last_service_id->cs_id;
                                    $add_vat_job['client_id'] = $client_id;
                                    $add_vat_job['job_status'] = 'New';
                                    $add_vat_job['user_id'] = $user_id;

                                    $next_vat_return =  StaticFunctions::dateRequets($next_vat_return);
                                    $vat_return_date =  StaticFunctions::dateRequets($vat_return_date);

                                    $add_vat_job['start_date'] = $next_vat_return;

                                    if(strtotime($year_end)<strtotime($add_vat_job['start_date'])) {
                                        $year_end = StaticFunctions::dateRequets(StaticFunctions::AddYears($year_end,1));
                                    }

                                    $add_vat_job['year_end'] = $year_end;
                                    $add_vat_job['due_date'] = $vat_return_date;
                                    $add_vat_job['created_at'] = date('Y-m-d H:i:s');
                                    ClientJob::create($add_vat_job);

                                    while($vat_return_date<date('Y-m-d')){
                                        $next_vat_return = date ( 'Y-m-d',strtotime ( '+'.intval($repeat_number).' '.$repeat_type , strtotime ( $next_vat_return ) ) );
                                        $vat_return_date = date ( 'Y-m-d',strtotime ( '+'.intval($repeat_number).' '.$repeat_type , strtotime ( $vat_return_date ) ) );
                                        $add_vat_job['start_date'] = $next_vat_return;

                                        if(strtotime($year_end)<strtotime($add_vat_job['start_date'])) {
                                            $year_end= StaticFunctions::dateRequets(StaticFunctions::AddYears($year_end,1));
                                        }

                                        $add_vat_job['year_end'] = $year_end;
                                        $add_vat_job['due_date'] = $vat_return_date;
                                        $add_vat_job['created_at'] = date('Y-m-d H:i:s');
                                        ClientJob::create($add_vat_job);
                                    }
                                }
                            }elseif(!empty($get_exist_service) && ($get_exist_service->service_status=='Inactive')) {
                                $add_vat_service['initial_date'] =  StaticFunctions::dateRequets($next_vat_return);
                                $add_vat_service['duration_month'] = $duration_month;
                                $add_vat_service['duration_day'] = $duration_day;
                                $add_vat_service['repeat_type'] = $repeat_type;
                                $add_vat_service['repeat_number'] = $repeat_number;
                                $add_vat_service['service_status'] = 'Active';                    
                                $last_service_id = ClientService::updateOrCreate(['cs_id' => $get_exist_service->cs_id],
                                    $add_vat_service);
                                $last_service_id = DB::getPdo()->lastInsertId();
                                $get_exist_jobs = ClientJob::where(['cs_id'=>$last_service_id,'client_id'=>$client_id,'job_status'=>'New'])->first();
                                if(!empty($get_exist_jobs)) {
                                    $add_vat_job['job_status'] = 'Cancelled';
                                    ClientJob::where(['cs_id'=>$last_service_id,'job_status'=>'New'])->update($add_vat_job);
                                }

                                $add_vat_job['cs_id'] = $get_exist_service->cs_id;
                                $add_vat_job['client_id'] = $client_id;
                                $add_vat_job['job_status'] = 'New';
                                $add_vat_job['user_id'] = $user_id;

                                $next_vat_return =  StaticFunctions::dateRequets($next_vat_return);
                                $vat_return_date =  StaticFunctions::dateRequets($vat_return_date);

                                $add_vat_job['start_date'] = $next_vat_return;

                                if(strtotime($year_end)<strtotime($add_vat_job['start_date'])) {
                                    $year_end= StaticFunctions::dateRequets(StaticFunctions::AddYears($year_end,1));
                                }

                                $add_vat_job['year_end'] = $year_end;
                                $add_vat_job['due_date'] = $vat_return_date;
                                ClientJob::create($add_vat_job);                    

                                while($vat_return_date<date('Y-m-d')){
                                    $next_vat_return = date ( 'Y-m-d',strtotime ( '+'.intval($repeat_number).' '.$repeat_type , strtotime ( $next_vat_return ) ) );
                                    $vat_return_date = date ( 'Y-m-d',strtotime ( '+'.intval($repeat_number).' '.$repeat_type , strtotime ( $vat_return_date ) ) );
                                    $add_vat_job['start_date'] = $next_vat_return;

                                    if(strtotime($year_end)<strtotime($add_vat_job['start_date'])) {
                                        $year_end= StaticFunctions::dateRequets(StaticFunctions::AddYears($year_end,1));
                                    }

                                    $add_vat_job['year_end'] = $year_end;
                                    $add_vat_job['due_date'] = $vat_return_date;
                                    $add_vat_job['created_at'] = date('Y-m-d H:i:s');
                                    ClientJob::create($add_vat_job); 
                                }
                            }
                        }
                        if($vat_registered==0 || $prepare_vat==0) {
                            $get_exist_service = ClientService::where(['service_name'=>'VAT Return','client_id'=>$client_id,'service_status'=>'Active'])->orderBy('client_id','DESC')->first();
                            
                            if(!empty($get_exist_service)){
                                $add_vat_service['service_status'] = 'Inactive';                    
                            }
                        }

                        if($prepare_payroll==1) {
                            $duration_month = 0;
                            $duration_day = 0;
                            $service_name[] = "Payroll";
                            
                            if($payroll_type=="Weekly") {
                                $duration_day = 7;
                                $repeat_type = 'Weeks';
                                $repeat_number = 1;
                            } else if($payroll_type=="Fortnightly") {
                                $duration_day = 15;
                                $repeat_type = 'Days';
                                $repeat_number = 15;
                            } else if($payroll_type=="FourWeekly") {
                                $duration_day = 28;
                                $repeat_type = 'Weeks';
                                $repeat_number = 4;
                            } else if($payroll_type=="Monthly") {
                                $duration_month = 1;
                                $repeat_type = 'Months';
                                $repeat_number = 1;
                            }
                            
                            $payroll_due_date = date ( 'd-m-Y',strtotime ( '+'.$repeat_number.' '.$repeat_type , strtotime ( $payroll_start_date ) ) );

                            $get_exist_service = ClientService::where(['service_name'=>'Payroll','client_id'=>$client_id])->orderBy('client_id','DESC')->first();
                            if(!empty($get_exist_service) && !empty($payroll_due_date)){
                                $add_payroll_service['client_id'] = $client_id;
                                $add_payroll_service['service_name'] = "Payroll";
                                $add_payroll_service['initial_date'] =  StaticFunctions::dateRequets($payroll_start_date);
                                $add_payroll_service['duration_month'] = $duration_month;
                                $add_payroll_service['duration_day'] = $duration_day;
                                $add_payroll_service['repeat_type'] = $repeat_type;
                                $add_payroll_service['repeat_number'] = $repeat_number;
                                $add_payroll_service['service_type'] = 'Optional';
                                
                                $last_service_id = ClientService::create($add_payroll_service);

                                if(!empty($last_service_id) && !empty($payroll_due_date)){
                                    $add_payroll_job['cs_id'] = $last_service_id->cs_id;
                                    $add_payroll_job['client_id'] = $client_id;
                                    $add_payroll_job['job_status'] = 'New';
                                    $add_payroll_job['user_id'] = $user_id;

                                    $payroll_start_date =  StaticFunctions::dateRequets($payroll_start_date);
                                    $payroll_due_date =  StaticFunctions::dateRequets($payroll_due_date);

                                    $add_payroll_job['start_date'] = $payroll_start_date;
                                    $add_payroll_job['year_end'] =  StaticFunctions::dateRequets($payroll_year_end);
                                    $add_payroll_job['due_date'] = $payroll_due_date;
                                    
                                    $add_payroll_job['assigned_to'] = $payroll_manager;
                                    $add_payroll_job['assigned_date'] = date('Y-m-d');
                                    $add_payroll_job['assigned_due_date'] = $payroll_due_date;
                                    
                                    ClientJob::create($add_payroll_job);

                                    while($payroll_due_date<date('Y-m-d')){
                                        $payroll_start_date = date ( 'Y-m-d',strtotime ( '+'.intval($repeat_number).' '.$repeat_type , strtotime ( $payroll_start_date ) ) );
                                        $payroll_due_date = date ( 'Y-m-d',strtotime ( '+'.intval($repeat_number).' '.$repeat_type , strtotime ( $payroll_due_date ) ) );

                                        $add_payroll_job['start_date'] = $payroll_start_date;
                                        $add_payroll_job['year_end'] =  StaticFunctions::dateRequets($payroll_year_end);
                                        $add_payroll_job['due_date'] = $payroll_due_date;
                                        
                                        $add_payroll_job['assigned_due_date'] = $payroll_due_date;
                                        
                                        ClientJob::create($add_payroll_job);
                                    }

                                }
                            } else if(!empty($get_exist_service) && ($get_exist_service->service_status=='Inactive')) {
                                $add_payroll_service['initial_date'] =  StaticFunctions::dateRequets($payroll_start_date);
                                $add_payroll_service['duration_month'] = $duration_month;
                                $add_payroll_service['duration_day'] = $duration_day;
                                $add_payroll_service['repeat_type'] = $repeat_type;
                                $add_payroll_service['repeat_number'] = $repeat_number;
                                $add_payroll_service['service_status'] = 'Active';                    
                                
                                $last_service_id = ClientService::updateOrCreate(['cs_id' => $get_exist_service->cs_id],
                                    $add_vat_service);
                                $last_service_id = DB::getPdo()->lastInsertId();
                                $get_exist_jobs = ClientJob::where(['cs_id'=>$last_service_id,'client_id'=>$client_id,'job_status'=>'New'])->first();
                                
                                if(!empty($get_exist_jobs)) {
                                    $add_vat_job['job_status'] = 'Cancelled';
                                    $add_vat_job['updated_at'] = date('Y-m-d H:i:s');
                                    ClientJob::where(['cs_id'=>$last_service_id,'job_status'=>'New'])->update($add_vat_job);
                                }


                                $add_payroll_job['cs_id'] = $last_service_id;
                                $add_payroll_job['client_id'] = $client_id;
                                $add_payroll_job['job_status'] = 'New';
                                $add_payroll_job['user_id'] = $user_id;

                                $payroll_start_date =  StaticFunctions::dateRequets($payroll_start_date);
                                $payroll_due_date =  StaticFunctions::dateRequets($payroll_due_date);

                                $add_payroll_job['start_date'] = $payroll_start_date;
                                $add_payroll_job['year_end'] =  StaticFunctions::dateRequets($payroll_year_end);
                                $add_payroll_job['due_date'] = $payroll_due_date;
                                
                                $add_payroll_job['assigned_to'] = $payroll_manager;
                                $add_payroll_job['assigned_date'] = date('Y-m-d');
                                $add_payroll_job['assigned_due_date'] = $payroll_due_date;
                                
                                $get_exist_jobs = ClientJob::where(['cs_id'=>$last_service_id,'client_id'=>$client_id,'start_date'=>$payroll_start_date,'due_date'=>$payroll_due_date])->where('job_status','<>','Cancelled')->first();

                                if(!empty($get_exist_jobs)){
                                    ClientJob::create($add_payroll_job);
                                }

                                while($payroll_due_date<date('Y-m-d')){
                                    $payroll_start_date = date ( 'Y-m-d',strtotime ( '+'.intval($repeat_number).' '.$repeat_type , strtotime ( $payroll_start_date ) ) );
                                    $payroll_due_date = date ( 'Y-m-d',strtotime ( '+'.intval($repeat_number).' '.$repeat_type , strtotime ( $payroll_due_date ) ) );

                                    $add_payroll_job['start_date'] = $payroll_start_date;
                                    $add_payroll_job['year_end'] =  StaticFunctions::dateRequets($payroll_year_end);
                                    $add_payroll_job['due_date'] = $payroll_due_date;
                                    
                                    $add_payroll_job['assigned_due_date'] = $payroll_due_date;
                                    
                                    $get_exist_jobs = ClientJob::where(['cs_id'=>$last_service_id,'client_id'=>$client_id,'start_date'=>$payroll_start_date,'due_date'=>$payroll_due_date])->where('job_status','<>','Cancelled')->first();

                                    if(!empty($get_exist_jobs)) {
                                        ClientJob::create($add_payroll_job);
                                    }
                                }
                            }               
                        }

                        $succes['SuccessMessage']   = ['Optional Services Added Successfully'];
                        return Response::json(['status' => 'success', 'data' => $succes]);

                    } else {
                        $error['ErrorMessage']   = ['Unable to Add Optional Services'];
                        return Response::json(['status' => 'error', 'data' => $error]);
                    }
                }
            }
        }
    }

    public function ListOptionalServic(Request $req)
    {
         $FilterRequest = ['company_id','client_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'client id' => 'client id',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|digits_between:1,11',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $database = CompanyConfig::where('company_id',$req->company_id)->get();
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');

        if($company_db) {
         $service = Service::where('service_category',Service::OPTIONAL)->get();

        foreach ($service as $keys => $value) 
        {
            $yearEnd = StaticFunctions::getClientsDeadlineYearEndDate($company_db,$data['client_id']);
            //$dueDate = StaticFunctions::getClientsDueDate($value->service_track,$yearEnd,$value->duration_month,$value->duration_day);
            $data['ClientID'] = $data['client_id'];
            $data['ServiceID'] = $value->service_id;
            $data['ServiceName'] = $value->service_name;
            $data['YearEnd'] = StaticFunctions::datGet($yearEnd);
            $data['DueDate'] = StaticFunctions::datGet($yearEnd);
            $data['ServiceStatus'] = StaticFunctions::getServiceStatus($company_db,$value->service_name,$data['client_id']);
            $data['ClientServiceID'] = StaticFunctions::getServiceID($company_db,$value->service_name,$data['client_id']);
            $new_array[] = $data;
        }
        if(!empty($new_array)) {

            $new_array1['ClientServiceData'] = $new_array;
            $new_array1['ClientDeadlines'] = StaticFunctions::getClientsDeadline($company_db,$data['client_id']);
            return Response::json(['status' => 'success', 'data' => $new_array1]);
        } else  {
            $errors['ErrorMessage'] = ['Service Does Not Exist'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }
}

public function clientJob(Request $req)
{
    $database = CompanyConfig::where('company_id',$req->company_id)->get();
    $company_db = StaticFunctions::GetKeyValue($database,'company_database');

    if($company_db) {
        $db_con = StaticFunctions::db_connection($company_db);
        if($db_con) {
             $getservices=ClientService::where('client_id',$req->client_id)->get();
             if(count($getservices)>0) {
                 foreach ($getservices as $service) {
                     $get_job = ClientJob::where(['cs_id' => $service->cs_id])->orderBy('job_id', 'DESC')->first();
                     if (!empty($get_job)) {
                         $client_jobs = ClientJobsResource::collection($getservices);
                         $mergedata[] = ['ClientData' => $client_jobs, 'ClientID' => $req->client_id, 'ClientType' => StaticFunctions::getClientTypeByID($req->client_id)];
                         return Response::json(['status' => 'success', 'data' => $mergedata]);
                     } else {
                         $job_data['YearEnd'] = $service->initial_date;
                         $job_data['DueDate'] = null;
                         $job_data['ClientID'] = $req->client_id;
                         $job_data['ClientType'] = StaticFunctions::getClientTypeByID($req->client_id);
                         $new_job[] = $job_data;
                     }
                 }

                 if (!empty($new_job)) {

                     return Response::json(['status' => 'success', 'data' => $new_job]);
                 }
             }else {
                 $errors['ClientID'] = $req->client_id;
                 $errors['ClientType'] = StaticFunctions::getClientTypeByID($req->client_id);
                 return Response::json(['status' => 'error', 'data' => $errors]);
             }
        }
        else {
            $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }  
    else {
        $errors['ErrorMessage'] = ['DataBase Not Found !!'];
        return Response::json(['status' => 'error', 'data' => $errors]);
    }
}

public function clientKyc(Request $req)
{
    $database = CompanyConfig::where('company_id',$req->company_id)->get();
    $company_db = StaticFunctions::GetKeyValue($database,'company_database');

    if($company_db) {

        $db_con = StaticFunctions::db_connection($company_db);
        if($db_con) {
            $ClientKyc = ClientOfficer::create([
                'client_id'=>$req->client_id,
                'first_name'=>$req->first_name,
                'last_name'=>$req->last_name,
                'contact_designation'=>$req->contact_designation,
                'contact_country'=>$req->contact_country,
            ]);
            if($ClientKyc){
                $sucess['Message'] = ['Record Inserted Successfully'];
                return Response::json(['status' => 'success', 'data' => $sucess]);
            }
        }  else {
            $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    } else {
        $errors['ErrorMessage'] = ['DataBase Not Found !!'];
        return Response::json(['status' => 'error', 'data' => $errors]);
    }
}

    public function applyKyc(Request $req)
{
    $database = CompanyConfig::where('company_id',$req->company_id)->get();
    $company_db = StaticFunctions::GetKeyValue($database,'company_database');

    if($company_db) {

        $db_con = StaticFunctions::db_connection($company_db);
        if($db_con) {
            $data['contact_email'] = $req->contact_email;
            $data['contact_country'] = $req->country;
            $UpdatedData = ClientOfficer::where('contact_id', $req->contact_id)->update($data);
            if ($UpdatedData) {
                $sucess['Message'] = ['Record Updated Successfully'];
                return Response::json(['status' => 'success', 'data' => $sucess]);
            } else {
                $errors['ErrorMessage'] = ['failed to update Data !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    } else {
        $errors['ErrorMessage'] = ['DataBase Not Found !!'];
        return Response::json(['status' => 'error', 'data' => $errors]);
    }
}

public function getAllRoles()
{
    $roles = LoginRole::select('role_id as RoleID', 'role_name as RoleName')->get();
    $sucess['Message'] = ['Record Updated Successfully'];
    return Response::json(['status' => 'success', 'data' => $roles]);
}

    public function UpdateOptionalService(Request $req)
    {
        $FilterRequest = ['company_id','client_id','service_id','module_id'];
        $data = (object) $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'client id' => 'client id',
        ];

        $validator = Validator::make($req->only($FilterRequest) ,[
            'company_id' => 'required|digits_between:1,11',
        ])->setAttributeNames($attributes);

        /*if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }*/
         $servicedata = Service::where('service_id', $data->service_id)->first();

        $database = CompanyConfig::where('company_id',$data->company_id)->get();
        $company_db = StaticFunctions::GetKeyValue($database,'company_database');
        if($company_db) {
            $db_con = StaticFunctions::db_connection($company_db);
            if ($db_con) {
                $deadLineData = ClientDeadline::where('client_id',$data->client_id)->first();
                $client_id = $data->client_id;
                $service_name =  $servicedata->service_name;
                $year_end =  !empty($deadLineData->accounting_reference) ? $deadLineData->accounting_reference : date('Y-m-d');
                $due_date =  $data->service_id == 63 ?  date('d-m-Y', strtotime(' + 1 days', strtotime($year_end))) : $year_end;
                $service_track =  $servicedata->service_track;
                $duration_month =  $servicedata->duration_month;
                $duration_day =  $servicedata->duration_day;
                $repeat_type =  $servicedata->repeat_type;
                $repeat_number =  $servicedata->repeat_number;
                $check_exist = ClientService::where(['service_name'=>$service_name,'service_type'=>'Optional','client_id'=>$client_id])->orderBy('cs_id','desc')->first();

                $oservice['client_id']  = $client_id;
                $oservice['service_name']  = $service_name;
                $oservice['initial_date']  = ($year_end);//date('Y-m-d');
                $oservice['year_end']  = ($year_end);
                $oservice['due_date']  = ($due_date);
                $oservice['service_type']  = 'Optional';
                $oservice['service_track']  = $service_track;
                $oservice['duration_month']  = $duration_month;
                $oservice['duration_day']  = $duration_day;
                $oservice['repeat_type']  = $repeat_type;
                $oservice['repeat_number']  = $repeat_number;

                if(empty($check_exist)){
                    return $return = $this->do_SaveOptionalServices($client_id,$oservice,$data->company_id);
                }else{
                    $oservice['service_status']  = 'Active';
                    $return = $this->do_SaveOptionalServices($client_id,$oservice,$data->company_id,$check_exist->cs_id);
                }

                echo json_encode($return);
            } else {
                return 'connection error';
            }
        }else {
            return 'company not found';
        }
    }

    public function do_SaveOptionalServices($client_id, $oservice,$company_id,$cs_id=0)
    {
         $service_data = array(
            'client_id'=> $client_id,
            'service_name'=>$oservice['service_name'],
            'initial_date'=>$oservice['initial_date'],
            'service_type'=>$oservice['service_type'],
            'service_track'=>$oservice['service_track'],
            'duration_month'=>$oservice['duration_month'],
            'duration_day'=>$oservice['duration_day'],
            'repeat_type'=>$oservice['repeat_type'],
            'repeat_number'=>$oservice['repeat_number']
        );

        if(isset($oservice['service_status']))
            $service_data['service_status']=$oservice['service_status'];

        if($cs_id==0){
             $csdata = ClientService::create($service_data);
            if($csdata){
                 $cs_id = $csdata->cs_id;
                if($oservice['service_track']=="Tracked" && !empty($oservice['due_date'])){

                    //$this->InsertNewJob($cs_id, $oservice['year_end'],$oservice['due_date'],$client_id,$oservice['initial_date']);
                    //SMAK Modification
                      $this->InsertNewJob($cs_id, $oservice['year_end'],$oservice['due_date'],$client_id,$company_id);
                }
                //$this->session->set_userdata('INF_MSG','Optional Service Saved Successfully');

                return Response::json(['status' => 'success', 'data' => $cs_id]);

            }else{
                //$this->session->set_userdata('ERR_MSG','Unable to Add Optional Service Plesae try Again');
                //$cs_id = $this->db->insert_id();
                return Response::json(['status' => 'success', 'data' => $cs_id]);

            }
        }else{
            ClientService::where('cs_id',$cs_id)->update($service_data);
            //SMAK Modification
            if($oservice['service_track']=="Tracked" && !empty($oservice['due_date'])){
                $query = ClientJob::where(['cs_id'=> $cs_id,
                    'client_id'=> $client_id,
                    'year_end'=> $oservice['year_end'],
                    'due_date'=> $oservice['due_date'],
                    'job_id'=> 'DESC'
                    ])->where( 'job_status','<>', 'Cancelled')->select('job_id','job_status')->get();
                if(count($query)>0)
                    $this->InsertNewJob($cs_id, $oservice['year_end'],$oservice['due_date'],$client_id);
            }
            return Response::json(['status' => 'success', 'data' => $cs_id]);
        }
        return Response::json(['status' => 'success', 'data' => $cs_id]);
    }

    public function InsertNewJob($cs_id, $year_end,$due_date,$client_id,$company_id,$start_date='')
    {
       $company_id = $company_id;
        $user_id = Auth::user()->user_id;

        $data['cs_id'] = $cs_id;
        if(!empty($start_date)){
            $data['start_date'] = $start_date;
        }
        $data['year_end'] = $year_end;
        $data['due_date'] = $due_date;
        $data['job_status'] = 'New';
        $data['client_id'] = $client_id;
        $data['user_id'] = $user_id;
        $data['created_at'] = date("Y-m-d H:i:s");
        try{
             ClientJob::create($data);
            return 'success';
        }catch (\Exception $e){return $e->getMessage();}

    }

}

