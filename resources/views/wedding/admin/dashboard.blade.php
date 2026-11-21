@extends('wedding.layouts.admin')

@section('title', 'Ringkasan Admin')
@section('eyebrow', 'Wedding Administration')
@section('page-title', 'Ringkasan')

@section('content')
@php
    $maxMonth = max(1, $months->max('total'));
    $submissionTones = [
        'menunggu_konfirmasi' => 'bg-amber-100 text-amber-800',
        'menunggu_pembayaran' => 'bg-orange-100 text-orange-800',
        'menunggu_verifikasi' => 'bg-sky-100 text-sky-800',
        'pembayaran_dikonfirmasi' => 'bg-emerald-100 text-emerald-800',
        'dipublikasikan' => 'bg-emerald-600 text-white',
        'revisi' => 'bg-red-100 text-red-700',
        'draft' => 'bg-[#17211a]/5 text-[#6b746c]',
    ];
@endphp
<div class="space-y-5">
    @unless($adminWhatsAppConfigured ?? true)
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert">
            <i data-lucide="alert-triangle" class="h-4 w-4 flex-none"></i>
            <span class="flex-1">Nomor WhatsApp admin belum dikonfigurasi. Tombol WhatsApp customer tidak akan berfungsi sampai nomor diatur.</span>
            <a href="{{ route('wedding.admin.settings.edit') }}#whatsapp-admin" class="rounded-full bg-[#17211a] px-4 py-1.5 text-[11px] font-semibold text-[#f4f1ea]">Atur Nomor</a>
        </div>
    @endunless
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Customer', 'value' => $stats['customer'], 'icon' => 'users', 'tone' => 'text-[#17211a]'],
            ['label' => 'Total Undangan', 'value' => $stats['undangan'], 'icon' => 'mail', 'tone' => 'text-[#17211a]'],
            ['label' => 'Undangan Terbit', 'value' => $stats['terbit'], 'icon' => 'globe', 'tone' => 'text-emerald-700'],
            ['label' => 'Pengajuan Aktif', 'value' => $stats['pengajuan'], 'icon' => 'send', 'tone' => 'text-[#8e6d2f]'],
        ] as $card)
            <div class="rounded-xl border border-[#17211a]/10 bg-white/75 p-4 backdrop-blur">
                <div class="flex items-center justify-between"><p class="text-[9px] uppercase tracking-[.18em] text-[#8a938b]">{{ $card['label'] }}</p><i data-lucide="{{ $card['icon'] }}" class="h-3.5 w-3.5 text-[#a8b0a9]"></i></div>
                <p class="mt-2 font-display text-2xl {{ $card['tone'] }}">{{ number_format($card['value'], 0, ',', '.') }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid gap-3 sm:grid-cols-3">
        @foreach([
            ['label' => 'Total Tamu', 'value' => $stats['tamu']],
            ['label' => 'Konfirmasi Hadir', 'value' => $stats['hadir']],
            ['label' => 'Ucapan', 'value' => $stats['ucapan']],
        ] as $card)
            <div class="flex items-center justify-between rounded-xl border border-[#17211a]/10 bg-white/50 px-4 py-3 backdrop-blur">
                <span class="text-[11px] text-[#6b746c]">{{ $card['label'] }}</span><span class="font-display text-lg">{{ number_format($card['value'], 0, ',', '.') }}</span>
            </div>
        @endforeach
    </section>

    <div class="grid gap-5 xl:grid-cols-[1.1fr_.9fr]">
        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
            <div class="flex items-center justify-between"><h3 class="font-display text-lg font-light">Undangan per Bulan</h3><span class="text-[10px] text-[#8a938b]">6 bulan terakhir</span></div>
            <div class="mt-5 flex h-44 items-end gap-3">
                @foreach($months as $month)
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <span class="text-[9px] font-semibold text-[#6b746c]">{{ $month['total'] }}</span>
                        <div class="w-full rounded-t-md bg-gradient-to-t from-[#17211a] to-[#4f6a58]" style="height: {{ max(4, ($month['total'] / $maxMonth) * 130) }}px"></div>
                        <span class="text-[9px] uppercase tracking-wider text-[#8a938b]">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
            <div class="flex items-center justify-between border-b border-[#17211a]/10 px-4 py-3"><h3 class="font-display text-lg font-light">Pengajuan Publikasi Terbaru</h3><a href="{{ route('wedding.admin.publications.index') }}" class="text-[10px] font-semibold text-[#8e6d2f]">Semua</a></div>
            <div class="divide-y divide-[#17211a]/5">
                @forelse($latestSubmissions as $submission)
                    <a href="{{ route('wedding.admin.publications.show', $submission) }}" class="flex items-center gap-3 px-4 py-3 transition hover:bg-[#fbf9f3]">
                        <div class="min-w-0 flex-1"><p class="truncate text-xs font-semibold">{{ $submission->coupleNames() }}</p><p class="text-[10px] text-[#8a938b]">{{ $submission->user?->name }} · {{ $submission->submitted_at?->translatedFormat('d M Y H:i') ?? '—' }}</p></div>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $submissionTones[$submission->submissionStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $submission->submissionStatusLabel() }}</span>
                    </a>
                @empty
                    <p class="px-4 py-8 text-center text-[11px] text-[#8a938b]">Belum ada pengajuan publikasi.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
        <div class="flex items-center justify-between border-b border-[#17211a]/10 px-4 py-3"><h3 class="font-display text-lg font-light">Undangan Terbaru</h3><a href="{{ route('wedding.admin.weddings.index') }}" class="text-[10px] font-semibold text-[#8e6d2f]">Semua</a></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-xs">
                <thead class="bg-[#f4f1ea]/70 text-[9px] uppercase tracking-[.14em] text-[#8a938b]"><tr><th class="px-4 py-2.5">Pasangan</th><th class="px-4 py-2.5">Customer</th><th class="px-4 py-2.5">Template</th><th class="px-4 py-2.5">Status</th><th class="px-4 py-2.5">Tanggal</th></tr></thead>
                <tbody class="divide-y divide-[#17211a]/5">
                    @forelse($latestWeddings as $wedding)
                        <tr class="hover:bg-[#fbf9f3]">
                            <td class="px-4 py-3"><p class="font-semibold">{{ $wedding->coupleNames() }}</p><p class="font-mono text-[10px] text-[#8a938b]">/{{ $wedding->slug }}</p></td>
                            <td class="px-4 py-3">{{ $wedding->user?->name }}</td>
                            <td class="px-4 py-3">{{ $wedding->template?->name }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $wedding->isPublished() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $wedding->isPublished() ? 'Terbit' : 'Draf' }}</span></td>
                            <td class="px-4 py-3 text-[#6b746c]">{{ $wedding->created_at?->translatedFormat('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-[#8a938b]">Belum ada undangan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
