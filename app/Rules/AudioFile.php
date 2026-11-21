<?php

namespace App\Rules;

use App\Support\AudioFormat;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Validasi berkas musik (MP3/WAV/M4A/OGG) berbasis isi berkas.
 *
 * Aturan bawaan `mimes:mp3,wav,m4a,ogg` menebak ekstensi dari hasil deteksi
 * MIME di sisi server (finfo/libmagic). Sebagian berkas MP3 yang sah dideteksi
 * libmagic sebagai `application/octet-stream` (mis. tag ID3 dengan ukuran
 * synchsafe yang tidak lazim), sehingga berkas valid ikut ditolak. Aturan ini
 * memeriksa tanda tangan (signature) isi berkas lewat App\Support\AudioFormat
 * agar MP3/WAV/M4A/OGG yang sah tetap diterima, tanpa mempercayai ekstensi
 * nama berkas semata. Berkas non-audio yang hanya diganti ekstensi menjadi
 * .mp3 tetap ditolak.
 */
class AudioFile implements ValidationRule
{
    public function __construct(private readonly int $maxKilobytes)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Bukan berkas unggahan yang sah (mis. unggahan gagal / bukan berkas).
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail($this->formatMessage());

            return;
        }

        if ($value->getSize() > $this->maxKilobytes * 1024) {
            $fail($this->sizeMessage());

            return;
        }

        // Ekstensi nama berkas harus salah satu format yang didukung. Ini hanya
        // salah satu syarat; isi berkas tetap diverifikasi di bawah.
        $extension = strtolower($value->getClientOriginalExtension());
        if (! in_array($extension, AudioFormat::EXTENSIONS, true)) {
            $fail($this->formatMessage());

            return;
        }

        // Verifikasi isi/MIME berkas. AudioFormat::detect juga menangani berkas
        // audio valid yang salah dideteksi sebagai application/octet-stream.
        if (AudioFormat::detect($value) !== null) {
            return;
        }

        $fail($this->formatMessage());
    }

    private function formatMessage(): string
    {
        return 'Format musik tidak didukung. Silakan gunakan MP3, WAV, M4A, atau OGG.';
    }

    private function sizeMessage(): string
    {
        return 'Ukuran file musik terlalu besar. Maksimal ukuran file adalah '.$this->humanLimit().'.';
    }

    private function humanLimit(): string
    {
        return $this->maxKilobytes % 1024 === 0
            ? intdiv($this->maxKilobytes, 1024).' MB'
            : $this->maxKilobytes.' KB';
    }
}
