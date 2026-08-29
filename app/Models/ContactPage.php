<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'email',
        'phone',
        'map_embed_url',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'linkedin_url',
    ];

    protected array $translatable = [
        'meta_title',
        'meta_description',
        'title',
        'subtitle',
        'footer_description',
    ];

    public static function current(): static
    {
        return static::query()->firstOrCreate();
    }
}