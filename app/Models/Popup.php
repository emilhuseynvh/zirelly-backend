<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Popup extends Model
{
    use HasTranslations;

    protected $fillable = [
        'image_id',
        'button_link',
        'delay_seconds',
        'is_active',
        'show_once',
    ];

    protected array $translatable = [
        'title',
        'description',
        'button_text',
    ];

    protected function casts(): array
    {
        return [
            'delay_seconds' => 'integer',
            'is_active' => 'boolean',
            'show_once' => 'boolean',
        ];
    }

    public static function current(): static
    {
        return static::query()->firstOrCreate();
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'image_id');
    }
}
