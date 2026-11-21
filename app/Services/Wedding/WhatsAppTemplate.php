<?php

namespace App\Services\Wedding;

use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Models\WeddingGuest;

/**
 * Penyusun pesan WhatsApp.
 *
 * Dua pemakaian berbeda:
 *  - Guest Management memakai render()/waUrl() untuk link personal per tamu
 *    (menyertakan nama tamu) — fitur ini tetap dipertahankan.
 *  - Broadcast WhatsApp memakai broadcastMessage()/shareUrl(): pesan yang SAMA
 *    untuk semua penerima, tanpa nomor tujuan. Daftar VARIABLES tidak memuat
 *    {{nama_tamu}} dan broadcastMessage() membuang sisa placeholder tersebut.
 */
class WhatsAppTemplate
{
    /**
     * Variabel broadcast: hanya informasi undangan yang identik untuk semua
     * penerima. TIDAK ada {{nama_tamu}} agar tidak ada personalisasi penerima.
     */
    public const VARIABLES = [
        '{{nama_pria}}',
        '{{nama_wanita}}',
        '{{tanggal_pernikahan}}',
        '{{lokasi_acara}}',
        '{{link_undangan}}',
    ];

    public const DEFAULT_MESSAGE = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nTanpa mengurangi rasa hormat, kami mengundang Anda untuk hadir di pernikahan {{nama_pria}} & {{nama_wanita}} pada {{tanggal_pernikahan}} di {{lokasi_acara}}.\n\nInformasi lengkap dan konfirmasi kehadiran:\n{{link_undangan}}\n\nTerima kasih dan kami tunggu kehadirannya.";

    /**
     * Peta nilai variabel broadcast untuk satu undangan. Dipakai baik untuk
     * render server maupun pratinjau langsung di sisi klien.
     *
     * @return array<string, string>
     */
    public function broadcastMap(Wedding $wedding): array
    {
        $event = $wedding->events->sortBy('sort_order')->first();
        $date = $event?->event_date ?? $wedding->event_date;
        $location = $event?->venue_name ?: ($event?->address ?: 'lokasi acara');

        // Tanggal Indonesia + jam WIB, mis. "Minggu, 15 November 2026 pukul 10.00 WIB".
        // Zona waktu aplikasi sudah Asia/Jakarta; tidak ada penambahan jam manual.
        $dateText = 'hari bahagia kami';
        if ($date) {
            $dateText = $date->translatedFormat('l, d F Y');
            if ($event?->start_time) {
                $dateText .= ' pukul '.WeddingEvent::clock($event->start_time).' WIB';
            }
        }

        return [
            '{{nama_pria}}' => (string) ($wedding->groom_nickname ?: $wedding->groom_name),
            '{{nama_wanita}}' => (string) ($wedding->bride_nickname ?: $wedding->bride_name),
            '{{tanggal_pernikahan}}' => $dateText,
            '{{lokasi_acara}}' => $location,
            '{{link_undangan}}' => $wedding->invitationUrl(),
        ];
    }

    /**
     * Ganti variabel undangan. Bila $guest null (mis. untuk broadcast), nama
     * tamu TIDAK diisi — bukan nama penerima dan bukan pula sapaan umum.
     */
    public function render(string $template, Wedding $wedding, ?WeddingGuest $guest = null): string
    {
        $replacements = $this->broadcastMap($wedding);
        $replacements['{{nama_tamu}}'] = $guest?->name ?: '';

        if ($guest?->token) {
            $replacements['{{link_undangan}}'] = $wedding->invitationUrl($guest);
        }

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /** URL Click to Chat ke nomor tertentu (dipakai Guest Management). */
    public function waUrl(?string $phone, string $message): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }

    /**
     * Pesan broadcast final: variabel undangan diisi, lalu sisa placeholder yang
     * tidak dikenal (termasuk {{nama_tamu}} dari template lama) dibuang agar
     * pesan tidak memuat nama tamu maupun variabel mentah. Pesan ini identik
     * untuk semua penerima.
     */
    public function broadcastMessage(Wedding $wedding, string $template): string
    {
        $message = $this->render($template, $wedding);

        return preg_replace('/\{\{[^{}]*\}\}/', '', $message) ?? $message;
    }

    /**
     * URL berbagi tanpa nomor tujuan: pengguna memilih kontak sendiri di
     * WhatsApp. Dipakai sebagai fallback bila Web Share API tidak tersedia.
     */
    public function shareUrl(string $message): string
    {
        return 'https://wa.me/?text='.rawurlencode($message);
    }

    /** Pratinjau pesan broadcast (tanpa data tamu). */
    public function preview(Wedding $wedding, string $template): array
    {
        $message = $this->broadcastMessage($wedding, $template);

        return [
            'message' => $message,
            'url' => $this->shareUrl($message),
        ];
    }

    public function variables(): array
    {
        return self::VARIABLES;
    }
}
