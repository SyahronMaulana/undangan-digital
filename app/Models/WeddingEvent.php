<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingEvent extends Model
{
    protected $guarded = ['id', 'wedding_id'];

    protected function casts(): array
    {
        return ['event_date' => 'date'];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /** Rentang waktu Indonesia, mis. "Pukul 10.00 WIB" atau "Pukul 10.00 – 12.00 WIB". */
    public function timeRange(): string
    {
        if (! $this->start_time) {
            return '';
        }

        $start = 'Pukul '.self::clock($this->start_time);

        return $this->end_time
            ? $start.' – '.self::clock($this->end_time).' WIB'
            : $start.' WIB';
    }

    /**
     * Jam dalam format Indonesia (10.00) tanpa konversi zona waktu. Nilai jam
     * disimpan apa adanya sebagai string "HH:MM" pada zona Asia/Jakarta.
     */
    public static function clock(?string $time): string
    {
        return str_replace(':', '.', substr((string) $time, 0, 5));
    }
}
