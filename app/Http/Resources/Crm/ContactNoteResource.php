<?php

namespace App\Http\Resources\Crm;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'author' => $this->author?->name,
            'author_id' => $this->crm_user_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'contact' => $this->whenLoaded('contact', fn () => [
                'id' => $this->contact->id,
                'name' => trim($this->contact->name.' '.($this->contact->surname ?? '')),
            ]),
        ];
    }
}
