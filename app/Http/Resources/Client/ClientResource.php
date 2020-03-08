<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'AddressID'=>$this->cci_id,
            'ClientID'=>$this->client_id,
            'AddressType' => $this->address_type,
            'AddressTypeOther' => $this->address_type_other,
            'Address'=>$this->address_line1,
            'City'=>$this->city,
            'County'=>$this->county,
            'Country'=>$this->country,
            'PostalCode'=>$this->postal_code,
            'PhoneNo'=>$this->phone_no,
            'FaxNo'=>$this->fax,
            'MobileNo' => $this->mobile,
            'Email' => $this->email,
            'Website' => $this->website,
            'ClientType' => $this->client_type->client_type,
        ];
    }
}
