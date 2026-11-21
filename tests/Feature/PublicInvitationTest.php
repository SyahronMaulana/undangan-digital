<?php

namespace Tests\Feature;

use App\Models\WeddingGuest;
use App\Models\WeddingRsvp;
use App\Models\WeddingSetting;
use App\Models\WeddingWish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

class PublicInvitationTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    public function test_link_umum_menampilkan_tamu_undangan(): void
    {
        $this->makeWedding($this->makeCustomer());
        $this->get('/wedding/andi-siti')
            ->assertOk()
            ->assertSee('Tamu Undangan')
            ->assertSee('Andi')
            ->assertSee('Siti');
    }

    public function test_draf_tidak_bisa_dilihat_publik_tetapi_bisa_dipratinjau_pemilik(): void
    {
        $owner = $this->makeCustomer();
        $wedding = $this->makeWedding($owner, 'andi-siti', 'draft');

        // Tamu anonim diarahkan ke login, bukan melihat konten pratinjau.
        $this->get("/wedding/invitations/{$wedding->id}/preview")->assertRedirect('/wedding/login');

        // Publik maupun pemilik TIDAK bisa membuka link publik undangan draft.
        $this->get('/wedding/andi-siti')->assertNotFound();
        $this->actingAs($owner)->get('/wedding/andi-siti')->assertNotFound();

        // Pratinjau internal (butuh login + otorisasi) tetap tersedia untuk pemilik.
        $this->actingAs($owner)->get("/wedding/invitations/{$wedding->id}/preview")->assertOk();

        // Customer lain tidak boleh mempratinjau undangan milik orang lain.
        $this->actingAs($this->makeCustomer())->get("/wedding/invitations/{$wedding->id}/preview")->assertNotFound();
    }

    public function test_undangan_draft_tidak_bisa_diberi_rsvp_atau_ucapan(): void
    {
        $this->makeWedding($this->makeCustomer(), 'andi-siti', 'draft');

        $this->post('/wedding/andi-siti/rsvp', [
            'name' => 'Tamu',
            'attendance' => WeddingRsvp::HADIR,
            'guests_count' => 1,
        ])->assertNotFound();

        $this->post('/wedding/andi-siti/ucapan', [
            'name' => 'Tamu',
            'message' => 'Semoga bahagia.',
        ])->assertNotFound();

        $this->assertSame(0, WeddingRsvp::count());
        $this->assertSame(0, WeddingWish::count());
    }

    public function test_pembayaran_dikonfirmasi_belum_tentu_dipublikasikan(): void
    {
        $owner = $this->makeCustomer();
        $admin = $this->makeAdmin();
        $wedding = $this->makeWedding($owner, 'andi-siti', 'draft');
        $wedding->markSubmitted();
        $wedding->markPaymentWaiting();
        $wedding->markPaymentVerifying();
        $wedding->confirmPayment($admin->id);

        // Pembayaran dikonfirmasi TIDAK otomatis membuka akses publik.
        $this->get('/wedding/andi-siti')->assertNotFound();

        // Hanya setelah Admin mempublikasikan barulah publik dapat mengakses.
        $wedding->refresh()->markPublished($admin->id);
        $this->get('/wedding/andi-siti')->assertOk();
    }

    public function test_admin_menarik_publikasi_langsung_menutup_akses_publik(): void
    {
        $owner = $this->makeCustomer();
        $admin = $this->makeAdmin();
        $wedding = $this->makeWedding($owner, 'andi-siti', 'draft');
        $wedding->markPublished($admin->id);

        $this->get('/wedding/andi-siti')->assertOk();

        $this->actingAs($admin)
            ->post("/wedding/admin/publications/{$wedding->id}/unpublish", ['unpublish_notes' => 'Data perlu revisi.'])
            ->assertRedirect();

        $wedding->refresh();
        $this->assertFalse($wedding->isPublished());
        $this->assertNull($wedding->published_at);
        $this->get('/wedding/andi-siti')->assertNotFound();
    }

    public function test_customer_tidak_bisa_mempublikasikan_sendiri(): void
    {
        $owner = $this->makeCustomer();
        $wedding = $this->makeWedding($owner, 'andi-siti', 'draft');
        $wedding->markSubmitted();

        // Route publikasi khusus admin: customer ditolak.
        $this->actingAs($owner)
            ->post("/wedding/admin/publications/{$wedding->id}/publish")
            ->assertForbidden();

        $this->assertFalse($wedding->fresh()->isPublished());
        $this->get('/wedding/andi-siti')->assertNotFound();
    }

    public function test_link_publik_undangan_berstatus_revisi_tetap_ditolak(): void
    {
        $owner = $this->makeCustomer();
        $wedding = $this->makeWedding($owner, 'andi-siti', 'draft');
        $wedding->markSubmitted();
        $wedding->markRevision('Mohon perbaiki tanggal acara.');

        $this->get('/wedding/andi-siti')->assertNotFound();
    }

    public function test_token_valid_menampilkan_nama_dari_database(): void
    {
        $wedding = $this->makeWedding($this->makeCustomer());
        $guest = $wedding->guests()->create(['name' => 'Syahron', 'phone' => '081234567890']);

        $this->get('/wedding/andi-siti?to=Palsu&token='.$guest->token)
            ->assertOk()
            ->assertSee('Syahron')
            ->assertDontSee('Palsu');
        $this->assertNotNull($guest->fresh()->opened_at);
    }

    public function test_nama_penerima_dari_parameter_to_ditampilkan(): void
    {
        $this->makeWedding($this->makeCustomer());

        $this->get('/wedding/andi-siti?to=Budi%20Santoso')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Tamu Undangan')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_token_tidak_valid_membuat_parameter_to_dipakai(): void
    {
        $this->makeWedding($this->makeCustomer());

        $this->get('/wedding/andi-siti?to=Budi&token=tokensalah')
            ->assertOk()
            ->assertSee('Budi');
    }

    public function test_parameter_to_kosong_atau_spasi_menampilkan_tamu_undangan(): void
    {
        $this->makeWedding($this->makeCustomer());

        $this->get('/wedding/andi-siti?to=')
            ->assertOk()
            ->assertSee('Tamu Undangan');

        $this->get('/wedding/andi-siti?to=%20%20%20')
            ->assertOk()
            ->assertSee('Tamu Undangan');
    }

    public function test_parameter_to_mendukung_multi_kata_dan_karakter_indonesia(): void
    {
        $this->makeWedding($this->makeCustomer());

        $this->get('/wedding/andi-siti?to=Ki%20Ageng%20S%C3%A9kar%20Wulandari')
            ->assertOk()
            ->assertSee('Ki Ageng Sékar Wulandari');
    }

    public function test_parameter_to_di_escape_untuk_mencegah_xss(): void
    {
        $this->makeWedding($this->makeCustomer());

        $this->get('/wedding/andi-siti?to='.rawurlencode('<script>alert(1)</script>'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_parameter_to_tidak_disimpan_ke_data_undangan(): void
    {
        $wedding = $this->makeWedding($this->makeCustomer());

        $this->get('/wedding/andi-siti?to=Budi%20Santoso')->assertOk();

        $this->assertSame(0, WeddingGuest::count());
        $this->assertNull($wedding->fresh()->cover_caption);
        $this->assertSame('Andi', $wedding->fresh()->groom_name);
    }

    public function test_nama_penerima_berlaku_di_semua_delapan_template(): void
    {
        $templates = ['melati', 'senja', 'lestari', 'maharaja', 'arum', 'puspita', 'asmara', 'nirmala'];

        foreach ($templates as $slug) {
            $wedding = $this->makeWedding($this->makeCustomer(), 'undangan-'.$slug);
            $wedding->template_id = $this->makeTemplate($slug)->id;
            $wedding->save();

            $this->get('/wedding/undangan-'.$slug.'?to=Budi%20Santoso')
                ->assertOk()
                ->assertSee('Budi Santoso');
        }
    }

    public function test_token_milik_undangan_lain_tidak_berlaku(): void
    {
        $this->makeWedding($this->makeCustomer(), 'andi-siti');
        $lain = $this->makeWedding($this->makeCustomer(), 'budi-ani');
        $tamuLain = $lain->guests()->create(['name' => 'Tamu Rahasia']);

        $this->get('/wedding/andi-siti?token='.$tamuLain->token)
            ->assertDontSee('Tamu Rahasia')
            ->assertSee('Tamu Undangan');
    }

    public function test_token_unik_dan_nomor_dinormalisasi(): void
    {
        $wedding = $this->makeWedding($this->makeCustomer());
        $a = $wedding->guests()->create(['name' => 'A', 'phone' => '0812-3456-7890']);
        $b = $wedding->guests()->create(['name' => 'B']);

        $this->assertNotSame($a->token, $b->token);
        $this->assertSame(40, strlen($a->token));
        $this->assertSame('+6281234567890', $a->phone);
    }

    public function test_rsvp_dengan_token_memakai_nama_dari_database(): void
    {
        $wedding = $this->makeWedding($this->makeCustomer());
        $guest = $wedding->guests()->create(['name' => 'Syahron', 'phone' => '081234567890']);

        $this->post("/wedding/andi-siti/rsvp?token={$guest->token}", [
            'name' => 'Nama Palsu',
            'attendance' => WeddingRsvp::HADIR,
            'guests_count' => 3,
            'message' => 'Insya Allah hadir.',
        ])->assertSessionHasNoErrors();

        $rsvp = $wedding->rsvps()->first();
        $this->assertSame('Syahron', $rsvp->name);
        $this->assertSame(WeddingRsvp::HADIR, $rsvp->attendance);
        $this->assertSame(3, $rsvp->guests_count);
    }

    public function test_rsvp_tanpa_token_wajib_mengisi_nama(): void
    {
        $this->makeWedding($this->makeCustomer());

        $this->post('/wedding/andi-siti/rsvp', [
            'attendance' => WeddingRsvp::HADIR,
            'guests_count' => 1,
        ])->assertSessionHasErrors('name');

        $this->assertSame(0, WeddingRsvp::count());
    }

    public function test_rsvp_tamu_tidak_hadir_selalu_satu_orang(): void
    {
        $wedding = $this->makeWedding($this->makeCustomer());
        $guest = $wedding->guests()->create(['name' => 'Syahron']);

        $this->post("/wedding/andi-siti/rsvp?token={$guest->token}", [
            'attendance' => WeddingRsvp::TIDAK_HADIR,
            'guests_count' => 5,
        ]);

        $this->assertSame(1, $wedding->rsvps()->first()->guests_count);
    }

    public function test_ucapan_tersimpan_dan_tampil_di_halaman_publik(): void
    {
        $this->makeWedding($this->makeCustomer());

        $this->post('/wedding/andi-siti/ucapan', [
            'name' => 'Bapak Syahron',
            'message' => 'Barakallahu lakuma wa baraka alaikuma.',
        ])->assertSessionHasNoErrors();

        $wish = WeddingWish::first();
        $this->assertSame('approved', $wish->status);
        $this->get('/wedding/andi-siti')->assertSee('Barakallahu lakuma');
    }

    public function test_ucapan_dari_token_memakai_nama_tamu(): void
    {
        $wedding = $this->makeWedding($this->makeCustomer());
        $guest = $wedding->guests()->create(['name' => 'Syahron']);

        $this->post("/wedding/andi-siti/ucapan?token={$guest->token}", [
            'name' => 'Nama Palsu',
            'message' => 'Selamat menempuh hidup baru.',
        ]);

        $wish = WeddingWish::first();
        $this->assertSame('Syahron', $wish->name);
        $this->assertSame($guest->id, $wish->wedding_guest_id);
    }

    public function test_ucapan_menunggu_moderasi_saat_pengaturan_pre(): void
    {
        WeddingSetting::put('wish_moderation', 'pre');
        $this->makeWedding($this->makeCustomer());

        $this->post('/wedding/andi-siti/ucapan', [
            'name' => 'Syahron',
            'message' => 'Semoga bahagia selalu.',
        ]);

        $wish = WeddingWish::first();
        $this->assertSame('hidden', $wish->status);
        $this->get('/wedding/andi-siti')->assertDontSee('Semoga bahagia selalu.');
    }

    public function test_ucapan_di_escape_untuk_mencegah_xss(): void
    {
        $this->makeWedding($this->makeCustomer());

        $this->post('/wedding/andi-siti/ucapan', [
            'name' => 'Tamu',
            'message' => '<script>alert(1)</script>',
        ]);

        $this->get('/wedding/andi-siti')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_rsvp_dibatasi_rate_limit(): void
    {
        $this->makeWedding($this->makeCustomer());

        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/wedding/andi-siti/rsvp', [
                'name' => 'Tamu '.$i,
                'attendance' => WeddingRsvp::HADIR,
                'guests_count' => 1,
            ]);
        }

        $this->postJson('/wedding/andi-siti/rsvp', [
            'name' => 'Tamu Terakhir',
            'attendance' => WeddingRsvp::HADIR,
            'guests_count' => 1,
        ])->assertStatus(429);
    }

    public function test_token_undangan_lain_tidak_bisa_rsvp_ke_undangan_tersebut(): void
    {
        $primary = $this->makeWedding($this->makeCustomer(), 'andi-siti');
        $other = $this->makeWedding($this->makeCustomer(), 'budi-ani');
        $guestOther = $other->guests()->create(['name' => 'Tamu Lain']);

        $this->post("/wedding/andi-siti/rsvp?token={$guestOther->token}", [
            'attendance' => WeddingRsvp::HADIR,
            'guests_count' => 2,
        ]);

        $this->assertSame(0, $primary->rsvps()->count());
    }

    public function test_inisial_mempelai_tampil_di_semua_delapan_template_tanpa_foto(): void
    {
        $classes = [
            'melati' => 'mel-initials',
            'senja' => 'sen-initials',
            'lestari' => 'les-initials',
            'maharaja' => 'mhr-initials',
            'arum' => 'arm-initials',
            'puspita' => 'pus-initials',
            'asmara' => 'asm-initials',
            'nirmala' => 'nir-initials',
        ];

        foreach ($classes as $slug => $class) {
            $wedding = $this->makeWedding($this->makeCustomer(), 'undangan-'.$slug);
            $wedding->template_id = $this->makeTemplate($slug)->id;
            $wedding->groom_name = 'Ahmad Fauzi';
            $wedding->bride_name = 'Siti Rahma';
            $wedding->groom_photo = null;
            $wedding->bride_photo = null;
            $wedding->save();

            $html = $this->get('/wedding/undangan-'.$slug)->assertOk()->getContent();

            $this->assertStringContainsString($class, $html, "Template {$slug} harus punya penanda inisial.");
            $this->assertStringContainsString('>AF<', $html, "Template {$slug} harus menampilkan inisial mempelai pria.");
            $this->assertStringContainsString('>SR<', $html, "Template {$slug} harus menampilkan inisial mempelai wanita.");
            $this->assertStringNotContainsString('demo/groom.jpg', $html, "Template {$slug} tidak boleh memakai foto demo.");
            $this->assertStringNotContainsString('demo/bride.jpg', $html, "Template {$slug} tidak boleh memakai foto demo.");
        }
    }

    public function test_inisial_mengikuti_perubahan_nama_mempelai(): void
    {
        $wedding = $this->makeWedding($this->makeCustomer(), 'andi-siti');
        $wedding->groom_photo = null;
        $wedding->bride_photo = null;
        $wedding->groom_name = 'Budi Santoso';
        $wedding->bride_name = 'Rina';
        $wedding->save();

        $this->get('/wedding/andi-siti')
            ->assertOk()
            ->assertSee('aria-hidden="true">BS</span>', false)
            ->assertSee('aria-hidden="true">RI</span>', false);
    }
}
