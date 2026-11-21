<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Models\WeddingGift;
use App\Models\WeddingPhoto;
use App\Models\WeddingStory;
use App\Rules\AudioFile;
use App\Services\Wedding\TemplateRegistry;
use App\Services\Wedding\WhatsAppTemplate;
use App\Support\AdminWhatsApp;
use App\Support\AudioFormat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        return view('wedding.invitations.index', [
            'weddings' => $request->user()->weddings()
                ->with('template')
                ->withCount(['guests', 'rsvps', 'wishes'])
                ->latest()->get(),
        ]);
    }

    public function create(Request $request, TemplateRegistry $registry)
    {
        return view('wedding.invitations.create', [
            'templates' => $registry->active(),
            'selectedTemplate' => $request->query('template'),
        ]);
    }

    public function store(Request $request, TemplateRegistry $registry): RedirectResponse
    {
        $user = $request->user();

        // Normalisasi slug (huruf besar & spasi) sebelum validasi. Slug ganda/tanda
        // hubung di awal-akhir/karakter khusus tetap ditolak oleh regex.
        $request->merge(['slug' => $this->normalizeSlug($request->input('slug'))]);

        $data = $request->validate([
            'groom_name' => ['required', 'string', 'max:80'],
            'bride_name' => ['required', 'string', 'max:80'],
            'slug' => [
                'required', 'string', 'min:3', 'max:60',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:weddings,slug',
                Rule::notIn(config('wedding.reserved_slugs')),
            ],
            'event_date' => ['nullable', 'date'],
            'template_id' => ['required', Rule::exists('templates', 'id')->where('is_active', true)],
            'language_style' => ['nullable', Rule::in(array_keys(config('wedding.language_styles')))],
        ], [
            'slug.regex' => 'Alamat hanya boleh huruf kecil, angka, dan tanda hubung (contoh: andi-siti).',
            'slug.unique' => 'Alamat ini sudah dipakai. Coba yang lain.',
            'slug.not_in' => 'Alamat ini dicadangkan sistem. Coba yang lain.',
            'groom_name.required' => 'Nama mempelai pria wajib diisi.',
            'bride_name.required' => 'Nama mempelai wanita wajib diisi.',
        ]);

        // Pastikan template yang dipilih memang dikenal modul.
        $template = Template::whereKey($data['template_id'])->where('is_active', true)->firstOrFail();
        abort_unless($registry->isKnown($template->slug), 422);

        $wedding = new Wedding($data);
        $wedding->user_id = $user->id;
        $wedding->template_id = $template->id;
        $wedding->language_style = $data['language_style'] ?? 'islami';
        $wedding->sections = config('wedding.default_sections');
        $wedding->design_settings = [];
        $wedding->status = Wedding::STATUS_DRAFT;
        $wedding->submission_status = Wedding::SUBMISSION_DRAFT;
        $wedding->payment_status = Wedding::PAYMENT_UNPAID;
        $wedding->save();

        return redirect()->route('wedding.invitations.edit', $wedding)
            ->with('status', 'Undangan berhasil dibuat. Lengkapi datanya, lalu ajukan publikasi ke Admin.');
    }

    public function show(Wedding $wedding, WhatsAppTemplate $whatsApp)
    {
        Gate::authorize('view', $wedding);

        $wedding->load(['template', 'events', 'stories', 'photos', 'gifts'])
            ->loadCount(['guests', 'rsvps', 'wishes']);

        // Pesan siap kirim untuk berbagi satu per satu. Link dibiarkan sebagai
        // penanda __LINK__ agar dapat diganti dengan link personal per penerima
        // (?to=...) di sisi klien. Nama penerima TIDAK disimpan ke data undangan.
        $messageTemplate = str_replace(
            '{{link_undangan}}',
            '__LINK__',
            WhatsAppTemplate::DEFAULT_MESSAGE
        );

        return view('wedding.invitations.show', [
            'wedding' => $wedding,
            'summary' => $wedding->rsvpSummary(),
            'shareData' => [
                'baseUrl' => $wedding->invitationUrl(),
                'messageTemplate' => $whatsApp->broadcastMessage($wedding, $messageTemplate),
            ],
        ]);
    }

    public function edit(Wedding $wedding, TemplateRegistry $registry)
    {
        Gate::authorize('update', $wedding);

        // Undangan terbit hanya bisa diubah lewat alur revisi admin. Jangan
        // tampilkan editor agar konten publik tidak berubah diam-diam.
        if ($wedding->isPublished()) {
            return redirect()
                ->route('wedding.invitations.show', $wedding)
                ->withErrors(['publish' => 'Undangan sudah dipublikasikan. Hubungi Admin untuk mengajukan revisi.']);
        }

        $wedding->load(['template', 'events', 'stories', 'photos', 'gifts']);

        return view('wedding.invitations.edit', [
            'wedding' => $wedding,
            'templates' => $registry->active(),
            'sectionLabels' => config('wedding.sections'),
            'languageStyles' => config('wedding.language_styles'),
            'quotes' => config('wedding.quotes'),
            'previewUrl' => route('wedding.invitations.preview', $wedding),
            'editorData' => [
                'form' => [
                    'groom_name' => $wedding->groom_name,
                    'groom_nickname' => $wedding->groom_nickname,
                    'groom_father' => $wedding->groom_father,
                    'groom_mother' => $wedding->groom_mother,
                    'groom_instagram' => $wedding->groom_instagram,
                    'bride_name' => $wedding->bride_name,
                    'bride_nickname' => $wedding->bride_nickname,
                    'bride_father' => $wedding->bride_father,
                    'bride_mother' => $wedding->bride_mother,
                    'bride_instagram' => $wedding->bride_instagram,
                    'event_date' => $wedding->event_date?->format('Y-m-d'),
                    'language_style' => $wedding->language_style,
                    'opening_text' => $wedding->opening_text,
                    'closing_text' => $wedding->closing_text,
                    'quote_key' => $wedding->quote_key,
                    'quote_text' => $wedding->quote_text,
                    'quote_source' => $wedding->quote_source,
                    'music_title' => $wedding->music_title,
                    'dress_code' => $wedding->dress_code,
                    'hashtag' => $wedding->hashtag,
                    'live_stream_url' => $wedding->live_stream_url,
                    'cover_caption' => $wedding->cover_caption,
                ],
                'events' => $wedding->events->map(fn (WeddingEvent $event) => [
                    'uid' => 'e'.$event->id,
                    'id' => $event->id,
                    'name' => $event->name,
                    'event_date' => $event->event_date?->format('Y-m-d'),
                    'start_time' => $event->start_time ? substr($event->start_time, 0, 5) : '',
                    'end_time' => $event->end_time ? substr($event->end_time, 0, 5) : '',
                    'venue_name' => $event->venue_name,
                    'address' => $event->address,
                    'maps_url' => $event->maps_url,
                ])->values()->all(),
                'stories' => $wedding->stories->map(fn (WeddingStory $story) => [
                    'uid' => 's'.$story->id,
                    'id' => $story->id,
                    'title' => $story->title,
                    'story_date' => $story->story_date,
                    'body' => $story->body,
                ])->values()->all(),
                'gifts' => $wedding->gifts->map(fn (WeddingGift $gift) => [
                    'uid' => 'g'.$gift->id,
                    'id' => $gift->id,
                    'bank_name' => $gift->bank_name,
                    'account_number' => $gift->account_number,
                    'account_name' => $gift->account_name,
                ])->values()->all(),
                'savedAt' => $wedding->updated_at?->format('H:i') ?? '—',
                // Berkas yang sudah tersimpan, ditampilkan di editor agar
                // pengguna bisa memverifikasi hasil unggahan tanpa membuka
                // halaman publik.
                'media' => [
                    'groom_photo' => $wedding->groomPhotoUrl(),
                    'bride_photo' => $wedding->bridePhotoUrl(),
                    'cover_photo' => $wedding->coverUrl(),
                    'music_title' => $wedding->music_title,
                    'music_url' => $wedding->music_path ? asset('storage/'.$wedding->music_path) : null,
                    'music_name' => $wedding->music_path ? basename($wedding->music_path) : null,
                ],
            ],
        ]);
    }

    public function update(Request $request, Wedding $wedding): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $wedding);
        $isAutosave = $request->expectsJson();

        // Undangan yang sudah terbit tidak boleh berubah diam-diam.
        if ($wedding->isPublished()) {
            $message = 'Undangan sudah dipublikasikan. Hubungi Admin untuk revisi agar konten publik tidak berubah diam-diam.';
            if ($isAutosave) {
                return response()->json(['ok' => false, 'message' => $message], 423);
            }

            return back()->withErrors(['publish' => $message]);
        }

        $data = $this->validateEditor($request);
        $wasAwaiting = $wedding->hasActiveSubmission();

        DB::transaction(function () use ($request, $wedding, $data, $wasAwaiting) {
            $this->persistEditor($wedding, $request, $data);

            // Perubahan setelah pengajuan membatalkan pengajuan: customer harus ajukan ulang.
            if ($wasAwaiting) {
                $wedding->forceFill([
                    'submission_status' => Wedding::SUBMISSION_REVISION,
                    'revision_notes' => 'Ada perubahan data setelah pengajuan. Periksa kembali, lalu ajukan publikasi ulang.',
                ])->save();
            }
        });

        if ($isAutosave) {
            return response()->json([
                'ok' => true,
                'message' => 'Perubahan tersimpan otomatis.',
                'updated_at' => now()->format('H:i:s'),
                'preview' => route('wedding.invitations.preview', $wedding),
            ]);
        }

        return redirect()->route('wedding.invitations.edit', $wedding)
            ->with('status', 'Undangan berhasil diperbarui.');
    }

    /** Aturan validasi data editor (dipakai Simpan, autosave, dan Ajukan Publikasi). */
    private function validateEditor(Request $request): array
    {
        return $request->validate([
            'groom_name' => ['required', 'string', 'max:80'],
            'groom_nickname' => ['nullable', 'string', 'max:40'],
            'groom_father' => ['nullable', 'string', 'max:80'],
            'groom_mother' => ['nullable', 'string', 'max:80'],
            'groom_instagram' => ['nullable', 'string', 'max:80'],
            'bride_name' => ['required', 'string', 'max:80'],
            'bride_nickname' => ['nullable', 'string', 'max:40'],
            'bride_father' => ['nullable', 'string', 'max:80'],
            'bride_mother' => ['nullable', 'string', 'max:80'],
            'bride_instagram' => ['nullable', 'string', 'max:80'],
            'event_date' => ['nullable', 'date'],
            'language_style' => ['required', Rule::in(array_keys(config('wedding.language_styles')))],
            'opening_text' => ['nullable', 'string', 'max:3000'],
            'closing_text' => ['nullable', 'string', 'max:3000'],
            'quote_key' => ['nullable', 'string', 'max:40'],
            'quote_text' => ['nullable', 'string', 'max:1200'],
            'quote_source' => ['nullable', 'string', 'max:120'],
            'music_title' => ['nullable', 'string', 'max:120'],
            'dress_code' => ['nullable', 'string', 'max:120'],
            'hashtag' => ['nullable', 'string', 'max:80'],
            'live_stream_url' => ['nullable', 'url', 'max:500'],
            'cover_caption' => ['nullable', 'string', 'max:160'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('wedding.limits.image_kb')],
            'groom_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('wedding.limits.image_kb')],
            'bride_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('wedding.limits.image_kb')],
            'music' => ['nullable', new AudioFile((int) config('wedding.limits.audio_kb'))],
            'remove_music' => ['nullable', 'boolean'],
            'remove_groom_photo' => ['nullable', 'boolean'],
            'remove_bride_photo' => ['nullable', 'boolean'],
            'remove_cover_photo' => ['nullable', 'boolean'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['boolean'],
            'events' => ['nullable', 'array', 'max:12'],
            'events.*.id' => ['nullable', 'integer'],
            'events.*.name' => ['nullable', 'string', 'max:80'],
            'events.*.event_date' => ['nullable', 'date'],
            'events.*.start_time' => ['nullable', 'date_format:H:i'],
            'events.*.end_time' => ['nullable', 'date_format:H:i'],
            'events.*.venue_name' => ['nullable', 'string', 'max:120'],
            'events.*.address' => ['nullable', 'string', 'max:500'],
            'events.*.maps_url' => ['nullable', 'url', 'max:500'],
            'stories' => ['nullable', 'array', 'max:12'],
            'stories.*.id' => ['nullable', 'integer'],
            'stories.*.title' => ['nullable', 'string', 'max:80'],
            'stories.*.story_date' => ['nullable', 'string', 'max:40'],
            'stories.*.body' => ['nullable', 'string', 'max:2000'],
            'gifts' => ['nullable', 'array', 'max:6'],
            'gifts.*.id' => ['nullable', 'integer'],
            'gifts.*.bank_name' => ['nullable', 'string', 'max:60'],
            'gifts.*.account_number' => ['nullable', 'string', 'max:40'],
            'gifts.*.account_name' => ['nullable', 'string', 'max:100'],
            'photos' => ['nullable', 'array', 'max:20'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('wedding.limits.image_kb')],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['integer'],
        ]);
    }

    /**
     * Simpan seluruh data editor (kolom utama, berkas, dan relasi) dalam satu
     * transaksi pemanggil. Relasi hanya disinkronkan bila form memang mengirim
     * bagian tersebut (ditandai sync_events/sync_stories/sync_gifts), sehingga
     * autosave sebagian tidak menghapus data yang tidak disertakan.
     */
    private function persistEditor(Wedding $wedding, Request $request, array $data): void
    {
        $files = [
            'cover_photo' => 'cover',
            'groom_photo' => 'mempelai',
            'bride_photo' => 'mempelai',
        ];

        // Simpan berkas baru lebih dulu, baru hapus berkas lama. Bila penyimpanan
        // gagal, berkas lama tidak ikut hilang.
        foreach ($files as $field => $folder) {
            if (! $request->hasFile($field)) {
                continue;
            }
            $new = $request->file($field)->store('wedding/'.$wedding->id.'/'.$folder, 'public');
            $old = $wedding->{$field};
            $wedding->{$field} = $new;
            $this->deleteStoredFile($old);
        }

        // Hapus foto mempelai/cover atas permintaan. Foto mempelai yang dihapus
        // akan ditampilkan sebagai inisial nama pada seluruh template.
        $removals = [
            'remove_cover_photo' => 'cover_photo',
            'remove_groom_photo' => 'groom_photo',
            'remove_bride_photo' => 'bride_photo',
        ];
        foreach ($removals as $flag => $field) {
            if ($request->hasFile($field)) {
                continue; // unggahan baru menang atas permintaan hapus
            }
            if ($request->boolean($flag) && $wedding->{$field}) {
                $this->deleteStoredFile($wedding->{$field});
                $wedding->{$field} = null;
            }
        }

        if ($request->hasFile('music')) {
            $file = $request->file('music');
            // Simpan memakai ekstensi hasil deteksi isi berkas, bukan tebakan
            // MIME, agar web server mengirim Content-Type audio yang benar dan
            // berkas dapat diputar di pratinjau maupun undangan publik.
            $extension = AudioFormat::detect($file) ?? strtolower($file->getClientOriginalExtension());
            $new = $file->storeAs('wedding/'.$wedding->id.'/music', Str::random(40).'.'.$extension, 'public');
            $old = $wedding->music_path;
            $wedding->music_path = $new;
            $this->deleteStoredFile($old);
        } elseif ($request->boolean('remove_music')) {
            // Hapus musik: lepaskan referensi di database lalu hapus berkas fisik.
            unset($data['music_title']);
            $old = $wedding->music_path;
            $wedding->music_path = null;
            $wedding->music_title = null;
            $this->deleteStoredFile($old);
        }

        $sections = config('wedding.default_sections');
        foreach (array_keys($sections) as $key) {
            $sections[$key] = (bool) ($data['sections'][$key] ?? false);
        }

        $wedding->fill(collect($data)->except([
            'sections', 'events', 'stories', 'gifts', 'photos', 'remove_photos', 'music', 'cover_photo',
            'groom_photo', 'bride_photo', 'remove_music', 'remove_groom_photo', 'remove_bride_photo',
            'remove_cover_photo',
        ])->all());

        // Hanya perbarui daftar section bila form memang mengirimnya (ditandai
        // sync_sections), supaya penyimpanan sebagian tidak mematikan section.
        if ($request->boolean('sync_sections') || $request->has('sections')) {
            $wedding->sections = $sections;
        }

        $wedding->save();

        if ($request->boolean('sync_events') || $request->has('events')) {
            $this->syncEvents($wedding, (array) $request->input('events', []));
        }
        if ($request->boolean('sync_stories') || $request->has('stories')) {
            $this->syncStories($wedding, (array) $request->input('stories', []));
        }
        if ($request->boolean('sync_gifts') || $request->has('gifts')) {
            $this->syncGifts($wedding, (array) $request->input('gifts', []));
        }

        $this->syncPhotos($request, $wedding);
    }

    /** True bila request membawa data editor (bukan hanya final_design). */
    private function editorPayloadPresent(Request $request): bool
    {
        return $request->boolean('sync_events')
            || $request->boolean('sync_stories')
            || $request->boolean('sync_gifts')
            || $request->hasFile('cover_photo')
            || $request->hasFile('groom_photo')
            || $request->hasFile('bride_photo')
            || $request->hasFile('music')
            || $request->filled('groom_name');
    }

    public function changeTemplate(Request $request, Wedding $wedding): RedirectResponse
    {
        Gate::authorize('update', $wedding);

        if ($wedding->isPublished()) {
            return back()->withErrors(['template_id' => 'Undangan sudah dipublikasikan. Ajukan revisi ke Admin untuk mengganti desain.']);
        }

        $data = $request->validate([
            'template_id' => ['required', Rule::exists('templates', 'id')->where('is_active', true)],
        ]);

        $wedding->update(['template_id' => $data['template_id']]);

        return back()->with('status', 'Desain undangan berhasil diganti.');
    }

    public function preview(Wedding $wedding)
    {
        Gate::authorize('view', $wedding);

        $wedding->load(['template', 'events', 'stories', 'photos', 'gifts', 'wishes' => fn ($q) => $q->where('status', 'approved')->latest()]);
        $slug = $wedding->template?->slug ?? 'melati';
        abort_unless(view()->exists('wedding.templates.'.$slug.'.index'), 404);

        return response()
            ->view('wedding.templates.'.$slug.'.index', [
                'wedding' => $wedding,
                'recipientName' => 'Bapak/Ibu/Saudara/i',
                'guest' => null,
                'myRsvp' => null,
                'isPreview' => true,
                'previewMode' => true,
                'embed' => true,
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Customer mengajukan publikasi. Bukan publikasi — hanya mengirim pengajuan ke Admin.
     * Customer tidak dapat mempublikasikan; publikasi hanya oleh Super Admin.
     */
    public function submitPublication(Request $request, Wedding $wedding): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $wedding);

        $message = null;

        if ($wedding->isPublished()) {
            $message = 'Undangan sudah dipublikasikan.';
        } elseif (! $wedding->canSubmitPublication()) {
            $message = 'Pengajuan Anda sudah dikirim dan sedang diproses Admin. Perubahan data akan membatalkan pengajuan dan Anda dapat mengajukan ulang.';
        }

        if ($message) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['publish' => $message]);
        }

        $request->validate([
            'final_design' => ['accepted'],
        ], [
            'final_design.accepted' => 'Konfirmasikan bahwa desain dan data undangan sudah final sebelum mengajukan publikasi.',
        ]);

        // Bila form editor ikut dikirim (mis. tombol Ajukan Publikasi di editor),
        // simpan dulu datanya. Bila penyimpanan gagal, pengajuan TIDAK ditandai
        // berhasil karena transaksi dibatalkan oleh exception.
        if ($this->editorPayloadPresent($request)) {
            $data = $this->validateEditor($request);
            DB::transaction(fn () => $this->persistEditor($wedding, $request, $data));
            $wedding->refresh();
        }

        $issues = $wedding->publicationIssues();
        if ($issues !== []) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $issues[0], 'issues' => $issues], 422);
            }

            return back()->withErrors(['publish' => $issues]);
        }

        DB::transaction(fn () => $wedding->markSubmitted());

        $success = 'Pengajuan publikasi berhasil dikirim ke Admin. Silakan lanjutkan konfirmasi pembayaran melalui chat Admin.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $success,
                'whatsapp' => AdminWhatsApp::publicationUrl($wedding->fresh(['user', 'template'])),
            ]);
        }

        return back()->with('status', $success);
    }

    /**
     * Customer menandai bahwa pembayaran manual sudah dilakukan.
     * Status pembayaran TIDAK dikonfirmasi di sini — hanya Admin yang boleh mengonfirmasi.
     */
    public function claimPayment(Request $request, Wedding $wedding): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $wedding);

        if ($wedding->submissionStatus() !== Wedding::SUBMISSION_WAITING_PAYMENT) {
            $message = 'Konfirmasi pembayaran hanya tersedia setelah Admin mengirim instruksi pembayaran.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['payment' => $message]);
        }

        DB::transaction(fn () => $wedding->markPaymentVerifying());

        $success = 'Terima kasih. Admin akan memverifikasi pembayaran Anda sebelum publikasi.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $success]);
        }

        return back()->with('status', $success);
    }

    public function duplicate(Wedding $wedding): RedirectResponse
    {
        Gate::authorize('view', $wedding);
        $user = request()->user();

        $copy = $wedding->replicate(['slug', 'views_count', 'published_at', 'submitted_at', 'payment_confirmed_at', 'payment_verifier_id', 'publisher_id', 'revision_notes']);
        $copy->slug = $this->uniqueSlug($wedding->slug.'-copy');
        $copy->status = Wedding::STATUS_DRAFT;
        $copy->submission_status = Wedding::SUBMISSION_DRAFT;
        $copy->payment_status = Wedding::PAYMENT_UNPAID;
        $copy->published_at = null;
        $copy->views_count = 0;
        $copy->save();

        foreach (['events', 'stories', 'gifts'] as $relation) {
            foreach ($wedding->{$relation} as $item) {
                $clone = $item->replicate();
                $clone->wedding_id = $copy->id;
                $clone->save();
            }
        }
        foreach ($wedding->photos as $photo) {
            $clone = $photo->replicate();
            $clone->wedding_id = $copy->id;
            $clone->save();
        }

        return redirect()->route('wedding.invitations.edit', $copy)
            ->with('status', 'Undangan diduplikasi sebagai draf baru.');
    }

    public function destroy(Wedding $wedding): RedirectResponse
    {
        Gate::authorize('delete', $wedding);
        $wedding->delete();
        Storage::disk('public')->deleteDirectory('wedding/'.$wedding->id);

        return redirect()->route('wedding.invitations.index')->with('status', 'Undangan dihapus.');
    }

    private function syncEvents(Wedding $wedding, array $rows): void
    {
        $kept = [];
        foreach (array_values($rows) as $i => $row) {
            if (empty($row['name'])) {
                continue;
            }
            $attrs = [
                'name' => $row['name'],
                'event_date' => $row['event_date'] ?? null,
                'start_time' => $row['start_time'] ?? null,
                'end_time' => $row['end_time'] ?? null,
                'venue_name' => $row['venue_name'] ?? null,
                'address' => $row['address'] ?? null,
                'maps_url' => $row['maps_url'] ?? null,
                'sort_order' => $i,
            ];
            $event = ! empty($row['id'])
                ? $wedding->events()->whereKey($row['id'])->first()
                : null;
            if ($event) {
                $event->update($attrs);
            } else {
                $event = $wedding->events()->create($attrs);
            }
            $kept[] = $event->id;
        }
        $wedding->events()->whereNotIn('id', $kept)->delete();
    }

    private function syncStories(Wedding $wedding, array $rows): void
    {
        $kept = [];
        foreach (array_values($rows) as $i => $row) {
            if (empty($row['title']) && empty($row['body'])) {
                continue;
            }
            $attrs = [
                'title' => $row['title'] ?? 'Momen',
                'story_date' => $row['story_date'] ?? null,
                'body' => $row['body'] ?? null,
                'sort_order' => $i,
            ];
            $story = ! empty($row['id']) ? $wedding->stories()->whereKey($row['id'])->first() : null;
            if ($story) {
                $story->update($attrs);
            } else {
                $story = $wedding->stories()->create($attrs);
            }
            $kept[] = $story->id;
        }
        $wedding->stories()->whereNotIn('id', $kept)->delete();
    }

    private function syncGifts(Wedding $wedding, array $rows): void
    {
        $kept = [];
        foreach (array_values($rows) as $i => $row) {
            if (empty($row['bank_name']) || empty($row['account_number'])) {
                continue;
            }
            $attrs = [
                'bank_name' => $row['bank_name'],
                'account_number' => $row['account_number'],
                'account_name' => $row['account_name'] ?? null,
                'sort_order' => $i,
            ];
            $gift = ! empty($row['id']) ? $wedding->gifts()->whereKey($row['id'])->first() : null;
            if ($gift) {
                $gift->update($attrs);
            } else {
                $gift = $wedding->gifts()->create($attrs);
            }
            $kept[] = $gift->id;
        }
        $wedding->gifts()->whereNotIn('id', $kept)->delete();
    }

    private function syncPhotos(Request $request, Wedding $wedding): void
    {
        $remove = array_filter((array) $request->input('remove_photos', []));
        if ($remove) {
            $photos = $wedding->photos()->whereIn('id', $remove)->get();
            foreach ($photos as $photo) {
                $this->deleteStoredFile($photo->path);
                $photo->delete();
            }
        }

        $order = (int) ($wedding->photos()->max('sort_order') ?? 0);
        foreach ($request->file('photos', []) as $file) {
            $wedding->photos()->create([
                'path' => $file->store('wedding/'.$wedding->id.'/galeri', 'public'),
                'sort_order' => ++$order,
            ]);
        }
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'wedding/')) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Normalisasi slug: huruf besar → kecil, spasi → tanda hubung. Tidak menutupi slug tidak valid. */
    private function normalizeSlug(?string $value): string
    {
        $value = strtolower(trim((string) $value));

        return preg_replace('/\s+/', '-', $value) ?? '';
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (Wedding::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
