<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasTranslations;

    protected $fillable = [
        'home_page_id',
        'position',
    ];

    protected array $translatable = [
        'question',
        'answer',
    ];

    public function homePage(): BelongsTo
    {
        return $this->belongsTo(HomePage::class);
    }
}