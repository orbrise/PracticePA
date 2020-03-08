<?php

namespace App\Http\Resources\CompanyConfig;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyConfigResource extends JsonResource
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
            'config_id'=>$this->config_id,
            'company_id' => $this->company_id,
            'config_name' => $this->config_name,
            'config_value' => $this->config_value,

        ];
    }
}
