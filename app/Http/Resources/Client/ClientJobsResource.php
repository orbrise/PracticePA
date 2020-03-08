<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientJobsResource extends JsonResource
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
            'ServiceID'=>$this->cs_id,
            'ServiceName'=>$this->service_name,
            'StartDate'=>$this->get_job['start_date'],
            'YearEnd'=>$this->get_job['year_end'],
            'DueDate'=>$this->get_job['start_date'],
            

        ];
    }
}
