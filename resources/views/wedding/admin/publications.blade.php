@extends('wedding.layouts.admin')

@section('title', 'Pengajuan Publikasi')
@section('eyebrow', 'Verifikasi & Publikasi')
@section('page-title', 'Pengajuan Publikasi')

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

@section('content')
<div class="space-y-5">
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['key' => 'menunggu_konfirmasi', 'label' => 'Menunggu Konfirmasi', 'icon' => 'inbox'],
            ['key' => 'menunggu_pembayaran', 'label' => 'Menunggu Pembayaran', 'icon' => 'wallet'],
            ['key' => 'menunggu_verifikasi', 'label' => 'Menunggu Verifikasi', 'icon' => 'search-check'],
            ['key' => 'pembayaran_dikonfirmasi', 'label' => 'Pembayaran Dikonfirmasi', 'icon' => 'badge-check'],
            ['key' => 'revisi', 'label' => 'Perlu Revisi', 'icon' => 'undo-2'],
        ] as $card)
            <a href="{{ route('wedding.admin.publications.index', ['submission' => $card['key']]) }}" class="rounded-xl border border-[#17211a]/10 bg-white/75 p-4 backdrop-blur transition hover:border-[#c7a15a]/60">
                <div class="flex items-center justify-between"><p class="text-[9px] uppercase tracking-[.16em] text-[#8a938b]">{{ $card['label'] }}</p><i data-lucide="{{ $card['icon'] }}" class="h-3.5 w-3.5 text-[#a8b0a9]"></i></div>
                <p class="mt-2 font-display text-2xl">{{ $statusCounts[$card['key']] ?? 0 }}</p>
            </a>
        @endforeach
    </section>

    <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-4 backdrop-blur">
        <form method="GET" action="{{ route('wedding.admin.publications.index') }}" class="flex flex-wrap gap-2">
            <div class="flex min-w-[240px] flex-1 items-center gap-2 rounded-lg border border-[#17211a]/15 bg-white px-3 py-2">
                <i data-lucide="search" class="h-4 w-4 text-[#a8b0a9]"></i>
                <input name="q" value="{{ request('q') }}" placeholder="Cari slug atau nama mempelai..." class="w-full border-0 bg-transparent text-xs outline-none">
            </div>
            <select name="submission" class="rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                <option value="">Semua status pengajuan</option>
                @foreach(\App\Models\Wedding::SUBMISSION_LABELS as $key => $label)
                    @continue($key === 'draft')
                    <option value="{{ $key }}" @selected(request('submission') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-[#17211a] px-4 py-2 text-xs font-semibold text-[#f4f1ea]">Filter</button>
            @if(request()->hasAny(['q', 'submission']))
                <a href="{{ route('wedding.admin.publications.index') }}" class="rounded-lg border border-[#17211a]/15 px-4 py-2 text-xs font-medium">Reset</a>
            @endif
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
        <div class="flex items-center justify-between border-b border-[#17211a]/10 px-4 py-3"><p class="text-[11px] font-semibold">{{ $submissions->total() }} pengajuan</p><span class="text-[10px] text-[#8a938b]">Diurutkan berdasarkan prioritas & waktu pengajuan</span></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1080px] text-left text-xs">
                <thead class="bg-[#f4f1ea]/70 text-[9px] uppercase tracking-[.14em] text-[#8a938b]">
                    <tr>
                        <th class="px-4 py-2.5">Customer</th>
                        <th class="px-4 py-2.5">Pasangan & Judul</th>
                        <th class="px-4 py-2.5">Template</th>
                        <th class="px-4 py-2.5">Alamat</th>
                        <th class="px-4 py-2.5">Diajukan</th>
                        <th class="px-4 py-2.5">Pengajuan</th>
                        <th class="px-4 py-2.5">Pembayaran</th>
                        <th class="px-4 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#17211a]/5">
                    @forelse($submissions as $submission)
                        <tr class="hover:bg-[#fbf9f3]">
                            <td class="px-4 py-3"><p class="font-semibold">{{ $submission->user?->name }}</p><p class="text-[10px] text-[#8a938b]">{{ $submission->user?->phone ?: $submission->user?->email }}</p></td>
                            <td class="px-4 py-3"><p class="font-semibold">{{ $submission->coupleNames() }}</p><p class="text-[10px] text-[#8a938b]">{{ $submission->cover_caption ?: $submission->coupleNames() }}</p></td>
                            <td class="px-4 py-3">{{ $submission->template?->name }}</td>
                            <td class="px-4 py-3"><p class="font-mono text-[10px] text-[#8a938b]">/wedding/{{ $submission->slug }}</p></td>
                            <td class="px-4 py-3 text-[#6b746c]">{{ $submission->submitted_at?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $submissionTones[$submission->submissionStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $submission->submissionStatusLabel() }}</span></td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $paymentTones[$submission->paymentStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $submission->paymentStatusLabel() }}</span></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('wedding.admin.publications.show', $submission) }}" class="rounded-lg bg-[#17211a] px-2.5 py-1.5 text-[9px] font-semibold text-[#f4f1ea]">Detail</a>
                                    <a href="{{ route('wedding.admin.publications.preview', $submission) }}" target="_blank" rel="noopener" class="rounded-lg border border-[#17211a]/15 px-2.5 py-1.5 text-[9px] font-semibold hover:border-[#17211a]/35">Preview</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-[#8a938b]">Belum ada pengajuan publikasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($submissions->hasPages())<div class="border-t border-[#17211a]/10 px-4 py-3">{{ $submissions->links() }}</div>@endif
    </section>
</div>
@endsection
