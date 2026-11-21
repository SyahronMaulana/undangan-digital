<?php

namespace Tests\Feature;

use App\Models\Wedding;
use App\Models\WeddingGuest;
use App\Models\WeddingWish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

class DataIsolationTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    public function test_customer_tidak_bisa_melihat_mengubah_atau_menghapus_undangan_orang_lain(): void
    {
        $pemilik = $this->makeCustomer();
        $penyusup = $this->makeCustomer();
        $wedding = $this->makeWedding($pemilik);

        $this->actingAs($penyusup)->get("/wedding/invitations/{$wedding->id}")->assertNotFound();
        $this->actingAs($penyusup)->get("/wedding/invitations/{$wedding->id}/edit")->assertNotFound();
        $this->actingAs($penyusup)->get("/wedding/invitations/{$wedding->id}/preview")->assertNotFound();
        $this->actingAs($penyusup)->put("/wedding/invitations/{$wedding->id}", ['groom_name' => 'Penyusup'])->assertNotFound();
        $this->actingAs($penyusup)->post("/wedding/invitations/{$wedding->id}/submit-publication", ['final_design' => '1'])->assertNotFound();
        $this->actingAs($penyusup)->delete("/wedding/invitations/{$wedding->id}")->assertNotFound();

        $this->assertNotNull(Wedding::find($wedding->id));
        $this->assertSame('Andi', $wedding->fresh()->groom_name);
    }

    public function test_daftar_undangan_hanya_milik_sendiri(): void
    {
        $a = $this->makeCustomer();
        $b = $this->makeCustomer();
        $this->makeWedding($a, 'milik-a');
        $this->makeWedding($b, 'milik-b');

        $this->actingAs($a)->get('/wedding/invitations')
            ->assertSee('/wedding/milik-a', false)
            ->assertDontSee('/wedding/milik-b', false);
    }

    public function test_pemilik_dapat_melihat_undangannya(): void
    {
        $a = $this->makeCustomer();
        $wedding = $this->makeWedding($a);
        $this->actingAs($a)->get("/wedding/invitations/{$wedding->id}")->assertOk();
    }

    public function test_customer_tidak_bisa_mengubah_tamu_orang_lain(): void
    {
        $pemilik = $this->makeCustomer();
        $penyusup = $this->makeCustomer();
        $wedding = $this->makeWedding($pemilik);
        $guest = $wedding->guests()->create(['name' => 'Tamu Pemilik']);

        $this->actingAs($penyusup)->put("/wedding/guests/{$guest->id}", ['name' => 'Diubah'])->assertNotFound();
        $this->actingAs($penyusup)->delete("/wedding/guests/{$guest->id}")->assertNotFound();
        $this->actingAs($penyusup)->get("/wedding/guests/{$guest->id}/link")->assertNotFound();

        $this->assertSame('Tamu Pemilik', $guest->fresh()->name);
    }

    public function test_customer_tidak_bisa_menambah_tamu_ke_undangan_orang_lain(): void
    {
        $pemilik = $this->makeCustomer();
        $penyusup = $this->makeCustomer();
        $wedding = $this->makeWedding($pemilik);

        $this->actingAs($penyusup)->post('/wedding/guests', [
            'wedding_id' => $wedding->id,
            'name' => 'Tamu Penyusup',
        ])->assertNotFound();

        $this->assertSame(0, $wedding->guests()->count());
    }

    public function test_statistik_dasbor_tidak_menghitung_data_customer_lain(): void
    {
        $a = $this->makeCustomer();
        $b = $this->makeCustomer();
        $weddingA = $this->makeWedding($a, 'milik-a');
        $weddingB = $this->makeWedding($b, 'milik-b');
        $weddingA->guests()->create(['name' => 'Tamu A']);
        $weddingB->guests()->create(['name' => 'Tamu B']);
        $weddingB->guests()->create(['name' => 'Tamu B2']);

        $this->actingAs($a)->get('/wedding/dashboard')
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['tamu'] === 1 && $stats['undangan'] === 1);
    }

    public function test_customer_tidak_bisa_memoderasi_ucapan_orang_lain(): void
    {
        $pemilik = $this->makeCustomer();
        $penyusup = $this->makeCustomer();
        $wedding = $this->makeWedding($pemilik);
        $wish = $wedding->wishes()->create([
            'name' => 'Tamu', 'message' => 'Selamat.', 'status' => 'approved',
        ]);

        $this->actingAs($penyusup)
            ->patch("/wedding/wishes/{$wish->id}", ['status' => 'hidden'])
            ->assertNotFound();
        $this->actingAs($penyusup)
            ->delete("/wedding/wishes/{$wish->id}")
            ->assertNotFound();

        $this->assertSame('approved', $wish->fresh()->status);
    }

    public function test_link_token_tidak_membocorkan_tamu_undangan_lain(): void
    {
        $a = $this->makeCustomer();
        $b = $this->makeCustomer();
        $this->makeWedding($a, 'milik-a');
        $weddingB = $this->makeWedding($b, 'milik-b');
        $guestB = $weddingB->guests()->create(['name' => 'Rahasia B']);

        $this->actingAs($a)
            ->get("/wedding/guests/{$guestB->id}/link")
            ->assertNotFound();
    }
}
