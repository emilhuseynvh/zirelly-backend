<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeStatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'label' => $this->translate('label'),
            'translations' => $this->when(
                $request->boolean('with_translations'),
                fn () => $this->translationsGrouped(),
            ),
        ];
    }
}