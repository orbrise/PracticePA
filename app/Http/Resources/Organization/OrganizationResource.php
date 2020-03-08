<?php

namespace App\Http\Resources\Organization;

use Illuminate\Http\Resources\Json\JsonResource;
use Arr;
class OrganizationResource extends JsonResource
{
   /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
   public function __construct($resource,$fields)
   {
     $this->fields = $fields;
     $this->resource = $resource;
   }

   public function toArray($request)
   {
      return [
         'OrganizationID' => $this->id,
         'OrganizationName' => $this->org_name,
         'OrganizationTitle' => $this->title,
         'FirstName' =>$this->first_name,
         'LastName' =>$this->last_name,
         'OrganizationEmail' =>$this->email,
         'OrganizationPosition' =>$this->designation,
         'OrganizationPhoneNumber' => $this->phone,
         $this->mergeWhen(Arr::get($this->fields, 'EditFor' ) == 1 ,
            [
               'CompanyID'=>$this->company_id,
               'OrganizationAddress' => $this->address,
               'OrganizationCity' => $this->city,
               'OrganizationCountry' => $this->country,
               'OrganizationPotalCode' => $this->postal_code,
               'OrganizationCounty' => $this->county,
            ]),
      ];
   }
}
