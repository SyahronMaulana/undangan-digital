<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingPhoto extends Model
{
    protected $guarded = ['id', 'wedding_id'];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }
}
