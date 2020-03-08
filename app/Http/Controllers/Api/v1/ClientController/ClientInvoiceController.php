<?php

namespace App\Http\Controllers\Api\v1\ClientController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyConfig;
use App\Invoice;
use App\InvoiceItem;
use DB;
use App\Http\StaticFunctions\StaticFunctions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Http\Resources\Client\ClientInvoiceResource;
use App\Http\Resources\Client\ClientInvoiceItemResource;
use Arr;

class ClientInvoiceController extends Controller
{
    public function getInvoiceList(Request $req)
    {
    	// return $req;
      $FilterRequest = ['invoice_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'invoice_id' => 'Invoice ID',
      ];
      $validator = Validator::make($data ,[
          'invoice_id' => 'digits_between:1,11',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
        $getinvoice  = Invoice::find($data['invoice_id']);
       // return $getinvoice;
	   if(!empty($getinvoice)) {
	   	    $client_invoice = new ClientInvoiceItemResource($getinvoice);
	      return Response::json(['status' => 'success', 'data' => $client_invoice]);
	   }else {
         $errors['ErrorMessage'] = ['Invoice Recode Not Found !!'];
         return Response::json(['status' => 'error', 'data' => $errors]);
      }
    }
    public function InvoiceItem(Request $req){
    	// return $req;
    	    $FilterRequest = ['module_id','company_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'module_id' => 'Module ID',
         'company_id' => 'Company ID',
      ];
      $validator = Validator::make($data ,[
          'company_id' => 'required|digits_between:1,11',
          'module_id' => 'required|string',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }

       $getinvoice  = Invoice::select('invoice_id as InvoiceID','company_id as CompanyID','invoice_status as InvoiceStatus','due_date as DueDate','invoice_month as InvoiceMonth','invoice_year as InvoiceYear','module_id as ModuleID')->where(['company_id'=>$data['company_id'],'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id'])])->get();
       
       	if(!empty($getinvoice)) {
	      	return Response::json(['status' => 'success', 'data' => $getinvoice]);
	   }else {
         $errors['ErrorMessage'] = ['Invoice Recode Not Found !!'];
         return Response::json(['status' => 'error', 'data' => $errors]);
      }
    }
}
