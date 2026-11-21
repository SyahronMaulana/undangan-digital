<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingGuest;
use App\Services\Wedding\WhatsAppTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $weddings = $user->weddings()->with('template')->orderBy('event_date')->get();
        $wedding = $this->resolveWedding($request, $user);

        $query = WeddingGuest::query()
            ->when($wedding, fn ($q) => $q->where('wedding_id', $wedding->id))
            ->when(! $wedding, fn ($q) => $q->whereIn('wedding_id', $weddings->pluck('id')))
            ->with(['wedding', 'rsvp'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn ($inner) => $inner->where('name', 'like', $term)->orWhere('phone', 'like', $term));
            })
            ->when($request->filled('group'), fn ($q) => $q->where('group_name', $request->string('group')))
            ->when($request->filled('status'), function ($q) use ($request) {
                $status = $request->string('status')->toString();
                if ($status === 'sudah_rsvp') {
                    $q->whereHas('rsvp');
                } elseif ($status === 'belum_rsvp') {
                    $q->whereDoesntHave('rsvp');
                }
            })
            ->latest();

        return view('wedding.guests.index', [
            'weddings' => $weddings,
            'wedding' => $wedding,
            'guests' => $query->paginate(20)->withQueryString(),
            'groups' => WeddingGuest::when($wedding, fn ($q) => $q->where('wedding_id', $wedding->id))
                ->whereNotNull('group_name')->distinct()->orderBy('group_name')->pluck('group_name'),
            'totalGuests' => WeddingGuest::when($wedding, fn ($q) => $q->where('wedding_id', $wedding->id))
                ->when(! $wedding, fn ($q) => $q->whereIn('wedding_id', $weddings->pluck('id')))->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'wedding_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'group_name' => ['nullable', 'string', 'max:60'],
            'max_pax' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $wedding = $user->weddings()->findOrFail($data['wedding_id']);

        $wedding->guests()->create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'group_name' => $data['group_name'] ?? null,
            'max_pax' => $data['max_pax'] ?? 2,
        ]);

        return back()->with('status', 'Tamu berhasil ditambahkan.');
    }

    public function update(Request $request, WeddingGuest $guest): RedirectResponse
    {
        Gate::authorize('update', $guest->wedding);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'group_name' => ['nullable', 'string', 'max:60'],
            'max_pax' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);
        $guest->update($data);

        return back()->with('status', 'Data tamu diperbarui.');
    }

    public function destroy(WeddingGuest $guest): RedirectResponse
    {
        Gate::authorize('update', $guest->wedding);
        $guest->delete();

        return back()->with('status', 'Tamu dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->validate([
            'wedding_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $wedding = $user->weddings()->findOrFail($request->integer('wedding_id'));

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (! $handle) {
            return back()->withErrors(['file' => 'File tidak dapat dibaca.']);
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ',';
        if ($firstLine !== false) {
            foreach ([';' => ';', "\t" => "\t"] as $candidate) {
                if (substr_count($firstLine, $candidate) > substr_count($firstLine, $delimiter)) {
                    $delimiter = $candidate;
                }
            }
        }

        $header = fgetcsv($handle, 0, $delimiter) ?: [];
        $map = $this->mapHeaders($header);
        if (! isset($map['name'])) {
            fclose($handle);

            return back()->withErrors(['file' => 'Header CSV harus memiliki kolom nama. Contoh: nama,telepon,grup,kuota']);
        }

        $rows = [];
        $skipped = 0;
        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($rows) >= config('wedding.limits.import_rows', 2000)) {
                break;
            }
            $name = trim((string) ($line[$map['name']] ?? ''));
            if ($name === '') {
                $skipped++;

                continue;
            }
            $rows[] = [
                'name' => mb_substr($name, 0, 100),
                'phone' => $this->cell($line, $map, 'phone'),
                'group_name' => $this->cell($line, $map, 'group'),
                'max_pax' => max(1, min(20, (int) ($this->cell($line, $map, 'pax') ?: 2))),
            ];
        }
        fclose($handle);

        DB::transaction(function () use ($wedding, $rows) {
            foreach ($rows as $row) {
                $wedding->guests()->create($row);
            }
        });

        return back()->with('status', count($rows).' tamu berhasil diimpor'.($skipped ? ", {$skipped} baris dilewati" : '').'.');
    }

    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        $user = $request->user();
        $wedding = $this->resolveWedding($request, $user);
        $query = WeddingGuest::with(['wedding', 'rsvp'])
            ->when($wedding, fn ($q) => $q->where('wedding_id', $wedding->id))
            ->when(! $wedding, fn ($q) => $q->whereIn('wedding_id', $user->weddings()->pluck('id')))
            ->orderBy('name');

        $filename = 'tamu-'.($wedding?->slug ?? 'semua').'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['nama', 'telepon', 'grup', 'kuota', 'status_rsvp', 'jumlah_hadir', 'link_undangan']);
            foreach ($query->cursor() as $guest) {
                fputcsv($out, [
                    $guest->name,
                    $guest->phone,
                    $guest->group_name,
                    $guest->max_pax,
                    $guest->rsvp?->attendanceLabel() ?? 'Belum konfirmasi',
                    $guest->rsvp?->guests_count ?? 0,
                    $guest->link(),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function link(Request $request, WeddingGuest $guest, WhatsAppTemplate $wa): JsonResponse
    {
        Gate::authorize('view', $guest->wedding);

        $link = $guest->link();
        $message = $wa->render("Assalamu'alaikum {{nama_tamu}},\n\nKami mengundang Anda untuk hadir di pernikahan {{nama_pria}} & {{nama_wanita}}.\n\n{{link_undangan}}", $guest->wedding, $guest);

        return response()->json([
            'link' => $link,
            'wa' => $guest->phone ? $wa->waUrl($guest->phone, $message) : null,
        ]);
    }

    private function resolveWedding(Request $request, $user): ?Wedding
    {
        if (! $request->filled('wedding')) {
            return null;
        }

        return $user->weddings()->findOrFail($request->integer('wedding'));
    }

    private function mapHeaders(array $header): array
    {
        $aliases = [
            'name' => ['nama', 'name', 'tamu', 'nama_tamu'],
            'phone' => ['telepon', 'phone', 'whatsapp', 'no_hp', 'nomor', 'hp'],
            'group' => ['grup', 'group', 'kategori', 'kelompok'],
            'pax' => ['kuota', 'pax', 'jumlah', 'jumlah_tamu', 'max_pax'],
        ];
        $map = [];
        foreach ($header as $i => $value) {
            $key = strtolower(trim((string) $value));
            $key = str_replace([' ', '-'], '_', $key);
            foreach ($aliases as $target => $names) {
                if (in_array($key, $names, true)) {
                    $map[$target] = $i;
                }
            }
        }

        return $map;
    }

    private function cell(array $line, array $map, string $key): ?string
    {
        if (! isset($map[$key])) {
            return null;
        }
        $value = trim((string) ($line[$map[$key]] ?? ''));

        return $value !== '' ? $value : null;
    }
}
