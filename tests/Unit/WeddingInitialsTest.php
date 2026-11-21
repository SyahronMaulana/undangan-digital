<?php

namespace Tests\Unit;

use App\Models\Wedding;
use PHPUnit\Framework\TestCase;

/**
 * Inisial nama dipakai sebagai pengganti foto mempelai. Selalu ada isinya
 * (tidak pernah kosong / "null" / "undefined") apa pun bentuk namanya.
 */
class WeddingInitialsTest extends TestCase
{
    public function test_dua_kata_memakai_huruf_pertama_dan_terakhir(): void
    {
        $this->assertSame('AF', Wedding::initials('Ahmad Fauzi'));
        $this->assertSame('SR', Wedding::initials('Siti Rahma'));
    }

    public function test_satu_kata_memakai_dua_huruf_pertama(): void
    {
        $this->assertSame('SI', Wedding::initials('Siti'));
        $this->assertSame('AN', Wedding::initials('Andi'));
    }

    public function test_lebih_dari_dua_kata_memakai_kata_pertama_dan_terakhir(): void
    {
        $this->assertSame('RF', Wedding::initials('R. Ahmad Fauzi'));
        $this->assertSame('KW', Wedding::initials('Ki Ageng Sékar Wulandari'));
    }

    public function test_membersihkan_spasi_dan_tanda_baca(): void
    {
        $this->assertSame('AF', Wedding::initials('  Ahmad   Fauzi  '));
        $this->assertSame('AF', Wedding::initials('Ahmad, Fauzi.'));
    }

    public function test_selalu_mengembalikan_isi_untuk_nama_kosong(): void
    {
        $this->assertSame('?', Wedding::initials(null));
        $this->assertSame('?', Wedding::initials(''));
        $this->assertSame('?', Wedding::initials('   '));
        $this->assertSame('?', Wedding::initials('...'));
    }

    public function test_tidak_pernah_mengembalikan_string_kosong(): void
    {
        foreach (['', ' ', null, 'A', 'Andi', 'Ahmad Fauzi', 'R. Ahmad Fauzi', '123'] as $name) {
            $this->assertNotSame('', Wedding::initials($name));
        }
    }
}
