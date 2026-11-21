@extends('wedding.layouts.admin')

@section('title', 'Pengajuan — '.$wedding->coupleNames())
@section('eyebrow', 'Detail Pengajuan Publikasi')
@section('page-title', $wedding->coupleNames())

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

@section('actions')
    <a href="{{ route('wedding.admin.publications.index') }}" class="inline-flex items-center gap-1.5 rounded-full border border-[#17211a]/15 px-3.5 py-2 text-[11px] font-medium text-[#4f5a51] hover:border-[#17211a]/35"><i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Kembali</a>
    <a href="{{ route('wedding.admin.publications.preview', $wedding) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-full border border-[#17211a]/15 px-3.5 py-2 text-[11px] font-medium text-[#4f5a51] hover:border-[#17211a]/35"><i data-lucide="eye" class="h-3.5 w-3.5"></i> Preview</a>
@endsection

@section('content')
<div class="space-y-5" x-data="{ tab: 'ringkasan' }">
    @if(session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800">
            <ul class="list-disc pl-4">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[10px] uppercase tracking-[.2em] text-[#8e6d2f]">Pengajuan Publikasi</p>
                <h2 class="mt-1 font-display text-2xl font-light">{{ $wedding->coupleNames() }}</h2>
                <p class="mt-1 text-[11px] text-[#8a938b]">{{ $wedding->cover_caption ?: 'Tanpa judul khusus' }} · {{ $wedding->template?->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full px-3 py-1 text-[9px] font-semibold {{ $submissionTones[$wedding->submissionStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">Pengajuan: {{ $wedding->submissionStatusLabel() }}</span>
                <span class="rounded-full px-3 py-1 text-[9px] font-semibold {{ $paymentTones[$wedding->paymentStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">Pembayaran: {{ $wedding->paymentStatusLabel() }}</span>
                <span class="rounded-full px-3 py-1 text-[9px] font-semibold {{ $wedding->isPublished() ? 'bg-emerald-600 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $wedding->isPublished() ? 'Terbit' : 'Draf' }}</span>
            </div>
        </div>

        <dl class="mt-5 grid gap-x-6 gap-y-3 text-[11px] sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Customer', $wedding->user?->name],
                ['Kontak customer', $wedding->user?->phone ?: ($wedding->user?->email ?: '—')],
                ['Nama mempelai', $wedding->coupleNames()],
                ['Judul undangan', $wedding->cover_caption ?: $wedding->coupleNames()],
                ['Desain / template', $wedding->template?->name],
                ['Alamat undangan', '/wedding/'.$wedding->slug],
                ['Waktu pengajuan', $wedding->submitted_at?->translatedFormat('d F Y H:i') ?? '—'],
                ['Pembayaran dikonfirmasi', $wedding->payment_confirmed_at?->translatedFormat('d F Y H:i') ?? '—'],
                ['Dikonfirmasi oleh', $wedding->paymentVerifier?->name ?? '—'],
                ['Dipublikasikan', $wedding->published_at?->translatedFormat('d F Y H:i') ?? '—'],
                ['Dipublikasikan oleh', $wedding->publisher?->name ?? '—'],
                ['Jumlah acara', $wedding->events->count().' acara'],
            ] as [$label, $value])
                <div class="border-b border-[#17211a]/5 pb-2"><dt class="text-[#8a938b]">{{ $label }}</dt><dd class="font-medium text-[#17211a]">{{ $value }}</dd></div>
            @endforeach
        </dl>

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('wedding.admin.publications.preview', $wedding) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-4 py-2 text-[11px] font-semibold text-[#f4f1ea]"><i data-lucide="eye" class="h-3.5 w-3.5"></i> Preview Undangan</a>
            @if($customerWaUrl)
                <a href="{{ $customerWaUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-4 py-2 text-[11px] font-semibold text-white hover:bg-emerald-700"><i data-lucide="message-circle" class="h-3.5 w-3.5"></i> WhatsApp Customer</a>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-[11px] font-medium text-amber-800"><i data-lucide="alert-triangle" class="h-3.5 w-3.5"></i> Customer belum punya nomor WhatsApp</span>
            @endif
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[1.25fr_.75fr]">
        <div class="space-y-5">
            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg font-light">Kelengkapan Data</h3>
                    @if($issues === [])
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[9px] font-semibold text-emerald-800">Lengkap</span>
                    @else
                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-[9px] font-semibold text-red-700">{{ count($issues) }} masalah</span>
                    @endif
                </div>
                @if($issues === [])
                    <p class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-[11px] text-emerald-800">Semua syarat data sudah terpenuhi dan slug valid.</p>
                @else
                    <ul class="mt-3 space-y-1.5">
                        @foreach($issues as $issue)
                            <li class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[11px] text-red-800"><i data-lucide="x-circle" class="mt-0.5 h-3.5 w-3.5 flex-none"></i> {{ $issue }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-[10px] text-[#8a938b]">Minta revisi ke customer agar data dilengkapi sebelum dipublikasikan.</p>
                @endif
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Rangkaian Acara</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @forelse($wedding->events as $event)
                        <div class="rounded-xl border border-[#17211a]/10 bg-[#fbf9f3] p-3.5">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#8e6d2f]">{{ $event->name }}</p>
                            <p class="mt-1 text-xs font-medium">{{ $event->event_date?->translatedFormat('d F Y') ?? 'Tanggal belum diisi' }}</p>
                            <p class="text-[10px] text-[#6b746c]">{{ $event->start_time ? substr($event->start_time,0,5) : '—' }}@if($event->end_time) – {{ substr($event->end_time,0,5) }}@endif</p>
                            <p class="mt-1.5 text-[10px] leading-4 text-[#5d675f]">{{ $event->venue_name }}@if($event->address)<br>{{ $event->address }}@endif</p>
                        </div>
                    @empty
                        <p class="text-[11px] text-[#8a938b]">Belum ada acara.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Galeri ({{ $wedding->photos->count() }})</h3>
                @if($wedding->photos->count())
                    <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-5">
                        @foreach($wedding->photos->take(10) as $photo)
                            <img src="{{ \App\Models\Wedding::assetUrl($photo->path) }}" alt="" class="aspect-square w-full rounded-lg object-cover">
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-[11px] text-[#8a938b]">Belum ada foto galeri.</p>
                @endif
            </section>
        </div>

        <div class="space-y-5">
            @if($wedding->isPublished())
                <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                    <h3 class="flex items-center gap-2 font-display text-lg font-light text-emerald-900"><i data-lucide="badge-check" class="h-4 w-4"></i> Sudah Dipublikasikan</h3>
                    <p class="mt-2 text-[11px] text-emerald-800">Undangan ini telah terbit pada {{ $wedding->published_at?->translatedFormat('d F Y H:i') }}. Konten publik tidak boleh berubah diam-diam; gunakan revisi bila customer meminta perubahan.</p>
                    <a href="{{ route('wedding.invitation.show', $wedding->slug) }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-700 px-4 py-2 text-[11px] font-semibold text-white"><i data-lucide="external-link" class="h-3.5 w-3.5"></i> Buka Undangan Publik</a>

                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3.5">
                        <p class="text-[11px] font-semibold text-red-900">Tarik / Batalkan Publikasi</p>
                        <p class="mt-1 text-[10px] leading-4 text-red-800">Akses publik akan langsung ditutup. Link <span class="font-mono">/wedding/{{ $wedding->slug }}</span> tidak lagi dapat dibuka sampai Admin mempublikasikan ulang.</p>
                        <form method="POST" action="{{ route('wedding.admin.publications.unpublish', $wedding) }}" class="mt-2 space-y-2">
                            @csrf
                            <textarea name="unpublish_notes" rows="2" maxlength="2000" placeholder="Catatan untuk customer (opsional)..." class="w-full rounded-lg border border-red-200 px-3 py-2 text-[11px]">{{ old('unpublish_notes') }}</textarea>
                            <button class="w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-[11px] font-semibold text-red-700 hover:bg-red-100" onclick="return confirm('Tarik publikasi undangan ini? Link publik akan langsung tidak dapat diakses.')">Tarik Publikasi</button>
                        </form>
                    </div>
                </section>
            @else
                <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                    <h3 class="font-display text-lg font-light">Tindakan Admin</h3>
                    <div class="mt-3 space-y-4">
                        {{-- 1. Instruksi pembayaran manual --}}
                        <div class="rounded-xl border border-[#17211a]/10 p-3.5">
                            <p class="text-[11px] font-semibold">1. Konfirmasi instruksi pembayaran</p>
                            <p class="mt-1 text-[10px] leading-4 text-[#8a938b]">Catat bahwa instruksi pembayaran manual sudah dikirim ke customer melalui chat. Status menjadi <b>Menunggu Pembayaran</b>.</p>
                            <form method="POST" action="{{ route('wedding.admin.publications.payment_instruction', $wedding) }}" class="mt-2">
                                @csrf
                                <button class="w-full rounded-lg border border-[#17211a]/20 px-3 py-2 text-[11px] font-semibold hover:border-[#17211a]/45" @disabled($wedding->submissionStatus() === 'menunggu_pembayaran')>Tandai Instruksi Terkirim</button>
                            </form>
                        </div>

                        {{-- 2. Konfirmasi pembayaran diterima --}}
                        <div class="rounded-xl border border-[#17211a]/10 p-3.5">
                            <p class="text-[11px] font-semibold">2. Konfirmasi pembayaran diterima</p>
                            <p class="mt-1 text-[10px] leading-4 text-[#8a938b]">Hanya tandai setelah Anda benar-benar memeriksa pembayaran customer.</p>
                            <form method="POST" action="{{ route('wedding.admin.publications.confirm_payment', $wedding) }}" class="mt-2 space-y-2">
                                @csrf
                                <label class="flex items-start gap-2 rounded-lg bg-[#fbf9f3] p-2.5 text-[10.5px] leading-4 text-[#4f5a51]">
                                    <input type="checkbox" name="payment_checked" value="1" required class="mt-0.5 h-3.5 w-3.5 rounded">
                                    <span>Saya sudah benar-benar memeriksa dan menerima pembayaran customer.</span>
                                </label>
                                <button class="w-full rounded-lg bg-[#17211a] px-3 py-2 text-[11px] font-semibold text-[#f4f1ea]" @disabled($wedding->isPaymentConfirmed())>Pembayaran Dikonfirmasi</button>
                            </form>
                        </div>

                        {{-- 3. Minta revisi --}}
                        <div class="rounded-xl border border-[#17211a]/10 p-3.5">
                            <p class="text-[11px] font-semibold">3. Minta revisi</p>
                            <form method="POST" action="{{ route('wedding.admin.publications.revision', $wedding) }}" class="mt-2 space-y-2">
                                @csrf
                                <textarea name="revision_notes" rows="3" required maxlength="2000" placeholder="Tuliskan catatan revisi untuk customer..." class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-[11px]">{{ old('revision_notes') }}</textarea>
                                <button class="w-full rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[11px] font-semibold text-red-700 hover:bg-red-100">Kirim Permintaan Revisi</button>
                            </form>
                        </div>

                        {{-- 4. Publikasikan --}}
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3.5">
                            <p class="text-[11px] font-semibold text-emerald-900">4. Publikasikan</p>
                            <p class="mt-1 text-[10px] leading-4 text-emerald-800">Publikasi hanya berhasil bila pengajuan aktif, pembayaran dikonfirmasi, data lengkap, dan slug valid.</p>
                            <form method="POST" action="{{ route('wedding.admin.publications.publish', $wedding) }}" class="mt-2">
                                @csrf
                                <button class="w-full rounded-lg bg-emerald-700 px-3 py-2 text-[11px] font-semibold text-white hover:bg-emerald-800" @disabled(! $wedding->isPaymentConfirmed() || $issues !== [])>Publikasikan Undangan</button>
                            </form>
                        </div>
                    </div>
                </section>
            @endif

            @if($wedding->revision_notes)
                <section class="rounded-2xl border border-red-200 bg-red-50 p-5">
                    <h3 class="font-display text-lg font-light text-red-900">Catatan Revisi Aktif</h3>
                    <p class="mt-2 text-[11px] text-red-800">{{ $wedding->revision_notes }}</p>
                </section>
            @endif

            @if($paymentNote)
                <section class="rounded-2xl border border-[#17211a]/10 bg-white/60 p-5">
                    <h3 class="font-display text-lg font-light">Catatan Pembayaran Manual</h3>
                    <p class="mt-2 text-[11px] leading-5 text-[#6b746c]">{{ $paymentNote }}</p>
                    <a href="{{ route('wedding.admin.settings.edit') }}" class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-[#8e6d2f]">Ubah di Pengaturan <i data-lucide="arrow-right" class="h-3 w-3"></i></a>
                </section>
            @endif
        </div>
    </div>
</div>
@endsection
