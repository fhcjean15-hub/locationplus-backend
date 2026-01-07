<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bien_id' => $this->bien_id,
            'user_id' => $this->user_id,
            'owner_id' => $this->owner_id,
            'tracking_token' => $this->tracking_token, // ajouté pour invité

            'client' => [
                'name' => $this->client_name,
                'email' => $this->client_email,
                'phone' => $this->client_phone,
            ],

            'category' => $this->category,
            'transaction_type' => $this->transaction_type,
            'reservation_type' => $this->reservation_type,
            'price' => $this->price,

            'dates' => [
                'start' => $this->start_date,
                'end' => $this->end_date,
                'visit' => $this->visit_date,
            ],

            'message' => $this->message,
            'status' => $this->status,

            'bien' => $this->whenLoaded('bien'),       // relation optionnelle
            'owner' => $this->whenLoaded('owner'),     // relation optionnelle

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
