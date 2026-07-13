<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductsPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'side_image_id',
    ];

    protected array $translatable = [
        'meta_title',
        'meta_description',
        'products_title',
    ];

    public static function current(): static
    {
        return static::query()->firstOrCreate();
    }

    public function sideImage(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'side_image_id');
    }

    public function slides(): BelongsToMany
    {
        return $this->belongsToMany(Upload::class, 'products_page_upload')
            ->withPivot('position')
            ->orderByPivot('position');
    }
}