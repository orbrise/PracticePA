<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientType extends Model
{
    protected $table ='client_type';

    const Limted =1;
    const LLP =2;
    const SoleTrader = 3;
    const Partnership = 4;
    
    protected $fillable = [
        'type','created_at', 'updated_at',
    ];

}
