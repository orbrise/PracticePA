<?php

namespace App\Http\Resources\TimeSheet;

use Illuminate\Http\Resources\Json\JsonResource;

class TimeSheetInovoiceResource extends JsonResource
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
            "InvoiceID" =>$this->invoice_id,
            'Description' => $this->description,
            'InvoiceTotal' => $this->grand_total,
            'InvoiceDate' =>  StaticFunctions::datGet($this->invoice_datetime),
            'ClientID' => $this->client_id,
            'ClientName' => !empty($this->client->client_name)?$this->client->client_name:'',
            'ReceivedAmount' =>!empty($this->get_job)?$this->get_job->cs_id:'',
            'ServiceName' => !empty($this->get_job->get_service)?$this->get_job->get_service->service_name:'',
            'Unit'=>$this->work_unit,
            'ChargeOutRate'=>$this->charge_out_rate,
            'Total'=>$this->charge_out_rate*$this->work_unit,
            'EmployeeID' => $this->employee_id,
            'EmployeeName' => StaticFunctions::getPartners(!empty($this->employee_id)?$this->employee_id:''),
        ];
    }
}
