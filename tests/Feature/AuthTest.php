<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    public function test_halaman_autentikasi_modul_berada_di_prefix_wedding(): void
    {
        $this->get('/wedding/login')->assertOk()->assertSee('Masuk');
        $this->get('/wedding/register')->assertOk()->assertSee('Daftar');

        // Route root platform tidak boleh diambil alih modul Wedding.
        $this->get('/login')->assertNotFound();
        $this->get('/register')->assertNotFound();
        $this->get('/dashboard')->assertNotFound();
    }

    public function test_registrasi_membuat_customer_dan_masuk_dasbor(): void
    {
        $response = $this->post('/wedding/register', [
            'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('wedding.dashboard'));
        $this->assertAuthenticated();
        $this->assertSame('customer', User::firstWhere('email', 'budi@example.com')->role);
    }

    public function test_role_tidak_bisa_diisi_lewat_registrasi(): void
    {
        $this->post('/wedding/register', [
            'name' => 'Jahat', 'email' => 'jahat@example.com', 'role' => 'super_admin',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ]);

        $this->assertSame('customer', User::firstWhere('email', 'jahat@example.com')->role);
    }

    public function test_login_customer_dan_admin_diarahkan_sesuai_role(): void
    {
        $customer = $this->makeCustomer();
        $this->post('/wedding/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('wedding.dashboard'));
        $this->post('/wedding/logout');

        $admin = $this->makeAdmin();
        $this->post('/wedding/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('wedding.admin.dashboard'));
    }

    public function test_login_gagal_dengan_kata_sandi_salah(): void
    {
        $customer = $this->makeCustomer();
        $this->from('/wedding/login')
            ->post('/wedding/login', ['email' => $customer->email, 'password' => 'salah'])
            ->assertRedirect('/wedding/login');
        $this->assertGuest();
    }

    public function test_logout_kembali_ke_beranda_modul(): void
    {
        $this->actingAs($this->makeCustomer())
            ->post('/wedding/logout')
            ->assertRedirect(route('wedding.home'));

        $this->assertGuest();
    }
}
