<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KycOnfido extends Model
{
    protected $table = 'kyc_onfido';

    protected $fillable = [
        'onfido_applicant_id','onfido_check_id','onfido_check_result','onfido_report_id','user_id',
        'client_id','reference_number','kyc_status','apply_date','updated',
    ];
}
