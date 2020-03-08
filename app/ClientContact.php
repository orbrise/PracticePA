<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
    protected $table ='client_contact';
    protected $primaryKey = 'contact_id';
    protected $fillable = [
        'company_id','contact_type','client_id','contact_title','contact_other_title','first_name','last_name','contact_designation','contact_phone_no','contact_email','contact_address_line1','contact_city','contact_county','contact_country','contact_postal_code','notes','status','created_at','updated_at','module_id'
    ];
}
