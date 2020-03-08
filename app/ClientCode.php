<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientCode extends Model
{
   	protected $table ='client_codes';
   	protected $primaryKey = 'id';
   	public $timestamps = false;
    protected $fillable = [
        'code_alpha','code_digit', 'client_id',
    ];
}
