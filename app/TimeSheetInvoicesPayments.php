<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeSheetInvoicesPayments extends Model
{
    protected $table ='timesheet_invoice_payments';
    protected $fillable = [
        'amount_received','received_date','invoice_id','module_id','created_at','updated_at'
    ];
}
