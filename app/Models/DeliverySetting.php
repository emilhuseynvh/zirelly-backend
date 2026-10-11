<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverySetting extends Model
{
    protected $fillable = [
        'baku_fee',
        'baku_free_threshold',
        'region_fee',
        'nakhchivan_fee',
        'karabakh_fee',
    ];

    protected function casts(): array
    {
        return [
            'baku_fee' => 'decimal:2',
            'baku_free_threshold' => 'decimal:2',
            'region_fee' => 'decimal:2',
            'nakhchivan_fee' => 'decimal:2',
            'karabakh_fee' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
