<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Deteksi format berkas musik dari isi (signature) dan MIME, bukan hanya dari
 * ekstensi nama berkas.
 *
 * Dipakai oleh aturan validasi App\Rules\AudioFile dan oleh penyimpanan berkas
 * di InvitationController agar berkas audio yang salah dideteksi MIME tetap
 * tersimpan dengan ekstensi yang benar (sehingga web server mengirim
 * Content-Type audio yang tepat dan berkas dapat diputar).
 */
class AudioFormat
{
    /** Ekstensi audio yang didukung. */
    public const EXTENSIONS = ['mp3', 'wav', 'm4a', 'ogg'];

    /** MIME non-`audio/*` yang tetap dikenali sebagai audio (Ogg). */
    private const AUDIO_MIMES = ['application/ogg'];

    /** Brand kotak `ftyp` MP4 yang menandakan kontainer audio (M4A/MP4 audio). */
    private const MP4_AUDIO_BRANDS = [
        'M4A ', 'M4B ', 'M4P ', 'F4A ', 'F4B ',
        'mp41', 'mp42', 'isom', 'iso2', 'iso5', 'iso6', 'dash',
    ];

    /** Jumlah byte awal yang dipindai untuk mencari frame audio MPEG. */
    private const MPEG_SCAN_BYTES = 262144;

    /** Apakah MIME (hasil deteksi server) menandakan audio. */
    public static function mimeLooksLikeAudio(?string $mime): bool
    {
        if (! is_string($mime) || $mime === '') {
            return false;
        }

        $mime = strtolower(trim($mime));

        return str_starts_with($mime, 'audio/') || in_array($mime, self::AUDIO_MIMES, true);
    }

    /**
     * Tentukan format audio (mp3/wav/m4a/ogg) dari isi berkas, atau null bila
     * bukan salah satu format yang didukung.
     */
    public static function signatureFormat(string $path): ?string
    {
        if ($path === '' || ! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return null;
        }

        try {
            $head = fread($handle, 12);
            if (! is_string($head) || strlen($head) < 12) {
                return null;
            }

            // WAV (RIFF) dan varian RF64.
            $container = substr($head, 0, 4);
            if (($container === 'RIFF' || $container === 'RF64') && substr($head, 8, 4) === 'WAVE') {
                return 'wav';
            }

            // OGG (Vorbis/Opus/FLAC dalam Ogg).
            if (substr($head, 0, 4) === 'OggS') {
                return 'ogg';
            }

            // M4A/MP4 audio: kotak `ftyp` + brand audio.
            if (substr($head, 4, 4) === 'ftyp' && in_array(substr($head, 8, 4), self::MP4_AUDIO_BRANDS, true)) {
                return 'm4a';
            }

            // MP3 dengan tag ID3v2.
            if (substr($head, 0, 3) === 'ID3') {
                return 'mp3';
            }

            // MP3 tanpa tag: cari frame MPEG yang berurutan secara valid.
            if (self::hasMpegAudioFrames($handle)) {
                return 'mp3';
            }

            return null;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Tentukan ekstensi format audio untuk berkas unggahan. Utamakan deteksi
     * isi; bila isi tidak dikenali, pakai MIME audio + ekstensi nama yang
     * didukung. Mengembalikan null bila berkas bukan audio yang didukung.
     */
    public static function detect(UploadedFile $file): ?string
    {
        $signature = self::signatureFormat($file->getRealPath());
        if ($signature !== null) {
            return $signature;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::EXTENSIONS, true) && self::mimeLooksLikeAudio($file->getMimeType())) {
            return $extension;
        }

        return null;
    }

    /**
     * Cari minimal dua frame MPEG yang berurutan dan konsisten pada awal berkas.
     * Verifikasi panjang frame mencegah data acak keliru dianggap MP3.
     *
     * @param  resource  $handle
     */
    private static function hasMpegAudioFrames($handle): bool
    {
        rewind($handle);
        $data = stream_get_contents($handle, self::MPEG_SCAN_BYTES);
        if (! is_string($data)) {
            return false;
        }

        $length = strlen($data);
        $last = $length - 4;

        for ($i = 0; $i <= $last; $i++) {
            if (ord($data[$i]) !== 0xFF) {
                continue;
            }

            $b1 = ord($data[$i + 1]);
            if (($b1 & 0xE0) !== 0xE0) {
                continue; // Sync 11 bit tidak cocok.
            }

            $version = ($b1 >> 3) & 0x03;
            $layer = ($b1 >> 1) & 0x03;
            if ($version === 0x01 || $layer === 0x00) {
                continue; // Versi reserved / layer reserved.
            }

            $b2 = ord($data[$i + 2]);
            $bitrateIndex = ($b2 >> 4) & 0x0F;
            $sampleRateIndex = ($b2 >> 2) & 0x03;
            if ($bitrateIndex === 0x00 || $bitrateIndex === 0x0F || $sampleRateIndex === 0x03) {
                continue; // Bitrate "free/bad" / sample rate reserved.
            }

            $frameLength = self::mpegFrameLength($version, $layer, $bitrateIndex, $sampleRateIndex, ord($data[$i + 3]));
            $next = $i + $frameLength;
            if ($frameLength < 4 || $next + 2 >= $length) {
                continue;
            }

            // Frame berikutnya harus frame MPEG yang konsisten (versi & layer sama).
            if (ord($data[$next]) !== 0xFF) {
                continue;
            }

            $n1 = ord($data[$next + 1]);
            if (($n1 & 0xE0) !== 0xE0 || (($n1 >> 3) & 0x03) !== $version || (($n1 >> 1) & 0x03) !== $layer) {
                continue;
            }

            $n2 = ord($data[$next + 2]);
            $nBitrate = ($n2 >> 4) & 0x0F;
            if ($nBitrate === 0x00 || $nBitrate === 0x0F || (($n2 >> 2) & 0x03) !== $sampleRateIndex) {
                continue;
            }

            return true;
        }

        return false;
    }

    /** Panjang satu frame MPEG (dalam byte) berdasarkan header-nya. */
    private static function mpegFrameLength(int $version, int $layer, int $bitrateIndex, int $sampleRateIndex, int $byte3): int
    {
        $padding = ($byte3 >> 1) & 0x01;

        // Layer: 3 = Layer I, 2 = Layer II, 1 = Layer III.
        $bitrateTable = match (true) {
            $version === 0x03 && $layer === 0x03 => [0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448],
            $version === 0x03 && $layer === 0x02 => [0, 32, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 384],
            $version === 0x03 && $layer === 0x01 => [0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320],
            $layer === 0x03 => [0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256],
            default => [0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160],
        };

        $bitrate = $bitrateTable[$bitrateIndex] ?? 0;
        if ($bitrate <= 0) {
            return 0;
        }

        $sampleRateTable = match ($version) {
            0x03 => [44100, 48000, 32000],
            0x02 => [22050, 24000, 16000],
            default => [11025, 12000, 8000],
        };

        $sampleRate = $sampleRateTable[$sampleRateIndex] ?? 0;
        if ($sampleRate <= 0) {
            return 0;
        }

        $samplesPerFrame = match (true) {
            $layer === 0x03 => 384,
            $layer === 0x02 => 1152,
            default => $version === 0x03 ? 1152 : 576,
        };

        return (int) floor(($samplesPerFrame / 8) * $bitrate * 1000 / $sampleRate) + $padding;
    }
}
