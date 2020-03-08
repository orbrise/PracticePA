<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class TimeSheetInvoices extends Model
{
    protected $table ='timesheet_invoices';
    protected $primaryKey = 'invoice_id';
    const INVOIVENEW ='New';
    const PENDING = 'Pending';
    const Complete ='Complete';
    protected $fillable = [
        'client_id','description','module_id','discount','discount_type','net_total','grand_total','invoice_datetime','invoice_due_date','invoice_status','created_at','updated_at'
    ];

    public function client()
    {
    	return $this->belongsTo(Client::class,'client_id','client_id');
	}

	public function invoices_payments()
    {
    	return $this->hasMany(TimeSheetInvoicesPayments::class,'invoice_id','invoice_id');
	}

    public function invoices_payments_select()
    {
        return $this->hasMany(TimeSheetInvoicesPayments::class,'invoice_id','invoice_id')->select('amount_received as AmountReceived','received_date as ReceivedDate','id as InvoicesPaymentsID','invoice_id as invoice_id');
    }


}
