<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title'),
            'meta_title' => $this->translate('meta_title'),
            'meta_description' => $this->translate('meta_description'),
            'description' => $this->translate('description'),
            'price' => (float) $this->price,
            'discount' => $this->discount !== null ? (float) $this->discount : null,
            'discount_type' => $this->discount_type?->value,
            'final_price' => $this->finalPrice(),
            'is_active' => $this->is_active,
            'images' => UploadResource::collection($this->whenLoaded('images')),
            'features' => ProductFeatureResource::collection($this->whenLoaded('features')),
            'rating' => [
                'average' => round((float) ($this->reviews_avg_rating ?? 0), 1),
                'count' => (int) ($this->reviews_count ?? 0),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translationsGrouped(),
            ),
        ];
    }
}