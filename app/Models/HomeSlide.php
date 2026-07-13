<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeSlide extends Model
{
    use HasTranslations;

    protected $fillable = [
        'home_page_id',
        'image_id',
        'link',
        'position',
    ];

    protected array $translatable = [
        'title',
        'description',
        'button_text',
    ];

    public function homePage(): BelongsTo
    {
        return $this->belongsTo(HomePage::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'image_id');
    }
}