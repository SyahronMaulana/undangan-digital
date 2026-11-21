<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    public function test_beranda_platform_merespons(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_beranda_modul_wedding_merespons(): void
    {
        $this->get('/wedding')->assertOk();
    }
}
