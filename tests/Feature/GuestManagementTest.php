<?php

namespace Tests\Feature;

use App\Models\WeddingGuest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

class GuestManagementTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    public function test_tamu_baru_disimpan_dengan_nomor_ternormalisasi(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);

        $this->actingAs($user)->post('/wedding/guests', [
            'wedding_id' => $wedding->id,
            'name' => 'Bapak Syahron',
            'phone' => '0812-3456-7890',
            'group_name' => 'Keluarga',
            'max_pax' => 4,
        ])->assertSessionHasNoErrors();

        $guest = $wedding->guests()->first();
        $this->assertSame('Bapak Syahron', $guest->name);
        $this->assertSame('+6281234567890', $guest->phone);
        $this->assertSame(4, $guest->max_pax);
    }

    public function test_tamu_dapat_ditambah_tanpa_batas_paket(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);

        for ($i = 1; $i <= 30; $i++) {
            $this->actingAs($user)->post('/wedding/guests', [
                'wedding_id' => $wedding->id,
                'name' => 'Tamu '.$i,
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(30, $wedding->guests()->count());
    }

    public function test_import_csv_membaca_header_bahasa_indonesia(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);

        $csv = "nama;telepon;grup;kuota\n"
            ."Bapak A;0812-1111-2222;Keluarga;3\n"
            ."Ibu B;0813-3333-4444;Tetangga;2\n"
            .";;Tanpa Nama;1\n";

        $this->actingAs($user)->post('/wedding/guests/import', [
            'wedding_id' => $wedding->id,
            'file' => UploadedFile::fake()->createWithContent('tamu.csv', $csv),
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, $wedding->guests()->count());
        $this->assertSame('+6281211112222', $wedding->guests()->where('name', 'Bapak A')->first()->phone);
        $this->assertSame('Keluarga', $wedding->guests()->where('name', 'Bapak A')->first()->group_name);
        $this->assertSame(3, $wedding->guests()->where('name', 'Bapak A')->first()->max_pax);
    }

    public function test_import_csv_tanpa_kolom_nama_ditolak(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);

        $this->actingAs($user)->post('/wedding/guests/import', [
            'wedding_id' => $wedding->id,
            'file' => UploadedFile::fake()->createWithContent('salah.csv', "telepon,grup\n0812,Teman\n"),
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, $wedding->guests()->count());
    }

    public function test_import_dan_export_tersedia_tanpa_paket(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);

        $this->actingAs($user)->post('/wedding/guests/import', [
            'wedding_id' => $wedding->id,
            'file' => UploadedFile::fake()->createWithContent('tamu.csv', "nama\nBapak A\n"),
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->get("/wedding/guests/export?wedding={$wedding->id}")
            ->assertOk();

        $this->assertSame(1, $wedding->guests()->count());
    }

    public function test_export_csv_memuat_data_tamu_dan_link_personal(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $guest = $wedding->guests()->create(['name' => 'Bapak A', 'phone' => '081211112222']);

        $response = $this->actingAs($user)->get("/wedding/guests/export?wedding={$wedding->id}");
        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('nama,telepon,grup,kuota', $content);
        $this->assertStringContainsString('Bapak A', $content);
        $this->assertStringContainsString($guest->token, $content);
    }

    public function test_link_personal_hanya_memakai_whatsapp_click_to_chat(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $guest = $wedding->guests()->create(['name' => 'Bapak A', 'phone' => '0812-1111-2222']);

        $response = $this->actingAs($user)
            ->getJson("/wedding/guests/{$guest->id}/link")
            ->assertOk()
            ->assertJsonPath('link', $guest->link());

        $wa = $response->json('wa');
        $this->assertStringStartsWith('https://wa.me/6281211112222?text=', $wa);
        $this->assertStringContainsString(rawurlencode('Bapak A'), $wa);
        $this->assertStringNotContainsString('api.whatsapp.com/send', $wa);
        $this->assertStringNotContainsString('terkirim', strtolower($wa));
    }

    public function test_link_personal_tanpa_nomor_tidak_membuat_url_whatsapp(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $guest = $wedding->guests()->create(['name' => 'Tanpa Nomor']);

        $this->actingAs($user)
            ->getJson("/wedding/guests/{$guest->id}/link")
            ->assertOk()
            ->assertJsonPath('wa', null);
    }

    public function test_whatsapp_tersedia_tanpa_paket(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $guest = $wedding->guests()->create(['name' => 'Bapak A', 'phone' => '081211112222']);

        $this->actingAs($user)
            ->getJson("/wedding/guests/{$guest->id}/link")
            ->assertOk();
    }

    public function test_tamu_dapat_diperbarui_dan_dihapus_oleh_pemilik(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $guest = $wedding->guests()->create(['name' => 'Nama Lama']);

        $this->actingAs($user)->put("/wedding/guests/{$guest->id}", [
            'name' => 'Nama Baru',
            'phone' => '0857-0000-1111',
            'max_pax' => 3,
        ])->assertSessionHasNoErrors();

        $this->assertSame('Nama Baru', $guest->fresh()->name);
        $this->assertSame('+6285700001111', $guest->fresh()->phone);

        $this->actingAs($user)->delete("/wedding/guests/{$guest->id}");
        $this->assertNull(WeddingGuest::find($guest->id));
    }
}
