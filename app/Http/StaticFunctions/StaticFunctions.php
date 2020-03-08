<?php

namespace App\Http\StaticFunctions;
use App\CompanyConfig;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Client;
use App\User;
use App\ServiceType;
use App\ClientType;
use App\Module;
use App\LoginRole;
use App\Company;
use App\ClientService;
use App\ClientDeadline;
use App\Service;
use App\ClientJob;
use App\Models\Client\UserNotification;



class StaticFunctions {

    public static function NewPa()
    {
        return env('LIVE_DB');
    }
    const NewPa ='devppa_newpa';
    public static $modules = [45 => 'practice-pa'];
    public static $moduleSlug = ['practice-pa' => 45];

    public static function GetKeyValue($data,$datakey)
    {
        $company_db = '';
        foreach ($data as $key => $value)
        {
            if($value->config_name == $datakey)
            {
                $company_db = $value->config_value;
            }
        }
        return $company_db;
    }

    public static function db_connection($company_db='')
    {
        $data[]=DB::connection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $data[]=DB::connection()->getPdo()->exec("use ".strtolower($company_db));
        $data[]=DB::connection()->setDatabaseName(strtolower($company_db));
        $data[]=DB::connection()->getDatabaseName();
        if(!empty($data)) {
            return $data;
        } else {
            return false;
        }
    }

    public static function keyServicesValidation($attr,$rules,$req)
    {
        // return false;
        $validator = Validator::make($req ,$rules)->setAttributeNames($attr);
        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
    }

    public static function dateRequets($date)
    {
        if(!empty($date)) {
            //$timestamp = strtotime($date);
            return date('Y-m-d', strtotime($date));
        } else {
            return null;
        }
    }

    public static function getServiceStatus($db,$service_name,$client_id)
    {
        $newDB = self::db_connection($db);
        $d = ClientService::where(['service_name' => $service_name, 'client_id' => $client_id])->orderBy('cs_id', 'desc')->first();
        return (!empty($d->service_status)) ? $d->service_status : null;
    }

    public static function getServiceID($db,$service_name,$client_id)
    {
        $newDB = self::db_connection($db);
        $d = ClientService::where(['service_name' => $service_name, 'client_id' => $client_id])->orderBy('cs_id', 'desc')->first();
        return (!empty($d->cs_id)) ? $d->cs_id : null;
    }


    public static function datGet($date)
    {
        if(!empty($date)) {
            $timestamp = strtotime($date);
            return date('d-m-Y', $timestamp );
        } else {
            return null;
        }
    }

    public static function InsertNewJob($cs_id, $year_end, $due_date, $client_id, $user_id, $start_date = '')
    {
        ClientJob::create([
          'cs_id'=>$cs_id,
          'start_date'=>!empty($start_date)?$start_date:null,
          'year_end'=>$year_end,
          'due_date'=>$due_date,
          'job_status'=>'New',
          'client_id'=>$client_id,
          'user_id'=>$user_id,
       ]);
    }


    public static function FormatMonth($monthNum)
    {
        $mon = $monthNum-1;
        $month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul","Aug", "Sep", "Oct", "Nov", "Dec"];
        return $month[$mon];
    }
    public static function GetMonth($date)
    {
        return date('m', strtotime($date));
    }

    public static function getClientTypeByID($id)
    {
        return Client::where('client_id',$id)->pluck('client_type')->first();
    }

    public static function getPartners($id)
    {
        self::db_connection(self::NewPa);
        $data = User::where('user_id',$id)->first();
        if(!empty($data)) {
            return $data->first_name." ".$data->last_name;
        } else {
            return null;
        }
    }

    public static function getServiceType($id)
    {
        self::db_connection(self::NewPa);
        $data = ServiceType::where('service_type_id',$id)->first();
        if(!empty($data)) {
            return $data->service_type_name;
        } else {
            return null;
        }
    }

    public static function getClientType($id)
    {
        self::db_connection(self::NewPa);
        $data = ClientType::where('id',$id)->first();
        if(!empty($data)) {
            return $data->type;
        } else {
            return null;
        }
    }

    public static function GenerateRandomReference()
    {
        $str = substr(md5(uniqid(rand(), true)), 2, 2);
        $today = date("Ymd");
        $rand = strtoupper(substr(uniqid(sha1(time())),0,4));
        $val = $unique = $str . $today . $rand;
        $code = strtoupper($val);
        return $code;
    }

    public static function getModuleSlugByID($slug)
    {
        if(array_search($slug,self::$modules)){
            return array_search($slug,self::$modules);
        } else {
            return null;
        }
    }

    public static function getModuleSlugFromID($id)
    {
        if(array_search($id,self::$moduleSlug)){
            return array_search($id,self::$moduleSlug);
        } else {
            return null;
        }
    }

    public static function getRoleByID($roleID)
    {
        $role = LoginRole::find($roleID);
        if(!empty($role))
        {
            return $role->role_name;
        } else {
            return null;
        }
    }

    public static function getUserNameByID($userID,$db)
    {
        $newDB = self::db_connection(self::NewPa);
        $userData = User::find($userID);
        $fullname = $userData->first_name.' '.$userData->last_name;
        self::db_connection(strtolower($db));
        return $fullname;
    }

    public static function getClientsService($db,$service_id,$client_id)
    {
    $newDB = self::db_connection($db);  
    $clientService = ClientService::where(['service_id'=>$service_id,'client_id'=>$client_id])->first();
        if(!empty($clientService))
            return !empty($clientService->service_id)?$clientService->service_id:'';
        else
            return null;
    }

    public static function getClientsDeadline($db,$client_id)
    {
        $newDB = self::db_connection($db);
        $clientDeadline = ClientDeadline::where(['client_id'=>$client_id])->select('vat_registered','vat_number','prepare_vat_return','vat_return_period','vat_return_date','prepare_payroll','payroll_start_date','payroll_type','next_vat_return')->first();
        if(!empty($clientDeadline))
            return $clientDeadline;
        else
            return null;
    }

    public static function getClientsDeadlineYearEndDate($db,$client_id)
    {
        $newDB = self::db_connection($db);
        $clientDeadlineYearDate = ClientDeadline::where('client_id',$client_id)->select('accounting_reference')->first();
        if(!empty($clientDeadlineYearDate->accounting_reference)){
            return $clientDeadlineYearDate->accounting_reference;
        } else {
            return date('d-m-Y');
        }
    }
    
    public static function getClientsDueDate($service_track,$date,$month,$day)
    {
        if($service_track==Service::TRACKED) {
            if($month>0 && $day>0) {
                $dateday = self::GetDayOfDate($date);
                $lastdaymonth = self::LastdayofMonth($date);
                if($dateday==$lastdaymonth) {
                    $generated_date = self::AddMonth($date,$month);
                    $newdate = self::LastdateofMonth($generated_date);
                } else {
                    $newdate = self::AddMonth($date,$month);
                }
                $newdate = self::AddDays($newdate,$day);
            } else {
                if($month>0){
                    $dateday = self::GetDayOfDate($date);
                    $lastdaymonth = self::LastdayofMonth($date);
                    if($dateday==$lastdaymonth) {
                        $generated_date = self::AddMonth($date,$month);
                        $newdate = self::LastdateofMonth($generated_date);
                    } else {
                        $newdate = self::AddMonth($date,$month);
                    }            
                }
                if($day>0){
                    $newdate = self::AddDays($newdate,$day);
                }
            }
            return !empty($newdate) ? $newdate:$date;      
        } else {
            return $date = '';
        }
    }
    
    public static function AddDays($date,$day)
    {
        return date('Y-m-d', strtotime($date. "+ $day days"));
    }

    public static function GetDayOfDate($date)
    {
        return date('d', strtotime($date));
    }

    public static function LastdayofMonth($date)
    {
        return date("t", strtotime($date));
    }

    public static function AddMonth($date,$month)
    {
        return date('Y-m-d', strtotime("+$month months", strtotime($date)));
    }
     public static function AddYears($date,$year)
    {
        return date('Y-m-d', strtotime("+$year year", strtotime($date)));
    }
    public static function LastdateofMonth($date)
    {
        return date("t-m-Y", strtotime($date));
    }




    public static function getCompanyModule($userid,$companyid = 0)
    {
        if($companyid != 0){
            $user = Company::where(['user_id' => $userid,'company_id' => $companyid])->first();
            if(!empty($user))
            {
                return $user->module_id;
            } else {return null;}
        }else {return null;}

    }


    public static function getImagePath($path)
    {
        if($path == 'local') {
            return env('LOCAL_IMAGE_PATH');
        } else {
            return env('LIVE_IMAGE_PATH'); 
        }
    }

    public static function getFrontendPath($path)
    {
        if($path == 'local') {
            return env('LOCAL_FRONTEND_PATH');
        } else {
            return env('LIVE_FRONTEND_PATH');
        }
    }

    public static function clean($string) {
        $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9]/', '', $string); // Removes special chars.
    }

    public static function totalUnitByCompany($unitpermin)
    {
       $working_hour_per_day = 8;
       $working_mintes_per_hour = 60;
       $total_min = $working_hour_per_day*$working_mintes_per_hour;
       $total_unit = $total_min/$unitpermin;
       return $total_unit;
    }

    public static function getDatesCompanyKeyService($client_id,$client_type,$service_type,$company='')
    {
        $client_deadline = ClientDeadline::where('client_id',$client_id)->first();
        self::db_connection(self::NewPa);
        $new_data = [];

        if( in_array($service_type, array(1,2,3,4,5,6,7)))
        {
            !empty($company['date_of_creation'])? $new_data['DateOfIncorporation_readonly'] = self::datGet($company['date_of_creation']) : $new_data['DateOfIncorporation'] =  self::datGet($client_deadline->date_of_incorporation);
        }

        if( in_array($service_type, array(1,2,3,4,5,6,7,8,9,12,13,14))) 
        {

            !empty($company['date_of_creation'])? $new_data['DateOfTrading_readonly'] = self::datGet($company['date_of_creation']):$new_data['DateOfTrading'] = self::datGet($client_deadline->date_of_trading);;
        } 

        if( in_array($service_type, array(8,10,11,13,14))) 
        {
            $new_data['TextReturnDate_readonly'] ='05/04';
            $new_data['TextReturnYear'] = !empty($client_deadline->tax_return_date) ? date('Y', strtotime($client_deadline->tax_return_date)) : '';
        }
        if( in_array($service_type, array(1,2,3,4,5,6,7)))
        {
            !empty($company['accounts']['last_accounts']['period_end_on']) ? $new_data['PriorAccountingReference_readonly'] = self::datGet($company['accounts']['last_accounts']['period_end_on']):$new_data['PriorAccountingReference'] = self::datGet($client_deadline->prior_accounting_reference);
        }

        if( in_array($service_type, array(1,2,3,4,5,6,7,9,12)))
         {
            !empty($company['accounts']['next_accounts']['period_end_on'])? $new_data['AccountingReference_readonly'] = self::datGet($company['accounts']['next_accounts']['period_end_on']):$new_data['AccountingReference'] = self::datGet($client_deadline->accounting_reference);
         }

         $new_data['RecieptOfAA01'] = !empty($client_deadline->reciept_of_AA01)?self::datGet($client_deadline->reciept_of_AA01):'';

        if( in_array($service_type, array(1,2,3,4,5,6,7)))
        {
            !empty($company['confirmation_statement']['next_made_up_to'])? $new_data['ConfirmationStatementDate_readonly'] = self::datGet($company['confirmation_statement']['next_made_up_to']): $new_data['ConfirmationStatementDate'] = self::datGet($client_deadline->annual_return_date);
        }

        if( in_array($service_type, array(9,12,13)))
        {
            $new_data['UnincorporatedAccountsDate'] = self::datGet($client_deadline->unincorporated_accounts_date);
        }

        
        if( in_array($service_type, array(1,2,3,4,5,6,7)))
        {
            $new_data['AccountsToCompaniesHouse_readonly'] = !empty($company['accounts']['next_accounts']['due_on'])?self::datGet($company['accounts']['next_accounts']['due_on']):'';
        }


            if( in_array($service_type, array(1,2,3,4,5,6)))
            {
                $new_data['AnnualConfirmationStatement_readonly'] = !empty($company['confirmation_statement']['next_due'])?self::datGet($company['confirmation_statement']['next_due']):self::datGet($client_deadline->annual_return);
            }

        if( in_array($service_type, array(2,3,4)))
        {
            $new_data['CorporationTaxPayable_readonly'] =  !empty($company['accounts']['next_accounts']['period_end_on'])?self::datGet($company['accounts']['next_accounts']['period_end_on']):self::datGet($client_deadline->corporation_tax_payable);
        }

        if( in_array($service_type, array(2,3,4)))
        {
            $new_data['CorporationTaxReturn_readonly'] =  !empty($company['accounts']['next_accounts']['period_end_on'])?self::datGet($company['accounts']['next_accounts']['period_end_on']):self::datGet($client_deadline->corporation_tax_return);
        }

        if( in_array($service_type, array(10,11,13,14)))
        {
            $new_data['TaxPartnershipReturn'] = (!empty($client_deadline->corporation_tax_return))?self::datGet($client_deadline->corporation_tax_return):'';
        }

        if( in_array($service_type, array(2,3,4,5,6,9,10,11,12,13,14)))
        {
            $new_data['DeadlinePayroll'] = self::datGet($client_deadline->deadline_payroll);
        }
        
        if($service_type == 9)
        {
            $new_data['ManualDueDate'] = self::datGet($client_deadline->manual_due_date);
        }
        $new_data['ClientID'] = $client_id;
        $new_data['ClientType'] = $client_type;
        $new_data['ServiceType'] = $service_type;

        return $new_data;
      }
   public static function SaveServices($args,$updateData,$data)
   {

      if($args['service_type'] == 'Primary') {
         ClientService::where(['client_id' => $data['client_id'], 'service_type' => 'Primary'])->delete();
         ClientJob::where(['client_id' => $data['client_id']])->delete();
      }

      if(!empty($updateData['accounts_to_company_house'])) {
         $duration_month = 9;
         $duration_day = 0;
         $addClientService = ClientService::create([
            'client_id' => $data['client_id'],
            'service_name' => 'Accounts to Companies House',
            'initial_date' => $updateData['accounting_reference'],
            'duration_month' => $duration_month,
            'duration_day' => $duration_day,
            'service_type' => 'Primary',
            'require_confirm' => 1
         ]);
         if ($addClientService) {
            self::InsertNewJob($addClientService->cs_id, $updateData['accounting_reference'],$updateData['accounts_to_company_house'], $data['client_id'], $data['user_id']);
         }
      }

      if(!empty($updateData['annual_return'])) {
         $duration_month = 0;
         $duration_day = 14;
         $addClientService = ClientService::create([
            'client_id' => $data['client_id'],
            'service_name' => 'Annual Confirmation Statement',
            'initial_date' => $updateData['annual_return_date'],
            'duration_month' => $duration_month,
            'duration_day' => $duration_day,
            'service_type' => 'Primary',
            'require_confirm' => 1
         ]);
         if($addClientService) {
            self::InsertNewJob($addClientService->cs_id, $updateData['annual_return_date'],$updateData['annual_return'],$data['client_id'], $data['user_id']);
         }
      }

      if(!empty($updateData['corporation_tax_payable'])) {
         $duration_month = 9;
         $duration_day = 1;

         $addClientService = ClientService::create([
            'client_id' => $data['client_id'],
            'service_name' => 'Corporation Tax Payable',
            'initial_date' => $updateData['accounting_reference'],
            'duration_month' => $duration_month,
            'duration_day' => $duration_day,
            'service_type' => 'Primary',
         ]);

         if($addClientService) {
            self::InsertNewJob($addClientService->cs_id, $updateData['accounting_reference'],$updateData['corporation_tax_payable'], $data['client_id'], $data['user_id']);
         }
      }

      if(!empty($updateData['corporation_tax_return'])) {
         $duration_month = 12;
         $duration_day = 0;
         $addClientService = ClientService::create([
            'client_id' => $data['client_id'],
            'service_name' => 'Corporation Tax Return',
            'initial_date' => $updateData['accounting_reference'],
            'duration_month' => $duration_month,
            'duration_day' => $duration_day,
            'service_type' => 'Primary',
         ]);

         if($addClientService) {
            self::InsertNewJob($addClientService->cs_id, $updateData['accounting_reference'],$updateData['corporation_tax_return'], $data['client_id'], $data['user_id']);
         }
      }

      if(!empty($updateData['tax_partnership_return'])) {
         $duration_month = 6;
         $duration_day = 26;
         $addClientService = ClientService::create([
            'client_id' => $data['client_id'],
            'service_name' => 'Tax Return / Partnership Return',
            'initial_date' => $updateData['tax_return_date'],
            'duration_month' => $duration_month,
            'duration_day' => $duration_day,
            'service_type' => 'Primary',
         ]);

         if($addClientService) {
            self::InsertNewJob($addClientService->cs_id, $updateData['tax_return_date'],$updateData['tax_partnership_return'], $data['client_id'], $data['user_id']);
         }
      }

      if(!empty($updateData['manual_due_date'])) {
         $duration_month = 12;
         $duration_day = 0;
         $addClientService = ClientService::create([
            'client_id' => $data['client_id'],
            'service_name' => 'Manual Due Date',
            'initial_date' => $updateData['accounting_reference'],
            'duration_month' => $duration_month,
            'duration_day' => $duration_day,
            'service_type' => 'Primary',
         ]);

         if($addClientService) {
            self::InsertNewJob($addClientService->cs_id, $updateData['accounting_reference'],$updateData['manual_due_date'], $data['client_id'], $data['user_id']);
         }
      }
   }

      public static function getCompanyNameByID($company_id)
      {
          return  Company::where('company_id',$company_id)->pluck('company_name')->first();
      }

      public static function ClientNotifications($company_id = null,
          $module_id = null, $section = null, $noti_type = null, $noti_title = null, $user_by = null)
      {
          $clientNotiAdd['module_id'] = ($module_id != null) ? $module_id : null;
          $clientNotiAdd['section'] = ($section != null) ? $section : null;
          $clientNotiAdd['noti_type'] = ($noti_type != null) ? $noti_type : null;
          $clientNotiAdd['noti_title'] = ($noti_title != null) ? $noti_title : null;
          $clientNotiAdd['user_by'] = ($user_by != null) ? $user_by : null;

          if ($company_id != null) {
                      $db_con = self::db_connection(self::new);
                      UserNotification::create($clientNotiAdd);
          } else {
              UserNotification::create($clientNotiAdd);
          }
      }

      public static function billingStatusByCompanyID($company_id)
      {
          return Company::where('company_id', $company_id)->pluck('billing_status')->first();
      }
}
