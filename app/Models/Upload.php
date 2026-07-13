<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $upload) {
            Storage::disk('public')->delete($upload->path);
        });
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}