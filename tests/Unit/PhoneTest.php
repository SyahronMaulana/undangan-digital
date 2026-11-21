<?php

namespace Tests\Unit;

use App\Support\Phone;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function test_normalisasi_nomor_indonesia(): void
    {
        $this->assertSame('+6281234567890', Phone::normalize('081234567890'));
        $this->assertSame('+6281234567890', Phone::normalize('0812-3456-7890'));
        $this->assertSame('+6281234567890', Phone::normalize('6281234567890'));
        $this->assertSame('+6281234567890', Phone::normalize('+62 812 3456 7890'));
        $this->assertSame('+6281234567890', Phone::normalize('81234567890'));
        $this->assertSame('+6281234567890', Phone::normalize('620812 3456 7890'));
        $this->assertNull(Phone::normalize('   '));
        $this->assertNull(Phone::normalize(null));
    }
}
