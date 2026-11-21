<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\Request;

class PublicInvitationController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $wedding = Wedding::with([
            'template', 'events', 'stories', 'photos', 'gifts',
            'wishes' => fn ($q) => $q->where('status', 'approved')->latest(),
        ])->where('slug', $slug)->firstOrFail();

        $isOwner = $request->user() && $request->user()->id === $wedding->user_id;

        // Draf hanya dapat dilihat pemiliknya.
        if (! $wedding->isPublished() && ! $isOwner) {
            abort(404);
        }

        // Nama penerima HANYA dari database lewat token valid milik undangan ini.
        // Parameter "to" di URL tidak pernah dipercaya.
        $guest = null;
        $token = $request->query('token');
        if (is_string($token) && $token !== '' && strlen($token) <= 64) {
            $guest = $wedding->guests()->where('token', $token)->first();

            if ($guest && ! $guest->opened_at && $wedding->isPublished()) {
                $guest->forceFill(['opened_at' => now()])->save();
            }
        }

        $view = 'invitations.templates.'.$wedding->template->slug;
        if (! view()->exists($view)) {
            $view = 'invitations.templates.placeholder';
        }

        return response()
            ->view($view, [
                'wedding' => $wedding,
                'recipientName' => $guest?->name ?? 'Tamu Undangan',
                'guest' => $guest,
                'isPreview' => ! $wedding->isPublished(),
            ])
            ->header('X-Robots-Tag', $guest ? 'noindex, nofollow' : 'index, follow');
    }
}
