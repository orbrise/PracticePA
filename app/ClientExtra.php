<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientExtra extends Model
{
    protected $table ='client_extra';
    protected $primaryKey = 'extra_id';
    protected $fillable = [
        'client_id','proof_of_identity','proof_of_address','created_at','updated_at','module_id'
    ];
}
