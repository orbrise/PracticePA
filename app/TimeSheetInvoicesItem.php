<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeSheetInvoicesItem extends Model
{
    protected $table ='timesheet_invoice_items';
    protected $primaryKey = 'ti_item_id';
    protected $fillable = [
        'timesheet_id','invoice_id','module_id','created_at','updated_at'
    ];
}
