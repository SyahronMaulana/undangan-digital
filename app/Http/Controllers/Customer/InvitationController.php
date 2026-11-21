<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard.invitations.index', [
            'weddings' => $request->user()->weddings()->with('template')->latest()->get(),
        ]);
    }

    public function create(Request $request)
    {
        $package = $request->user()->activePackage();
        $used = $request->user()->weddings()->count();

        return view('dashboard.invitations.create', [
            'templates' => Template::where('is_active', true)->orderBy('sort_order')->get(),
            'limitReached' => $used >= $package->max_invitations,
            'package' => $package,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $package = $user->activePackage();

        if ($user->weddings()->count() >= $package->max_invitations) {
            return back()->withErrors([
                'limit' => "Paket {$package->name} hanya mendukung {$package->max_invitations} undangan. Tingkatkan paket untuk membuat lebih banyak.",
            ]);
        }

        $data = $request->validate([
            'groom_name' => ['required', 'string', 'max:80'],
            'bride_name' => ['required', 'string', 'max:80'],
            'slug' => [
                'required', 'string', 'min:3', 'max:60',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:weddings,slug',
                Rule::notIn(config('invitation.reserved_slugs')),
            ],
            'event_date' => ['nullable', 'date', 'after_or_equal:today'],
            'template_id' => ['required', Rule::exists('templates', 'id')->where('is_active', true)],
        ], [
            'slug.regex' => 'Alamat hanya boleh huruf kecil, angka, dan tanda hubung (contoh: andi-siti).',
            'slug.unique' => 'Alamat ini sudah dipakai. Coba yang lain.',
            'slug.not_in' => 'Alamat ini dicadangkan sistem. Coba yang lain.',
        ]);

        $wedding = new Wedding($data);
        $wedding->user_id = $user->id;
        $wedding->sections = config('invitation.default_sections');
        $wedding->design_settings = [];
        $wedding->status = Wedding::STATUS_DRAFT;
        $wedding->save();

        return redirect()->route('dashboard.invitations.show', $wedding)
            ->with('status', 'Undangan dibuat sebagai draf.');
    }

    public function show(Wedding $wedding)
    {
        Gate::authorize('view', $wedding);

        return view('dashboard.invitations.show', ['wedding' => $wedding->load('template')]);
    }

    public function publish(Wedding $wedding): RedirectResponse
    {
        Gate::authorize('publish', $wedding);

        $publishing = ! $wedding->isPublished();
        $wedding->status = $publishing ? Wedding::STATUS_PUBLISHED : Wedding::STATUS_DRAFT;
        $wedding->published_at = $publishing ? now() : null;
        $wedding->save();

        return back()->with('status', $publishing ? 'Undangan dipublikasikan.' : 'Undangan dikembalikan ke draf.');
    }

    public function destroy(Wedding $wedding): RedirectResponse
    {
        Gate::authorize('delete', $wedding);
        $wedding->delete();

        return redirect()->route('dashboard.invitations.index')->with('status', 'Undangan dihapus.');
    }
}
