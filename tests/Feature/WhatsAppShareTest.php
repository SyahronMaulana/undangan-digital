<?php

namespace Tests\Feature;

use App\Models\Wedding;
use App\Support\AdminWhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

/**
 * Berbagi undangan satu per satu (bukan broadcast massal) dan format tanggal
 * Indonesia + WIB pada pesan WhatsApp.
 */
class WhatsAppShareTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    public function test_halaman_ringkasan_menampilkan_ui_bagikan_satu_per_satu(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'published');
        // Tanggal tetap: Minggu, 15 November 2026.
        $wedding->events()->update(['event_date' => '2026-11-15', 'start_time' => '10:00', 'end_time' => null]);
        $wedding->refresh();

        $this->actingAs($user)
            ->get("/wedding/invitations/{$wedding->id}")
            ->assertOk()
            ->assertSee('Bagikan Undangan')
            ->assertSee('Nama penerima')
            ->assertSee('Link personal')
            ->assertSee('Kirim via WhatsApp')
            ->assertSee('Tamu Berikutnya')
            // Pesan dibuat di sisi klien: berbagi perangkat atau wa.me tanpa nomor tujuan.
            ->assertSee('weddingShare(', false)
            ->assertSee('navigator.share', false)
            ->assertSee('wa.me/?text=', false)
            ->assertSee('encodeURIComponent', false)
            // Tanggal Indonesia + WIB pada pesan siap kirim.
            ->assertSee('Minggu, 15 November 2026 pukul 10.00 WIB')
            // Link dasar tetap ditampilkan (juga untuk disalin).
            ->assertSee('/wedding/andi-siti');
    }

    public function test_halaman_draf_tidak_menampilkan_kontrol_bagikan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)
            ->get("/wedding/invitations/{$wedding->id}")
            ->assertOk()
            ->assertSee('Undangan masih draf')
            ->assertDontSee('Kirim via WhatsApp');
    }

    public function test_pesan_pengajuan_ke_admin_memakai_tanggal_indonesia_dan_wib(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 15, 10, 0, 0, config('app.timezone')));

        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $message = AdminWhatsApp::publicationMessage($wedding);

        $this->assertStringContainsString('Waktu pengajuan: Minggu, 15 November 2026 pukul 10.00 WIB', $message);

        Carbon::setTestNow();
    }
}
