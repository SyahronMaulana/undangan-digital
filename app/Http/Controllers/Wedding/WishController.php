<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $weddings = $user->weddings()->with('template')->get();
        $wedding = $request->filled('wedding')
            ? $user->weddings()->findOrFail($request->integer('wedding'))
            : null;
        $ids = $wedding ? collect([$wedding->id]) : $weddings->pluck('id');

        $wishes = WeddingWish::with(['guest', 'wedding'])
            ->whereIn('wedding_id', $ids)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn ($inner) => $inner->where('name', 'like', $term)->orWhere('message', 'like', $term));
            })
            ->latest()->paginate(20)->withQueryString();

        return view('wedding.wishes.index', [
            'weddings' => $weddings,
            'wedding' => $wedding,
            'wishes' => $wishes,
            'counts' => [
                'all' => WeddingWish::whereIn('wedding_id', $ids)->count(),
                'approved' => WeddingWish::whereIn('wedding_id', $ids)->where('status', 'approved')->count(),
                'hidden' => WeddingWish::whereIn('wedding_id', $ids)->where('status', 'hidden')->count(),
            ],
        ]);
    }

    public function moderate(Request $request, WeddingWish $wish): RedirectResponse
    {
        abort_unless($wish->wedding->user_id === $request->user()->id, 404);
        $data = $request->validate([
            'status' => ['required', 'in:approved,hidden'],
        ]);
        $wish->update(['status' => $data['status']]);

        return back()->with('status', $data['status'] === 'approved' ? 'Ucapan ditampilkan.' : 'Ucapan disembunyikan.');
    }

    public function destroy(Request $request, WeddingWish $wish): RedirectResponse
    {
        abort_unless($wish->wedding->user_id === $request->user()->id, 404);
        $wish->delete();

        return back()->with('status', 'Ucapan dihapus.');
    }
}
