<?php

namespace App\Http\Controllers\Wedding\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Support\AdminWhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Menu PENGAJUAN PUBLIKASI (Super Admin).
 * Customer hanya mengajukan; Admin memverifikasi pembayaran manual lalu mempublikasikan.
 */
class PublicationController extends Controller
{
    public function index(Request $request)
    {
        $submissions = Wedding::with(['user', 'template'])
            ->withCount(['guests', 'rsvps', 'wishes'])
            ->whereNotIn('submission_status', [Wedding::SUBMISSION_DRAFT])
            ->when($request->filled('submission'), fn ($q) => $q->where('submission_status', $request->string('submission')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn ($inner) => $inner->where('slug', 'like', $term)->orWhere('groom_name', 'like', $term)->orWhere('bride_name', 'like', $term));
            })
            ->orderByRaw("CASE submission_status
                WHEN 'menunggu_konfirmasi' THEN 1
                WHEN 'menunggu_verifikasi' THEN 2
                WHEN 'menunggu_pembayaran' THEN 3
                WHEN 'pembayaran_dikonfirmasi' THEN 4
                WHEN 'revisi' THEN 5
                ELSE 6 END")
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('wedding.admin.publications', [
            'submissions' => $submissions,
            'statusCounts' => [
                'menunggu_konfirmasi' => Wedding::where('submission_status', Wedding::SUBMISSION_WAITING_CONFIRMATION)->count(),
                'menunggu_pembayaran' => Wedding::where('submission_status', Wedding::SUBMISSION_WAITING_PAYMENT)->count(),
                'menunggu_verifikasi' => Wedding::where('submission_status', Wedding::SUBMISSION_WAITING_VERIFICATION)->count(),
                'pembayaran_dikonfirmasi' => Wedding::where('submission_status', Wedding::SUBMISSION_PAYMENT_CONFIRMED)->count(),
                'revisi' => Wedding::where('submission_status', Wedding::SUBMISSION_REVISION)->count(),
            ],
        ]);
    }

    public function show(Wedding $wedding)
    {
        abort_unless($wedding->submissionStatus() !== Wedding::SUBMISSION_DRAFT, 404);

        $wedding->load(['user', 'template', 'events', 'photos', 'gifts', 'paymentVerifier', 'publisher']);

        return view('wedding.admin.publication-show', [
            'wedding' => $wedding,
            'issues' => $wedding->publicationIssues(),
            'paymentNote' => \App\Models\WeddingSetting::get('manual_payment_note', ''),
            'customerWaUrl' => $this->customerWaUrl($wedding),
        ]);
    }

    /** Preview undangan apa adanya (draf sekalipun) untuk Super Admin. */
    public function preview(Wedding $wedding)
    {
        $wedding->load(['template', 'events', 'stories', 'photos', 'gifts']);

        $template = $wedding->template?->slug ?? 'melati';
        if (! view()->exists('wedding.templates.'.$template.'.index')) {
            $template = 'melati';
        }

        return response()
            ->view('wedding.templates.'.$template.'.index', [
                'wedding' => $wedding,
                'recipientName' => 'Tamu Undangan',
                'guest' => null,
                'myRsvp' => null,
                'isPreview' => true,
                'previewMode' => false,
                'embed' => false,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /** Admin mengirim instruksi pembayaran manual (via chat) → Menunggu Pembayaran. */
    public function paymentInstruction(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->ensureSubmission($wedding);

        if ($wedding->isPublished()) {
            return back()->withErrors(['payment' => 'Undangan sudah dipublikasikan.']);
        }

        DB::transaction(fn () => $wedding->markPaymentWaiting());

        return back()->with('status', 'Instruksi pembayaran dicatat. Status undangan: Menunggu Pembayaran.');
    }

    /** Admin menandai pembayaran diterima — hanya setelah benar-benar mengecek pembayaran. */
    public function confirmPayment(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->ensureSubmission($wedding);

        $request->validate([
            'payment_checked' => ['accepted'],
        ], [
            'payment_checked.accepted' => 'Centang konfirmasi bahwa Anda sudah benar-benar memeriksa pembayaran customer.',
        ]);

        if ($wedding->isPublished()) {
            return back()->withErrors(['payment' => 'Undangan sudah dipublikasikan.']);
        }

        DB::transaction(fn () => $wedding->confirmPayment($request->user()->id));

        return back()->with('status', 'Pembayaran dikonfirmasi. Undangan siap dipublikasikan.');
    }

    /** Admin meminta revisi. */
    public function requestRevision(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->ensureSubmission($wedding);

        $data = $request->validate([
            'revision_notes' => ['required', 'string', 'max:2000'],
        ], [
            'revision_notes.required' => 'Tuliskan catatan revisi untuk customer.',
        ]);

        DB::transaction(fn () => $wedding->markRevision($data['revision_notes']));

        return back()->with('status', 'Permintaan revisi dikirim ke customer.');
    }

    /** Publikasikan — hanya setelah semua syarat terpenuhi. */
    public function publish(Request $request, Wedding $wedding): RedirectResponse
    {        $this->ensureSubmission($wedding);

        $problems = [];

        if ($wedding->isPublished()) {
            $problems[] = 'Undangan sudah dipublikasikan.';
        }
        if (! $wedding->hasActiveSubmission()) {
            $problems[] = 'Tidak ada pengajuan publikasi yang aktif.';
        }
        if (! $wedding->isPaymentConfirmed()) {
            $problems[] = 'Pembayaran belum dikonfirmasi oleh Admin.';
        }
        foreach ($wedding->publicationIssues() as $issue) {
            $problems[] = $issue;
        }
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $wedding->slug)) {
            $problems[] = 'Slug tidak valid.';
        }
        if (Wedding::where('slug', $wedding->slug)->whereKeyNot($wedding->id)->exists()) {
            $problems[] = 'Slug sudah dipakai undangan lain.';
        }

        if ($problems !== []) {
            throw ValidationException::withMessages(['publish' => $problems]);
        }

        DB::transaction(function () use ($wedding, $request) {
            $wedding->markPublished($request->user()->id);
        });

        return redirect()->route('wedding.admin.publications.index')
            ->with('status', 'Undangan '.$wedding->coupleNames().' berhasil dipublikasikan.');
    }

    /**
     * Admin menarik/membatalkan publikasi. Akses publik langsung ditutup
     * (PublicInvitationController menolak semua status selain published).
     */
    public function unpublish(Request $request, Wedding $wedding): RedirectResponse
    {
        $data = $request->validate([
            'unpublish_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $wedding->isPublished()) {
            return back()->withErrors(['publish' => 'Undangan ini belum dipublikasikan.']);
        }

        DB::transaction(fn () => $wedding->markUnpublished($data['unpublish_notes'] ?? null));

        return back()->with('status', 'Publikasi undangan '.$wedding->coupleNames().' ditarik. Link publik kini tidak dapat diakses.');
    }

    private function ensureSubmission(Wedding $wedding): void
    {
        abort_unless($wedding->submissionStatus() !== Wedding::SUBMISSION_DRAFT, 404);
    }

    private function customerWaUrl(Wedding $wedding): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $wedding->user?->phone) ?: '';
        if ($phone === '') {
            return null;
        }

        $message = "Halo {$wedding->user?->name}, terima kasih telah mengajukan publikasi undangan {$wedding->coupleNames()}.\n\n"
            .\App\Models\WeddingSetting::get('manual_payment_note', '');
        $message .= "\n\nSetelah pembayaran dikonfirmasi, undangan Anda akan segera dipublikasikan.";

        return AdminWhatsApp::customerUrl($wedding, $message);
    }
}
