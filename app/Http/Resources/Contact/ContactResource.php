<?php

namespace App\Http\Resources\Contact;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'ContactID'=>$this->contact_id,
            'CompanyID' =>$this->company_id,
            'ContactOtherTitle' => $this->contact_other_title,
            'FirstName' =>$this->first_name,
            'LastName' =>$this->last_name,
            'Position' =>$this->contact_designation,
            'ContactPhoneNumber' =>$this->contact_phone_no,  
            'ContactEmail' =>$this->contact_email,
            "ContactAddressLine1"=> $this->contact_address_line1,
            "ContactCity"=> $this->contact_city,
            "ContactCounty"=> $this->contact_county,
            "ContactCountry"=> $this->contact_country,
            "ContactPostalCode"=> $this->contact_postal_code,
            "Notes"=> $this->notes,  
        ];
    }
}
