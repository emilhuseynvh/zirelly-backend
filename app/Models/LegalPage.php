<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalPage extends Model
{
    use HasTranslations;

    public const SLUGS = ['return-policy', 'privacy-policy', 'delivery-payment', 'terms-of-use'];

    protected $fillable = [
        'slug',
        'og_image_id',
    ];

    protected array $translatable = [
        'title',
        'content',
        'og_title',
        'og_description',
    ];

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'og_image_id');
    }

    public static function forSlug(string $slug): static
    {
        return static::query()->firstOrCreate(['slug' => $slug]);
    }
}
