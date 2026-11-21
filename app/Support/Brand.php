<?php

namespace App\Support;

/**
 * Identitas merek Markas Punokawan terpusat.
 *
 * Logo resmi dipakai sebagai identitas utama platform (bukan hanya modul
 * Wedding). Semua tampilan mengambil path aset dari config('brand.*')
 * sehingga cukup satu sumber aset logo untuk diperbarui.
 */
class Brand
{
    public static function name(): string
    {
        return (string) config('brand.name', 'Markas Punokawan');
    }

    /** URL aset logo utama (PNG transparan, proporsi asli dijaga). */
    public static function logo(): string
    {
        return asset((string) config('brand.logo', 'brand/markas-punokawan-logo.png'));
    }

    public static function logoAlt(): string
    {
        return (string) config('brand.logo_alt', self::name());
    }

    /** Rasio asli logo (lebar/tinggi) untuk menjaga proporsi tanpa distorsi. */
    public static function logoRatio(): float
    {
        $width = (float) config('brand.logo_width', 568);
        $height = (float) config('brand.logo_height', 221);

        return $height > 0 ? $width / $height : 1.0;
    }

    /** Favicon utama (ICO). */
    public static function favicon(): string
    {
        return asset((string) config('brand.favicon', 'favicon.ico'));
    }

    /** Favicon turunan (PNG) untuk apple-touch-icon dan browser modern. */
    public static function faviconPng(): string
    {
        return asset((string) config('brand.favicon_png', 'brand/favicon.png'));
    }
}
