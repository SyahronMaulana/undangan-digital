<?php

namespace Tests\Feature;

use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

/**
 * Regresi untuk penyimpanan data editor undangan: foto, musik, rekening, dan
 * kisah harus tersimpan pada Simpan maupun Ajukan Publikasi, tidak terhapus
 * saat menyimpan bagian lain, dan berkas lama tidak hilang saat penyimpanan gagal.
 */
class InvitationEditorPersistenceTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    private function png(): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent('foto.png', $bytes);
    }

    /** Payload meniru form editor: selalu mengirim penanda sync_* dan minimal satu acara. */
    private function basePayload(array $over = []): array
    {
        return array_merge([
            'groom_name' => 'Andi',
            'bride_name' => 'Siti',
            'language_style' => 'islami',
            'sync_events' => '1',
            'sync_stories' => '1',
            'sync_gifts' => '1',
            'events' => [[
                'name' => 'Akad Nikah',
                'event_date' => now()->addMonth()->toDateString(),
                'start_time' => '08:00',
                'end_time' => '10:00',
                'venue_name' => 'Pendopo Punokawan',
            ]],
        ], $over);
    }

    /**
     * Payload untuk memperbarui undangan lewat form multipart. Form editor
     * mengirim POST dengan _method=PUT agar berkas tetap terkirim.
     */
    private function updatePayload(array $over = []): array
    {
        return $this->basePayload(array_merge(['_method' => 'PUT'], $over));
    }

    public function test_simpan_lengkap_menyimpan_berkas_rekening_dan_kisah(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'groom_photo' => $this->png(),
            'bride_photo' => $this->png(),
            'cover_photo' => $this->png(),
            'music' => UploadedFile::fake()->create('song.mp3', 50, 'audio/mpeg'),
            'photos' => [$this->png(), $this->png()],
            'stories' => [
                ['title' => 'Pertemuan', 'story_date' => '2020', 'body' => 'Kami bertemu di kampus.'],
            ],
            'gifts' => [
                ['bank_name' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'Andi Wijaya'],
            ],
        ]), ['Accept' => 'application/json'])->assertOk()->assertJson(['ok' => true]);

        $wedding->refresh();
        $this->assertNotNull($wedding->groom_photo);
        $this->assertNotNull($wedding->bride_photo);
        $this->assertNotNull($wedding->cover_photo);
        $this->assertNotNull($wedding->music_path);
        $this->assertSame(2, $wedding->photos()->count());
        $this->assertSame(1, $wedding->stories()->count());
        $this->assertSame(1, $wedding->gifts()->count());
        $this->assertSame('BCA', $wedding->gifts()->first()->bank_name);
        $this->assertSame('Pertemuan', $wedding->stories()->first()->title);
        Storage::disk('public')->assertExists($wedding->groom_photo);
        Storage::disk('public')->assertExists($wedding->music_path);
    }

    public function test_autosave_sebagian_tidak_menghapus_relasi_lain(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');
        $wedding->stories()->create(['title' => 'Kisah', 'body' => 'Isi']);
        $wedding->gifts()->create(['bank_name' => 'Mandiri', 'account_number' => '999', 'account_name' => 'Andi']);

        $this->actingAs($user)->putJson("/wedding/invitations/{$wedding->id}", [
            'groom_name' => 'Andi Wijaya',
            'bride_name' => 'Siti Aminah',
            'language_style' => 'umum',
        ])->assertOk()->assertJson(['ok' => true]);

        $wedding->refresh();
        $this->assertSame('Andi Wijaya', $wedding->groom_name);
        $this->assertSame(1, $wedding->events()->count());
        $this->assertSame(1, $wedding->stories()->count());
        $this->assertSame(1, $wedding->gifts()->count());
    }

    public function test_mengosongkan_daftar_dengan_penanda_sync_tetap_tersimpan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');
        $wedding->stories()->create(['title' => 'Kisah', 'body' => 'Isi']);

        $this->actingAs($user)->putJson("/wedding/invitations/{$wedding->id}", [
            'groom_name' => 'Andi',
            'bride_name' => 'Siti',
            'language_style' => 'islami',
            'sync_stories' => '1',
        ])->assertOk();

        $this->assertSame(0, $wedding->stories()->count());
        // Acara tetap ada karena bagian events tidak dikirim.
        $this->assertSame(1, $wedding->events()->count());
    }

    public function test_editor_menampilkan_kembali_data_yang_tersimpan(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'cover_caption' => 'Selamanya',
            'music_title' => 'Lagu Kita',
            'stories' => [['title' => 'Momen Spesial', 'story_date' => '2021', 'body' => 'Kami bertunangan.']],
            'gifts' => [['bank_name' => 'BNI', 'account_number' => '555', 'account_name' => 'Siti']],
        ]), ['Accept' => 'application/json'])->assertOk();

        $this->actingAs($user)->get("/wedding/invitations/{$wedding->id}/edit")
            ->assertOk()
            ->assertSee('Selamanya')
            ->assertSee('Lagu Kita')
            ->assertSee('Momen Spesial')
            ->assertSee('BNI');
    }

    public function test_ajukan_publikasi_menyimpan_data_editor_terlebih_dahulu(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}/submit-publication", $this->basePayload([
            'final_design' => '1',
            'groom_name' => 'Andi Wijaya',
            'gifts' => [['bank_name' => 'BCA', 'account_number' => '321', 'account_name' => 'Andi']],
        ]))->assertSessionHasNoErrors();

        $wedding->refresh();
        $this->assertSame(Wedding::SUBMISSION_WAITING_CONFIRMATION, $wedding->submissionStatus());
        $this->assertSame('Andi Wijaya', $wedding->groom_name);
        $this->assertSame(1, $wedding->gifts()->count());
    }

    public function test_ajukan_publikasi_gagal_validasi_tidak_menandai_terkirim(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}/submit-publication", $this->basePayload([
            'final_design' => '1',
            'groom_name' => '',
        ]))->assertSessionHasErrors('groom_name');

        $wedding->refresh();
        $this->assertSame(Wedding::SUBMISSION_DRAFT, $wedding->submissionStatus());
        $this->assertNull($wedding->submitted_at);
    }

    public function test_berkas_lama_tidak_hilang_saat_penyimpanan_gagal(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'groom_photo' => $this->png(),
        ]), ['Accept' => 'application/json'])->assertOk();
        $old = $wedding->fresh()->groom_photo;
        $this->assertNotNull($old);

        // Kirim berkas baru bersama data tidak valid: validasi gagal, berkas lama tetap ada.
        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'groom_name' => '',
            'groom_photo' => $this->png(),
        ]), ['Accept' => 'application/json'])->assertStatus(422);

        $this->assertSame($old, $wedding->fresh()->groom_photo);
        Storage::disk('public')->assertExists($old);
    }

    public function test_mengganti_foto_menghapus_berkas_lama(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'groom_photo' => $this->png(),
        ]), ['Accept' => 'application/json'])->assertOk();
        $old = $wedding->fresh()->groom_photo;

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'groom_photo' => $this->png(),
        ]), ['Accept' => 'application/json'])->assertOk();
        $new = $wedding->fresh()->groom_photo;

        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);
    }

    public function test_menghapus_satu_foto_mempelai_menampilkan_inisial_tanpa_mengganggu_foto_lain(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'groom_photo' => $this->png(),
            'bride_photo' => $this->png(),
        ]), ['Accept' => 'application/json'])->assertOk();

        $wedding->refresh();
        $groomOld = $wedding->groom_photo;
        $brideOld = $wedding->bride_photo;
        $this->assertNotNull($groomOld);
        $this->assertNotNull($brideOld);

        // Hapus hanya foto mempelai pria.
        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'remove_groom_photo' => '1',
        ]), ['Accept' => 'application/json'])->assertOk();

        $wedding->refresh();
        $this->assertNull($wedding->groom_photo);
        $this->assertSame($brideOld, $wedding->bride_photo);
        Storage::disk('public')->assertMissing($groomOld);
        Storage::disk('public')->assertExists($brideOld);

        // Terbitkan lalu buka halaman publik: mempelai pria tampil sebagai
        // inisial, mempelai wanita tetap memakai foto.
        $wedding->markPublished($this->makeAdmin()->id);
        $this->get('/wedding/andi-siti')
            ->assertOk()
            ->assertSee('mel-initials', false)
            ->assertSee('aria-hidden="true">AN</span>', false)
            ->assertSee('alt="Foto Siti"', false);
    }

    public function test_mengganti_dan_menghapus_musik(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        // Unggah musik pertama.
        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'music' => UploadedFile::fake()->create('lagu-satu.mp3', 40, 'audio/mpeg'),
            'music_title' => 'Lagu Satu',
        ]), ['Accept' => 'application/json'])->assertOk();

        $wedding->refresh();
        $first = $wedding->music_path;
        $this->assertNotNull($first);
        $this->assertSame('Lagu Satu', $wedding->music_title);
        Storage::disk('public')->assertExists($first);

        // Ganti musik: berkas lama dihapus, referensi diperbarui.
        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'music' => UploadedFile::fake()->create('lagu-dua.mp3', 42, 'audio/mpeg'),
            'music_title' => 'Lagu Dua',
        ]), ['Accept' => 'application/json'])->assertOk();

        $wedding->refresh();
        $second = $wedding->music_path;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
        $this->assertSame('Lagu Dua', $wedding->music_title);

        // Hapus musik: referensi dibersihkan dan berkas fisik dihapus.
        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'remove_music' => '1',
        ]), ['Accept' => 'application/json'])->assertOk();

        $wedding->refresh();
        $this->assertNull($wedding->music_path);
        $this->assertNull($wedding->music_title);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_musik_lama_tetap_tersimpan_saat_menyimpan_bagian_lain(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'music' => UploadedFile::fake()->create('lagu.mp3', 40, 'audio/mpeg'),
        ]), ['Accept' => 'application/json'])->assertOk();
        $path = $wedding->fresh()->music_path;
        $this->assertNotNull($path);

        // Simpan bagian lain tanpa menyentuh musik.
        $this->actingAs($user)->putJson("/wedding/invitations/{$wedding->id}", [
            'groom_name' => 'Andi Wijaya',
            'bride_name' => 'Siti Aminah',
            'language_style' => 'umum',
        ])->assertOk();

        $this->assertSame($path, $wedding->fresh()->music_path);
        Storage::disk('public')->assertExists($path);
    }

    /**
     * Buat berkas unggahan nyata dari byte sehingga deteksi MIME memakai isi
     * berkas asli (bukan tipe yang dipalsukan seperti pada fake()->create).
     */
    private function audioFile(string $name, string $bytes): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'wed_audio_');
        file_put_contents($path, $bytes);

        return new UploadedFile($path, $name, null, null, true);
    }

    /** Rangkaian frame MPEG-1 Layer III valid (masing-masing 417 byte). */
    private function mpegFrames(int $count = 40): string
    {
        return str_repeat("\xFF\xFB\x90\x64".str_repeat("\x00", 413), $count);
    }

    public function test_mp3_yang_salah_dideteksi_mime_tetap_dapat_diunggah(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        // Tag ID3 dengan ukuran synchsafe yang tidak lazim membuat libmagic
        // melaporkan `application/octet-stream` walaupun isinya MP3 yang sah.
        // Aturan lama `mimes:mp3,wav,m4a,ogg` menolak berkas seperti ini.
        $bytes = "ID3\x03\x00\x00\x80\x00\x00\x00".str_repeat("\x00", 100).$this->mpegFrames();

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'music' => $this->audioFile('Westlife - Beautiful in white (Lyrics).mp3', $bytes),
            'music_title' => 'Beautiful in White',
        ]), ['Accept' => 'application/json'])->assertOk()->assertJson(['ok' => true]);

        $wedding->refresh();
        $this->assertNotNull($wedding->music_path);
        $this->assertSame('Beautiful in White', $wedding->music_title);
        // Tersimpan dengan ekstensi audio yang benar, bukan `.bin`, agar dapat
        // dilayani sebagai audio dan diputar pada pratinjau/undangan publik.
        $this->assertStringEndsWith('.mp3', $wedding->music_path);
        Storage::disk('public')->assertExists($wedding->music_path);
        // Berkas tersimpan utuh sehingga dapat diputar pada pratinjau/publik.
        $this->assertSame($bytes, Storage::disk('public')->get($wedding->music_path));
    }

    public function test_semua_format_audio_yang_didukung_dapat_diunggah(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $formats = [
            'lagu.mp3' => $this->mpegFrames(),
            'lagu.wav' => 'RIFF'.pack('V', 36 + 4000).'WAVEfmt '.pack('V', 16).pack('v', 1).pack('v', 1)
                .pack('V', 8000).pack('V', 8000).pack('v', 1).pack('v', 8).'data'.pack('V', 4000).str_repeat("\x80", 4000),
            'lagu.m4a' => pack('N', 24).'ftypM4A '.pack('N', 0).'M4A mp42isom'.pack('N', 8 + 4000).'mdat'.str_repeat("\x00", 4000),
            'lagu.ogg' => "OggS\x00\x02".str_repeat("\x00", 22)."\x01\x1e\x01vorbis".str_repeat("\x00", 4000),
        ];

        foreach ($formats as $name => $bytes) {
            $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
                'music' => $this->audioFile($name, $bytes),
            ]), ['Accept' => 'application/json'])->assertOk();

            $wedding->refresh();
            $this->assertNotNull($wedding->music_path, "Format {$name} seharusnya diterima.");
            $this->assertStringEndsWith('.'.pathinfo($name, PATHINFO_EXTENSION), $wedding->music_path);
            Storage::disk('public')->assertExists($wedding->music_path);
        }
    }

    public function test_musik_palsu_dan_melebihi_batas_ditolak_dengan_pesan_indonesia(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        // Simpan musik valid terlebih dahulu.
        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'music' => $this->audioFile('lagu.mp3', $this->mpegFrames()),
        ]), ['Accept' => 'application/json'])->assertOk();
        $existing = $wedding->fresh()->music_path;
        $this->assertNotNull($existing);

        // Berkas non-audio yang hanya diganti ekstensi menjadi .mp3.
        $response = $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'music' => $this->audioFile('lagu-palsu.mp3', 'Ini jelas bukan audio. '.str_repeat('halo dunia ', 500)),
        ]), ['Accept' => 'application/json']);
        $response->assertStatus(422)->assertJsonValidationErrors(['music']);
        $this->assertSame(
            'Format musik tidak didukung. Silakan gunakan MP3, WAV, M4A, atau OGG.',
            $response->json('errors.music.0')
        );
        // Musik lama tidak hilang saat unggahan gagal.
        $this->assertSame($existing, $wedding->fresh()->music_path);
        Storage::disk('public')->assertExists($existing);

        // Ukuran melebihi batas (batas sistem 10 MB).
        $response = $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'music' => UploadedFile::fake()->create('lagu-besar.mp3', 11000, 'audio/mpeg'),
        ]), ['Accept' => 'application/json']);
        $response->assertStatus(422)->assertJsonValidationErrors(['music']);
        $this->assertSame(
            'Ukuran file musik terlalu besar. Maksimal ukuran file adalah 10 MB.',
            $response->json('errors.music.0')
        );
        $this->assertSame($existing, $wedding->fresh()->music_path);
        Storage::disk('public')->assertExists($existing);
    }

    public function test_undangan_publik_memutar_musik_yang_diunggah(): void
    {
        Storage::fake('public');
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)->post("/wedding/invitations/{$wedding->id}", $this->updatePayload([
            'music' => $this->audioFile('lagu.mp3', $this->mpegFrames()),
        ]), ['Accept' => 'application/json'])->assertOk();

        $path = $wedding->fresh()->music_path;
        $this->assertNotNull($path);

        $wedding->markPublished($this->makeAdmin()->id);

        $this->get('/wedding/andi-siti')
            ->assertOk()
            ->assertSee('id="tw-music"', false)
            ->assertSee($path, false);
    }
}
