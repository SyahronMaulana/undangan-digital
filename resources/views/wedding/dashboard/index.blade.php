@extends('wedding.layouts.dashboard')

@section('title', 'Dasbor')
@section('eyebrow', 'Ringkasan Modul Wedding')
@section('page-title', 'Dasbor')

@section('actions')
    <a href="{{ route('wedding.invitations.create') }}" class="inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-4 py-2 text-[11px] font-semibold text-[#f4f1ea] transition hover:bg-[#26352b]">
        <i data-lucide="plus" class="h-3.5 w-3.5"></i> Buat Undangan
    </a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Sambutan --}}
    <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-gradient-to-br from-[#111b15] via-[#16231b] to-[#1d2c23] p-6 text-[#f1eee6] sm:p-8">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-[.24em] text-[#c7a15a]">Selamat datang kembali</p>
                <h2 class="mt-2 font-display text-3xl font-light leading-tight tracking-tight sm:text-4xl">{{ auth()->user()->name }}</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-white/55">
                    @if($upcoming)
                        Undangan <b class="text-white/80">{{ $upcoming->coupleNames() }}</b> akan digelar {{ $upcoming->event_date?->translatedFormat('d F Y') }}.
                        Pastikan semua data sudah siap sebelum hari bahagia.
                    @else
                        Belum ada undangan aktif. Mulai dengan memilih desain, lalu isi cerita Anda.
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($upcoming)
                    <a href="{{ route('wedding.invitations.edit', $upcoming) }}" class="inline-flex items-center gap-1.5 rounded-full bg-[#c7a15a] px-4 py-2.5 text-[11px] font-semibold text-[#14130e] transition hover:bg-[#dfc087]">
                        <i data-lucide="pen-line" class="h-3.5 w-3.5"></i> Edit Undangan
                    </a>
                    <a href="{{ $upcoming->invitationUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-full border border-white/20 px-4 py-2.5 text-[11px] font-semibold text-white/80 transition hover:border-[#c7a15a]/70 hover:text-[#e8d5aa]">
                        <i data-lucide="external-link" class="h-3.5 w-3.5"></i> Lihat Undangan
                    </a>
                @else
                    <a href="{{ route('wedding.templates.index') }}" class="inline-flex items-center gap-1.5 rounded-full bg-[#c7a15a] px-4 py-2.5 text-[11px] font-semibold text-[#14130e] transition hover:bg-[#dfc087]">
                        <i data-lucide="palette" class="h-3.5 w-3.5"></i> Pilih Desain
                    </a>
                @endif
            </div>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-white/10 bg-white/10 sm:grid-cols-4">
            @foreach([
                ['label' => 'Undangan', 'value' => $stats['undangan'], 'icon' => 'mail'],
                ['label' => 'Total Tamu', 'value' => $stats['tamu'], 'icon' => 'users'],
                ['label' => 'Konfirmasi Hadir', 'value' => $stats['hadir'], 'icon' => 'check-circle-2'],
                ['label' => 'Ucapan Masuk', 'value' => $stats['ucapan'], 'icon' => 'message-heart'],
            ] as $item)
                <div class="bg-[#111b15]/80 px-4 py-4">
                    <div class="flex items-center gap-1.5 text-[9px] uppercase tracking-[.16em] text-white/40"><i data-lucide="{{ $item['icon'] }}" class="h-3 w-3"></i> {{ $item['label'] }}</div>
                    <p class="mt-2 font-display text-2xl font-light text-[#e8dcc1]">{{ number_format($item['value'], 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Kartu statistik detail --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Draf', 'value' => $stats['draft'], 'hint' => 'Belum dipublikasikan', 'icon' => 'file-pen-line', 'tone' => 'amber'],
            ['label' => 'Belum RSVP', 'value' => $stats['belum'], 'hint' => 'Tamu belum konfirmasi', 'icon' => 'clock-3', 'tone' => 'stone'],
            ['label' => 'Total Orang Hadir', 'value' => $stats['orang_hadir'], 'hint' => 'Termasuk keluarga tamu', 'icon' => 'user-check', 'tone' => 'emerald'],
            ['label' => 'Tidak Hadir', 'value' => $stats['tidak_hadir'], 'hint' => 'Konfirmasi tidak hadir', 'icon' => 'user-x', 'tone' => 'rose'],
        ] as $card)
            <article class="rounded-2xl border border-[#17211a]/10 bg-white/70 p-5 backdrop-blur">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-[#8a938b]">{{ $card['label'] }}</p>
                        <p class="mt-3 font-display text-3xl font-light">{{ number_format($card['value'], 0, ',', '.') }}</p>
                    </div>
                    <span class="grid h-9 w-9 place-items-center rounded-full border border-[#17211a]/10 text-[#4f5a51]"><i data-lucide="{{ $card['icon'] }}" class="h-4 w-4"></i></span>
                </div>
                <p class="mt-2 text-[11px] text-[#8a938b]">{{ $card['hint'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="grid gap-6 xl:grid-cols-[1.4fr_.6fr]">
        {{-- Undangan terbaru --}}
        <section class="rounded-2xl border border-[#17211a]/10 bg-white/70 p-5 backdrop-blur sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-display text-xl font-light">Undangan Saya</h3>
                    <p class="text-[11px] text-[#8a938b]">Kelola undangan yang sedang dan akan berjalan.</p>
                </div>
                <a href="{{ route('wedding.invitations.index') }}" class="text-[10px] font-semibold uppercase tracking-wider text-[#8e6d2f] hover:text-[#17211a]">Semua</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($recent as $wedding)
                    <article class="flex flex-wrap items-center gap-4 rounded-xl border border-[#17211a]/10 bg-[#fbf9f3] p-4 transition hover:border-[#c7a15a]/50">
                        <div class="grid h-12 w-12 flex-none place-items-center overflow-hidden rounded-lg bg-[#17211a] text-[#c7a15a]">
                            @if($wedding->coverUrl())
                                <img src="{{ $wedding->coverUrl() }}" alt="" class="h-full w-full object-cover">
                            @else
                                <i data-lucide="mail" class="h-5 w-5"></i>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="truncate font-display text-base">{{ $wedding->coupleNames() }}</h4>
                                @if($wedding->isPublished())
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wider text-emerald-800">Terbit</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wider text-amber-800">Draf</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-[11px] text-[#8a938b]">
                                {{ $wedding->template?->name ?? 'Template' }} · {{ $wedding->event_date?->translatedFormat('d M Y') ?? 'Tanggal belum diisi' }}
                                · {{ $wedding->guests_count ?? 0 }} tamu
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('wedding.invitations.show', $wedding) }}" class="rounded-lg p-2 text-[#6b746c] transition hover:bg-[#17211a]/5 hover:text-[#17211a]" title="Ringkasan"><i data-lucide="eye" class="h-4 w-4"></i></a>
                            <a href="{{ route('wedding.invitations.edit', $wedding) }}" class="rounded-lg p-2 text-[#6b746c] transition hover:bg-[#17211a]/5 hover:text-[#17211a]" title="Edit"><i data-lucide="pen-line" class="h-4 w-4"></i></a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-[#17211a]/20 px-6 py-10 text-center">
                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-[#17211a]/5 text-[#6b746c]"><i data-lucide="mail-plus" class="h-5 w-5"></i></span>
                        <p class="mt-3 text-sm font-medium">Belum ada undangan</p>
                        <p class="mt-1 text-[11px] text-[#8a938b]">Mulai dari template favorit Anda.</p>
                        <a href="{{ route('wedding.templates.index') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-4 py-2 text-[11px] font-semibold text-[#f4f1ea]">Lihat Desain <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></a>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="space-y-6">
            {{-- Status Publikasi & Pembayaran --}}
            <section class="rounded-2xl border border-[#17211a]/10 bg-white/70 p-5 backdrop-blur">
                <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-[#8a938b]">Status Layanan & Publikasi</p>
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
                <div class="mt-2 space-y-2">
                    @if($publication)
                        <div class="rounded-xl border border-[#17211a]/10 bg-[#fbf9f3] p-3">
                            <p class="truncate text-xs font-semibold text-[#17211a]">{{ $publication->coupleNames() }}</p>
                            <p class="mt-0.5 text-[10px] text-[#8a938b]">{{ $publication->template?->name ?? 'Template' }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $submissionTones[$publication->submissionStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $publication->submissionStatusLabel() }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $paymentTones[$publication->paymentStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $publication->paymentStatusLabel() }}</span>
                            </div>
                        </div>
                        <p class="text-xs font-medium text-[#17211a]">Pengajuan publikasi Anda sedang diproses Admin. Pantau status di atas.</p>
                        <div class="pt-1">
                            {{-- Link pengajuan yang sama; membuka WhatsApp tidak membuat pengajuan baru
                                 dan tidak mengubah status menjadi terbit. Nomor diambil dari
                                 pengaturan platform (dapat diubah di dasbor Super Admin). --}}
                            @include('wedding.partials.admin-whatsapp-button', [
                                'url' => \App\Support\AdminWhatsApp::publicationUrl($publication),
                                'note' => 'Silakan hubungi admin melalui WhatsApp untuk mendapatkan informasi pembayaran dan proses publikasi undangan Anda.',
                            ])
                        </div>
                        <a href="{{ route('wedding.invitations.show', $publication) }}" class="flex w-full items-center justify-center gap-1.5 rounded-full border border-[#17211a]/15 px-4 py-2 text-[10px] font-semibold text-[#4f5a51] hover:border-[#17211a]/35">
                            <i data-lucide="eye" class="h-3.5 w-3.5"></i> Lihat Detail Pengajuan
                        </a>
                    @else
                        <p class="text-xs font-medium text-[#17211a]">Semua fitur terbuka sepenuhnya untuk Anda. Publikasi undangan memerlukan konfirmasi pembayaran melalui WhatsApp Admin.</p>
                        <div class="pt-2">
                            @include('wedding.partials.admin-whatsapp-button', [
                                'url' => \App\Support\AdminWhatsApp::generalUrl(),
                            ])
                        </div>
                    @endif
                </div>
            </section>

            {{-- Ucapan terbaru --}}
            <section class="rounded-2xl border border-[#17211a]/10 bg-white/70 p-5 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-[#8a938b]">Ucapan Terbaru</p>
                    <a href="{{ route('wedding.wishes.index') }}" class="text-[10px] font-semibold uppercase tracking-wider text-[#8e6d2f] hover:text-[#17211a]">Semua</a>
                </div>
                <div class="mt-3 space-y-3">
                    @forelse($recentWishes as $wish)
                        <div class="rounded-lg border border-[#17211a]/10 bg-[#fbf9f3] p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-semibold">{{ $wish->name }}</p>
                                <span class="text-[9px] text-[#a8b0a9]">{{ $wish->created_at?->diffForHumans() }}</span>
                            </div>
                            <p class="mt-1 line-clamp-2 text-[11px] leading-5 text-[#5d675f]">{{ $wish->message }}</p>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-[#17211a]/20 px-3 py-6 text-center text-[11px] text-[#8a938b]">Belum ada ucapan.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    {{-- Pintasan --}}
    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['label' => 'Daftar Tamu', 'desc' => 'Tambah, impor, dan bagikan link personal.', 'icon' => 'users', 'route' => 'wedding.guests.index'],
            ['label' => 'Konfirmasi Kehadiran', 'desc' => 'Pantau RSVP dan jumlah orang hadir.', 'icon' => 'clipboard-check', 'route' => 'wedding.rsvp.index'],
            ['label' => 'WhatsApp', 'desc' => 'Bagikan satu pesan ke kontak pilihan Anda.', 'icon' => 'message-circle', 'route' => 'wedding.broadcast.index'],
            ['label' => 'Tanda Kasih', 'desc' => 'Atur rekening dan amplop digital.', 'icon' => 'wallet-cards', 'route' => 'wedding.invitations.index'],
        ] as $shortcut)
            <a href="{{ route($shortcut['route']) }}" class="group rounded-2xl border border-[#17211a]/10 bg-white/70 p-5 backdrop-blur transition hover:-translate-y-0.5 hover:border-[#c7a15a]/60 hover:shadow-lg hover:shadow-[#17211a]/5">
                <span class="grid h-10 w-10 place-items-center rounded-full border border-[#17211a]/10 text-[#4f5a51] transition group-hover:border-[#c7a15a]/60 group-hover:text-[#8e6d2f]"><i data-lucide="{{ $shortcut['icon'] }}" class="h-4 w-4"></i></span>
                <h3 class="mt-3 text-sm font-semibold">{{ $shortcut['label'] }}</h3>
                <p class="mt-1 text-[11px] leading-5 text-[#8a938b]">{{ $shortcut['desc'] }}</p>
            </a>
        @endforeach
    </section>
</div>
@endsection
