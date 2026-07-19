<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductHowToUseStep extends Model
{
    use HasTranslations;

    protected $fillable = [
        'product_id',
        'position',
    ];

    protected array $translatable = [
        'title',
        'description',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
