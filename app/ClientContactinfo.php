<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Client;

class ClientContactinfo extends Model
{
    protected $table ='client_contact_info';
    protected $primaryKey = 'cci_id';
    protected $fillable = [
        'client_id','address_type','address_type_other','address_line1','address_line2','city','county','postal_code','country','mobile','phone_no','fax','email','website','module_id'
    ];
    public function client_type()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }
}
