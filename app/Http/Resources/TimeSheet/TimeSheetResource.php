<?php

namespace App\Http\Resources\TimeSheet;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\StaticFunctions\StaticFunctions;

class TimeSheetResource extends JsonResource
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
            "SheetID" =>$this->sheet_id,
            'JobID' => $this->job_id,
            'PostStatus' => $this->post_status,
            'WorkDescription' => $this->work_desc,
            'WorkUnit' => $this->work_unit,
            'WorkDate' =>  StaticFunctions::datGet($this->work_date),
            'ClientID' => $this->client_id,
            'ClientName' => !empty($this->client->code->code_alpha)?$this->client->code->code_alpha.'-':''.!empty($this->client->code->code_digit)?$this->client->code->code_digit.' ':''.!empty($this->client->client_name)?$this->client->client_name:'',
            'ClientServicesID' =>!empty($this->get_service)?$this->get_service->cs_id:'',
            'ServiceName' => !empty($this->get_service)?$this->get_service->service_name:'',
            'EmployeeID' => $this->employee_id,
            'EmployeeName' => StaticFunctions::getPartners(!empty($this->employee_id)?$this->employee_id:''),
        ];
    }
}
