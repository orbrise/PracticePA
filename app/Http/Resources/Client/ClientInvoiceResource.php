<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    
    public function toArray($request)
    {
        return [
            'Name'=>$this->name,
            // 'CompanyID'=>$this->company_id,
            // 'InvoiceStatus'=>$this->invoice_status,
            // 'PaymentType'=>$this->payment_type,
            // 'DueDate'=>$this->due_date,
            // 'InvoiceMonth'=>$this->invoice_month,
            // 'InvoiceYear'=>$this->invoice_year,
            // 'VatTax'=>$this->vat_tax,
               
        ];
    }
}
