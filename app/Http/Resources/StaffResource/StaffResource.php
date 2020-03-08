<?php

namespace App\Http\Resources\StaffResource;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\StaticFunctions\StaticFunctions;
use Arr;
class StaffResource extends JsonResource
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
            'StaffID' => $this->id,
            'UserID' => $this->user->user_id,
            'CompanyID'=>$this->user->company_id,
            'FirstName' =>$this->user->first_name,
            'LastName' =>$this->user->last_name,
            'UserEmail' =>$this->user->user_email,
            'Account' =>$this->user->account_type,
            'ChargeOutRate' =>$this->user->charge_out_rate,
            'UserRole' => StaticFunctions::getRoleByID($this->role_id),
            'RegisteredAt' => date('d/m/Y',strtotime($this->user->created_at)),
            'UserStatus' => $this->user->user_status,
            $this->mergeWhen(Arr::get($this->fields, 'EditFor' ) == 1 ,
                [
                    'StaffPhoneNumber' => $this->user->phone,
                    'StaffCity' => $this->user->city,
                    'StaffCountry' => $this->user->country,
                    'StaffPotalCode' => $this->user->postal_code,
                    'StaffCounty' => $this->user->county,
                    'UserRoleID' => $this->role_id,
                    'ReportTo' => $this->report_to
                ]),

        ];
    }
}
