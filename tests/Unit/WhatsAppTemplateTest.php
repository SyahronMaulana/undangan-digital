<?php

namespace Tests\Unit;

use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Models\WeddingGuest;
use App\Services\Wedding\WhatsAppTemplate;
use Illuminate\Support\Collection;
use Tests\TestCase;

class WhatsAppTemplateTest extends TestCase
{
    private function wedding(): Wedding
    {
        $wedding = new Wedding([
            'slug' => 'andi-siti',
            'groom_name' => 'Andi Wijaya',
            'groom_nickname' => 'Andi',
            'bride_name' => 'Siti Aminah',
            'bride_nickname' => 'Siti',
        ]);
        $wedding->setRelation('events', new Collection([
            new WeddingEvent([
                'name' => 'Akad Nikah',
                'event_date' => now()->setDate(2027, 8, 24),
                'start_time' => '08:00',
                'venue_name' => 'Pendopo Agung',
                'sort_order' => 1,
            ]),
        ]));

        return $wedding;
    }

    public function test_semua_variabel_dirender_dengan_data_undangan_dan_tamu(): void
    {
        $guest = new WeddingGuest(['name' => 'Bapak Syahron']);
        $message = (new WhatsAppTemplate)->render(
            'Halo {{nama_tamu}}, undangan {{nama_pria}} & {{nama_wanita}} di {{lokasi_acara}} pada {{tanggal_pernikahan}}. {{link_undangan}}',
            $this->wedding(),
            $guest
        );

        $this->assertStringContainsString('Halo Bapak Syahron', $message);
        $this->assertStringContainsString('Andi & Siti', $message);
        $this->assertStringContainsString('Pendopo Agung', $message);
        $this->assertStringContainsString('2027', $message);
        $this->assertStringContainsString('/wedding/andi-siti', $message);
        $this->assertStringNotContainsString('{{', $message);
    }

    public function test_broadcast_tidak_menyisipkan_nama_tamu(): void
    {
        $message = (new WhatsAppTemplate)->render(
            "Assalamu'alaikum {{nama_tamu}}, undangan {{nama_pria}} & {{nama_wanita}}.",
            $this->wedding()
        );

        $this->assertStringNotContainsString('{{nama_tamu}}', $message);
        $this->assertStringNotContainsString('Bapak Syahron', $message);
        $this->assertStringNotContainsString('Bapak/Ibu', $message);
        $this->assertStringContainsString('Andi & Siti', $message);
    }

    public function test_variabel_broadcast_tidak_memuat_nama_tamu(): void
    {
        $this->assertNotContains('{{nama_tamu}}', WhatsAppTemplate::VARIABLES);
        $this->assertContains('{{link_undangan}}', WhatsAppTemplate::VARIABLES);
    }

    public function test_url_whatsapp_hanya_memakai_click_to_chat_resmi(): void
    {
        $url = (new WhatsAppTemplate)->waUrl('+62 812-3456-7890', 'Halo & selamat');

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString(rawurlencode('Halo & selamat'), $url);
        $this->assertStringNotContainsString('api.whatsapp.com', $url);
        $this->assertStringNotContainsString('web.whatsapp.com', $url);
    }

    public function test_url_berbagi_tanpa_nomor_tujuan(): void
    {
        $url = (new WhatsAppTemplate)->shareUrl('Halo & selamat');

        $this->assertStringStartsWith('https://wa.me/?text=', $url);
        $this->assertStringContainsString(rawurlencode('Halo & selamat'), $url);
        $this->assertStringNotContainsString('api.whatsapp.com', $url);
    }

    public function test_pesan_default_tidak_mengklaim_terkirim(): void
    {
        $message = WhatsAppTemplate::DEFAULT_MESSAGE;

        $this->assertStringNotContainsString('terkirim', strtolower($message));
        $this->assertStringContainsString('{{link_undangan}}', $message);
        $this->assertStringNotContainsString('{{nama_tamu}}', $message);
    }

    public function test_preview_broadcast_tanpa_data_tamu(): void
    {
        $preview = (new WhatsAppTemplate)->preview($this->wedding(), 'Undangan {{nama_pria}} di {{lokasi_acara}}');

        $this->assertSame('Undangan Andi di Pendopo Agung', $preview['message']);
        $this->assertStringStartsWith('https://wa.me/?text=', $preview['url']);
        $this->assertArrayNotHasKey('guest', $preview);
    }

    public function test_pesan_broadcast_membuang_placeholder_nama_tamu_dan_variabel_mentah(): void
    {
        $message = (new WhatsAppTemplate)->broadcastMessage(
            $this->wedding(),
            "Assalamu'alaikum {{nama_tamu}}, undangan {{nama_pria}} & {{nama_wanita}} di {{lokasi_acara}}. {{variabel_tidak_dikenal}}"
        );

        $this->assertStringNotContainsString('{{', $message);
        $this->assertStringNotContainsString('nama_tamu', $message);
        $this->assertStringNotContainsString('variabel_tidak_dikenal', $message);
        $this->assertStringContainsString('Andi & Siti di Pendopo Agung', $message);
    }

    public function test_broadcast_map_menampilkan_tanggal_indonesia_dan_jam_wib(): void
    {
        $map = (new WhatsAppTemplate)->broadcastMap($this->wedding());

        $this->assertSame('Selasa, 24 Agustus 2027 pukul 08.00 WIB', $map['{{tanggal_pernikahan}}']);
        $this->assertSame('Pendopo Agung', $map['{{lokasi_acara}}']);
        $this->assertSame('Andi', $map['{{nama_pria}}']);
        $this->assertSame('Siti', $map['{{nama_wanita}}']);
    }

    public function test_broadcast_map_tanpa_jam_hanya_tanggal_indonesia(): void
    {
        $wedding = $this->wedding();
        $wedding->events->first()->start_time = null;

        $map = (new WhatsAppTemplate)->broadcastMap($wedding);

        $this->assertSame('Selasa, 24 Agustus 2027', $map['{{tanggal_pernikahan}}']);
        $this->assertStringNotContainsString('pukul', $map['{{tanggal_pernikahan}}']);
    }

    public function test_time_range_menampilkan_pukul_dan_wib(): void
    {
        $event = new WeddingEvent(['start_time' => '10:00', 'end_time' => null]);
        $this->assertSame('Pukul 10.00 WIB', $event->timeRange());

        $range = new WeddingEvent(['start_time' => '10:00', 'end_time' => '12:00']);
        $this->assertSame('Pukul 10.00 – 12.00 WIB', $range->timeRange());
    }

    public function test_time_range_kosong_bila_tanpa_jam_mulai(): void
    {
        $event = new WeddingEvent(['start_time' => null, 'end_time' => '12:00']);

        $this->assertSame('', $event->timeRange());
    }

    public function test_clock_memakai_titik_sebagai_pemisah_jam(): void
    {
        $this->assertSame('08.00', WeddingEvent::clock('08:00'));
        $this->assertSame('08.00', WeddingEvent::clock('08:00:00'));
        $this->assertSame('', WeddingEvent::clock(null));
    }
}
