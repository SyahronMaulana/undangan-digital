<?php

namespace Tests\Feature;

use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

class PublicationWorkflowTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    public function test_customer_mengajukan_publikasi_dan_masuk_daftar_admin(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/submit-publication", ['final_design' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(Wedding::SUBMISSION_WAITING_CONFIRMATION, $wedding->fresh()->submissionStatus());

        $this->actingAs($this->makeAdmin())
            ->get('/wedding/admin/publications')
            ->assertOk()
            ->assertSee('Andi & Siti');
    }

    public function test_admin_tidak_bisa_publikasi_sebelum_pembayaran_dikonfirmasi(): void
    {
        $wedding = $this->makeSubmittedWedding($this->makeCustomer());

        $this->actingAs($this->makeAdmin())
            ->post("/wedding/admin/publications/{$wedding->id}/publish")
            ->assertSessionHasErrors('publish');

        $this->assertFalse($wedding->fresh()->isPublished());
        $this->assertSame(Wedding::SUBMISSION_WAITING_CONFIRMATION, $wedding->fresh()->submissionStatus());
    }

    public function test_alur_instruksi_pembayaran_dan_klaim_customer(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post("/wedding/admin/publications/{$wedding->id}/payment-instruction")
            ->assertSessionHasNoErrors();
        $this->assertSame(Wedding::SUBMISSION_WAITING_PAYMENT, $wedding->fresh()->submissionStatus());
        $this->assertSame(Wedding::PAYMENT_UNPAID, $wedding->fresh()->paymentStatus());

        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/claim-payment")
            ->assertSessionHasNoErrors();
        $this->assertSame(Wedding::SUBMISSION_WAITING_VERIFICATION, $wedding->fresh()->submissionStatus());
        $this->assertSame(Wedding::PAYMENT_WAITING_VERIFICATION, $wedding->fresh()->paymentStatus());
    }

    public function test_customer_tidak_bisa_menandai_pembayaran_sebelum_instruksi(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/claim-payment")
            ->assertSessionHasErrors('payment');

        $this->assertSame(Wedding::PAYMENT_UNPAID, $wedding->fresh()->paymentStatus());
    }

    public function test_admin_wajib_memeriksa_pembayaran_sebelum_konfirmasi(): void
    {
        $wedding = $this->makeSubmittedWedding($this->makeCustomer());

        $this->actingAs($this->makeAdmin())
            ->post("/wedding/admin/publications/{$wedding->id}/confirm-payment")
            ->assertSessionHasErrors('payment_checked');

        $this->assertSame(Wedding::PAYMENT_UNPAID, $wedding->fresh()->paymentStatus());
        $this->assertFalse($wedding->fresh()->isPaymentConfirmed());
    }

    public function test_admin_mengonfirmasi_pembayaran_lalu_mempublikasikan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post("/wedding/admin/publications/{$wedding->id}/confirm-payment", ['payment_checked' => '1'])
            ->assertSessionHasNoErrors();

        $wedding->refresh();
        $this->assertTrue($wedding->isPaymentConfirmed());
        $this->assertSame(Wedding::SUBMISSION_PAYMENT_CONFIRMED, $wedding->submissionStatus());
        $this->assertSame($admin->id, $wedding->payment_verifier_id);
        $this->assertNotNull($wedding->payment_confirmed_at);

        $this->actingAs($admin)
            ->post("/wedding/admin/publications/{$wedding->id}/publish")
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('wedding.admin.publications.index'));

        $wedding->refresh();
        $this->assertTrue($wedding->isPublished());
        $this->assertSame(Wedding::SUBMISSION_PUBLISHED, $wedding->submissionStatus());
        $this->assertSame($admin->id, $wedding->publisher_id);
        $this->assertNotNull($wedding->published_at);
    }

    public function test_publikasi_diblokir_bila_data_belum_lengkap(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);
        $admin = $this->makeAdmin();

        // Lengkapi syarat pembayaran, lalu hapus acara agar data jadi tidak lengkap.
        $wedding->confirmPayment($admin->id);
        $wedding->events()->delete();

        $this->actingAs($admin)
            ->post("/wedding/admin/publications/{$wedding->id}/publish")
            ->assertSessionHasErrors('publish');

        $this->assertFalse($wedding->fresh()->isPublished());
    }

    public function test_admin_tidak_bisa_mengelola_undangan_tanpa_pengajuan(): void
    {
        $wedding = $this->makeWedding($this->makeCustomer(), 'andi-siti', 'draft');

        $this->actingAs($this->makeAdmin())
            ->get("/wedding/admin/publications/{$wedding->id}")
            ->assertNotFound();
    }

    public function test_admin_meminta_revisi_dan_customer_mengajukan_ulang(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post("/wedding/admin/publications/{$wedding->id}/revision", [
                'revision_notes' => 'Tolong lengkapi alamat akad nikah.',
            ])
            ->assertSessionHasNoErrors();

        $wedding->refresh();
        $this->assertSame(Wedding::SUBMISSION_REVISION, $wedding->submissionStatus());
        $this->assertSame('Tolong lengkapi alamat akad nikah.', $wedding->revision_notes);

        // Customer dapat mengajukan ulang setelah revisi.
        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/submit-publication", ['final_design' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame(Wedding::SUBMISSION_WAITING_CONFIRMATION, $wedding->fresh()->submissionStatus());
    }

    public function test_customer_tidak_bisa_mengakses_menu_publikasi_admin(): void
    {
        $wedding = $this->makeSubmittedWedding($this->makeCustomer());

        $this->actingAs($this->makeCustomer())
            ->get('/wedding/admin/publications')
            ->assertForbidden();
        $this->actingAs($this->makeCustomer())
            ->post("/wedding/admin/publications/{$wedding->id}/publish")
            ->assertForbidden();
        $this->actingAs($this->makeCustomer())
            ->post("/wedding/admin/publications/{$wedding->id}/confirm-payment", ['payment_checked' => '1'])
            ->assertForbidden();
    }

    public function test_customer_tidak_punya_route_konfirmasi_pembayaran(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        $this->actingAs($user)
            ->post("/wedding/admin/publications/{$wedding->id}/confirm-payment", ['payment_checked' => '1'])
            ->assertForbidden();

        $this->assertFalse($wedding->fresh()->isPaymentConfirmed());
    }

    public function test_undangan_terbit_tidak_berubah_diam_diam(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);
        $admin = $this->makeAdmin();

        $wedding->confirmPayment($admin->id);
        $wedding->markPublished($admin->id);

        $this->actingAs($user)->putJson("/wedding/invitations/{$wedding->id}", [
            'groom_name' => 'Nama Baru',
            'bride_name' => 'Siti',
            'language_style' => 'umum',
        ])->assertStatus(423);

        $this->assertSame('Andi', $wedding->fresh()->groom_name);
    }

    public function test_slug_tidak_valid_memblokir_publikasi(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);
        $admin = $this->makeAdmin();

        $wedding->confirmPayment($admin->id);
        // Simulasi data kotor yang lolos dari validasi awal.
        $wedding->forceFill(['slug' => 'andi--siti'])->save();

        $this->actingAs($admin)
            ->post("/wedding/admin/publications/{$wedding->id}/publish")
            ->assertSessionHasErrors('publish');

        $this->assertFalse($wedding->fresh()->isPublished());
    }

    public function test_semua_halaman_admin_dapat_dirender(): void
    {
        $user = $this->makeCustomer();
        $user->phone = '081234567890';
        $user->save();
        $wedding = $this->makeSubmittedWedding($user);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get('/wedding/admin')->assertOk();
        $this->actingAs($admin)->get('/wedding/admin/weddings')->assertOk();
        $this->actingAs($admin)->get('/wedding/admin/customers')->assertOk();
        $this->actingAs($admin)->get("/wedding/admin/customers/{$user->id}")->assertOk();
        $this->actingAs($admin)->get('/wedding/admin/templates')->assertOk();
        $this->actingAs($admin)->get('/wedding/admin/settings')->assertOk();
        $this->actingAs($admin)->get('/wedding/admin/publications')->assertOk();
        $this->actingAs($admin)
            ->get("/wedding/admin/publications/{$wedding->id}")
            ->assertOk()
            ->assertSee('Tindakan Admin')
            ->assertSee('WhatsApp Customer');
        $this->actingAs($admin)
            ->get("/wedding/admin/publications/{$wedding->id}/preview")
            ->assertOk();
    }

    public function test_nomor_whatsapp_admin_diatur_di_pengaturan_dan_dipakai_customer(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put('/wedding/admin/settings', [
                'support_whatsapp' => '0812-7777-6666',
                'support_email' => 'halo@example.test',
                'manual_payment_note' => 'Transfer manual ke rekening Markas Punokawan.',
                'wish_moderation' => 'post',
            ])
            ->assertSessionHasNoErrors();

        // Nomor WhatsApp admin diubah lewat bagian "Kontak WhatsApp Admin".
        $this->actingAs($admin)
            ->put('/wedding/admin/settings/whatsapp', [
                'admin_whatsapp' => '0812-9999-8888',
            ])
            ->assertSessionHasNoErrors();

        // Nomor tersimpan terpusat dan dinormalisasi untuk link wa.me.
        $this->assertSame('6281299998888', \App\Support\AdminWhatsApp::number());

        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user);

        // Halaman customer memakai nomor admin terpusat (bukan hardcoded).
        $this->actingAs($user)
            ->get("/wedding/invitations/{$wedding->id}")
            ->assertOk()
            ->assertSee('https://wa.me/6281299998888', false);
    }
}
