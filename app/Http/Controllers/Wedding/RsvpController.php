<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingGuest;
use App\Models\WeddingRsvp;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $weddings = $user->weddings()->with('template')->get();
        $wedding = $request->filled('wedding')
            ? $user->weddings()->with('template')->findOrFail($request->integer('wedding'))
            : null;
        $ids = $wedding ? collect([$wedding->id]) : $weddings->pluck('id');

        $rsvps = WeddingRsvp::with(['guest', 'wedding'])
            ->whereIn('wedding_id', $ids)
            ->when($request->filled('attendance'), fn ($q) => $q->where('attendance', $request->string('attendance')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where('name', 'like', $term);
            })
            ->latest()->paginate(20)->withQueryString();

        $summary = [
            'hadir' => WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::HADIR)->count(),
            'tidak_hadir' => WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::TIDAK_HADIR)->count(),
            'ragu' => WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::RAGU)->count(),
            'orang' => (int) WeddingRsvp::whereIn('wedding_id', $ids)->where('attendance', WeddingRsvp::HADIR)->sum('guests_count'),
        ];

        $guestTotal = WeddingGuest::whereIn('wedding_id', $ids)->count();
        $summary['belum'] = max(0, $guestTotal - WeddingRsvp::whereIn('wedding_id', $ids)->whereNotNull('wedding_guest_id')->count());

        return view('wedding.rsvp.index', compact('weddings', 'wedding', 'rsvps', 'summary'));
    }
}
