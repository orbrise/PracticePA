<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $primaryKey = 'invoice_id';
	public static $InvoiceResourceFields = [
        'SingleFor' => 0,
        'MultiList'=> 0
    ];
    protected $fillable = [
        'invoice_items','due_date','invoice_month','invoice_year','company_id','invoice_status'

    ];
    public function invoice_item()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'invoice_id')->select('id as InvoiceItemID','name as Name','email as Email','item_type as Type','amount as Amount');
    }
   
}
