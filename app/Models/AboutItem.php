<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'about_page_id',
        'position',
    ];

    protected array $translatable = [
        'title',
        'description',
    ];

    public function aboutPage(): BelongsTo
    {
        return $this->belongsTo(AboutPage::class);
    }
}