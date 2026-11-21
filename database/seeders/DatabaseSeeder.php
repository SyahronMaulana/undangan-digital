<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $templates = config('wedding.templates');
        foreach (array_keys($templates) as $i => $slug) {
            $meta = $templates[$slug];
            Template::updateOrCreate(['slug' => $slug], [
                'name' => $meta['name'],
                'style_label' => $meta['tagline'],
                'description' => $meta['description'],
                'palette' => $meta['palette'],
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }

        // Akun Super Admin. GANTI kata sandi ini sebelum dipakai di server publik.
        $admin = User::firstOrNew(['email' => env('ADMIN_EMAIL', 'admin@markaspunokawan.test')]);
        if (! $admin->exists) {
            $admin->name = 'Super Admin Wedding';
            $admin->password = env('ADMIN_PASSWORD', 'ganti-kata-sandi-ini');
            $admin->role = User::ROLE_ADMIN;
            $admin->save();
        }

        if (app()->environment('local')) {
            $this->seedDemoCustomer();
        }
    }

    private function seedDemoCustomer(): void
    {
        $user = User::firstOrNew(['email' => 'demo@markaspunokawan.test']);
        if (! $user->exists) {
            $user->name = 'Rangga & Ayu';
            $user->phone = '081234567890';
            $user->password = Hash::make('demo12345');
            $user->role = User::ROLE_CUSTOMER;
            $user->save();
        }

        $template = Template::where('slug', 'melati')->first();
        if (! $template) {
            return;
        }

        $wedding = Wedding::firstOrNew(['slug' => 'rangga-ayu']);
        if ($wedding->exists) {
            return;
        }

        $eventDate = now()->addMonths(4)->startOfMonth()->addDays(23);
        $wedding->fill([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'slug' => 'rangga-ayu',
            'groom_name' => 'Rangga Adiputra',
            'groom_nickname' => 'Rangga',
            'groom_father' => 'Bapak H. Suryana',
            'groom_mother' => 'Ibu Hj. Ratna Dewi',
            'groom_photo' => 'wedding-assets/assets/images/demo/groom.jpg',
            'groom_instagram' => '@rangga',
            'bride_name' => 'Ayu Larasati',
            'bride_nickname' => 'Ayu',
            'bride_father' => 'Bapak H. Muhaimin',
            'bride_mother' => 'Ibu Hj. Sri Wahyuni',
            'bride_photo' => 'wedding-assets/assets/images/demo/bride.jpg',
            'bride_instagram' => '@ayu',
            'cover_photo' => 'wedding-assets/assets/images/demo/cover.jpg',
            'cover_caption' => 'The beginning of our forever',
            'event_date' => $eventDate,
            'language_style' => 'islami',
            'quote_key' => 'ar-rum-21',
            'quote_text' => config('wedding.quotes.ar-rum-21.text'),
            'quote_source' => config('wedding.quotes.ar-rum-21.source'),
            'opening_text' => config('wedding.openings.islami'),
            'closing_text' => 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu.',
            'dress_code' => 'Earth tone & formal',
            'hashtag' => '#RanggaAyuForever',
            'music_title' => 'A Thousand Years — Christina Perri',
            'sections' => array_fill_keys(array_keys(config('wedding.sections')), true),
            'design_settings' => [],
            'status' => Wedding::STATUS_PUBLISHED,
            'submission_status' => Wedding::SUBMISSION_PUBLISHED,
            'payment_status' => Wedding::PAYMENT_CONFIRMED,
            'submitted_at' => now()->subDays(3),
            'payment_confirmed_at' => now()->subDays(2),
            'published_at' => now(),
        ])->save();

        $wedding->events()->create([
            'name' => 'Akad Nikah',
            'event_date' => $eventDate,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'venue_name' => 'Pendopo Agung Punokawan',
            'address' => 'Jl. Malioboro No. 12, Yogyakarta',
            'maps_url' => 'https://maps.google.com/?q=Pendopo+Agung+Yogyakarta',
            'sort_order' => 1,
        ]);
        $wedding->events()->create([
            'name' => 'Resepsi Pernikahan',
            'event_date' => $eventDate,
            'start_time' => '11:00',
            'end_time' => '15:00',
            'venue_name' => 'Ballroom Hotel Punokawan',
            'address' => 'Jl. Jenderal Sudirman No. 88, Yogyakarta',
            'maps_url' => 'https://maps.google.com/?q=Hotel+Punokawan+Yogyakarta',
            'sort_order' => 2,
        ]);

        foreach ([
            ['Awal Pertemuan', '2021', 'Pertemuan sederhana di sebuah acara kampus tumbuh menjadi percakapan yang tidak pernah selesai.'],
            ['Menjalin Hubungan', '2023', 'Dengan doa dan restu keluarga, kami memilih untuk melangkah bersama dan saling menguatkan.'],
            ['Lamaran', '2026', 'Di hadapan keluarga, kami mengikat janji untuk memulai babak baru dalam hidup kami.'],
            ['Hari Bahagia', (string) $eventDate->year, 'Hari ketika kami mengucapkan janji suci dan memulai perjalanan seumur hidup.'],
        ] as $i => [$title, $date, $body]) {
            $wedding->stories()->create(['title' => $title, 'story_date' => $date, 'body' => $body, 'sort_order' => $i]);
        }

        foreach (config('wedding.gallery') as $i => $path) {
            $wedding->photos()->create(['path' => $path, 'caption' => 'Momen bahagia '.($i + 1), 'sort_order' => $i]);
        }

        $wedding->gifts()->create([
            'bank_name' => 'Bank Mandiri', 'account_number' => '1370001234567',
            'account_name' => 'Rangga Adiputra', 'sort_order' => 1,
        ]);
        $wedding->gifts()->create([
            'bank_name' => 'BCA', 'account_number' => '1234567890',
            'account_name' => 'Ayu Larasati', 'sort_order' => 2,
        ]);

        $guests = [
            ['Bapak Syahron', '081234567890', 'Keluarga', 4],
            ['Ibu Ratna', '081298765432', 'Tetangga', 2],
            ['Dinda & Fikri', '085712345678', 'Sahabat', 2],
            ['Keluarga Hartono', '081311223344', 'Keluarga', 5],
            ['Tim Kantor Ayu', '082122334455', 'Rekan Kerja', 6],
            ['Sahabat Kampus', '085699887766', 'Sahabat', 3],
        ];
        foreach ($guests as [$name, $phone, $group, $pax]) {
            $wedding->guests()->create([
                'name' => $name, 'phone' => $phone, 'group_name' => $group, 'max_pax' => $pax,
            ]);
        }

        foreach ($wedding->guests()->take(3)->get() as $i => $guest) {
            $wedding->rsvps()->create([
                'wedding_guest_id' => $guest->id,
                'name' => $guest->name,
                'attendance' => $i === 2 ? 'ragu' : 'hadir',
                'guests_count' => $guest->max_pax,
                'message' => 'Insya Allah hadir.',
            ]);
            $wedding->wishes()->create([
                'wedding_guest_id' => $guest->id,
                'name' => $guest->name,
                'message' => 'Barakallahu lakuma wa baraka alaikuma wa jamaa bainakuma fii khair.',
                'status' => 'approved',
            ]);
        }
    }
}
