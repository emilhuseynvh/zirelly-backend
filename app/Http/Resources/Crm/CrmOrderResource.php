<?php

namespace App\Http\Resources\Crm;

use App\Http\Resources\OrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrmOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $customerName = $this->contact
            ? trim($this->contact->name.' '.($this->contact->surname ?? ''))
            : trim(($this->user?->name ?? '').' '.($this->user?->surname ?? ''));

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'channel' => $this->channel,
            'contact_id' => $this->contact_id,
            'customer' => $customerName !== '' ? $customerName : null,
            'phone' => $this->contact?->phone ?? $this->user?->phone,
            'email' => $this->contact?->email ?? $this->user?->email,
            'items_count' => (int) ($this->items_count ?? ($this->relationLoaded('items') ? $this->items->count() : 0)),
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'total' => (float) $this->total,
            'delivery_fee' => (float) $this->delivery_fee,
            'grand_total' => round((float) $this->total + (float) $this->delivery_fee, 2),
            'promocode_code' => $this->promocode_code,
            'address' => $this->address,
            'note' => $this->note,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
        ];
    }
}
