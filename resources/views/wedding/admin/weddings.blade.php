@extends('wedding.layouts.admin')

@section('title', 'Undangan')
@section('eyebrow', 'Semua Undangan')
@section('page-title', 'Undangan')

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
@endphp
<div class="space-y-5">
    <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-4 backdrop-blur">
        <form method="GET" action="{{ route('wedding.admin.weddings.index') }}" class="flex flex-wrap gap-2">
            <div class="flex min-w-[240px] flex-1 items-center gap-2 rounded-lg border border-[#17211a]/15 bg-white px-3 py-2">
                <i data-lucide="search" class="h-4 w-4 text-[#a8b0a9]"></i>
                <input name="q" value="{{ request('q') }}" placeholder="Cari slug atau nama mempelai..." class="w-full border-0 bg-transparent text-xs outline-none">
            </div>
            <select name="status" class="rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                <option value="">Semua status</option>
                <option value="published" @selected(request('status') === 'published')>Terbit</option>
                <option value="draft" @selected(request('status') === 'draft')>Draf</option>
            </select>
            <select name="submission" class="rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                <option value="">Semua pengajuan</option>
                @foreach(\App\Models\Wedding::SUBMISSION_LABELS as $key => $label)
                    <option value="{{ $key }}" @selected(request('submission') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-[#17211a] px-4 py-2 text-xs font-semibold text-[#f4f1ea]">Filter</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
        <div class="flex items-center justify-between border-b border-[#17211a]/10 px-4 py-3"><p class="text-[11px] font-semibold">{{ $weddings->total() }} undangan</p><span class="text-[10px] text-[#8a938b]">Data seluruh customer</span></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left text-xs">
                <thead class="bg-[#f4f1ea]/70 text-[9px] uppercase tracking-[.14em] text-[#8a938b]"><tr><th class="px-4 py-2.5">Pasangan</th><th class="px-4 py-2.5">Customer</th><th class="px-4 py-2.5">Template</th><th class="px-4 py-2.5">Statistik</th><th class="px-4 py-2.5">Status Undangan</th><th class="px-4 py-2.5">Pengajuan</th><th class="px-4 py-2.5">Pembayaran</th><th class="px-4 py-2.5">Aksi</th></tr></thead>
                <tbody class="divide-y divide-[#17211a]/5">
                    @forelse($weddings as $wedding)
                        <tr class="hover:bg-[#fbf9f3]">
                            <td class="px-4 py-3"><p class="font-semibold">{{ $wedding->coupleNames() }}</p><p class="font-mono text-[10px] text-[#8a938b]">/wedding/{{ $wedding->slug }}</p></td>
                            <td class="px-4 py-3"><p>{{ $wedding->user?->name }}</p><p class="text-[10px] text-[#8a938b]">{{ $wedding->user?->email }}</p></td>
                            <td class="px-4 py-3">{{ $wedding->template?->name }}</td>
                            <td class="px-4 py-3 text-[10px] text-[#6b746c]">{{ $wedding->guests_count }} tamu · {{ $wedding->rsvps_count }} RSVP · {{ $wedding->wishes_count }} ucapan</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $wedding->isPublished() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $wedding->isPublished() ? 'Terbit' : 'Draf' }}</span></td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $submissionTones[$wedding->submissionStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $wedding->submissionStatusLabel() }}</span></td>
                            <td class="px-4 py-3"><span class="text-[10px] text-[#6b746c]">{{ $wedding->paymentStatusLabel() }}</span></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('wedding.invitation.show', $wedding->slug) }}" target="_blank" rel="noopener" class="rounded-lg border border-[#17211a]/15 px-2 py-1 text-[9px] font-semibold hover:border-[#17211a]/35">Lihat</a>
                                    <a href="{{ route('wedding.admin.publications.show', $wedding) }}" class="rounded-lg border border-[#17211a]/15 px-2 py-1 text-[9px] font-semibold hover:border-[#17211a]/35">Kelola</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-[#8a938b]">Belum ada undangan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($weddings->hasPages())<div class="border-t border-[#17211a]/10 px-4 py-3">{{ $weddings->links() }}</div>@endif
    </section>
</div>
@endsection
