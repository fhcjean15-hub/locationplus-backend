<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'annonce_id'      => $this->annonce_id,
            'type'            => $this->type,
            'requester_name'  => $this->requester_name,
            'requester_email' => $this->requester_email,
            'requester_phone' => $this->requester_phone,
            'message'         => $this->message,
            'created_at'      => $this->created_at,
            'annonce'         => new AnnonceResource($this->whenLoaded('annonce')),
        ];
    }
}
