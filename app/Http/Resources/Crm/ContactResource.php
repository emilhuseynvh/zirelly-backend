<?php

namespace App\Http\Resources\Crm;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'email' => $this->email,
            'birth_date' => $this->birth_date?->toDateString(),
            'channel' => $this->channel,
            'orders_count' => (int) ($this->orders_count ?? 0),
            'orders_total' => round((float) ($this->orders_total ?? 0), 2),
            'first_order_at' => $this->first_order_at,
            'last_order_at' => $this->last_order_at,
            'created_at' => $this->created_at?->toIso8601String(),
            'notes' => ContactNoteResource::collection($this->whenLoaded('notes')),
            'orders' => CrmOrderResource::collection($this->whenLoaded('orders')),
        ];
    }
}
