<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BienResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'category'         => $this->category,
            'transaction_type' => $this->transaction_type,
            'title'            => $this->title,
            'description'      => $this->description,
            'price'            => $this->price,
            'city'             => $this->city,
            'district'         => $this->district,
            'images'           => $this->images,
            'attributes'       => $this->attributes,
            'status'            => $this->status,
            'actif'            => $this->actif,
            'created_at'       => $this->created_at?->toDateTimeString(),

            // 👤 Propriétaire
            'owner' => [
                'id'    => $this->user?->id,
                'name' => in_array($this->user?->account_type, ['entreprise', 'admin'])
                    ? $this->user?->company_name
                    : $this->user?->full_name,
                'email' => $this->user?->email,
                'avatar_url' => $this->user?->avatar_url,
                'user'  => $this->user,
            ],
        ];
    }
}
