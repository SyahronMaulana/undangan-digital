<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesWeddings;
use Tests\TestCase;

class TemplateCatalogTest extends TestCase
{
    use RefreshDatabase, CreatesWeddings;

    private const TEMPLATES = ['melati', 'senja', 'lestari', 'maharaja', 'arum', 'puspita', 'asmara', 'nirmala'];

    public function test_beranda_modul_dan_beranda_platform_terpisah(): void
    {
        $this->get('/')->assertOk()->assertSee('Markas Punokawan', false);
        $this->get('/wedding')->assertOk()->assertSee('Delapan desain', false);
    }

    public function test_katalog_menampilkan_delapan_template_dengan_preview_asli(): void
    {
        $response = $this->get('/wedding/templates')->assertOk();

        foreach (self::TEMPLATES as $slug) {
            $response->assertSee(route('wedding.templates.preview', $slug), false);
        }

        $response->assertSee('Lihat Desain')->assertSee('Gunakan Template');
    }

    public function test_semua_preview_template_dapat_dirender_dengan_css_dan_class_masing_masing(): void
    {
        foreach (self::TEMPLATES as $slug) {
            $this->get("/wedding/templates/{$slug}/preview")
                ->assertOk()
                ->assertSee('tpl-'.$slug, false)
                ->assertSee("wedding-assets/assets/templates/{$slug}.css", false)
                ->assertSee('wedding-assets/assets/templates/base.css', false)
                ->assertSee('demo-'.$slug, false);
        }
    }

    public function test_halaman_detail_template_dapat_dibuka(): void
    {
        foreach (self::TEMPLATES as $slug) {
            $this->get("/wedding/templates/{$slug}")
                ->assertOk()
                ->assertSee('Gunakan Template Ini');
        }
    }

    public function test_template_tidak_dikenal_menghasilkan_404(): void
    {
        $this->get('/wedding/templates/tidak-ada')->assertNotFound();
        $this->get('/wedding/templates/tidak-ada/preview')->assertNotFound();
    }

    public function test_gunakan_template_mengarahkan_tamu_ke_login_modul(): void
    {
        $this->get('/wedding/templates/melati/gunakan')
            ->assertRedirect(route('wedding.login'));
    }

    public function test_gunakan_template_mengarahkan_customer_ke_form_dengan_template_terpilih(): void
    {
        $user = $this->makeCustomer();

        $this->actingAs($user)
            ->get('/wedding/templates/senja/gunakan')
            ->assertRedirect(route('wedding.invitations.create', ['template' => 'senja']));
    }

    public function test_admin_diarahkan_ke_kelola_template(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get('/wedding/templates/melati/gunakan')
            ->assertRedirect(route('wedding.admin.templates.index'));
    }

    public function test_css_dan_js_setiap_template_tersedia_di_folder_aset_modul(): void
    {
        foreach (self::TEMPLATES as $slug) {
            $this->assertFileExists(public_path("wedding-assets/assets/templates/{$slug}.css"));
            $this->assertFileExists(public_path("wedding-assets/assets/templates/{$slug}.js"));
        }

        $this->assertFileExists(public_path('wedding-assets/assets/templates/base.css'));
        $this->assertFileExists(public_path('wedding-assets/assets/js/invitation-core.js'));
        $this->assertFileExists(public_path('wedding-assets/assets/css/site.css'));

        // Folder public/wedding tidak boleh ada karena akan menutup route /wedding.
        $this->assertDirectoryDoesNotExist(public_path('wedding'));
    }
}
