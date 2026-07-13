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
    ];

    protected array $translatable = [
        'meta_title',
        'meta_description',
        'title',
        'subtitle',
    ];

    public static function current(): static
    {
        return static::query()->firstOrCreate();
    }
}