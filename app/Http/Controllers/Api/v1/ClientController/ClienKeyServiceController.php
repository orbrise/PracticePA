<?php

namespace App\Http\Controllers\Api\V1\ClientController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyConfig;
use App\Http\StaticFunctions\StaticFunctions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Client;
use App\Http\Controllers\Api\v1\CompanyController\CompanyController;
use App\ClientDeadline;
use App\ClientService;
use App\ClientJob;

class ClienKeyServiceController extends Controller
{
    public function getCompanyKeyService(Request $req)
    {
        $FilterRequest = ['client_id', 'company_id', 'module_id'];
        $data = $req->only($FilterRequest);

        // changing the input fields name
        $attributes = [
            'client_id' => 'Client ID',
            'company_id' => 'Company ID',
            'module_id' => 'Module ID',
        ];
        $validator = Validator::make($data, [
            'client_id' => 'required|numeric',
            'company_id' => 'required|numeric',
            'module_id' => 'required|string',
        ])->setAttributeNames($attributes);

        if ($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $database = CompanyConfig::where('company_id', $data['company_id'])->get();
        if ($database) {
            $company_db = StaticFunctions::GetKeyValue($database, 'company_database');
            if ($company_db) {
                $db_con = StaticFunctions::db_connection(strtolower($company_db));
                if ($db_con) {
                    $client = Client::where(['client_id' => $data['client_id'], 'company_id' => $data['company_id'], 'module_id' => StaticFunctions::getModuleSlugByID($data['module_id'])])->first();
                    if (!empty($client)) {
                        $company = (!empty($client->registration_no)) ? CompanyController::GetCompany('companynumber', $client->registration_no) : $client;

                        $new_data = StaticFunctions::getDatesCompanyKeyService($client->client_id, $client->client_type, $client->service_type, $company);

                        return Response::json(['status' => 'success', 'data' => $new_data]);

                    } else {
                        $errors['ErrorMessage'] = ['Client Not Found !!'];
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
   
    public function addKeyServices(Request $req)
    {
        $FilterRequest = ['client_id','company_id','module_id','first_year','date_of_incorporation','date_of_trading','prior_accounting_reference','accounting_reference','ard','reciept_of_AA01','annual_return_date','bank_authority_date','bank_letter','unincorporated_accounts_date','tax_return_date','tax_return_to_filled_online','tax_return_sep', 'accounts_to_company_house', 'annual_return', 'corporation_tax_payable','corporation_tax_return', 'tax_partnership_return','manual_due_date','deadline_payroll','user_id','client_type','service_type_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company ID',
            'client_id' => 'CLient ID',
            'module_id' => 'Module ID',
            'user_id' => 'User ID',
            'client_type' => 'Client Type',
            'service_type_id' => 'Service Type ID',
            'first_year' => 'First Year',
            'date_of_incorporation' => 'Date Of Incorporation',
            'date_of_trading' => 'Date Of Trading',
            'prior_accounting_reference' => 'Prior Accounting Reference',
            'accounting_reference' => 'Accounting Reference',
            'ard' => 'Ard',
            'reciept_of_AA01' => 'Reciept Of AA01',
            'annual_return_date' => 'Annual Return Date',
            'bank_authority_date' => 'Bank Authority Date',
            'bank_letter' => 'Bank Letter',
            'annual_return' => 'annual_return',
            'corporation_tax_payable' => 'Corporation Tax Payable',
            'corporation_tax_return' => 'Corporation Tax Return',
            'unincorporated_accounts_date' => 'Unincorporated Accounts Date',
            'tax_return_date' => 'Tax Return Date',
            'tax_return_to_filled_online' => 'Tax Return To Filled Online',
            'tax_return_sep' => 'Tax Return Sep',
            'accounts_to_company_house' => 'Accounts To Company House',
            'tax_partnership_return' => 'Tax Partnership Return',
            'manual_due_date' => 'Manual Due Date',
            'deadline_payroll' => 'Deadline Payroll',
        ];
        $validator = Validator::make($data, [
            'company_id' => 'required|numeric',
            'client_id' => 'required|numeric',
            'module_id' => 'required|string',
            'user_id' => 'required|numeric',
            'client_type' => 'required|numeric',
            'service_type_id' => 'required|numeric',
        ])->setAttributeNames($attributes);
        if ($validator->fails()) 
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $database = CompanyConfig::where('company_id', $data['company_id'])->get();
        if($database) {
            $company_db = StaticFunctions::GetKeyValue($database, 'company_database');
            if($company_db) {
                $db_con = StaticFunctions::db_connection(strtolower($company_db));
                if($db_con) {
                    $updateData = array(
                        'first_year' => (!empty($data['first_year']) && isset($data['first_year'])) ? $data['first_year'] : null,
                        'date_of_incorporation' => (!empty($data['date_of_incorporation']) && isset($data['date_of_incorporation'])) ? StaticFunctions::dateRequets($data['date_of_incorporation']) : null,
                        'date_of_trading' => (!empty($data['date_of_trading']) && isset($data['date_of_trading'])) ? StaticFunctions::dateRequets($data['date_of_trading']) : null,
                        'prior_accounting_reference' => (!empty($data['prior_accounting_reference']) && isset($data['prior_accounting_reference']) ) ? StaticFunctions::dateRequets($data['prior_accounting_reference']) : null,
                        'accounting_reference' => (!empty($data['accounting_reference']) && isset($data['accounting_reference']) ) ? StaticFunctions::dateRequets($data['accounting_reference']) : null,
                        'ard' => (!empty($data['ard']) && isset($data['ard']) ) ? $data['ard'] : null,
                        'reciept_of_AA01' => (!empty($data['reciept_of_AA01']) && isset($data['reciept_of_AA01']) ) ? StaticFunctions::dateRequets($data['reciept_of_AA01']) : null,
                        'annual_return_date' => (!empty($data['annual_return_date']) && isset($data['annual_return_date']) ) ? StaticFunctions::dateRequets($data['annual_return_date']) : null,
                        'bank_authority_date' => (!empty($data['bank_authority_date']) && isset($data['bank_authority_date']) ) ? StaticFunctions::dateRequets($data['bank_authority_date']) : null,
                        'bank_letter' => (!empty($data['bank_letter']) && isset($data['bank_letter']) ) ? StaticFunctions::dateRequets($data['bank_letter']) : null,
                        'accounts_to_company_house' => (!empty($data['accounts_to_company_house']) && isset($data['accounts_to_company_house']) ) ? StaticFunctions::dateRequets($data['accounts_to_company_house']) : null,
                        'annual_return' => (!empty($data['annual_return']) && isset($data['annual_return']) ) ? StaticFunctions::dateRequets($data['annual_return']) : null,
                        'corporation_tax_payable' => (!empty($data['corporation_tax_payable']) && isset($data['corporation_tax_payable']) ) ? StaticFunctions::dateRequets($data['corporation_tax_payable']) : null,
                        'corporation_tax_return' => (!empty($data['corporation_tax_return']) && isset($data['corporation_tax_return']) ) ? StaticFunctions::dateRequets($data['corporation_tax_return']) : null,
                        'unincorporated_accounts_date' => (!empty($data['unincorporated_accounts_date']) && isset($data['unincorporated_accounts_date']) ) ? StaticFunctions::dateRequets($data['unincorporated_accounts_date']) : null,
                        'tax_return_date' => (!empty($data['tax_return_date']) && isset($data['tax_return_date']) ) ? StaticFunctions::dateRequets($data['tax_return_date']) : null,
                        'tax_return_to_filled_online' => (!empty($data['tax_return_to_filled_online']) && isset($data['tax_return_to_filled_online']) ) ? $data['tax_return_to_filled_online'] : null,
                        'tax_return_sep' => (!empty($data['tax_return_sep']) && isset($data['tax_return_sep']) ) ? $data['tax_return_sep'] : null,
                        'tax_partnership_return' => (!empty($data['tax_partnership_return']) && isset($data['tax_partnership_return']) ) ? StaticFunctions::dateRequets($data['tax_partnership_return']) : null,
                        'manual_due_date' => (!empty($data['manual_due_date']) && isset($data['manual_due_date']) ) ? StaticFunctions::dateRequets($data['manual_due_date']) : null,
                        'deadline_payroll' => (!empty($data['deadline_payroll']) && isset($data['deadline_payroll']) ) ? StaticFunctions::dateRequets($data['deadline_payroll']) : null,
                    );
                    $ClientKeyService = ClientDeadline::where('client_id', $data['client_id'])->update($updateData);
                    if($ClientKeyService) {
                        $args['client_id'] = $data['client_id'];
                        $args['client_type'] = $data['client_type'];
                        $args['service_type_id'] = $data['service_type_id'];
                        $args['service_type'] = 'Primary';
                        
                        StaticFunctions::SaveServices($args,$updateData,$data);
                       
                        $succes['SuccessMessage'] = ['Key Service add succesfully.'];
                        return Response::json(['status' => 'success', 'data' => $succes]);
                    } else {
                        $errors['ErrorMessage'] = ['Fail to add the Key Service, please try again.'];
                        return Response::json(['status' => 'error', 'data' => $errors]);
                    }
                } else {
                    $errors['ErrorMessage'] = ['Client Not Found !!'];
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
    }

    public function cancelServiceView(Request $req)
    {
        $FilterRequest = ['client_id', 'company_id', 'module_id','cs_id','with_service'];
        $data = $req->only($FilterRequest);

        $attributes = [
            'client_id' => 'Client ID',
            'company_id' => 'Company ID',
            'module_id' => 'Module ID',
            'cs_id' => 'Module ID',
            'with_service' => 'With Service',
        ];
        $validator = Validator::make($data, [
            'client_id' => 'required|numeric',
            'company_id' => 'required|numeric',
            'cs_id' => 'required|numeric',
            'module_id' => 'required|string',
            'with_service' => 'required|string',
        ])->setAttributeNames($attributes);

        if ($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $database = CompanyConfig::where('company_id', $data['company_id'])->get();
        if ($database) {
            $company_db = StaticFunctions::GetKeyValue($database, 'company_database');
            if ($company_db) {
                $db_con = StaticFunctions::db_connection(strtolower($company_db));
                if ($db_con) {
                    if($data['with_service']=='No'){
                        $cancelService = ClientService::where(['cs_id'=>$data['cs_id'],'client_id'=>$data['client_id']])->update(['service_status'=>ClientService::INACTIVE]);
                        if($cancelService) {
                            $success['SuccessMessage'] = ['Success Fully Cancelled Client Service !!'];
                            return Response::json(['status' => 'error', 'data' => $success]);
                        } else {
                            $errors['ErrorMessage'] = ['Failled To Cancell Client Service !!'];
                            return Response::json(['status' => 'error', 'data' => $errors]);
                        }
                    } else if($data['with_service']=='Yes') {
                         $cancelServiceJob = ClientJob::where(['cs_id'=>$data['cs_id'],'client_id'=>$data['client_id']])->update(['job_status'=>ClientJob::JOB_CANCELLED]);
                        if($cancelServiceJob) {
                            $success['SuccessMessage'] = ['Success Fully Cancelled ClientService with Job !!'];
                            return Response::json(['status' => 'error', 'data' => $success]);
                        } else {
                            $errors['ErrorMessage'] = ['Failled To Cancell ClientService !!'];
                            return Response::json(['status' => 'error', 'data' => $errors]);
                        }
                    } else {
                        $errors['ErrorMessage'] = ["Only Send For 'Yes' or 'No'. Your Request is ".$data['with_service']];
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
