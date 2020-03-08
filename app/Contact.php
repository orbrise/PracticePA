<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Contact extends Model
{
    const CONTACTYPECOMPANY = 'company';
    protected $table ='client_contact';
    protected $primaryKey = 'contact_id';
    protected $fillable = [
        'company_id','contact_type','client_id','contact_title','contact_other_title','first_name','last_name','contact_designation','contact_phone_no','contact_email','contact_address_line1','contact_city','contact_county','contact_country','contact_postal_code','notes','created_at','updated_at','module_id'
    ];


    public static function db_connection($company_db='')
    {
        $data[]=DB::connection()->getPdo()->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $data[]=DB::connection()->getPdo()->exec("use ".strtolower($company_db));
        $data[]=DB::connection()->setDatabaseName(strtolower($company_db));
        $data[]=DB::connection()->getDatabaseName();
        return $data;
    }
}
