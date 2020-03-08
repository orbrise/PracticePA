<?php

namespace App\Http\Resources\ClientOfficer;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientOfficerResource extends JsonResource
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
            'OfficerName' => $this->first_name.' '.$this->last_name,
            'KYCStatus' => $this->getStatus['kyc_status'],
            'CompanyID'=>$this->company_id,
            'ClientID'=>$this->client_id,
            'ContactType'=>$this->contact_type,
            'OfficerType'=>$this->officer_type,
            'CeasedOn'=>$this->ceased_on,
            'DateOfBirth'=>$this->date_of_birth,
            'AppointedOn'=>$this->appointed_on,
            'ResignedOn'=>$this->resigned_on,
            'ContactTitle'=>$this->contact_title,
            'ContactOtherTitle'=>$this->contact_other_title,
           'FirstName'=>$this->first_name,
            'LastName'=>$this->last_name,
            'ContactDesignation'=>$this->contact_designation,
           'ContactPhoneNo'=>$this->contact_phone_no,
            'ContactEmail'=>$this->contact_email,
            'ContactAddressLine1'=>$this->contact_address_line1,
            'ContactCity'=>$this->contact_city,
            'ContactCounty'=>$this->contact_county,
           'ContactCountry'=>$this->contact_country,
            'Nationality'=>$this->nationality,
            'ContactPostalCode'=>$this->contact_postal_code,
           'Notes'=>$this->notes,
        ];
    }
}
