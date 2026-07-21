<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->translate('title'),
            'description' => $this->translate('description'),
            'button_text' => $this->translate('button_text'),
            'button_link' => $this->button_link,
            'image' => new UploadResource($this->whenLoaded('image')),
            'delay_seconds' => $this->delay_seconds,
            'is_active' => $this->is_active,
            'show_once' => $this->show_once,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translationsGrouped(),
            ),
        ];
    }
}
