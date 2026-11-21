<?php

namespace App\Support;

class Phone
{
    /** Normalisasi nomor telepon Indonesia ke format +62xxxxxxxx. */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '' || $digits === null) {
            return null;
        }

        if (str_starts_with($digits, '620')) {
            return '+62'.substr($digits, 3);
        }
        if (str_starts_with($digits, '62')) {
            return '+'.$digits;
        }
        if (str_starts_with($digits, '0')) {
            return '+62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '8')) {
            return '+62'.$digits;
        }

        return '+'.$digits;
    }
}
