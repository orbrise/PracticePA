<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Arr;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public function __construct($resource, $fields)
    {
        $this->fields = $fields;
        $this->resource = $resource;
    }

    public function toArray($request)
    {
        return [
            'ID' => $this->user_id,
            'Name' => $this->first_name,
            'LastName' => $this->last_name,
            'Email' => $this->user_email,
            'UserStatus' => $this->user_status,
            $this->mergeWhen(Arr::get($this->fields, 'VerificationCode' ) ==1 ,
                ['EmailVerificationCode' => $this->verification_code]),

        ];
    }
}
