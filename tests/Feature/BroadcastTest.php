<?php

namespace Tests\Feature;

use App\Models\BroadcastCampaign;
use App\Models\WeddingActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

class BroadcastTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    public function test_customer_membuat_template_broadcast(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);

        $this->actingAs($user)->post('/wedding/broadcast', [
            'wedding_id' => $wedding->id,
            'name' => 'Promo Keluarga',
            'message_template' => 'Undangan {{nama_pria}} & {{nama_wanita}}',
        ])->assertSessionHasNoErrors();

        $template = BroadcastCampaign::first();
        $this->assertSame('Promo Keluarga', $template->name);
        $this->assertSame($wedding->id, $template->wedding_id);
    }

    public function test_customer_mengedit_template_broadcast(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $template = $wedding->campaigns()->create(['name' => 'Awal', 'message_template' => 'Pesan awal']);

        $this->actingAs($user)->put("/wedding/broadcast/{$template->id}", [
            'name' => 'Diperbarui',
            'message_template' => 'Pesan baru',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Diperbarui', $template->fresh()->name);
        $this->assertSame('Pesan baru', $template->fresh()->message_template);
    }

    public function test_customer_menghapus_template_broadcast(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $template = $wedding->campaigns()->create(['name' => 'Hapus', 'message_template' => 'Pesan']);

        $this->actingAs($user)->delete("/wedding/broadcast/{$template->id}")
            ->assertRedirect('/wedding/broadcast');

        $this->assertSame(0, BroadcastCampaign::count());
    }

    public function test_halaman_template_menampilkan_pratinjau_tanpa_nama_tamu(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $template = $wedding->campaigns()->create([
            'name' => 'Undangan',
            'message_template' => 'Undangan {{nama_pria}} & {{nama_wanita}} di {{lokasi_acara}}',
        ]);

        $this->actingAs($user)->get("/wedding/broadcast/{$template->id}")
            ->assertOk()
            ->assertSee('Undangan Andi & Siti di Pendopo Punokawan')
            ->assertDontSee('nama_tamu');
    }

    public function test_berbagi_mencatat_riwayat_dan_mengembalikan_url_tanpa_nomor(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $template = $wedding->campaigns()->create([
            'name' => 'Promo',
            'message_template' => 'Undangan {{nama_pria}} & {{nama_wanita}}',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/wedding/broadcast/{$template->id}/share", ['channel' => 'whatsapp'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertStringStartsWith('https://wa.me/?text=', $response->json('url'));
        $this->assertStringNotContainsString('api.whatsapp.com', $response->json('url'));

        $log = WeddingActivityLog::where('event', 'broadcast_share')->firstOrFail();
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('Promo', $log->meta['template_name']);
        $this->assertSame('Undangan Andi & Siti', $log->meta['message']);
        $this->assertSame('whatsapp', $log->meta['channel']);
        $this->assertNotNull($log->created_at);
    }

    public function test_pesan_berbagi_tidak_memuat_nama_tamu(): void
    {
        $user = $this->makeCustomer();
        $wedding = $this->makeWedding($user);
        $wedding->guests()->create(['name' => 'Bapak Syahron', 'phone' => '081211112222']);
        $template = $wedding->campaigns()->create([
            'name' => 'Promo',
            'message_template' => "Assalamu'alaikum {{nama_tamu}}, undangan {{nama_pria}} & {{nama_wanita}}",
        ]);

        $response = $this->actingAs($user)
            ->postJson("/wedding/broadcast/{$template->id}/share", ['channel' => 'web_share'])
            ->assertOk();

        $url = $response->json('url');
        $this->assertStringNotContainsString(rawurlencode('Bapak Syahron'), $url);
        $this->assertStringNotContainsString('nama_tamu', $url);
    }

    public function test_template_customer_lain_tidak_dapat_diakses(): void
    {
        $owner = $this->makeCustomer();
        $wedding = $this->makeWedding($owner);
        $template = $wedding->campaigns()->create(['name' => 'Milik Orang', 'message_template' => 'Pesan']);

        $this->actingAs($this->makeCustomer())->get("/wedding/broadcast/{$template->id}")->assertNotFound();
        $this->actingAs($this->makeCustomer())->put("/wedding/broadcast/{$template->id}", [
            'name' => 'Curi', 'message_template' => 'Curi',
        ])->assertNotFound();
    }

    public function test_halaman_broadcast_dapat_dirender_tanpa_tamu(): void
    {
        $user = $this->makeCustomer();
        $this->makeWedding($user);

        $this->actingAs($user)->get('/wedding/broadcast')
            ->assertOk()
            ->assertSee('Template Pesan')
            ->assertDontSee('Pilih Tamu');
    }
}
