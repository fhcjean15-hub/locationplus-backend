<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'user_id'               => $this->user_id,
            'amount'                => $this->amount,
            'status'                => $this->status,
            'method'                => $this->method,
            'transaction_reference' => $this->transaction_reference,
            'created_at'            => $this->created_at,
        ];
    }
}
