<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url(),
            'original_name' => $this->original_name,
            'alt' => $this->alt,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
        ];
    }
}