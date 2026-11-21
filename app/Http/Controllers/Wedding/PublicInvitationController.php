<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingGuest;
use App\Models\WeddingRsvp;
use App\Models\WeddingSetting;
use App\Models\WeddingWish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicInvitationController extends Controller
{
    public function show(Request $request, string $slug)
    {
        // HANYA undangan yang benar-benar diterbitkan Admin boleh diakses publik.
        // Status draft / pengajuan / pembayaran dikonfirmasi TIDAK cukup. Pemilik
        // dan Admin memakai route preview internal yang dilindungi autentikasi.
        $wedding = Wedding::with([
            'template', 'events', 'stories', 'photos', 'gifts',
            'wishes' => fn ($q) => $q->where('status', 'approved')->latest()->take(60),
        ])->where('slug', $slug)->firstOrFail();

        if (! $wedding->isPublished()) {
            abort(404);
        }

        $guest = $this->guestFromToken($request, $wedding);
        $rsvp = $guest?->rsvp;

        // Nama penerima: dari token tamu (paling tepercaya), atau dari parameter
        // ?to= pada URL (mis. ?to=Budi%20Santoso). Tidak disimpan ke data undangan.
        $recipientName = $guest?->name ?: $this->recipientFromQuery($request);

        if ($guest && ! $guest->opened_at && $wedding->isPublished()) {
            $guest->forceFill(['opened_at' => now()])->save();
        }

        if ($wedding->isPublished()) {
            $sessionKey = 'wedding_viewed_'.$wedding->id;
            if (! $request->session()->has($sessionKey)) {
                $wedding->increment('views_count');
                $request->session()->put($sessionKey, true);
            }
        }

        $template = $wedding->template?->slug ?? 'melati';
        if (! view()->exists('wedding.templates.'.$template.'.index')) {
            $template = 'melati';
        }

        return response()
            ->view('wedding.templates.'.$template.'.index', [
                'wedding' => $wedding,
                'recipientName' => $recipientName,
                'guest' => $guest,
                'myRsvp' => $rsvp,
                'isPreview' => false,
                'previewMode' => false,
                'embed' => false,
            ])
            ->header('X-Robots-Tag', ($guest || $request->filled('to')) ? 'noindex, nofollow' : 'index, follow');
    }

    public function rsvp(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $wedding = Wedding::where('slug', $slug)->where('status', Wedding::STATUS_PUBLISHED)->firstOrFail();
        $guest = $this->guestFromToken($request, $wedding);

        $data = $request->validate([
            'name' => [$guest ? 'nullable' : 'required', 'string', 'max:100'],
            'attendance' => ['required', Rule::in(array_keys(WeddingRsvp::ATTENDANCE_LABELS))],
            'guests_count' => ['required', 'integer', 'min:1', 'max:20'],
            'message' => ['nullable', 'string', 'max:1000'],
        ], [
            'attendance.required' => 'Silakan pilih konfirmasi kehadiran.',
            'attendance.in' => 'Pilihan konfirmasi tidak valid.',
        ]);

        $name = $guest?->name ?: trim($data['name']);
        if ($name === '') {
            return back()->withErrors(['name' => 'Nama wajib diisi.'])->withInput();
        }

        $attributes = [
            'name' => $name,
            'attendance' => $data['attendance'],
            'guests_count' => $data['attendance'] === WeddingRsvp::HADIR ? $data['guests_count'] : 1,
            'message' => $data['message'] ?? null,
        ];

        if ($guest) {
            $wedding->rsvps()->updateOrCreate(['wedding_guest_id' => $guest->id], $attributes);
        } else {
            $wedding->rsvps()->create($attributes);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Terima kasih, konfirmasi kehadiran Anda telah kami terima.']);
        }

        return redirect()->to($wedding->invitationUrl($guest).'#konfirmasi')
            ->with('rsvp_success', 'Terima kasih, konfirmasi kehadiran Anda telah kami terima.');
    }

    public function wish(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $wedding = Wedding::where('slug', $slug)->where('status', Wedding::STATUS_PUBLISHED)->firstOrFail();
        $guest = $this->guestFromToken($request, $wedding);

        $data = $request->validate([
            'name' => [$guest ? 'nullable' : 'required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:600'],
        ]);

        $name = $guest?->name ?: trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            return back()->withErrors(['name' => 'Nama wajib diisi.'])->withInput();
        }

        $status = WeddingSetting::get('wish_moderation', 'post') === 'pre' ? 'hidden' : 'approved';

        $wedding->wishes()->create([
            'wedding_guest_id' => $guest?->id,
            'name' => $name,
            'message' => $data['message'],
            'status' => $status,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $status === 'approved'
                    ? 'Ucapan dan doa Anda telah dikirim.'
                    : 'Ucapan Anda dikirim dan menunggu moderasi.',
            ]);
        }

        return redirect()->to($wedding->invitationUrl($guest).'#ucapan')
            ->with('wish_success', $status === 'approved'
                ? 'Terima kasih, ucapan dan doa Anda telah kami terima.'
                : 'Terima kasih, ucapan Anda menunggu moderasi.');
    }

    /**
     * Nama penerima dari parameter ?to= pada URL. Nilai tidak dipercaya: hanya
     * dipakai sebagai teks tampilan (di-escape oleh Blade), dipangkas, dan
     * dibatasi panjangnya. Kosong/tidak valid → "Tamu Undangan".
     */
    private function recipientFromQuery(Request $request): string
    {
        $raw = $request->query('to');
        if (! is_string($raw)) {
            return 'Tamu Undangan';
        }

        // Buang karakter kontrol, rapikan spasi ganda.
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $raw);
        $name = trim((string) preg_replace('/\s+/u', ' ', (string) $name));

        if ($name === '') {
            return 'Tamu Undangan';
        }

        $name = mb_substr($name, 0, 100);

        return $name !== '' ? $name : 'Tamu Undangan';
    }

    private function guestFromToken(Request $request, Wedding $wedding): ?WeddingGuest
    {
        $token = $request->query('token') ?: $request->input('token');
        if (! is_string($token) || $token === '' || strlen($token) > 64) {
            return null;
        }

        return $wedding->guests()->where('token', $token)->first();
    }
}
