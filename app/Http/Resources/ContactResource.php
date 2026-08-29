<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'meta_title' => $this->translate('meta_title'),
            'meta_description' => $this->translate('meta_description'),
            'title' => $this->translate('title'),
            'subtitle' => $this->translate('subtitle'),
            'email' => $this->email,
            'phone' => $this->phone,
            'map_embed_url' => $this->map_embed_url,
            'facebook_url' => $this->facebook_url,
            'instagram_url' => $this->instagram_url,
            'tiktok_url' => $this->tiktok_url,
            'linkedin_url' => $this->linkedin_url,
            'footer_description' => $this->translate('footer_description'),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translationsGrouped(),
            ),
        ];
    }
}