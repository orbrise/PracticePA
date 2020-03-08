<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TradeCode extends Model
{
    protected $table ='trade_list';
    protected $primaryKey = 'trade_id';
    protected $fillable = [
        'trade_name','trade_code','created_at', 'updated_at',
    ];
}
