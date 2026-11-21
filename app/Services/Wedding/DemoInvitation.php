<?php

namespace App\Services\Wedding;

use App\Models\Template;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Models\WeddingGift;
use App\Models\WeddingPhoto;
use App\Models\WeddingRsvp;
use App\Models\WeddingStory;
use App\Models\WeddingWish;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DemoInvitation
{
    public static function forTemplate(string $slug): Wedding
    {
        $meta = config('wedding.templates.'.$slug, config('wedding.templates.melati'));
        $template = Template::firstOrNew(['slug' => $slug], [
            'name' => $meta['name'],
            'style_label' => $meta['tagline'],
            'palette' => $meta['palette'],
            'is_active' => true,
        ]);
        $template->setRelation('weddings', collect());

        [$groom, $bride, $dateText, $city] = $meta['demo'];
        $date = match ($slug) {
            'melati' => Carbon::create(2027, 8, 24),
            'senja' => Carbon::create(2027, 9, 12),
            'lestari' => Carbon::create(2027, 10, 3),
            'maharaja' => Carbon::create(2027, 11, 7),
            'arum' => Carbon::create(2027, 5, 19),
            'puspita' => Carbon::create(2027, 12, 2),
            'asmara' => Carbon::create(2027, 6, 26),
            'nirmala' => Carbon::create(2028, 1, 14),
            default => Carbon::create(2027, 8, 24),
        };

        $wedding = new Wedding([
            'slug' => 'demo-'.$slug,
            'groom_name' => $groom.' Pratama',
            'groom_nickname' => $groom,
            'groom_father' => 'Bapak H. Suryana',
            'groom_mother' => 'Ibu Hj. Ratna Dewi',
            'groom_photo' => 'wedding-assets/assets/images/demo/groom.jpg',
            'groom_instagram' => '@'.$groom,
            'bride_name' => $bride.' Larasati',
            'bride_nickname' => $bride,
            'bride_father' => 'Bapak H. Muhaimin',
            'bride_mother' => 'Ibu Hj. Sri Wahyuni',
            'bride_photo' => 'wedding-assets/assets/images/demo/bride.jpg',
            'bride_instagram' => '@'.$bride,
            'cover_photo' => 'wedding-assets/assets/images/demo/cover.jpg',
            'cover_caption' => 'The beginning of our forever',
            'event_date' => $date,
            'language_style' => 'islami',
            'quote_key' => 'ar-rum-21',
            'quote_text' => config('wedding.quotes.ar-rum-21.text'),
            'quote_source' => config('wedding.quotes.ar-rum-21.source'),
            'opening_text' => config('wedding.openings.islami'),
            'closing_text' => 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu.',
            'music_title' => 'A Thousand Years — Christina Perri',
            'dress_code' => 'Earth tone & formal',
            'hashtag' => '#'.$groom.$bride,
            'sections' => array_fill_keys(array_keys(config('wedding.sections')), true),
            'design_settings' => [],
            'status' => Wedding::STATUS_PUBLISHED,
            'published_at' => now(),
            'views_count' => 1280,
        ]);
        $wedding->setRelation('template', $template);

        $wedding->setRelation('events', new Collection([
            tap(new WeddingEvent([
                'name' => 'Akad Nikah',
                'event_date' => $date->copy(),
                'start_time' => '08:00',
                'end_time' => '10:00',
                'venue_name' => 'Pendopo Agung '.$city,
                'address' => 'Jl. Malioboro No. 12, '.$city,
                'maps_url' => 'https://maps.google.com/?q='.urlencode($city),
                'sort_order' => 1,
            ]), fn ($e) => $e->setRelation('wedding', $wedding)),
            tap(new WeddingEvent([
                'name' => 'Resepsi Pernikahan',
                'event_date' => $date->copy(),
                'start_time' => '11:00',
                'end_time' => '15:00',
                'venue_name' => 'Ballroom Hotel Punokawan',
                'address' => 'Jl. Sudirman No. 88, '.$city,
                'maps_url' => 'https://maps.google.com/?q='.urlencode($city),
                'sort_order' => 2,
            ]), fn ($e) => $e->setRelation('wedding', $wedding)),
        ]));

        $wedding->setRelation('stories', new Collection([
            new WeddingStory(['title' => 'Awal Pertemuan', 'story_date' => '2021', 'body' => 'Pertemuan sederhana di sebuah acara kampus tumbuh menjadi percakapan yang tidak pernah selesai.', 'sort_order' => 1]),
            new WeddingStory(['title' => 'Menjalin Hubungan', 'story_date' => '2023', 'body' => 'Dengan doa dan restu keluarga, kami memilih untuk melangkah bersama dan saling menguatkan.', 'sort_order' => 2]),
            new WeddingStory(['title' => 'Lamaran', 'story_date' => '2026', 'body' => 'Di hadapan keluarga, kami mengikat janji untuk memulai babak baru dalam hidup kami.', 'sort_order' => 3]),
            new WeddingStory(['title' => 'Hari Bahagia', 'story_date' => $date->format('Y'), 'body' => 'Hari ketika kami mengucapkan janji suci dan memulai perjalanan seumur hidup.', 'sort_order' => 4]),
        ]));

        $wedding->setRelation('photos', new Collection(array_map(
            fn ($path, $i) => new WeddingPhoto(['path' => $path, 'caption' => 'Momen '.($i + 1), 'sort_order' => $i]),
            config('wedding.gallery'),
            array_keys(config('wedding.gallery'))
        )));

        $wedding->setRelation('gifts', new Collection([
            new WeddingGift(['bank_name' => 'Bank Mandiri', 'account_number' => '1370001234567', 'account_name' => $groom.' '.$bride, 'sort_order' => 1]),
            new WeddingGift(['bank_name' => 'BCA', 'account_number' => '1234567890', 'account_name' => $bride.' Larasati', 'sort_order' => 2]),
        ]));

        $wedding->setRelation('wishes', new Collection([
            new WeddingWish(['name' => 'Keluarga Hartono', 'message' => 'Barakallahu lakuma wa baraka alaikuma wa jamaa bainakuma fii khair. Selamat menempuh hidup baru!', 'status' => 'approved', 'created_at' => now()->subHours(8)]),
            new WeddingWish(['name' => 'Dinda & Fikri', 'message' => 'Semoga menjadi keluarga yang sakinah, mawaddah, warahmah. Sampai jumpa di hari bahagia kalian!', 'status' => 'approved', 'created_at' => now()->subHours(4)]),
            new WeddingWish(['name' => 'Sahabat Kampus', 'message' => 'Akhirnya! Selamat ya, semoga cinta kalian tumbuh semakin kuat setiap hari.', 'status' => 'approved', 'created_at' => now()->subHour()]),
        ]));

        $wedding->setRelation('rsvps', new Collection([
            new WeddingRsvp(['name' => 'Keluarga Hartono', 'attendance' => WeddingRsvp::HADIR, 'guests_count' => 4]),
            new WeddingRsvp(['name' => 'Dinda & Fikri', 'attendance' => WeddingRsvp::HADIR, 'guests_count' => 2]),
            new WeddingRsvp(['name' => 'Sahabat Kampus', 'attendance' => WeddingRsvp::RAGU, 'guests_count' => 1]),
        ]));

        $wedding->setRelation('guests', new Collection());

        return $wedding;
    }
}
