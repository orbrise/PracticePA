<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
	public static $OrganizationResourceFields = [
        'EditFor' => 0
    ];
    protected $fillable = [
        'org_name','title','first_name','last_name','designation', 'phone','email','address','city', 'county','country','postal_code','company_id', 'notes','status','created_at','updated_at'
    ];

}
