<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingGuest;
use App\Models\WeddingRsvp;
use App\Models\WeddingWish;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ids = $user->weddings()->pluck('id');
        $upcoming = $user->weddings()
            ->where('status', 'published')
            ->whereNotNull('event_date')
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->first();

        $stats = [
            'undangan' => $ids->count(),
            'draft' => $user->weddings()->where('status', 'draft')->count(),
            'tamu' => WeddingGuest::whereIn('wedding_id', $ids)->count(),
            'hadir' => WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::HADIR)->count(),
            'tidak_hadir' => WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::TIDAK_HADIR)->count(),
            'belum' => WeddingGuest::whereIn('wedding_id', $ids)->whereDoesntHave('rsvp')->count(),
            'orang_hadir' => (int) WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::HADIR)->sum('guests_count'),
            'ucapan' => WeddingWish::whereIn('wedding_id', $ids)->count(),
        ];

        return view('wedding.dashboard.index', [
            'stats' => $stats,
            'recent' => $user->weddings()->with('template')->latest()->take(4)->get(),
            'upcoming' => $upcoming?->load('template', 'events'),
            // Undangan dengan pengajuan publikasi aktif (belum draf, belum terbit),
            // dipakai untuk menampilkan status + tombol WhatsApp admin di dasbor.
            'publication' => $user->weddings()
                ->with('template')
                ->whereNotIn('submission_status', [Wedding::SUBMISSION_DRAFT, Wedding::SUBMISSION_PUBLISHED])
                ->latest('submitted_at')
                ->latest('id')
                ->first(),
            'recentWishes' => WeddingWish::whereIn('wedding_id', $ids)
                ->latest()->take(4)->get(),
            'guestTotal' => $stats['tamu'],
        ]);
    }
}
