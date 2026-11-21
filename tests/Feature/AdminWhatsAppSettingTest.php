<?php

namespace Tests\Feature;

use App\Models\WeddingSetting;
use App\Support\AdminWhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

/**
 * Pengaturan nomor WhatsApp admin melalui Dashboard Super Admin.
 *
 *  - Menu "Pengaturan Platform" → bagian "Kontak WhatsApp Admin".
 *  - Nomor dinormalisasi ke format internasional tanpa +, spasi, tanda hubung.
 *  - Nomor tidak valid ditolak dan tidak disimpan.
 *  - Hanya Super Admin yang dapat melihat/mengubah; customer ditolak (403).
 *  - Nomor terpusat: seluruh tombol WhatsApp customer langsung memakai
 *    nomor terbaru (tanpa cache, tanpa restart).
 *  - Bila nomor belum diatur, tidak ada tautan wa.me tanpa tujuan.
 */
class AdminWhatsAppSettingTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    private const SUCCESS = 'Nomor WhatsApp admin berhasil diperbarui.';

    // ---------------------------------------------------------------------
    // 1. Tampilan Pengaturan Platform
    // ---------------------------------------------------------------------

    public function test_super_admin_membuka_pengaturan_platform_dan_melihat_nomor_saat_ini(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get('/wedding/admin/settings')
            ->assertOk()
            ->assertSee('Pengaturan Platform')
            ->assertSee('Kontak WhatsApp Admin')
            ->assertSee('Nomor WhatsApp admin saat ini')
            // Nomor aktif saat ini ditampilkan (dari env/config default).
            ->assertSee(AdminWhatsApp::display())
            ->assertSee('https://wa.me/'.AdminWhatsApp::number(), false);
    }

    public function test_menu_pengaturan_platform_ada_di_sidebar_admin(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get('/wedding/admin')
            ->assertOk()
            ->assertSee('Pengaturan Platform');
    }

    // ---------------------------------------------------------------------
    // 2. Normalisasi nomor (08, 62, +62)
    // ---------------------------------------------------------------------

    /**
     * @return array<string, array{0:string,1:string}>
     */
    public static function nomorValidProvider(): array
    {
        return [
            'awalan 08' => ['085155269940', '6285155269940'],
            'awalan 62' => ['6285155269940', '6285155269940'],
            'awalan +62' => ['+6285155269940', '6285155269940'],
            'dengan spasi/dash' => ['+62 851-5526-9940', '6285155269940'],
            '08 dengan dash' => ['0812-9999-8888', '6281299998888'],
            '08 dengan spasi' => ['0812 9999 8888', '6281299998888'],
            'spasi di ujung' => ['  085155269940  ', '6285155269940'],
        ];
    }

    #[DataProvider('nomorValidProvider')]
    public function test_nomor_dinormalisasi_ke_format_internasional(string $input, string $expected): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => $input])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('wedding.admin.settings.edit'))
            ->assertSessionHas('status', self::SUCCESS);

        // Tersimpan apa adanya dalam format internasional tanpa +/spasi/dash.
        $this->assertSame($expected, WeddingSetting::where('key', 'admin_whatsapp')->value('value'));
        $this->assertSame($expected, AdminWhatsApp::number());
    }

    // ---------------------------------------------------------------------
    // 3. Validasi nomor tidak valid
    // ---------------------------------------------------------------------

    /**
     * @return array<string, array{0:string}>
     */
    public static function nomorTidakValidProvider(): array
    {
        return [
            'kosong' => [''],
            'hanya huruf' => ['abcdefghij'],
            'huruf tercampur' => ['085155269940abc'],
            'terlalu pendek' => ['0812'],
            'bukan nomor Indonesia' => ['1234567890'],
            'kode negara salah' => ['0000000000'],
            'simbol aneh' => ['0812#9999'],
            'hanya tanda' => ['+ - ( )'],
            'terlalu panjang' => ['628515526994012345'],
        ];
    }

    #[DataProvider('nomorTidakValidProvider')]
    public function test_nomor_tidak_valid_ditolak_dan_tidak_disimpan(string $input): void
    {
        // Nomor valid yang sudah ada tidak boleh berubah karena input tidak valid.
        WeddingSetting::put('admin_whatsapp', '628111111111');

        $this->actingAs($this->makeAdmin())
            ->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => $input])
            ->assertSessionHasErrors('admin_whatsapp');

        $this->assertSame('628111111111', WeddingSetting::where('key', 'admin_whatsapp')->value('value'));
        $this->assertSame('628111111111', AdminWhatsApp::number());
    }

    public function test_notifikasi_sukses_memakai_teks_yang_diminta(): void
    {
        $this->actingAs($this->makeAdmin())
            ->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => '085155269940'])
            ->assertSessionHas('status', 'Nomor WhatsApp admin berhasil diperbarui.');
    }

    // ---------------------------------------------------------------------
    // 4. Hak akses
    // ---------------------------------------------------------------------

    public function test_customer_tidak_bisa_melihat_atau_mengubah_pengaturan(): void
    {
        $customer = $this->makeCustomer();

        $this->actingAs($customer)->get('/wedding/admin/settings')->assertForbidden();
        $this->actingAs($customer)->get('/wedding/admin')->assertForbidden();

        WeddingSetting::put('admin_whatsapp', '628111111111');

        // Request langsung ke endpoint update juga ditolak (bukan hanya menu disembunyikan).
        $this->actingAs($customer)
            ->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => '628222222222'])
            ->assertForbidden();

        $this->assertSame('628111111111', WeddingSetting::where('key', 'admin_whatsapp')->value('value'));
    }

    public function test_tamu_belum_masuk_diarahkan_ke_login(): void
    {
        $this->get('/wedding/admin/settings')->assertRedirect(route('wedding.login'));
        $this->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => '085155269940'])
            ->assertRedirect(route('wedding.login'));
    }

    // ---------------------------------------------------------------------
    // 5. Integrasi: seluruh tombol WhatsApp memakai nomor terbaru
    // ---------------------------------------------------------------------

    public function test_perubahan_nomor_langsung_dipakai_tombol_customer(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $this->actingAs($this->makeAdmin())
            ->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => '0812-9999-8888'])
            ->assertSessionHasNoErrors();

        // Tanpa cache/restart: halaman customer langsung memakai nomor baru.
        $this->actingAs($user)
            ->get("/wedding/invitations/{$wedding->id}")
            ->assertOk()
            ->assertSee('https://wa.me/6281299998888', false)
            ->assertDontSee('https://wa.me/6285155269940', false);

        $this->actingAs($user)
            ->get('/wedding/dashboard')
            ->assertOk()
            ->assertSee('https://wa.me/6281299998888', false);
    }

    public function test_template_pesan_tidak_berubah_saat_nomor_diperbarui(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $before = AdminWhatsApp::publicationMessage($wedding);

        $this->actingAs($this->makeAdmin())
            ->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => '0812-9999-8888']);

        $after = AdminWhatsApp::publicationMessage($wedding);

        // Isi pesan identik (nomor hanya ada di URL, bukan di badan pesan).
        $this->assertSame($before, $after);
        $this->assertStringContainsString('Andi & Siti', $after);
        $this->assertStringContainsString('Desain Undangan:', $after);
        $this->assertStringContainsString('Melati', $after);
        $this->assertStringContainsString('Slug Undangan:', $after);
        $this->assertStringContainsString('andi-siti', $after);
    }

    // ---------------------------------------------------------------------
    // 6. Penyimpanan database: satu nilai aktif, tidak menghapus yang lain
    // ---------------------------------------------------------------------

    public function test_hanya_ada_satu_baris_nomor_whatsapp_admin(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => '081111111111']);
        $this->actingAs($admin)->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => '082222222222']);
        $this->actingAs($admin)->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => '083333333333']);

        $this->assertSame(1, WeddingSetting::where('key', 'admin_whatsapp')->count());
        $this->assertSame('6283333333333', AdminWhatsApp::number());
    }

    public function test_pengaturan_lain_tidak_terhapus_saat_nomor_diubah(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->put('/wedding/admin/settings', [
            'support_whatsapp' => '0812-7777-6666',
            'support_email' => 'halo@example.test',
            'manual_payment_note' => 'Transfer manual ke rekening Markas Punokawan.',
            'wish_moderation' => 'pre',
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->put('/wedding/admin/settings/whatsapp', ['admin_whatsapp' => '085155269940'])
            ->assertSessionHasNoErrors();

        $this->assertSame('halo@example.test', WeddingSetting::get('support_email'));
        $this->assertSame('pre', WeddingSetting::get('wish_moderation'));
        $this->assertSame('6285155269940', AdminWhatsApp::number());
    }

    // ---------------------------------------------------------------------
    // 7. Nomor belum diatur: tidak ada tautan kosong
    // ---------------------------------------------------------------------

    public function test_nomor_belum_diatur_tidak_membuat_tautan_wa_me_kosong(): void
    {
        config(['wedding.admin_whatsapp' => null]);
        $this->assertFalse(AdminWhatsApp::isConfigured());
        $this->assertNull(AdminWhatsApp::url('Halo'));
        $this->assertSame('', AdminWhatsApp::display());

        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        // Halaman customer menampilkan pemberitahuan, bukan tombol tanpa tujuan.
        $this->actingAs($user)
            ->get("/wedding/invitations/{$wedding->id}")
            ->assertOk()
            ->assertSee('Kontak admin belum dikonfigurasi.')
            ->assertDontSee('Hubungi Admin via WhatsApp');

        $this->actingAs($user)
            ->get('/wedding/dashboard')
            ->assertOk()
            ->assertSee('Kontak admin belum dikonfigurasi.')
            ->assertDontSee('Hubungi Admin via WhatsApp');
    }

    public function test_dasbor_admin_memberi_peringatan_bila_nomor_belum_diatur(): void
    {
        config(['wedding.admin_whatsapp' => null]);

        $this->actingAs($this->makeAdmin())
            ->get('/wedding/admin')
            ->assertOk()
            ->assertSee('Nomor WhatsApp admin belum dikonfigurasi.')
            ->assertSee('Atur Nomor');
    }

    public function test_dasbor_admin_tidak_memberi_peringatan_bila_nomor_sudah_diatur(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get('/wedding/admin')
            ->assertOk()
            ->assertDontSee('Nomor WhatsApp admin belum dikonfigurasi.');
    }
}
