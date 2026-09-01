<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'surname' => $this->user->surname,
                'email' => $this->when($request->user()?->isAdmin() ?? false, fn () => $this->user->email),
            ]),
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'title' => $this->product->translate('title', 'az') ?? $this->product->translate('title'),
                'slug' => $this->product->slug,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}