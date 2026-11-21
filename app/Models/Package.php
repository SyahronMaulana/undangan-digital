<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'allow_music' => 'boolean',
            'allow_whatsapp' => 'boolean',
            'allow_export' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function priceLabel(): string
    {
        return $this->price > 0 ? 'Rp'.number_format($this->price, 0, ',', '.') : 'Gratis';
    }
}
