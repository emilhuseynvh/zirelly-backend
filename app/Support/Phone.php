<?php

namespace App\Support;

class Phone
{
    public const AZ_PATTERN = '/^\+994(10|50|51|55|60|70|77|99)[0-9]{7}$/';

    public static function normalize(string $value): string
    {
        $digits = preg_replace('/[\s().\-]/', '', trim($value));
        $digits = ltrim($digits, '+');

        if (! ctype_digit($digits)) {
            return trim($value);
        }

        if (str_starts_with($digits, '994') && strlen($digits) === 12) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = substr($digits, 1);
        }

        return '+'.(strlen($digits) === 9 ? '994'.$digits : $digits);
    }
}
