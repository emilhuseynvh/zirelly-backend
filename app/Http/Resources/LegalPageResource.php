<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegalPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->translate('title'),
            'content' => $this->translate('content'),
            'og_title' => $this->translate('og_title'),
            'og_description' => $this->translate('og_description'),
            'og_image' => new UploadResource($this->whenLoaded('ogImage')),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translationsGrouped(),
            ),
        ];
    }
}
