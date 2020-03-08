<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CompanyConfig;
use DB;

class DatasbeseConnection extends Model
{
    public static function getDbName($id)
    {
    	 $data_get = CompanyConfig::where('company_id',$id)->get();
	     $company_db='';
		    foreach ($data_get as $key => $value) {
	        if($value->config_name == 'company_database') 
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
      
        return $data;
     
    }
}
