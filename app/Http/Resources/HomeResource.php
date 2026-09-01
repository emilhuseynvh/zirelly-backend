<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'meta_title' => $this->translate('meta_title'),
            'meta_description' => $this->translate('meta_description'),
            'og_title' => $this->translate('og_title'),
            'og_description' => $this->translate('og_description'),
            'og_image' => new UploadResource($this->whenLoaded('ogImage')),
            'slides' => HomeSlideResource::collection($this->whenLoaded('slides')),
            'stats' => [
                'title' => $this->translate('stats_title'),
                'items' => HomeStatResource::collection($this->whenLoaded('stats')),
            ],
            'banner' => [
                'button_text' => $this->translate('banner_button_text'),
                'link' => $this->banner_link,
                'image' => new UploadResource($this->whenLoaded('bannerImage')),
            ],
            'testimonials' => [
                'title' => $this->translate('testimonials_title'),
                'items' => TestimonialResource::collection($this->whenLoaded('testimonials')),
            ],
            'faq' => [
                'title' => $this->translate('faq_title'),
                'items' => FaqResource::collection($this->whenLoaded('faqs')),
            ],
            'updated_at' => $this->updated_at?->toIso8601String(),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translationsGrouped(),
            ),
        ];
    }
}