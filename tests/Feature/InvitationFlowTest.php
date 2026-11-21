<?php

namespace Tests\Feature;

use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    private function payload(array $over = []): array
    {
        return array_merge([
            'groom_name' => 'Andi', 'bride_name' => 'Siti', 'slug' => 'andi-siti',
            'template_id' => $this->makeTemplate()->id,
        ], $over);
    }

    public function test_customer_membuat_undangan_draf_dengan_template(): void
    {
        $user = $this->makeCustomer();
        $this->actingAs($user)
            ->post('/wedding/invitations', $this->payload())
            ->assertSessionHasNoErrors();

        $wedding = Wedding::firstWhere('slug', 'andi-siti');
        $this->assertSame($user->id, $wedding->user_id);
        $this->assertSame('draft', $wedding->status);
        $this->assertSame(Wedding::SUBMISSION_DRAFT, $wedding->submissionStatus());
        $this->assertSame(Wedding::PAYMENT_UNPAID, $wedding->paymentStatus());
        $this->assertFalse($wedding->sectionEnabled('kisah'));
        $this->assertTrue($wedding->sectionEnabled('rsvp'));
    }

    public function test_slug_yang_dicadangkan_ditolak(): void
    {
        $this->actingAs($this->makeCustomer())
            ->post('/wedding/invitations', $this->payload(['slug' => 'admin']))
            ->assertSessionHasErrors('slug');
    }

    public function test_slug_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->makeCustomer())
            ->post('/wedding/invitations', $this->payload(['slug' => 'Andi Siti!']))
            ->assertSessionHasErrors('slug');
    }

    /**
     * Matriks slug: tanda hubung ganda, di awal, di akhir, dan karakter khusus ditolak.
     */
    #[DataProvider('slugTidakValidProvider')]
    public function test_slug_dengan_tanda_hubung_atau_karakter_khusus_ditolak(string $slug): void
    {
        $this->actingAs($this->makeCustomer())
            ->post('/wedding/invitations', $this->payload(['slug' => $slug]))
            ->assertSessionHasErrors('slug');

        $this->assertSame(0, Wedding::count());
    }

    public static function slugTidakValidProvider(): array
    {
        return [
            'tanda hubung ganda' => ['rangga--ayu'],
            'tanda hubung di awal' => ['-rangga'],
            'tanda hubung di akhir' => ['rangga-'],
            'garis miring (/)' => ['rangga/ayu'],
            'karakter khusus (@)' => ['rangga@ayu'],
            'underscore' => ['andi_siti'],
            'titik' => ['andi.siti'],
        ];
    }

    /** Matriks slug valid sesuai permintaan (huruf kecil, angka, tanda hubung tunggal). */
    public static function slugValidProvider(): array
    {
        return [
            'rangga-ayu' => ['rangga-ayu'],
            'ahmad-siti' => ['ahmad-siti'],
            'undangan-2026' => ['undangan-2026'],
            'undangan-pernikahan-2026' => ['undangan-pernikahan-2026'],
            'budi123-putri456' => ['budi123-putri456'],
        ];
    }

    #[DataProvider('slugValidProvider')]
    public function test_slug_bertanda_hubung_diterima(string $slug): void
    {
        $this->actingAs($this->makeCustomer())
            ->post('/wedding/invitations', $this->payload(['slug' => $slug]))
            ->assertSessionHasNoErrors();

        $this->assertSame($slug, Wedding::firstWhere('slug', $slug)->slug);
    }

    public function test_slug_huruf_besar_dinormalisasi_menjadi_huruf_kecil(): void
    {
        $this->actingAs($this->makeCustomer())
            ->post('/wedding/invitations', $this->payload(['slug' => 'Rangga-Ayu']))
            ->assertSessionHasNoErrors();

        $this->assertSame('rangga-ayu', Wedding::first()->slug);
    }

    public function test_slug_huruf_besar_dan_spasi_dinormalisasi(): void
    {
        $this->actingAs($this->makeCustomer())
            ->post('/wedding/invitations', $this->payload(['slug' => 'Andi Siti']))
            ->assertSessionHasNoErrors();

        $this->assertSame('andi-siti', Wedding::first()->slug);
    }

    public function test_slug_harus_unik(): void
    {
        $owner = $this->makeCustomer();
        $this->makeWedding($owner, 'andi-siti');

        $this->actingAs($this->makeCustomer())
            ->post('/wedding/invitations', $this->payload())
            ->assertSessionHasErrors('slug');
    }

    public function test_slug_yang_sudah_dipakai_ditolak_dengan_pesan_jelas(): void
    {
        $owner = $this->makeCustomer();
        $this->makeWedding($owner, 'rangga-ayu');

        $this->actingAs($this->makeCustomer())
            ->post('/wedding/invitations', $this->payload(['slug' => 'rangga-ayu']))
            ->assertSessionHasErrors(['slug' => 'Alamat ini sudah dipakai. Coba yang lain.']);
    }

    public function test_form_memuat_pola_slug_yang_mendukung_tanda_hubung(): void
    {
        $this->actingAs($this->makeCustomer())
            ->get('/wedding/invitations/create')
            ->assertOk()
            ->assertSee('pattern="[a-z0-9]+(?:-[a-z0-9]+)*"', false);
    }

    public function test_undangan_bertanda_hubung_dapat_dibuka_lewat_url_publik(): void
    {
        $owner = $this->makeCustomer();
        $this->makeWedding($owner, 'rangga-ayu', 'published');

        $this->get('/wedding/rangga-ayu')->assertOk();

        $this->assertStringEndsWith(
            '/wedding/rangga-ayu',
            Wedding::firstWhere('slug', 'rangga-ayu')->invitationUrl()
        );
    }

    public function test_customer_bisa_membuat_banyak_undangan_tanpa_paket(): void
    {
        $user = $this->makeCustomer();
        $this->actingAs($user)->post('/wedding/invitations', $this->payload(['slug' => 'undangan-pertama']));
        $this->actingAs($user)->post('/wedding/invitations', $this->payload(['slug' => 'undangan-kedua']))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $user->weddings()->count());
    }

    public function test_customer_mengajukan_publikasi_tidak_mempublikasikan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/submit-publication", ['final_design' => '1'])
            ->assertSessionHasNoErrors();

        $wedding->refresh();
        $this->assertSame(Wedding::SUBMISSION_WAITING_CONFIRMATION, $wedding->submissionStatus());
        $this->assertFalse($wedding->isPublished());
        $this->assertSame('draft', $wedding->status);
        $this->assertNotNull($wedding->submitted_at);
    }

    public function test_pengajuan_publikasi_wajib_konfirmasi_final(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/submit-publication")
            ->assertSessionHasErrors('final_design');

        $this->assertSame(Wedding::SUBMISSION_DRAFT, $wedding->fresh()->submissionStatus());
    }

    public function test_customer_tidak_bisa_mempublikasikan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/publish")
            ->assertNotFound();

        $this->assertFalse($wedding->fresh()->isPublished());
    }

    public function test_pengajuan_ditolak_tanpa_rangkaian_acara(): void
    {
        $user = $this->makeCustomer();
        $wedding = new Wedding([
            'template_id' => $this->makeTemplate()->id,
            'slug' => 'tanpa-acara',
            'groom_name' => 'Andi',
            'bride_name' => 'Siti',
            'status' => 'draft',
        ]);
        $wedding->user_id = $user->id;
        $wedding->save();

        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/submit-publication", ['final_design' => '1'])
            ->assertSessionHasErrors('publish');

        $this->assertSame(Wedding::SUBMISSION_DRAFT, $wedding->fresh()->submissionStatus());
    }

    public function test_mengubah_data_saat_pengajuan_aktif_mengembalikan_ke_revisi(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeSubmittedWedding($user, 'andi-siti');

        $this->actingAs($user)->putJson("/wedding/invitations/{$wedding->id}", [
            'groom_name' => 'Andi Wijaya',
            'bride_name' => 'Siti Aminah',
            'language_style' => 'umum',
        ])->assertOk();

        $wedding->refresh();
        $this->assertSame(Wedding::SUBMISSION_REVISION, $wedding->submissionStatus());
        $this->assertNotNull($wedding->revision_notes);
    }

    public function test_undangan_terbit_tidak_bisa_diubah_diam_diam(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'published');

        $this->actingAs($user)->putJson("/wedding/invitations/{$wedding->id}", [
            'groom_name' => 'Diubah Diam-diam',
            'bride_name' => 'Siti',
            'language_style' => 'umum',
        ])->assertStatus(423);

        $this->assertSame('Andi', $wedding->fresh()->groom_name);
    }

    public function test_halaman_editor_undangan_terbit_diarahkan_ke_ringkasan(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'published');

        $this->actingAs($user)
            ->get("/wedding/invitations/{$wedding->id}/edit")
            ->assertRedirect("/wedding/invitations/{$wedding->id}")
            ->assertSessionHasErrors('publish');
    }

    public function test_template_dapat_diganti_tanpa_kehilangan_data(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');
        $senja = $this->makeTemplate('senja');

        $this->actingAs($user)
            ->post("/wedding/invitations/{$wedding->id}/template", ['template_id' => $senja->id])
            ->assertSessionHasNoErrors();

        $wedding->refresh();
        $this->assertSame($senja->id, $wedding->template_id);
        $this->assertSame('Andi', $wedding->groom_name);
    }

    public function test_preview_dan_halaman_publik_memakai_tampilan_yang_sama(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'published');

        $preview = $this->actingAs($user)->get("/wedding/invitations/{$wedding->id}/preview");
        $public = $this->get("/wedding/{$wedding->slug}");

        $preview->assertOk();
        $public->assertOk();

        foreach ([$preview, $public] as $response) {
            $response->assertSee('wedding-assets/assets/templates/base.css', false)
                ->assertSee('wedding-assets/assets/templates/melati.css', false)
                ->assertSee('Andi')
                ->assertSee('Siti');
        }
    }

    public function test_autosave_json_menyimpan_perubahan_tanpa_reload(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)
            ->putJson("/wedding/invitations/{$wedding->id}", [
                'groom_name' => 'Andi Wijaya',
                'bride_name' => 'Siti Aminah',
                'language_style' => 'umum',
                'sections' => collect(config('wedding.default_sections'))
                    ->mapWithKeys(fn ($value, $key) => [$key => true])
                    ->all(),
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame('Andi Wijaya', $wedding->fresh()->groom_name);
        $this->assertTrue($wedding->fresh()->sectionEnabled('kisah'));
    }

    public function test_editor_menampilkan_dua_panel_dan_preview(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user, 'andi-siti', 'draft');

        $this->actingAs($user)
            ->get("/wedding/invitations/{$wedding->id}/edit")
            ->assertOk()
            ->assertSee('Pratinjau')
            ->assertSee(route('wedding.invitations.preview', $wedding), false);
    }
}
