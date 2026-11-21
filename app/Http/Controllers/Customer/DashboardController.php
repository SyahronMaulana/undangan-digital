<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\WeddingGuest;
use App\Models\WeddingRsvp;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ids = $user->weddings()->pluck('id'); // hanya undangan milik user ini

        $tamu = WeddingGuest::whereIn('wedding_id', $ids)->count();

        $stats = [
            'undangan' => $ids->count(),
            'tamu' => $tamu,
            'hadir' => WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::HADIR)->count(),
            'tidak_hadir' => WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::TIDAK_HADIR)->count(),
            'belum' => WeddingGuest::whereIn('wedding_id', $ids)->whereDoesntHave('rsvp')->count(),
            'orang_hadir' => (int) WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::HADIR)->sum('guests_count'),
        ];

        return view('dashboard.index', [
            'stats' => $stats,
            'package' => $user->activePackage(),
            'recent' => $user->weddings()->with('template')->latest()->take(3)->get(),
        ]);
    }
}
