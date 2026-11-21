<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Models\BroadcastCampaign;
use App\Models\WeddingActivityLog;
use App\Services\Wedding\WhatsAppTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Broadcast WhatsApp yang sederhana: kumpulan TEMPLATE PESAN yang dapat
 * dipakai berulang kali lalu dibagikan lewat Web Share API (atau fallback
 * wa.me) sehingga pengguna memilih sendiri kontak penerimanya di WhatsApp.
 *
 * Tidak ada daftar penerima, nomor manual, maupun personalisasi nama tamu.
 * Riwayat hanya mencatat aktivitas berbagi (siapa, template apa, pesan apa,
 * kapan tombol bagikan ditekan) — bukan klaim bahwa pesan sudah terkirim.
 */
class BroadcastController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $weddings = $user->weddings()->with('template')->get();
        $wedding = $request->filled('wedding')
            ? $user->weddings()->findOrFail($request->integer('wedding'))
            : $weddings->first();
        $ids = $weddings->pluck('id');

        return view('wedding.broadcast.index', [
            'weddings' => $weddings,
            'wedding' => $wedding,
            'templates' => BroadcastCampaign::whereIn('wedding_id', $ids)->latest()->paginate(12),
            'history' => $this->recentShares($ids->all()),
            'defaultMessage' => WhatsAppTemplate::DEFAULT_MESSAGE,
            'variables' => WhatsAppTemplate::VARIABLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'wedding_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'message_template' => ['required', 'string', 'max:4000'],
        ]);

        $wedding = $user->weddings()->findOrFail($data['wedding_id']);
        $template = $wedding->campaigns()->create([
            'name' => $data['name'],
            'message_template' => $data['message_template'],
        ]);

        return redirect()->route('wedding.broadcast.show', $template)
            ->with('status', 'Template pesan disimpan. Tinjau lalu bagikan melalui WhatsApp.');
    }

    public function show(Request $request, BroadcastCampaign $campaign, WhatsAppTemplate $wa)
    {
        $wedding = $campaign->wedding()->with(['events', 'template'])->firstOrFail();
        abort_unless($wedding->user_id === $request->user()->id, 404);

        return view('wedding.broadcast.show', [
            'campaign' => $campaign,
            'wedding' => $wedding,
            'variables' => $wa->variables(),
            'map' => $wa->broadcastMap($wedding),
            'preview' => $wa->preview($wedding, $campaign->message_template),
            'history' => $this->recentShares([$wedding->id], $campaign->id),
        ]);
    }

    public function update(Request $request, BroadcastCampaign $campaign): RedirectResponse
    {
        $wedding = $campaign->wedding()->firstOrFail();
        abort_unless($wedding->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'message_template' => ['required', 'string', 'max:4000'],
        ]);

        $campaign->update([
            'name' => $data['name'],
            'message_template' => $data['message_template'],
        ]);

        return redirect()->route('wedding.broadcast.show', $campaign)
            ->with('status', 'Template pesan diperbarui.');
    }

    /**
     * Catat aktivitas berbagi. Dipanggil dari sisi klien tepat setelah pengguna
     * menekan tombol bagikan. Server TIDAK mengirim pesan apa pun.
     */
    public function share(Request $request, BroadcastCampaign $campaign, WhatsAppTemplate $wa): JsonResponse
    {
        $wedding = $campaign->wedding()->with('events')->firstOrFail();
        abort_unless($wedding->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'channel' => ['nullable', 'in:web_share,whatsapp'],
        ]);

        $message = $wa->broadcastMessage($wedding, $campaign->message_template);

        WeddingActivityLog::create([
            'wedding_id' => $wedding->id,
            'user_id' => $request->user()->id,
            'event' => 'broadcast_share',
            'description' => $campaign->name,
            'meta' => [
                'broadcast_campaign_id' => $campaign->id,
                'template_name' => $campaign->name,
                'message' => $message,
                'channel' => $data['channel'] ?? 'web_share',
            ],
        ]);

        return response()->json([
            'ok' => true,
            'url' => $wa->shareUrl($message),
        ]);
    }

    public function destroy(Request $request, BroadcastCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->wedding->user_id === $request->user()->id, 404);
        $campaign->delete();

        return redirect()->route('wedding.broadcast.index')->with('status', 'Template pesan dihapus.');
    }

    /** @param array<int, int|string> $weddingIds */
    private function recentShares(array $weddingIds, ?int $campaignId = null)
    {
        $logs = WeddingActivityLog::with('user')
            ->whereIn('wedding_id', $weddingIds)
            ->where('event', 'broadcast_share')
            ->latest()
            ->limit(60)
            ->get();

        if ($campaignId !== null) {
            $logs = $logs->filter(fn ($log) => (int) ($log->meta['broadcast_campaign_id'] ?? 0) === $campaignId);
        }

        return $logs->take(15)->values();
    }
}
