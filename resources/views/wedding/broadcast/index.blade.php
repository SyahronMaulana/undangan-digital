@extends('wedding.layouts.dashboard')

@section('title', 'Broadcast WhatsApp')
@section('eyebrow', 'Template Pesan')
@section('page-title', 'Broadcast WhatsApp')

@section('actions')
    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1.5 text-[10px] font-semibold text-emerald-800"><i data-lucide="shield-check" class="h-3.5 w-3.5"></i> Anda pilih kontak di WhatsApp</span>
@endsection

@section('content')
<div class="space-y-5">
    <section class="rounded-2xl border border-[#17211a]/10 bg-gradient-to-br from-[#111b15] to-[#1d2c23] p-5 text-[#f1eee6] sm:p-6">
        <div class="max-w-2xl">
            <p class="text-[10px] uppercase tracking-[.22em] text-[#c7a15a]">Bagikan lewat WhatsApp</p>
            <h2 class="mt-2 font-display text-2xl font-light sm:text-3xl">Satu pesan untuk dibagikan ke kontak pilihan Anda.</h2>
            <p class="mt-2 text-sm leading-6 text-white/55">Susun template pesan, lihat pratinjau, lalu tekan <b class="text-white/80">Bagikan melalui WhatsApp</b>. Pesan yang sama dibagikan ke semua penerima dan Anda memilih sendiri kontaknya di WhatsApp. Website tidak membaca kontak Anda dan tidak mengirim pesan otomatis.</p>
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[1.3fr_.7fr]">
        <div class="space-y-5">
            <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
                <div class="border-b border-[#17211a]/10 px-4 py-3">
                    <h3 class="font-display text-lg font-light">Template Pesan</h3>
                    <p class="text-[10px] text-[#8a938b]">Pilih template untuk diedit, disalin, atau dibagikan.</p>
                </div>
                <div class="divide-y divide-[#17211a]/5">
                    @forelse($templates as $template)
                        <a href="{{ route('wedding.broadcast.show', $template) }}" class="flex flex-wrap items-center gap-3 px-4 py-3.5 transition hover:bg-[#fbf9f3]">
                            <span class="grid h-9 w-9 flex-none place-items-center rounded-full bg-[#17211a]/5 text-[#4f5a51]"><i data-lucide="message-square-text" class="h-4 w-4"></i></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $template->name }}</p>
                                <p class="truncate text-[10px] text-[#8a938b]">{{ \Illuminate\Support\Str::limit($template->message_template, 70) }}</p>
                            </div>
                            <span class="text-[9px] text-[#a8b0a9]">{{ $template->created_at?->diffForHumans() }}</span>
                            <i data-lucide="chevron-right" class="h-4 w-4 text-[#a8b0a9]"></i>
                        </a>
                    @empty
                        <div class="px-4 py-10 text-center text-[11px] text-[#8a938b]">Belum ada template. Buat template pertama di samping.</div>
                    @endforelse
                </div>
                @if($templates->hasPages())<div class="border-t border-[#17211a]/10 px-4 py-3">{{ $templates->links() }}</div>@endif
            </section>

            <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
                <div class="border-b border-[#17211a]/10 px-4 py-3">
                    <h3 class="font-display text-lg font-light">Riwayat Berbagi</h3>
                    <p class="text-[10px] text-[#8a938b]">Catatan kapan Anda menekan tombol bagikan. Bukan bukti pesan terkirim.</p>
                </div>
                <div class="divide-y divide-[#17211a]/5">
                    @forelse($history as $log)
                        <div class="px-4 py-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs font-semibold">{{ $log->meta['template_name'] ?? $log->description ?? 'Template' }}</p>
                                <span class="text-[9px] text-[#a8b0a9]">{{ $log->created_at?->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                            <p class="mt-1 line-clamp-2 whitespace-pre-line text-[10px] leading-4 text-[#5d675f]">{{ $log->meta['message'] ?? '' }}</p>
                            <p class="mt-1 text-[9px] text-[#a8b0a9]">Oleh {{ $log->user?->name ?? 'Pengguna' }} · {{ ($log->meta['channel'] ?? 'web_share') === 'web_share' ? 'Bagikan perangkat' : 'WhatsApp' }}</p>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center text-[11px] text-[#8a938b]">Belum ada aktivitas berbagi.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="h-fit rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
            <h3 class="font-display text-lg font-light">Buat Template</h3>
            @if($wedding)
                <form method="POST" action="{{ route('wedding.broadcast.store') }}" class="mt-3 space-y-3" x-data="{ message: @js($defaultMessage) }">
                    @csrf
                    <div>
                        <label class="editor-label">Undangan</label>
                        <select name="wedding_id" required class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs">
                            @foreach($weddings as $item)<option value="{{ $item->id }}" @selected($wedding->id === $item->id)>{{ $item->coupleNames() }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="editor-label">Nama template *</label><input name="name" required maxlength="100" placeholder="Promo Undangan Keluarga" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs"></div>
                    <div>
                        <label class="editor-label">Isi pesan *</label>
                        <textarea name="message_template" x-model="message" rows="7" maxlength="4000" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs leading-5"></textarea>
                        <p class="mt-1.5 text-[9px] text-[#8a938b]">Variabel informasi undangan (opsional). Pesan tidak memuat nama tamu.</p>
                        <div class="mt-1.5 flex flex-wrap gap-1">
                            @foreach($variables as $variable)
                                <button type="button" @click="message += '{{ $variable }}'" class="rounded-full border border-[#17211a]/10 bg-[#f4f1ea] px-1.5 py-0.5 font-mono text-[8px] text-[#6b746c] hover:border-[#c7a15a]">{{ $variable }}</button>
                            @endforeach
                        </div>
                    </div>
                    <button class="w-full rounded-full bg-[#17211a] px-4 py-2.5 text-xs font-semibold text-[#f4f1ea]">Simpan Template</button>
                </form>
            @else
                <p class="mt-2 text-[11px] text-[#8a938b]">Buat undangan terlebih dahulu.</p>
            @endif
        </section>
    </div>
</div>
@endsection
