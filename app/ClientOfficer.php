<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\StaticFunctions\StaticFunctions;


class ClientOfficer extends Model
{
    const ACTICESTATUS = 1;
    const DEACTIVESTATUS = 0;
    const OFFICERACTIVE = 'Active';
    protected $table ='client_officers';
    protected $primaryKey = 'contact_id';
    protected $fillable = [
        'company_id','contact_type','officer_type','ceased_on','date_of_birth','appointed_on','resigned_on','client_id','contact_title','contact_other_title','first_name','last_name','contact_designation','contact_phone_no','contact_email','contact_address_line1','contact_city','contact_county','contact_country','nationality','contact_postal_code','notes','status','created_at','updated_at','module_id'
    ];

    public static function getClientOfficers($company,$client_id,$company_id)
    {
        $data_client_officer = array();
        if(count($company) > 0)
        {
            foreach($company['items'] as $officer)
            {
                $data_client_officer['client_id'] = $client_id;
                $data_client_officer['company_id'] = $company_id;
                $officer_full_name = trim($officer['name']);
                $name = explode(", ", $officer_full_name);

                if(sizeof($name) > 1)
                {
                    $data_client_officer['last_name'] = strtolower($name[0]);
                    $data_client_officer['first_name'] = strtolower($name[1]);
                } else {
                    $data_client_officer['first_name'] = strtolower($name[0]);
                }

                if(isset($officer['officer_role']))
                {
                    $data_client_officer['contact_designation'] = $officer['officer_role'];
                }

                if(isset($officer['address']['premises'])) {
                    if(isset($officer['address']['address_line_1'])) {
                        $data_client_officer['contact_address_line1'] = $officer['address']['premises'].', '.$officer['address']['address_line_1'];
                    }
                } else {
                    if(isset($officer['address']['address_line_1'])) {
                        $data_client_officer['contact_address_line1'] = $officer['address']['address_line_1'];
                    } else {
                        $data_client_officer['contact_address_line1'] = $officer['address']['premises'];
                    }
                }

                if(isset($officer['address']['postal_code'])) {
                    $data_client_officer['contact_postal_code']  = $officer['address']['postal_code'];
                }

                if(isset($officer['address']['locality'])) {
                    $data_client_officer['contact_city']  = $officer['address']['locality'];
                    $data_client_officer['contact_county']= $officer['address']['locality'];
                }

                if(isset($officer['nationality'])) {
                    $data_client_officer['nationality'] = $officer['nationality'];
                }

                if(isset($officer['resigned_on'])) {
                    $data_client_officer['officer_type'] = "Resigned";
                    $data_client_officer['resigned_on'] = $officer['resigned_on'];
                } else {
                    $data_client_officer['officer_type'] = "Active";
                }

                if(isset($officer['address']['country'])) {
                    $data_client_officer['contact_country']  = $officer['address']['country'];
                }

                if(isset($officer['appointed_on'])) {
                    $data_client_officer['appointed_on'] = $officer['appointed_on'];
                }

                if(isset($officer['date_of_birth']['year'])) {
                    if(isset($officer['date_of_birth']['month'])) {
                        $data_client_officer['date_of_birth'] = StaticFunctions::FormatMonth($officer['date_of_birth']['month']).' '.$officer['date_of_birth']['year'];
                    } else {
                        $data_client_officer['date_of_birth'] = $officer['date_of_birth']['year'];
                    }
                }
                $data_client_officer['status']= self::ACTICESTATUS;
                $new_array2[]=$data_client_officer;
            }
            return $new_array2;
        }
    }
    public static function getClientPersons($psc_officers_data,$client_id,$company_id)
    {
        $officers  = array();
        $i=0;
        if(isset($psc_officers_data['items']) && count($psc_officers_data['items'])>0){
            foreach($psc_officers_data['items'] as $psc_officers)
            {
                if(isset($psc_officers['name_elements']['title']))
                {
                    $officers[$i]['contact_title'] = $psc_officers['name_elements']['title'];
                    //$data['contact_title'] = $psc_officers['name_elements']['title'];
                }

                if(isset($psc_officers['name_elements']['forename']))
                {
                    $officers[$i]['first_name'] = $psc_officers['name_elements']['forename'];
                    // $data['first_name'] = $psc_officers['name_elements']['forename'];
                }
                if(isset($psc_officers['name_elements']['middle_name']))
                {
                    $officers[$i]['first_name'] = $psc_officers['name_elements']['forename'].' '.$psc_officers['name_elements']['middle_name'];
                }

                if(isset($psc_officers['name_elements']['surname']))
                {
                    $officers[$i]['last_name'] = $psc_officers['name_elements']['surname'];
                }

                if(isset($psc_officers['date_of_birth']))
                {
                    $officers[$i]['date_of_birth'] = (isset($psc_officers['date_of_birth']['month']) ? StaticFunctions::FormatMonth($psc_officers['date_of_birth']['month']).' '.$psc_officers['date_of_birth']['year'] : '');
                }

                if(isset($psc_officers['country_of_residence']))
                {
                    $officers[$i]['contact_country'] = $psc_officers['country_of_residence'];
                }

                if(isset($psc_officers['address']['premises']))
                {
                    if(isset($psc_officers['address']['address_line_1']))
                    {
                        $officers[$i]['contact_address_line1'] = $psc_officers['address']['premises'].', '.$psc_officers['address']['address_line_1'];
                    }
                } else {
                    if(isset($psc_officers['address']['address_line_1']))
                    {
                        $officers[$i]['contact_address_line1'] = $psc_officers['address']['address_line_1'];
                    } else {
                        $officers[$i]['contact_address_line1'] = $psc_officers['address']['premises'];
                    }
                }

                if(isset($psc_officers['address']['postal_code']))
                {
                    $officers[$i]['contact_postal_code']  = $psc_officers['address']['postal_code'];
                }

                if(isset($psc_officers['address']['locality']))
                {
                    $officers[$i]['contact_city']  = $psc_officers['address']['locality'];
                    $officers[$i]['contact_county']= $psc_officers['address']['locality'];
                }

                if(isset($officer['nationality'])){
                    $data['nationality'] = $officer['nationality'];
                }

                if(isset($psc_officers['ceased_on']) && !empty($psc_officers['ceased_on'] ))
                {
                    $officers[$i]['ceased_on']  = $psc_officers['ceased_on'];
                }
                if(isset($psc_officers['notified_on']) && !empty($psc_officers['notified_on'] ))
                {
                    $officers[$i]['appointed_on']  = $psc_officers['notified_on'];
                }

                $officers[$i]['officer_type']  =  'Significant';
                $officers[$i]['client_id'] =   $client_id;
                $officers[$i]['company_id'] =  $company_id;
                $officers[$i]['status']= self::ACTICESTATUS;
                $i++;
                // $new_array2[]=$officers;

            }

            return $officers;
        }
    }

    public function getStatus()
    {
        return $this->hasOne(KycOnfido::class,'user_id','contact_id');
    }
}
