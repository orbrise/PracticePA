<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;
use Arr;
class ClientInvoiceItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    // public function __construct($resource,$fields)
    // {
    //     $this->resource = $resource;
    //     $this->fields = $fields;
    // }

    public function toArray($request)
    {
       return [

                'InvoiceID'=>$this->invoice_id,
                'CompanyID'=>$this->company_id,
                'InvoiceStatus'=>$this->invoice_status,
                'PaymentType'=>$this->payment_type,
                'DueDate'=>$this->due_date,
                'InvoiceMonth'=>$this->invoice_month,
                'InvoiceYear'=>$this->invoice_year,
                'VatTax'=>$this->vat_tax,
                'ModuleID'=>$this->module_id,
                'InvoiceItems' => $this->invoice_item
                
                    /*'Name'=>ClientInvoiceResource::collection($this->invoice_item->name),
                    // 'Email'=>$this->invoice_item->email,
                    // 'Amount'=>$this->invoice_item->amount,
                    // 'ItemType'=>$this->invoice_item->item_type, */
                
            
               
                           
        ];
    }
}
