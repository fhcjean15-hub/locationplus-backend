<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'full_name'           => $this->full_name,
            'company_name'        => $this->company_name,
            'email'               => $this->email,
            'phone'               => $this->phone,
            'avatar_url'          => $this->avatar_url,
            'ifu'                 => $this->ifu,
            'adresse'             => $this->adresse,
            'ville'               => $this->ville,
            'account_type'        => $this->account_type,
            'account_category_id' => $this->account_category_id,
            'documents_urls'      => $this->documents_urls,
            'verified_documents'  => $this->verified_documents,
            'activated'           => $this->activated,
            'payment_status'      => $this->payment_status,
            'payment_valid_until' => $this->payment_valid_until,
            'email_verified_at'   => $this->email_verified_at,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,

            // 🔥 Ajout de la relation accountCategory
            'account_category' => $this->whenLoaded('accountCategory', function () {
                return [
                    'id' => $this->accountCategory?->id ?? 0,
                    'name' => $this->accountCategory?->name ?? '',
                    'price' => $this->accountCategory?->price ?? 0,
                ];
            }),
        ];
    }
}
