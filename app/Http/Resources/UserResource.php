<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date?->toDateString(),
            'email' => $this->email,
            'role' => $this->role->value,
            'email_verified' => $this->hasVerifiedEmail(),
            'created_at' => $this->created_at?->toIso8601String(),
            'orders_count' => $this->whenCounted('orders'),
            'orders_total' => $this->whenHas('orders_total', fn () => round((float) $this->orders_total, 2)),
        ];
    }
}