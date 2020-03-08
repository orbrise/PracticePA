<?php

namespace App\Http\Resources\CompanyConfig;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyInviteResource extends JsonResource
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
            'CompanyID' => $this->getCompanyName->company_name,
            'UserID' => $this->user_id,
            'InvitationCode' => $this->invitation_code,
            'FirstName' => $this->invitation_code,
            'InvitationEmail' => $this->invitation_email,
            'SignupEmail' => $this->signup_email,
            'InvitationRole' => $this->invitation_role,
            'ModuleSlug' => $this->module_slug,
            'InviteType' => $this->invite_type,
            'InvitationStatus' => $this->invitation_status,
            'ModuleStatus' => $this->module_status,

        ];
    }
}
