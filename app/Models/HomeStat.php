<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeStat extends Model
{
    use HasTranslations;

    protected $fillable = [
        'home_page_id',
        'value',
        'position',
    ];

    protected array $translatable = [
        'label',
    ];

    public function homePage(): BelongsTo
    {
        return $this->belongsTo(HomePage::class);
    }
}