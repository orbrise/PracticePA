<?php

namespace App\Http\Resources\Notes;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\StaticFunctions\StaticFunctions;

class NotesResource extends JsonResource
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
            'NoteID' => $this->note_id,
            'ClientID'=>$this->client_id,
            'UserID' => $this->user_id,
            'NoteDate' =>$this->note_date,
            'NoteTime' =>$this->note_time,
            'ServiceID' =>$this->service_id,
            'DueDate' =>$this->due_date,
            'NoteData' =>$this->note_data,
            'TelephoneConversation' =>$this->telephone_conversation,
            'ClientType' => $this->clientType->client_type,
            'UserName' => StaticFunctions::getPartners(!empty($this->user_id)?$this->user_id:''),
        ];
    }
}
