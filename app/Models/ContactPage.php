<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'email',
        'phone',
        'whatsapp_number',
        'map_embed_url',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'linkedin_url',
        'og_image_id',
    ];

    protected array $translatable = [
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'title',
        'subtitle',
        'footer_description',
    ];

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'og_image_id');
    }

    public static function current(): static
    {
        return static::query()->firstOrCreate();
    }
}