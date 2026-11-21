<?php

namespace Tests\Unit;

use App\Support\AudioFormat;
use PHPUnit\Framework\TestCase;

class AudioFormatTest extends TestCase
{
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $path) {
            @unlink($path);
        }
        $this->files = [];

        parent::tearDown();
    }

    private function write(string $name, string $bytes): string
    {
        $path = tempnam(sys_get_temp_dir(), 'audio_test_');
        file_put_contents($path, $bytes);
        $this->files[] = $path;

        return $path;
    }

    private function frames(int $count = 20): string
    {
        return str_repeat("\xFF\xFB\x90\x64".str_repeat("\x00", 413), $count);
    }

    public function test_mendeteksi_mp3_dengan_tag_id3_meski_mime_octet_stream(): void
    {
        // Tag ID3 dengan ukuran synchsafe ber-high-bit membuat libmagic
        // melaporkan application/octet-stream; signature tetap mengenali MP3.
        $bytes = "ID3\x03\x00\x00\x80\x00\x00\x00".str_repeat("\x00", 100).$this->frames();

        $this->assertSame('mp3', AudioFormat::signatureFormat($this->write('a.mp3', $bytes)));
    }

    public function test_mendeteksi_mp3_tanpa_tag(): void
    {
        $this->assertSame('mp3', AudioFormat::signatureFormat($this->write('b.mp3', $this->frames())));
    }

    public function test_mendeteksi_wav_m4a_dan_ogg(): void
    {
        $wav = 'RIFF'.pack('V', 36).'WAVEfmt '.str_repeat("\x00", 40);
        $m4a = pack('N', 24).'ftypM4A '.pack('N', 0).'M4A mp42isom'.str_repeat("\x00", 20);
        $ogg = "OggS\x00\x02".str_repeat("\x00", 40);

        $this->assertSame('wav', AudioFormat::signatureFormat($this->write('c.wav', $wav)));
        $this->assertSame('m4a', AudioFormat::signatureFormat($this->write('d.m4a', $m4a)));
        $this->assertSame('ogg', AudioFormat::signatureFormat($this->write('e.ogg', $ogg)));
    }

    public function test_menolak_data_bukan_audio(): void
    {
        $this->assertNull(AudioFormat::signatureFormat($this->write('f.mp3', 'Ini hanya teks biasa '.str_repeat('halo ', 200))));
        $this->assertNull(AudioFormat::signatureFormat($this->write('g.mp3', "\x89PNG\r\n\x1a\n".str_repeat("\x00", 2000))));
    }

    public function test_mengenali_mime_audio(): void
    {
        $this->assertTrue(AudioFormat::mimeLooksLikeAudio('audio/mpeg'));
        $this->assertTrue(AudioFormat::mimeLooksLikeAudio('audio/x-m4a'));
        $this->assertTrue(AudioFormat::mimeLooksLikeAudio('application/ogg'));
        $this->assertFalse(AudioFormat::mimeLooksLikeAudio('application/octet-stream'));
        $this->assertFalse(AudioFormat::mimeLooksLikeAudio('text/plain'));
    }
}
