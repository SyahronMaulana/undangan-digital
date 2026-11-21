<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['palette' => 'array', 'is_active' => 'boolean'];
    }

    public function weddings(): HasMany
    {
        return $this->hasMany(Wedding::class);
    }

    public function meta(): array
    {
        return config('wedding.templates.'.$this->slug, []);
    }

    public function tagline(): string
    {
        return $this->meta()['tagline'] ?? $this->style_label;
    }

    public function description(): string
    {
        return $this->meta()['description'] ?? (string) $this->description;
    }

    public function fonts(): array
    {
        return $this->meta()['fonts'] ?? ['Cormorant Garamond', 'DM Sans'];
    }

    public function previewRoute(): string
    {
        return route('wedding.templates.preview', $this->slug);
    }

    public function useRoute(): string
    {
        return route('wedding.templates.use', $this->slug);
    }
}
