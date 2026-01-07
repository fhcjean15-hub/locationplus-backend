<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'annonce_id'  => $this->annonce_id,
            'url'         => $this->url,
            'full_url'    => asset('storage/' . $this->url),
            'type'        => $this->type,
            'order_index' => $this->order_index,
            'created_at'  => $this->created_at,
        ];
    }
}
