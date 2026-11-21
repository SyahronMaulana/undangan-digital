<?php

namespace Tests\Concerns;

use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingEvent;

trait CreatesWeddings
{
    protected function makeTemplate(string $slug = 'melati'): Template
    {
        return Template::firstOrCreate(['slug' => $slug], [
            'name' => ucfirst($slug), 'style_label' => 'Uji', 'palette' => ['#fff'], 'is_active' => true,
        ]);
    }

    protected function makeCustomer(): User
    {
        $user = User::factory()->create();
        $user->role = User::ROLE_CUSTOMER;
        $user->save();

        return $user;
    }

    protected function makeAdmin(): User
    {
        $user = User::factory()->create();
        $user->role = User::ROLE_ADMIN;
        $user->save();

        return $user;
    }

    protected function makeWedding(User $owner, string $slug = 'andi-siti', string $status = 'published'): Wedding
    {
        $wedding = new Wedding([
            'template_id' => $this->makeTemplate()->id,
            'slug' => $slug,
            'groom_name' => 'Andi',
            'groom_nickname' => 'Andi',
            'bride_name' => 'Siti',
            'bride_nickname' => 'Siti',
            'status' => $status,
            'event_date' => now()->addMonth(),
            'sections' => config('wedding.default_sections'),
        ]);
        $wedding->user_id = $owner->id;
        $wedding->save();

        $this->makeEvent($wedding);

        return $wedding;
    }

    /** Undangan draf dengan pengajuan publikasi aktif (Menunggu Konfirmasi). */
    protected function makeSubmittedWedding(User $owner, string $slug = 'andi-siti'): Wedding
    {
        $wedding = $this->makeWedding($owner, $slug, 'draft');
        $wedding->markSubmitted();

        return $wedding->refresh();
    }

    protected function makeEvent(Wedding $wedding, array $overrides = []): WeddingEvent
    {
        return $wedding->events()->create(array_merge([
            'name' => 'Akad Nikah',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'venue_name' => 'Pendopo Punokawan',
            'address' => 'Jl. Malioboro No. 12, Yogyakarta',
            'sort_order' => 1,
        ], $overrides));
    }
}
