<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'owner_id'      => $this->owner_id,
            'category_id'   => $this->category_id,

            'title'         => $this->title,
            'description'   => $this->description,
            'price'         => $this->price,

            'location_text' => $this->location_text,
            'lat'           => $this->lat,
            'lng'           => $this->lng,

            'data_json'     => $this->data_json ? json_decode($this->data_json, true) : null,

            'status'        => $this->status,
            'created_at'    => $this->created_at,

            'owner'         => new UserResource($this->whenLoaded('owner')),
            'medias'        => MediaResource::collection($this->whenLoaded('medias')),
        ];
    }
}
