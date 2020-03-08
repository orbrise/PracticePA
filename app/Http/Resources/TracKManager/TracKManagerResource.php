<?php

namespace App\Http\Resources\TracKManager;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\StaticFunctions\StaticFunctions;

class TracKManagerResource extends JsonResource
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
            "JobID" =>$this->job_id,
            'JobID' => $this->job_id,
            'ClientServicesID' => $this->cs_id,
            'ClientID' => $this->client_id,
            'PeriodEndOn' => $this->start_date,
            'StartDate' => $this->start_date,
            'DueDate' => $this->due_date,
            'YearEnd' => $this->year_end,
            'JobStatus' => $this->job_status,
            'ClientName' => !empty($this->client->code->code_alpha)?$this->client->code->code_alpha.'-':''.!empty($this->client->code->code_digit)?$this->client->code->code_digit.' ':''.!empty($this->client->client_name)?$this->client->client_name:'',
            'AssignedDate' => $this->assigned_date,
            'AssignedDueDate' => $this->assigned_due_date,
            'ServiceName' => !empty($this->get_service->service_name)?$this->get_service->service_name:'',
            'ServiceType' => !empty($this->get_service->service_type)?$this->get_service->service_type:'',
            'PartenName' => StaticFunctions::getPartners(!empty($this->user_id)?$this->user_id:''),
            'UserID' => $this->user_id,
            'AssignedTo' => StaticFunctions::getPartners(!empty($this->assigned_to)?$this->assigned_to:''),
            'CompletedBy' => StaticFunctions::getPartners(!empty($this->completed_by)?$this->completed_by:''),
            'CancelledBy' => StaticFunctions::getPartners(!empty($this->user_id)?$this->user_id:''),
            'CompletedOn' => $this->completed_on,
            'CanContactClient' => $this->can_contact_client,
            'IsDocumentsReceived' => $this->is_documents_received,
        ];
    }
}
