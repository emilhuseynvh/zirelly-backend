<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Blog extends Model
{
    use HasTranslations, HasUniqueSlug;

    protected $fillable = [
        'slug',
        'image',
        'is_published',
        'published_at',
    ];

    protected array $translatable = [
        'title',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function imageUrl(): ?string
    {
        return $this->image
            ? Storage::disk('public')->url($this->image)
            : null;
    }

}