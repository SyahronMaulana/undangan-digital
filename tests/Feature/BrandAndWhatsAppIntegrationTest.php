<?php

namespace Tests\Feature;

use App\Models\Wedding;
use App\Support\AdminWhatsApp;
use App\Support\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

/**
 * Integrasi identitas Markas Punokawan (logo resmi) dan alur pengajuan
 * publikasi via WhatsApp admin:
 *  - Logo resmi dipakai di navbar, footer, dasbor, dan halaman autentikasi.
 *  - Nomor WhatsApp admin terpusat (bukan hardcoded di Blade/Controller).
 *  - Pesan pengajuan memuat data aktual dan tautan preview yang aman.
 *  - Membuka WhatsApp tidak mempublikasikan undangan dan tidak membuat
 *    pengajuan baru.
 */
class BrandAndWhatsAppIntegrationTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    // ---------------------------------------------------------------------
    // Aset logo
    // ---------------------------------------------------------------------

    public function test_aset_logo_dan_favicon_tersedia_dan_valid(): void
    {
        $logo = public_path('brand/markas-punokawan-logo.png');
        $faviconPng = public_path('brand/favicon.png');
        $faviconIco = public_path('favicon.ico');

        $this->assertFileExists($logo);
        $this->assertFileExists($faviconPng);
        $this->assertFileExists($faviconIco);
        $this->assertGreaterThan(0, filesize($logo));
        $this->assertGreaterThan(0, filesize($faviconPng));
        $this->assertGreaterThan(0, filesize($faviconIco));

        // Logo utama: PNG dengan kanal alpha (RGBA) dan proporsi asli 568x221.
        [$width, $height, $colorType] = $this->pngHeader($logo);
        $this->assertSame(568, $width);
        $this->assertSame(221, $height);
        $this->assertSame(6, $colorType, 'Logo utama harus PNG RGBA (transparan).');

        // Favicon turunan berbentuk persegi.
        [$fw, $fh] = $this->pngHeader($faviconPng);
        $this->assertSame($fw, $fh);
    }

    public function test_helper_brand_mengarah_ke_satu_aset_logo_utama(): void
    {
        $this->assertStringContainsString('brand/markas-punokawan-logo.png', Brand::logo());
        $this->assertStringContainsString('favicon.ico', Brand::favicon());
        $this->assertStringContainsString('brand/favicon.png', Brand::faviconPng());

        // Proporsi asli dijaga (lebar/tinggi) untuk mencegah distorsi.
        $this->assertEqualsWithDelta(568 / 221, Brand::logoRatio(), 0.001);
    }

    public function test_halaman_utama_platform_memakai_logo_dan_favicon(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('brand/markas-punokawan-logo.png', false)
            ->assertSee('favicon.ico', false)
            ->assertSee('brand/favicon.png', false)
            ->assertSee('Markas Punokawan', false)
            // Placeholder lama harus hilang.
            ->assertDontSee('class="mark"', false);
    }

    public function test_navbar_dan_footer_modul_wedding_memakai_logo(): void
    {
        $html = $this->get('/wedding')->assertOk()->getContent();

        // Navbar + footer memakai aset logo yang sama.
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'brand/markas-punokawan-logo.png'));
        $this->assertStringContainsString('brand/favicon.png', $html);
        $this->assertStringNotContainsString('brand-mark', $html);
    }

    public function test_halaman_login_memakai_logo(): void
    {
        $this->get('/wedding/login')
            ->assertOk()
            ->assertSee('brand/markas-punokawan-logo.png', false)
            ->assertSee('favicon.ico', false);
    }

    public function test_dasbor_customer_dan_admin_memakai_logo(): void
    {
        $user = $this->makeCustomer();
        $this->actingAs($user)
            ->get('/wedding/dashboard')
            ->assertOk()
            ->assertSee('brand/markas-punokawan-logo.png', false);

        $this->actingAs($this->makeAdmin())
            ->get('/wedding/admin')
            ->assertOk()
            ->assertSee('brand/markas-punokawan-logo.png', false);
    }

    // ---------------------------------------------------------------------
    // Pesan pengajuan WhatsApp
    // ---------------------------------------------------------------------

    public function test_pesan_pengajuan_memuat_data_aktual_undangan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $message = AdminWhatsApp::publicationMessage($wedding);

        $this->assertStringContainsString('Nama Mempelai:', $message);
        $this->assertStringContainsString('Andi & Siti', $message);
        $this->assertStringContainsString('Desain Undangan:', $message);
        $this->assertStringContainsString('Melati', $message); // nama template
        $this->assertStringContainsString('Link Preview Undangan:', $message);
        $this->assertStringContainsString('Slug Undangan:', $message);
        $this->assertStringContainsString('andi-siti', $message);
        $this->assertStringContainsString('Status Pengajuan:', $message);
        $this->assertStringContainsString($wedding->submissionStatusLabel(), $message);

        // Tautan preview menunjuk ke route internal yang dilindungi.
        $this->assertStringContainsString(
            route('wedding.admin.publications.preview', $wedding),
            $message
        );
    }

    public function test_pesan_pengajuan_tidak_memuat_rahasia(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $message = strtolower(AdminWhatsApp::publicationMessage($wedding));

        foreach (['password', 'token', 'api key', 'api_key', 'secret', 'bearer'] as $needle) {
            $this->assertStringNotContainsString($needle, $message);
        }
    }

    public function test_url_pengajuan_memakai_nomor_terpusat_dan_pesan_terenkode(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $url = AdminWhatsApp::publicationUrl($wedding);

        $this->assertStringStartsWith('https://wa.me/'.AdminWhatsApp::number().'?text=', $url);
        $this->assertSame('6285155269940', AdminWhatsApp::number());
        $this->assertStringNotContainsString('+', substr($url, 0, 30));
        $this->assertStringNotContainsString(' ', $url);

        // Pesan ter-encode utuh, termasuk baris baru.
        $this->assertStringContainsString(rawurlencode("Andi & Siti"), $url);
        $this->assertStringContainsString('%0A', $url); // newline
    }

    public function test_pesan_pengajuan_dihasilkan_identik_di_dasbor_dan_halaman_ringkasan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $expected = AdminWhatsApp::publicationUrl($wedding);

        // Halaman ringkasan dan dasbor memakai helper terpusat yang sama.
        $this->actingAs($user)
            ->get("/wedding/invitations/{$wedding->id}")
            ->assertOk()
            ->assertSee($expected, false);

        $this->actingAs($user)
            ->get('/wedding/dashboard')
            ->assertOk()
            ->assertSee($expected, false);
    }

    // ---------------------------------------------------------------------
    // Perilaku alur: tidak publikasi otomatis, tidak ada pengajuan ganda
    // ---------------------------------------------------------------------

    public function test_membuka_tautan_whatsapp_tidak_mempublikasikan_undangan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        // Tautan hanya berupa link keluar ke wa.me (bukan aksi server).
        $url = AdminWhatsApp::publicationUrl($wedding);
        $this->assertStringStartsWith('https://wa.me/', $url);

        // Mengunjungi halaman (yang memuat tombol) tidak mengubah status.
        $this->actingAs($user)->get("/wedding/invitations/{$wedding->id}")->assertOk();

        $wedding->refresh();
        $this->assertFalse($wedding->isPublished());
        $this->assertSame(Wedding::SUBMISSION_WAITING_CONFIRMATION, $wedding->submissionStatus());
    }

    public function test_pengajuan_ulang_tidak_membuat_undangan_baru(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $countBefore = Wedding::count();

        // Pengajuan kedua ditolak karena sudah ada pengajuan aktif.
        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/submit-publication", ['final_design' => '1'])
            ->assertSessionHasErrors('publish');

        $this->assertSame($countBefore, Wedding::count());
        $this->assertSame(Wedding::SUBMISSION_WAITING_CONFIRMATION, $wedding->fresh()->submissionStatus());
        $this->assertFalse($wedding->fresh()->isPublished());
    }

    public function test_dasbor_customer_menampilkan_status_dan_tombol_whatsapp_saat_komunikasi_dibutuhkan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $this->actingAs($user)
            ->get('/wedding/dashboard')
            ->assertOk()
            ->assertSee('Hubungi Admin via WhatsApp')
            ->assertSee('Silakan hubungi admin melalui WhatsApp untuk mendapatkan informasi pembayaran dan proses publikasi undangan Anda.')
            ->assertSee($wedding->submissionStatusLabel())
            ->assertSee($wedding->paymentStatusLabel());
    }

    public function test_dasbor_customer_tidak_memakai_nomor_whatsapp_hardcoded(): void
    {
        $user = $this->makeCustomer();

        $this->actingAs($user)
            ->get('/wedding/dashboard')
            ->assertOk()
            ->assertDontSee('6281234567890');
    }

    /**
     * Membaca IHDR PNG tanpa ekstensi GD.
     *
     * @return array{0:int,1:int,2:int} lebar, tinggi, color type
     */
    private function pngHeader(string $path): array
    {
        $bytes = file_get_contents($path, false, null, 0, 33);
        $this->assertNotFalse($bytes);
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($bytes, 0, 8), 'Berkas harus PNG valid.');

        $width = unpack('N', substr($bytes, 16, 4))[1];
        $height = unpack('N', substr($bytes, 20, 4))[1];
        $colorType = ord($bytes[25]);

        return [$width, $height, $colorType];
    }
}
