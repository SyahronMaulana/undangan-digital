<?php

namespace App\Http\Controllers\Wedding\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use Illuminate\Http\Request;

class WeddingController extends Controller
{
    public function index(Request $request)
    {
        $weddings = Wedding::with(['user', 'template'])
            ->withCount(['guests', 'rsvps', 'wishes'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn ($inner) => $inner->where('slug', 'like', $term)->orWhere('groom_name', 'like', $term)->orWhere('bride_name', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('submission'), fn ($q) => $q->where('submission_status', $request->string('submission')))
            ->latest()->paginate(20)->withQueryString();

        return view('wedding.admin.weddings', compact('weddings'));
    }
}
