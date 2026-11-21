<?php

namespace App\Support;

use App\Models\Wedding;
use App\Models\WeddingSetting;

/**
 * Nomor dan template pesan WhatsApp admin terpusat.
 *
 * Nomor diambil dari Pengaturan admin (wedding_settings) dengan fallback
 * konfigurasi platform (config/wedding.php → WEDDING_ADMIN_WHATSAPP),
 * sehingga tidak ada nomor yang ditulis ulang di Blade/Controller/JS.
 *
 * Template pesan pengajuan publikasi juga terpusat di sini agar halaman
 * pengajuan dan dashboard customer menghasilkan pesan yang identik.
 */
class AdminWhatsApp
{
    public const SETTING_KEY = 'admin_whatsapp';

    public static function number(): string
    {
        $raw = WeddingSetting::get(self::SETTING_KEY, config('wedding.admin_whatsapp'));

        $normalized = Phone::normalize(is_string($raw) ? $raw : null);

        return $normalized ? ltrim($normalized, '+') : '';
    }

    /**
     * Apakah nomor WhatsApp admin sudah dikonfigurasi (valid & tidak kosong).
     * Dipakai untuk menahan pembuatan tautan wa.me tanpa tujuan dan menampilkan
     * peringatan di dasbor Super Admin.
     */
    public static function isConfigured(): bool
    {
        return self::number() !== '';
    }

    /**
     * Tautan click-to-chat ke nomor admin. Mengembalikan null bila nomor belum
     * dikonfigurasi agar tidak ada tautan wa.me tanpa nomor tujuan.
     */
    public static function url(string $message): ?string
    {
        $number = self::number();

        if ($number === '') {
            return null;
        }

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    /**
     * Tampilan nomor untuk UI, mis. `+62 851-5526-9940`. Kosong bila belum diatur.
     */
    public static function display(): string
    {
        $number = self::number();

        if ($number === '') {
            return '';
        }

        $country = substr($number, 0, 2);
        $rest = substr($number, 2);

        if (strlen($rest) >= 8) {
            $rest = substr($rest, 0, 3).'-'.substr($rest, 3, 4).'-'.substr($rest, 7);
        }

        return '+'.$country.' '.$rest;
    }

    /**
     * Pesan otomatis ke admin saat customer mengajukan publikasi.
     *
     * Mengambil data aktual undangan: nama mempelai, template yang benar-benar
     * dipilih, konsep template, link preview internal (khusus Admin), slug, dan
     * status pengajuan. Bagian opsional yang kosong dihilangkan, bukan diisi
     * data karangan. Tidak pernah memuat password/token/API key.
     */
    public static function publicationMessage(Wedding $wedding): string
    {
        $wedding->loadMissing('template');
        $template = $wedding->template;

        $lines = [
            "Assalamu'alaikum Admin Markas Punokawan.",
            '',
            'Saya ingin mengajukan publikasi undangan pernikahan melalui Markas Punokawan.',
            '',
            'Berikut informasi pengajuan saya:',
            '',
            'Nama Mempelai:',
            self::coupleNames($wedding),
        ];

        $design = trim((string) ($template?->name ?? ''));
        if ($design !== '') {
            $lines[] = '';
            $lines[] = 'Desain Undangan:';
            $lines[] = $design;
        }

        $concept = self::templateConcept($template);
        if ($concept !== '') {
            $lines[] = '';
            $lines[] = 'Kategori Desain:';
            $lines[] = $concept;
        }

        // Preview internal khusus Admin (dilindungi autentikasi & otorisasi).
        // Slug dan nama desain tetap dicantumkan sebagai cadangan bila Admin
        // membuka WhatsApp dari perangkat yang belum login.
        $preview = self::adminPreviewUrl($wedding);
        if ($preview !== null) {
            $lines[] = '';
            $lines[] = 'Link Preview Undangan:';
            $lines[] = $preview;
        }

        $slug = trim((string) $wedding->slug);
        if ($slug !== '') {
            $lines[] = '';
            $lines[] = 'Slug Undangan:';
            $lines[] = $slug;
        }

        $lines[] = '';
        $lines[] = 'Status Pengajuan:';
        $lines[] = $wedding->submissionStatusLabel();

        // Waktu pengajuan memakai zona Asia/Jakarta (config app.timezone).
        $now = now();
        $lines[] = '';
        $lines[] = 'Waktu pengajuan: '.$now->translatedFormat('l, d F Y').' pukul '.$now->format('H.i').' WIB';

        $lines[] = '';
        $lines[] = 'Mohon informasi mengenai proses pembayaran dan tahapan selanjutnya agar undangan saya dapat segera diterbitkan.';
        $lines[] = '';
        $lines[] = 'Terima kasih.';

        return implode("\n", $lines);
    }

    /** URL WhatsApp pengajuan publikasi untuk satu undangan (dipakai tombol). */
    public static function publicationUrl(Wedding $wedding): ?string
    {
        return self::url(self::publicationMessage($wedding));
    }

    /** Pesan umum ke admin saat belum ada pengajuan tertentu. */
    public static function generalMessage(): string
    {
        return implode("\n", [
            "Assalamu'alaikum Admin Markas Punokawan.",
            '',
            'Saya ingin bertanya mengenai publikasi undangan pernikahan melalui Markas Punokawan.',
            '',
            'Terima kasih.',
        ]);
    }

    public static function generalUrl(): ?string
    {
        return self::url(self::generalMessage());
    }

    /** Pesan admin ke customer (tombol "WhatsApp Customer"). */
    public static function customerUrl(Wedding $wedding, string $message): string
    {
        $phone = Phone::normalize($wedding->user?->phone);
        $digits = $phone ? ltrim($phone, '+') : '';

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }

    /** Nama mempelai lengkap (fallback ke nama panggilan bila nama lengkap kosong). */
    private static function coupleNames(Wedding $wedding): string
    {
        $groom = trim((string) ($wedding->groom_name ?: $wedding->groom_nickname));
        $bride = trim((string) ($wedding->bride_name ?: $wedding->bride_nickname));

        if ($groom === '' && $bride === '') {
            return '(belum diisi)';
        }

        return ($groom !== '' ? $groom : '(belum diisi)').' & '.($bride !== '' ? $bride : '(belum diisi)');
    }

    /** Konsep/kategori desain dari metadata template (tagline + deskripsi). */
    private static function templateConcept(?object $template): string
    {
        if ($template === null) {
            return '';
        }

        $parts = [];
        foreach ([$template->tagline(), $template->description()] as $part) {
            $part = trim((string) $part);
            if ($part !== '' && ! in_array($part, $parts, true)) {
                $parts[] = $part;
            }
        }

        return implode(' — ', $parts);
    }

    /**
     * URL preview internal untuk Admin. Hanya dibuat bila route tersedia dan
     * undangan punya slug. Preview dilindungi middleware auth + role:super_admin.
     */
    private static function adminPreviewUrl(Wedding $wedding): ?string
    {
        if (trim((string) $wedding->slug) === '' || $wedding->getKey() === null) {
            return null;
        }

        try {
            return route('wedding.admin.publications.preview', $wedding);
        } catch (\Throwable) {
            return null;
        }
    }
}
