<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignalementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'motif'              => $this->motif,
            'commentaire'        => $this->commentaire,
            'processed_by_admin' => $this->processed_by_admin,
            'action_taken'       => $this->action_taken,
            'created_at'         => $this->created_at,

            'annonce'            => new AnnonceResource($this->whenLoaded('annonce')),
            'reporter'           => new UserResource($this->whenLoaded('reporter')),
        ];
    }
}
