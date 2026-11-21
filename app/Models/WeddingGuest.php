<?php

namespace App\Models;

use App\Support\Phone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class WeddingGuest extends Model
{
    protected $guarded = ['id', 'wedding_id', 'token'];

    protected function casts(): array
    {
        return ['opened_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (WeddingGuest $guest) {
            // Token acak 40 karakter (kriptografis) unik per tamu.
            do {
                $guest->token = $guest->token ?: Str::random(40);
            } while (self::where('token', $guest->token)->exists());
            $guest->phone = Phone::normalize($guest->phone);
        });

        static::updating(function (WeddingGuest $guest) {
            if ($guest->isDirty('phone')) {
                $guest->phone = Phone::normalize($guest->phone);
            }
        });
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function rsvp(): HasOne
    {
        return $this->hasOne(WeddingRsvp::class, 'wedding_guest_id');
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(WeddingWish::class, 'wedding_guest_id');
    }

    public function link(): string
    {
        return $this->wedding->invitationUrl($this);
    }

    public function waPhone(): string
    {
        return preg_replace('/\D+/', '', (string) $this->phone);
    }
}
