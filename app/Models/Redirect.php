<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = [
        'from_path',
        'to_path',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'code' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function normalizePath(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = '/'.ltrim(trim($path), '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
