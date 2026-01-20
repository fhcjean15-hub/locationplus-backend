<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'kind'          => $this->kind, // 🔥 NEW
            'max_annonces'  => $this->max_annonces,
            'description'   => $this->description,
            'price'         => $this->price,
            'created_at'    => $this->created_at,
        ];
    }
}



