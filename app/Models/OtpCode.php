<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class OtpCode extends Model
{
    public const TYPE_REGISTER = 'register';
    public const TYPE_RESET_PASSWORD = 'reset_password';

    public const EXPIRY_MINUTES = 10;
    public const MAX_ATTEMPTS = 5;

    protected $fillable = [
        'email',
        'type',
        'code',
        'attempts',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Yeni 6 rəqəmli kod yaradır, köhnələri silir və açıq kodu qaytarır.
     */
    public static function issue(string $email, string $type): string
    {
        static::query()->where('email', $email)->where('type', $type)->delete();

        $code = (string) random_int(100000, 999999);

        static::create([
            'email' => $email,
            'type' => $type,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
        ]);

        return $code;
    }

    /**
     * Kodu yoxlayır. Uğurlu olduqda qeydi silir və true qaytarır.
     */
    public static function verifyAndConsume(string $email, string $type, string $code): bool
    {
        $otp = static::query()
            ->where('email', $email)
            ->where('type', $type)
            ->latest('id')
            ->first();

        if ($otp === null || $otp->expires_at->isPast()) {
            return false;
        }

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            $otp->delete();

            return false;
        }

        if (! Hash::check($code, $otp->code)) {
            $otp->increment('attempts');

            return false;
        }

        $otp->delete();

        return true;
    }
}
