<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\WeddingGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $wedding = Wedding::with(['template', 'events', 'wishes' => fn ($q) => $q->where('is_approved', true)->latest()])->where('slug', $slug)->where('status', 'published')->firstOrFail();
        $guest = $request->filled('token') ? $wedding->guests()->where('token', $request->string('token'))->first() : null;
        return view('invitations.show', compact('wedding', 'guest'));
    }

    public function demo(string $template)
    {
        abort_unless(in_array($template, config('invitation.templates')), 404);
        $wedding = new Wedding(['slug' => 'demo', 'groom_name' => 'Rangga', 'bride_name' => 'Ayu', 'groom_parents' => 'Bapak Hadi & Ibu Nita', 'bride_parents' => 'Bapak Budi & Ibu Rani', 'event_date' => now()->addMonths(3), 'language_style' => 'islami', 'opening_text' => "Dengan memohon rahmat dan ridho Allah Subhanahu Wa Ta'ala, kami mengundang Bapak/Ibu/Saudara/i untuk hadir.", 'quote_text' => 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan hidup dari jenismu sendiri. (QS. Ar-Rum: 21)']);
        $wedding->setRelation('template', (object) ['slug' => $template, 'name' => ucfirst($template)]);
        $wedding->setRelation('events', collect([(object) ['title' => 'Akad Nikah', 'starts_at' => now()->addMonths(3)->setTime(8, 0), 'location' => 'Pendopo Kusuma', 'address' => 'Jakarta Selatan', 'maps_url' => 'https://maps.google.com'] ]));
        $guest = (object) ['name' => 'Tamu Undangan'];
        return view('invitations.show', compact('wedding', 'guest'));
    }

    public function rsvp(Request $request, Wedding $wedding)
    {
        $data = $request->validate(['name'=>'required|string|max:100','attendance'=>'required|in:hadir,tidak_hadir,belum_pasti','guest_count'=>'required|integer|min:1|max:10','message'=>'nullable|string|max:1000','token'=>'nullable|string|max:64']);
        $guest = !empty($data['token']) ? $wedding->guests()->where('token', $data['token'])->first() : null;
        $wedding->rsvps()->create([...$data, 'wedding_guest_id' => $guest?->id]);
        return back()->with('success', 'Konfirmasi kehadiran berhasil disimpan. Terima kasih.');
    }

    public function wish(Request $request, Wedding $wedding)
    {
        $data = $request->validate(['name'=>'required|string|max:100','message'=>'required|string|max:1000','token'=>'nullable|string|max:64']);
        $guest = !empty($data['token']) ? $wedding->guests()->where('token', $data['token'])->first() : null;
        $wedding->wishes()->create([...$data, 'wedding_guest_id' => $guest?->id, 'is_approved' => false]);
        return back()->with('success', 'Ucapan Anda telah dikirim dan menunggu moderasi.');
    }
}
