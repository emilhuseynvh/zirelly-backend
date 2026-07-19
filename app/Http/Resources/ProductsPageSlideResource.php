<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductsPageSlideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->translate('title'),
            'button_text' => $this->translate('button_text'),
            'link' => $this->link,
            'image' => new UploadResource($this->whenLoaded('image')),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translationsGrouped(),
            ),
        ];
    }
}
