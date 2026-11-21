<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    public function test_tamu_diarahkan_ke_login_modul(): void
    {
        $this->get('/wedding/dashboard')->assertRedirect(route('wedding.login'));
        $this->get('/wedding/admin')->assertRedirect(route('wedding.login'));
        $this->get('/wedding/invitations')->assertRedirect(route('wedding.login'));
    }

    public function test_customer_tidak_bisa_membuka_admin(): void
    {
        $this->actingAs($this->makeCustomer())->get('/wedding/admin')->assertForbidden();
        $this->actingAs($this->makeCustomer())->get('/wedding/admin/customers')->assertForbidden();
    }

    public function test_admin_bisa_membuka_admin_dan_tidak_masuk_dasbor_customer(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get('/wedding/admin')->assertOk();
        $this->actingAs($admin)->get('/wedding/dashboard')->assertForbidden();
    }

    public function test_customer_membuka_dasbor(): void
    {
        $this->actingAs($this->makeCustomer())
            ->get('/wedding/dashboard')
            ->assertOk()
            ->assertSee('Undangan Saya');
    }

    public function test_semua_route_modul_memakai_prefix_wedding(): void
    {
        $uris = collect(app('router')->getRoutes()->getRoutes())
            ->map(fn ($route) => $route->uri())
            ->reject(fn ($uri) => in_array($uri, ['/', 'up'], true) || str_starts_with($uri, 'storage/'));

        $this->assertTrue($uris->isNotEmpty());
        $this->assertTrue(
            $uris->every(fn ($uri) => str_starts_with($uri, 'wedding')),
            'Ada route modul yang keluar dari prefix wedding: '.$uris->reject(fn ($uri) => str_starts_with($uri, 'wedding'))->implode(', ')
        );
    }
}
