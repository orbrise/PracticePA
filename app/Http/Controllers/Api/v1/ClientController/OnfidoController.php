<?php

namespace App\Http\Controllers\Api\v1\ClientController;

use App\Http\StaticFunctions\StaticFunctions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ClientOfficer;
use App\KycOnfido;
use Validator;
use App\CompanyConfig;
use Response;
use App\Invoice;
use App\InvoiceItem;

class OnfidoController extends Controller
{
    const KYCSTATUS = 'Applied';
    const KYCFEE = 10;
    const ONFIDOKEY = 'test_5BTDsPSi0MqF2sS8drvtmJ7k9vNQFjcS';
    const NEWPA = 'devppa_newpa';
    const COUNTRYONFIDO = 'GBR';
    const COUNTRYDB = 'London';

    public function __construct()
    {
        \Onfido\Config::init()->set_token(self::ONFIDOKEY);
    }

    public function createApplicant(Request $req)
    {
        $attributes = [
            'email' => 'Email',
            'contact_id' => 'Contact ID',
            'company_id' => 'Company ID',
        ];
        $validator = Validator::make($req->all() ,[
            'contact_id' => 'required',
            'email' => 'required',
            'company_id' => 'required'
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $database = CompanyConfig::where('company_id',$req->company_id)->get();
        if($database)
        {

            $company_db = StaticFunctions::GetKeyValue($database,'company_database');
            if($company_db) {
                $db_con = StaticFunctions::db_connection(strtolower($company_db));
                $OfficerData = ClientOfficer::find($req->contact_id);
                $clientType = StaticFunctions::getClientTypeByID($OfficerData->client_id);
                if($db_con)
                {
                    $kycCheck = KycOnfido::where(['user_id' => $req->contact_id, 'kyc_status' => self::KYCSTATUS])->first();
                    if(!empty($kycCheck))
                    {
                        $errors['ErrorMessage'] = ['Already Applied'];
                        $errors['ClientID'] = $kycCheck->client_id;
                        $errors['ClientType'] = StaticFunctions::getClientTypeByID($kycCheck->client_id);
                        return Response::json(['status' => 'error', 'data' => $errors]);
                    }
                    $updateOfficeData = ClientOfficer::where('contact_id', $req->contact_id)
                        ->update(['contact_email' => $req->email, 'contact_country' => self::COUNTRYDB]);
                    if($updateOfficeData == false)
                    {
                        $errors['ErrorMessage'] = ['Update Office Data Failed! try again'];
                        return Response::json(['status' => 'error', 'data' => $errors]);
                    }
                    if($req->submit_mode == 1) {
                        try {
                            $officerDetails = ClientOfficer::find($req->contact_id);
                            $applicant = new \Onfido\Applicant();
                            $applicant->first_name = $officerDetails->first_name;
                            $applicant->last_name = $officerDetails->last_name;
                            $applicant->email = $officerDetails->contact_email;
                            $applicant->country = self::COUNTRYONFIDO;
                            $response = $applicant->create();
                            foreach ($response as $key => $value) {
                                $data[$key] = $value;
                            }
                            $applicantID = $data['id'];

                            $checkDetails = $this->getApplicant($applicantID);
                            $checkID = $checkDetails['id'];
                            $status = $checkDetails['status'];
                            foreach ($checkDetails['reports'] as $reportkey => $reportvalue) {
                                foreach ($reportvalue as $rpkey => $rpvalue) {
                                    $reportData[$rpkey] = $rpvalue;
                                }
                            }
                            $reportID = $reportData['id'];
                            $reference_number = 'PA-' . StaticFunctions::GenerateRandomReference();
                            $kyc_data['onfido_applicant_id'] = $applicantID;
                            $kyc_data['onfido_check_id'] = $checkID ;
                            $kyc_data['onfido_check_result'] = $status;
                            $kyc_data['onfido_report_id'] = $reportID;
                            $kyc_data['user_id'] = $req->contact_id;
                            $kyc_data['client_id'] = $officerDetails->client_id;
                            $kyc_data['reference_number'] = $reference_number;
                            $kyc_data['kyc_status'] = 'Applied';
                            $kyc_data['apply_date'] = date('Y-m-d H:i:s');
                            KycOnfido::create($kyc_data);

                            $officerName = $officerDetails->first_name." ".$officerDetails->last_name;
                            $officerEmail = $officerDetails->contact_email;
                            $clientID = $officerDetails->client_id;

                            //invoice data
                            StaticFunctions::db_connection(self::NEWPA);
                            $addInvoice = new Invoice;
                            $addInvoice->due_date= date('Y')."-".date('m')."-07";
                            $addInvoice->invoice_month = date('m');
                            $addInvoice->invoice_year = date('Y');
                            $addInvoice->company_id = $req->company_id;
                            $addInvoice->invoice_status = 'Pending';
                            $addInvoice->save();

                            $invoice_item['user_id'] = $req->contact_id;
                            $invoice_item['name'] = $officerName;
                            $invoice_item['email'] = $officerEmail;
                            $invoice_item['amount'] = self::KYCFEE;
                            $invoice_item['invoice_id'] = $addInvoice->invoice_id;
                            $invoice_item['address'] = 'Address';
                            $invoice_item['item_type'] = 'Officer';
                            $invoice_item['client_id'] = $clientID ;
                            InvoiceItem::create($invoice_item);

                        } catch (\Exception $e){
                            $errors['ErrorMessage'] = [$e->getMessage()];
                            $errors['ClientID'] = $OfficerData->client_id;
                            $errors['ClientType'] = $clientType;
                            return Response::json(['status' => 'error', 'data' => $errors]);
                        }

                        $success['SuccessMessage'] = ['KYC Applied'];
                        $success['ClientID'] = $OfficerData->client_id;
                        $success['ClientType'] = $clientType;
                        return Response::json(['status' => 'success', 'data' => $success]);

                    } else
                    {
                        $success['SuccessMessage'] = ['Officer Data is Saved !!'];
                        $success['ClientID'] = $OfficerData->client_id;
                        $success['ClientType'] = $clientType;
                        return Response::json(['status' => 'success', 'data' => $success]);
                    }

                } else {
                    $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } else {
                $errors['ErrorMessage'] = ['Datasbese Not Found !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else
        {
            $errors['ErrorMessage'] = ['company Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function getApplicant($app_id){
        $check = new \Onfido\Check();
        $check->type = 'standard';
        $report1 = new \Onfido\CheckReport();
        $report1->name = 'identity';
        $check->reports = Array(
            $report1
        );
        $response = $check->create_for($app_id);
        foreach($response as $checkkey => $checkvalue)
        {
            $checkData[$checkkey] = $checkvalue;
        }
        return $checkData;
    }
}
