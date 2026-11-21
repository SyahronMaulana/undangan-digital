<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Template pesan Broadcast WhatsApp.
 *
 * Satu baris = satu template pesan yang dapat dipakai berulang kali. Tidak ada
 * daftar penerima maupun pesan per tamu; pesan yang dibagikan sama untuk semua.
 */
class BroadcastCampaign extends Model
{
    protected $guarded = ['id', 'wedding_id'];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }
}
