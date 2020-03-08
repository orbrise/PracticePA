<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Invoice;

class InvoiceItem extends Model
{
    protected $fillable =[
        'user_id','name','email','amount','invoice_id','address','item_type','address','item_type','client_id',
    ];
    
}
