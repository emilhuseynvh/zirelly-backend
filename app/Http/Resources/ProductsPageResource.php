<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductsPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'meta_title' => $this->translate('meta_title'),
            'meta_description' => $this->translate('meta_description'),
            'products_title' => $this->translate('products_title'),
            'slides' => UploadResource::collection($this->whenLoaded('slides')),
            'side_image' => new UploadResource($this->whenLoaded('sideImage')),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translationsGrouped(),
            ),
        ];
    }
}