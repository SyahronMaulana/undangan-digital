<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingRsvp extends Model
{
    public const HADIR = 'hadir';
    public const TIDAK_HADIR = 'tidak_hadir';
    public const RAGU = 'ragu';

    public const ATTENDANCE_LABELS = [
        self::HADIR => 'Akan hadir',
        self::TIDAK_HADIR => 'Tidak dapat hadir',
        self::RAGU => 'Belum dapat memastikan',
    ];

    protected $guarded = ['id', 'wedding_id'];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(WeddingGuest::class, 'wedding_guest_id');
    }

    public function attendanceLabel(): string
    {
        return self::ATTENDANCE_LABELS[$this->attendance] ?? $this->attendance;
    }
}
