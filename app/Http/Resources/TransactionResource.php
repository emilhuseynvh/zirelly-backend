<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'method' => $this->method,
            'amount' => (float) $this->amount,
            'reference' => $this->reference,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
