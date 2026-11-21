@extends('wedding.layouts.admin')

@section('title', $customer->name)
@section('eyebrow', 'Detail Customer')
@section('page-title', $customer->name)

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
    $published = $customer->weddings->where('status', 'published')->count();
@endphp

@section('actions')
    <a href="{{ route('wedding.admin.customers.index') }}" class="inline-flex items-center gap-1.5 rounded-full border border-[#17211a]/15 px-3.5 py-2 text-[11px] font-medium text-[#4f5a51] hover:border-[#17211a]/35"><i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Kembali</a>
@endsection

@section('content')
<div class="space-y-5">
    <div class="grid gap-5 lg:grid-cols-[.9fr_1.1fr]">
        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
            <div class="flex items-center gap-4">
                <span class="grid h-14 w-14 place-items-center rounded-full bg-[#17211a]/5 font-display text-xl">{{ strtoupper(mb_substr($customer->name,0,1)) }}</span>
                <div><h2 class="font-display text-xl font-light">{{ $customer->name }}</h2><p class="text-[11px] text-[#8a938b]">{{ $customer->email }}</p></div>
            </div>
            <dl class="mt-4 space-y-2 text-[11px]">
                @foreach([
                    ['Telepon', $customer->phone ?: '—'],
                    ['Terdaftar', $customer->created_at?->translatedFormat('d F Y')],
                    ['Total Undangan', $customer->weddings->count().' undangan'],
                    ['Sudah Terbit', $published.' undangan'],
                    ['Akses', 'Penuh (semua template & fitur)'],
                ] as [$label, $value])
                    <div class="flex justify-between gap-3 border-b border-[#17211a]/5 pb-2"><dt class="text-[#8a938b]">{{ $label }}</dt><dd class="font-medium">{{ $value }}</dd></div>
                @endforeach
            </dl>
        </section>

        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
            <h3 class="font-display text-lg font-light">Ringkasan Pengajuan</h3>
            <div class="mt-3 grid grid-cols-2 gap-3 text-[11px]">
                @foreach([
                    ['Menunggu Diproses', $customer->weddings->filter(fn ($w) => $w->isAwaitingPublication())->count()],
                    ['Terbit', $published],
                    ['Draf', $customer->weddings->where('status', 'draft')->count()],
                    ['Perlu Revisi', $customer->weddings->where('submission_status', 'revisi')->count()],
                ] as [$label, $value])
                    <div class="rounded-lg bg-[#f4f1ea] px-3 py-2.5"><dt class="text-[9px] uppercase tracking-wider text-[#8a938b]">{{ $label }}</dt><dd class="mt-0.5 font-display text-lg">{{ $value }}</dd></div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
        <div class="border-b border-[#17211a]/10 px-4 py-3"><h3 class="font-display text-lg font-light">Undangan Customer</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-left text-xs">
                <thead class="bg-[#f4f1ea]/70 text-[9px] uppercase tracking-[.14em] text-[#8a938b]"><tr><th class="px-4 py-2.5">Pasangan</th><th class="px-4 py-2.5">Template</th><th class="px-4 py-2.5">Status</th><th class="px-4 py-2.5">Pengajuan</th><th class="px-4 py-2.5">Pembayaran</th><th class="px-4 py-2.5">Tamu</th><th class="px-4 py-2.5"></th></tr></thead>
                <tbody class="divide-y divide-[#17211a]/5">
                    @forelse($customer->weddings as $wedding)
                        <tr class="hover:bg-[#fbf9f3]">
                            <td class="px-4 py-3"><p class="font-semibold">{{ $wedding->coupleNames() }}</p><p class="font-mono text-[10px] text-[#8a938b]">/{{ $wedding->slug }}</p></td>
                            <td class="px-4 py-3">{{ $wedding->template?->name }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $wedding->isPublished() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $wedding->isPublished() ? 'Terbit' : 'Draf' }}</span></td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $submissionTones[$wedding->submissionStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $wedding->submissionStatusLabel() }}</span></td>
                            <td class="px-4 py-3 text-[10px] text-[#6b746c]">{{ $wedding->paymentStatusLabel() }}</td>
                            <td class="px-4 py-3">{{ $wedding->guests()->count() }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('wedding.invitation.show', $wedding->slug) }}" target="_blank" rel="noopener" class="rounded-lg border border-[#17211a]/15 px-2.5 py-1.5 text-[10px] font-semibold hover:border-[#17211a]/35">Lihat</a>
                                    <a href="{{ route('wedding.admin.publications.show', $wedding) }}" class="rounded-lg border border-[#17211a]/15 px-2.5 py-1.5 text-[10px] font-semibold hover:border-[#17211a]/35">Kelola</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-[#8a938b]">Belum ada undangan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
