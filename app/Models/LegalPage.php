<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    use HasTranslations;

    public const SLUGS = ['return-policy', 'privacy-policy'];

    protected $fillable = [
        'slug',
    ];

    protected array $translatable = [
        'title',
        'content',
    ];

    public static function forSlug(string $slug): static
    {
        return static::query()->firstOrCreate(['slug' => $slug]);
    }
}
