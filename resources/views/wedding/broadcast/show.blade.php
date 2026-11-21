@extends('wedding.layouts.dashboard')

@section('title', 'Template — '.$campaign->name)
@section('eyebrow', $wedding->coupleNames())
@section('page-title', $campaign->name)

@section('actions')
    <a href="{{ route('wedding.broadcast.index') }}" class="inline-flex items-center gap-1.5 rounded-full border border-[#17211a]/15 px-3.5 py-2 text-[11px] font-medium text-[#4f5a51] transition hover:border-[#17211a]/35">
        <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Semua Template
    </a>
@endsection

@section('content')
<div class="space-y-5" x-data="broadcastTemplate({
    name: @js($campaign->name),
    message: @js($campaign->message_template),
    map: @js($map),
    endpoint: @js(route('wedding.broadcast.share', $campaign)),
    csrf: @js(csrf_token())
})">
    <div class="grid gap-5 xl:grid-cols-[1.4fr_.6fr]">
        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-display text-lg font-light">Editor Pesan</h3>
                    <p class="text-[10px] text-[#8a938b]">Pesan yang sama akan dibagikan ke semua penerima.</p>
                </div>
                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-wider text-emerald-800">Tanpa nama tamu</span>
            </div>

            <form method="POST" action="{{ route('wedding.broadcast.update', $campaign) }}" class="mt-4 space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="editor-label">Nama template *</label>
                    <input name="name" x-model="name" required maxlength="100" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="editor-label">Isi pesan *</label>
                    <textarea name="message_template" x-model="message" rows="12" maxlength="4000" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-sm leading-6"></textarea>
                    <p class="mt-1.5 text-[9px] text-[#8a938b]">Variabel informasi undangan (opsional). Pesan tidak memuat nama tamu atau data personal penerima.</p>
                    <div class="mt-1.5 flex flex-wrap gap-1">
                        @foreach($variables as $variable)
                            <button type="button" @click="message += '{{ $variable }}'" class="rounded-full border border-[#17211a]/10 bg-[#f4f1ea] px-1.5 py-0.5 font-mono text-[8px] text-[#6b746c] hover:border-[#c7a15a]">{{ $variable }}</button>
                        @endforeach
                    </div>
                </div>
                <button class="inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-5 py-2.5 text-xs font-semibold text-[#f4f1ea]"><i data-lucide="save" class="h-3.5 w-3.5"></i> Simpan Perubahan</button>
            </form>
        </section>

        <div class="space-y-4">
            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Pratinjau Pesan</h3>
                <p class="text-[10px] text-[#8a938b]">Inilah pesan yang akan dibagikan.</p>
                <div class="mt-3 whitespace-pre-line rounded-xl border border-[#17211a]/10 bg-[#e7f6e9] p-3 text-[11px] leading-5 text-[#1f3b25]" x-text="resolved">{{ $preview['message'] }}</div>
                <div class="mt-3 flex flex-col gap-2">
                    <button type="button" @click="share()" class="flex items-center justify-center gap-1.5 rounded-full bg-emerald-600 px-4 py-2.5 text-[11px] font-semibold text-white hover:bg-emerald-700">
                        <i data-lucide="message-circle" class="h-3.5 w-3.5"></i> Bagikan melalui WhatsApp
                    </button>
                    <button type="button" :data-copy="resolved" class="flex items-center justify-center gap-1.5 rounded-full border border-[#17211a]/20 px-4 py-2.5 text-[11px] font-semibold text-[#4f5a51] hover:border-[#17211a]/45">
                        <i data-lucide="copy" class="h-3.5 w-3.5"></i> <span data-copy-label>Salin Pesan</span>
                    </button>
                </div>
                <p class="mt-2 text-[9px] leading-4 text-[#8a938b]">Tombol bagikan memakai berbagi perangkat bila tersedia, atau membuka WhatsApp dengan pesan siap kirim. Anda memilih kontaknya sendiri.</p>
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Riwayat Berbagi</h3>
                <p class="text-[10px] text-[#8a938b]">Catatan kapan template ini dibagikan.</p>
                <div class="mt-3 space-y-2">
                    @forelse($history as $log)
                        <div class="rounded-lg border border-[#17211a]/10 bg-[#fbf9f3] p-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[11px] font-semibold">{{ $log->user?->name ?? 'Pengguna' }}</p>
                                <span class="text-[9px] text-[#a8b0a9]">{{ $log->created_at?->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                            <p class="mt-1 text-[9px] uppercase tracking-wider text-[#8a938b]">{{ ($log->meta['channel'] ?? 'web_share') === 'web_share' ? 'Bagikan perangkat' : 'WhatsApp' }}</p>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-[#17211a]/20 px-3 py-6 text-center text-[10px] text-[#8a938b]">Belum ada aktivitas berbagi.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Etika Berbagi</h3>
                <ul class="mt-2 space-y-2 text-[10px] leading-4 text-[#6b746c]">
                    <li>Website tidak membaca kontak WhatsApp Anda dan tidak meminta izin kontak.</li>
                    <li>Tidak ada pengiriman otomatis maupun pesan massal. Anda yang menekan kirim.</li>
                    <li>Tidak ada API WhatsApp berbayar yang dipakai.</li>
                </ul>
                <form method="POST" action="{{ route('wedding.broadcast.destroy', $campaign) }}" onsubmit="return confirm('Hapus template ini?')" class="mt-4">@csrf @method('DELETE')
                    <button class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[10px] font-semibold text-red-700">Hapus Template</button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
function broadcastTemplate(config) {
    return {
        name: config.name,
        message: config.message,
        map: config.map,
        endpoint: config.endpoint,
        csrf: config.csrf,
        get resolved() {
            let text = this.message || '';
            for (const [key, value] of Object.entries(this.map || {})) {
                text = text.split(key).join(value);
            }
            // Buang placeholder yang tidak dikenal (mis. sisa variabel personal
            // dari template lama) agar pesan yang dibagikan tidak memuat nama
            // tamu atau variabel mentah.
            return text.replace(/\{\{[^{}]*\}\}/g, '');
        },
        record(channel) {
            fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body: JSON.stringify({ channel }),
            }).catch(() => {});
        },
        share() {
            const text = this.resolved;
            const fallback = 'https://wa.me/?text=' + encodeURIComponent(text);
            if (typeof navigator.share === 'function') {
                this.record('web_share');
                try {
                    navigator.share({ text }).catch(() => window.open(fallback, '_blank', 'noopener'));
                } catch (error) {
                    window.open(fallback, '_blank', 'noopener');
                }
            } else {
                this.record('whatsapp');
                window.open(fallback, '_blank', 'noopener');
            }
        },
    };
}
</script>
@endverbatim
@endpush
