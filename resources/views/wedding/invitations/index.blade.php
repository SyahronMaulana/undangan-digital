@extends('wedding.layouts.dashboard')

@section('title', 'Undangan Saya')
@section('eyebrow', 'Manajemen Undangan')
@section('page-title', 'Undangan Saya')

@section('actions')
    <a href="{{ route('wedding.invitations.create') }}" class="inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-4 py-2 text-[11px] font-semibold text-[#f4f1ea] transition hover:bg-[#26352b]">
        <i data-lucide="plus" class="h-3.5 w-3.5"></i> Buat Undangan
    </a>
@endsection

@section('content')
@php
    $submissionTones = [
        'draft' => 'bg-[#17211a]/5 text-[#6b746c]',
        'menunggu_konfirmasi' => 'bg-amber-100 text-amber-800',
        'menunggu_pembayaran' => 'bg-orange-100 text-orange-800',
        'menunggu_verifikasi' => 'bg-sky-100 text-sky-800',
        'pembayaran_dikonfirmasi' => 'bg-emerald-100 text-emerald-800',
        'dipublikasikan' => 'bg-emerald-600 text-white',
        'revisi' => 'bg-red-100 text-red-700',
    ];
    $paymentTones = [
        'belum_bayar' => 'bg-[#17211a]/5 text-[#6b746c]',
        'menunggu_verifikasi' => 'bg-sky-100 text-sky-800',
        'dikonfirmasi' => 'bg-emerald-100 text-emerald-800',
    ];
@endphp
<div class="space-y-5">
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Total Undangan', 'value' => $weddings->count(), 'icon' => 'mail'],
            ['label' => 'Terbit', 'value' => $weddings->where('status', 'published')->count(), 'icon' => 'globe'],
            ['label' => 'Draf', 'value' => $weddings->where('status', 'draft')->count(), 'icon' => 'file-pen-line'],
            ['label' => 'Menunggu Publikasi', 'value' => $weddings->filter(fn ($w) => $w->isAwaitingPublication())->count(), 'icon' => 'clock'],
        ] as $stat)
            <div class="flex items-center gap-3 rounded-xl border border-[#17211a]/10 bg-white/70 px-4 py-3.5 backdrop-blur">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-[#17211a]/5 text-[#4f5a51]"><i data-lucide="{{ $stat['icon'] }}" class="h-4 w-4"></i></span>
                <div><p class="text-[9px] uppercase tracking-[.16em] text-[#8a938b]">{{ $stat['label'] }}</p><p class="font-display text-xl">{{ $stat['value'] }}</p></div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($weddings as $wedding)
            <article class="group flex flex-col overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur transition hover:-translate-y-0.5 hover:border-[#c7a15a]/60 hover:shadow-xl hover:shadow-[#17211a]/5">
                <div class="relative aspect-[16/10] overflow-hidden bg-[#111b15]">
                    @if($wedding->coverUrl())
                        <img src="{{ $wedding->coverUrl() }}" alt="Cover {{ $wedding->coupleNames() }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]">
                    @else
                        <div class="grid h-full w-full place-items-center text-[#c7a15a]/60"><i data-lucide="image" class="h-8 w-8"></i></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0c120e]/80 via-transparent to-transparent"></div>
                    <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
                        @if($wedding->isPublished())
                            <span class="rounded-full bg-emerald-500/90 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-white">Terbit</span>
                        @else
                            <span class="rounded-full bg-amber-400/95 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-[#4a3200]">Draf</span>
                        @endif
                        <span class="rounded-full bg-black/35 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-wider text-white/85 backdrop-blur">{{ $wedding->template?->name ?? 'Template' }}</span>
                    </div>
                    <div class="absolute inset-x-3 bottom-3">
                        <h3 class="font-display text-xl font-light text-white">{{ $wedding->coupleNames() }}</h3>
                        <p class="text-[10px] text-white/65">{{ $wedding->event_date?->translatedFormat('d F Y') ?? 'Tanggal belum diisi' }}</p>
                    </div>
                </div>

                <div class="flex flex-1 flex-col p-4">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        @foreach([
                            ['label' => 'Tamu', 'value' => $wedding->guests_count],
                            ['label' => 'RSVP', 'value' => $wedding->rsvps_count],
                            ['label' => 'Ucapan', 'value' => $wedding->wishes_count],
                        ] as $metric)
                            <div class="rounded-lg bg-[#17211a]/[.035] py-2">
                                <p class="font-display text-base">{{ $metric['value'] }}</p>
                                <p class="text-[9px] uppercase tracking-wider text-[#8a938b]">{{ $metric['label'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-3 truncate rounded-md bg-[#f4f1ea] px-2.5 py-1.5 font-mono text-[10px] text-[#6b746c]">/wedding/{{ $wedding->slug }}</p>

                    <dl class="mt-3 space-y-1.5 text-[10.5px]">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-[#8a938b]">Status Undangan</dt>
                            <dd class="font-semibold {{ $wedding->isPublished() ? 'text-emerald-700' : 'text-amber-700' }}">{{ $wedding->isPublished() ? 'Dipublikasikan' : 'Draf' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-[#8a938b]">Status Pengajuan</dt>
                            <dd><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $submissionTones[$wedding->submissionStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $wedding->submissionStatusLabel() }}</span></dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-[#8a938b]">Status Pembayaran</dt>
                            <dd><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $paymentTones[$wedding->paymentStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $wedding->paymentStatusLabel() }}</span></dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-[#8a938b]">Dibuat</dt>
                            <dd class="font-medium text-[#17211a]">{{ $wedding->created_at?->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-[#8a938b]">Dipublikasikan</dt>
                            <dd class="font-medium text-[#17211a]">{{ $wedding->published_at?->translatedFormat('d M Y') ?? '—' }}</dd>
                        </div>
                    </dl>

                    @if($wedding->submissionStatus() === 'revisi' && $wedding->revision_notes)
                        <p class="mt-3 rounded-lg border border-red-200 bg-red-50 px-2.5 py-2 text-[10px] leading-4 text-red-800"><b>Revisi dari Admin:</b> {{ $wedding->revision_notes }}</p>
                    @endif

                    <div class="mt-3 flex items-center gap-1.5">
                        @if($wedding->isPublished())
                            <a href="{{ route('wedding.invitations.show', $wedding) }}" class="flex-1 rounded-lg bg-[#17211a] px-3 py-2 text-center text-[10px] font-semibold text-[#f4f1ea] transition hover:bg-[#26352b]">Ringkasan</a>
                            <a href="{{ $wedding->invitationUrl() }}" target="_blank" rel="noopener" class="rounded-lg border border-[#17211a]/15 p-2 text-[#4f5a51] transition hover:border-[#17211a]/35 hover:text-[#17211a]" title="Lihat undangan"><i data-lucide="external-link" class="h-4 w-4"></i></a>
                            <button type="button" data-copy="{{ $wedding->invitationUrl() }}" class="rounded-lg border border-[#17211a]/15 p-2 text-[#4f5a51] transition hover:border-[#17211a]/35 hover:text-[#17211a]" title="Salin tautan"><i data-lucide="copy" class="h-4 w-4"></i></button>
                            <a href="https://wa.me/?text={{ urlencode($wedding->coupleNames().' — '.$wedding->invitationUrl()) }}" target="_blank" rel="noopener" class="rounded-lg border border-emerald-600/30 bg-emerald-50 p-2 text-emerald-700 transition hover:bg-emerald-100" title="Bagikan via WhatsApp"><i data-lucide="message-circle" class="h-4 w-4"></i></a>
                        @else
                            <a href="{{ route('wedding.invitations.edit', $wedding) }}" class="flex-1 rounded-lg bg-[#17211a] px-3 py-2 text-center text-[10px] font-semibold text-[#f4f1ea] transition hover:bg-[#26352b]">Edit Undangan</a>
                            <a href="{{ route('wedding.invitations.show', $wedding) }}" class="rounded-lg border border-[#17211a]/15 p-2 text-[#4f5a51] transition hover:border-[#17211a]/35 hover:text-[#17211a]" title="Ringkasan"><i data-lucide="bar-chart-3" class="h-4 w-4"></i></a>
                            @if($wedding->canSubmitPublication())
                                <a href="{{ route('wedding.invitations.show', $wedding) }}#publikasi" class="rounded-lg border border-[#c7a15a]/50 bg-[#c7a15a]/10 p-2 text-[#8e6d2f] transition hover:bg-[#c7a15a]/20" title="Ajukan publikasi"><i data-lucide="send" class="h-4 w-4"></i></a>
                            @endif
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-[#17211a]/20 bg-white/40 px-6 py-16 text-center">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-[#17211a]/5 text-[#6b746c]"><i data-lucide="mail-plus" class="h-6 w-6"></i></span>
                <h3 class="mt-4 font-display text-2xl font-light">Belum ada undangan</h3>
                <p class="mx-auto mt-1.5 max-w-sm text-sm text-[#8a938b]">Pilih salah satu dari delapan desain, lalu isi cerita Anda. Anda dapat mengubah desain kapan saja.</p>
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    <a href="{{ route('wedding.templates.index') }}" class="rounded-full bg-[#17211a] px-5 py-2.5 text-[11px] font-semibold text-[#f4f1ea]">Jelajahi Desain</a>
                    <a href="{{ route('wedding.invitations.create') }}" class="rounded-full border border-[#17211a]/20 px-5 py-2.5 text-[11px] font-semibold">Buat Langsung</a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
