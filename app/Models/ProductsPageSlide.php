<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductsPageSlide extends Model
{
    use HasTranslations;

    protected $fillable = [
        'products_page_id',
        'image_id',
        'link',
        'position',
    ];

    protected array $translatable = [
        'title',
        'button_text',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'image_id');
    }
}
