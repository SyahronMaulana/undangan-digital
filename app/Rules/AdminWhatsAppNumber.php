<?php

namespace App\Rules;

use App\Support\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validasi nomor WhatsApp admin.
 *
 * Menerima format Indonesia yang umum: `08xxxxxxxxxx`, `628xxxxxxxxxx`, atau
 * `+628xxxxxxxxxx` (spasi, tanda hubung, titik, dan tanda kurung diizinkan
 * sebagai pemisah). Huruf dan karakter lain ditolak. Nomor harus berupa nomor
 * seluler Indonesia yang sah: kode negara 62, diawali 8, dengan panjang wajar.
 */
class AdminWhatsAppNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $raw = is_string($value) ? trim($value) : '';

        if ($raw === '') {
            $fail('Nomor WhatsApp admin wajib diisi.');

            return;
        }

        // Hanya angka dan pemisah umum yang diizinkan (tanpa huruf/simbol lain).
        if (preg_match('/^[0-9+().\-\s]+$/', $raw) !== 1) {
            $fail('Nomor WhatsApp hanya boleh berisi angka, spasi, tanda hubung, tanda kurung, dan awalan +.');

            return;
        }

        $normalized = Phone::normalize($raw);

        // Nomor seluler Indonesia: 62 + 8 + 7..11 digit.
        if ($normalized === null || preg_match('/^\+628[0-9]{7,11}$/', $normalized) !== 1) {
            $fail('Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx, 628xxxxxxxxxx, atau +628xxxxxxxxxx.');
        }
    }
}
