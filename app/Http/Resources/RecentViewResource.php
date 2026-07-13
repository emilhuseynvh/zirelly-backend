<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentViewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'viewed_at' => $this->viewed_at?->toIso8601String(),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}