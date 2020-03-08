<?php

namespace App\Http\Resources\Modules;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
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

            'ModuleName' => $this->name,
            'ModuleLogo' => $this->logo,
            'ModuleSLug' => $this->slug,

        ];
    }
}
