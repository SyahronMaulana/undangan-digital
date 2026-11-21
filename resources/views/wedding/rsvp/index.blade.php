@extends('wedding.layouts.dashboard')

@section('title', 'Konfirmasi Kehadiran')
@section('eyebrow', 'RSVP')
@section('page-title', 'Konfirmasi Kehadiran')

@section('content')
<div class="space-y-5">
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Akan Hadir', 'value' => $summary['hadir'], 'icon' => 'check-circle-2', 'tone' => 'text-emerald-700'],
            ['label' => 'Total Orang', 'value' => $summary['orang'], 'icon' => 'users', 'tone' => 'text-[#17211a]'],
            ['label' => 'Tidak Hadir', 'value' => $summary['tidak_hadir'], 'icon' => 'user-x', 'tone' => 'text-red-600'],
            ['label' => 'Belum Konfirmasi', 'value' => $summary['belum'], 'icon' => 'clock-3', 'tone' => 'text-amber-700'],
        ] as $stat)
            <div class="rounded-xl border border-[#17211a]/10 bg-white/70 px-4 py-3.5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <p class="text-[9px] uppercase tracking-[.16em] text-[#8a938b]">{{ $stat['label'] }}</p>
                    <i data-lucide="{{ $stat['icon'] }}" class="h-3.5 w-3.5 text-[#a8b0a9]"></i>
                </div>
                <p class="mt-2 font-display text-2xl {{ $stat['tone'] }}">{{ number_format($stat['value'], 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-4 backdrop-blur sm:p-5">
        <form method="GET" action="{{ route('wedding.rsvp.index') }}" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <select name="wedding" class="rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                <option value="">Semua undangan</option>
                @foreach($weddings as $item)<option value="{{ $item->id }}" @selected($wedding?->id === $item->id)>{{ $item->coupleNames() }}</option>@endforeach
            </select>
            <select name="attendance" class="rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                <option value="">Semua jawaban</option>
                <option value="hadir" @selected(request('attendance') === 'hadir')>Akan hadir</option>
                <option value="tidak_hadir" @selected(request('attendance') === 'tidak_hadir')>Tidak hadir</option>
                <option value="ragu" @selected(request('attendance') === 'ragu')>Belum pasti</option>
            </select>
            <div class="flex items-center gap-2 rounded-lg border border-[#17211a]/15 bg-white px-3 py-2">
                <i data-lucide="search" class="h-3.5 w-3.5 text-[#a8b0a9]"></i>
                <input name="q" value="{{ request('q') }}" placeholder="Cari nama tamu..." class="w-full border-0 bg-transparent text-xs outline-none">
            </div>
            <button class="rounded-lg bg-[#17211a] px-3 py-2 text-xs font-semibold text-[#f4f1ea]">Terapkan Filter</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
        <div class="flex items-center justify-between gap-3 border-b border-[#17211a]/10 px-4 py-3">
            <p class="text-[11px] font-semibold">{{ $rsvps->total() }} konfirmasi</p>
            <span class="text-[10px] text-[#8a938b]">Real-time dari undangan publik</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-xs">
                <thead class="bg-[#f4f1ea]/70 text-[9px] uppercase tracking-[.14em] text-[#8a938b]">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Nama</th>
                        <th class="px-4 py-2.5 font-semibold">Undangan</th>
                        <th class="px-4 py-2.5 font-semibold">Jawaban</th>
                        <th class="px-4 py-2.5 font-semibold">Jumlah</th>
                        <th class="px-4 py-2.5 font-semibold">Pesan</th>
                        <th class="px-4 py-2.5 font-semibold">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#17211a]/5">
                    @forelse($rsvps as $rsvp)
                        @php $tone = $rsvp->attendance === 'hadir' ? 'bg-emerald-100 text-emerald-800' : ($rsvp->attendance === 'tidak_hadir' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800'); @endphp
                        <tr class="hover:bg-[#fbf9f3]">
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $rsvp->name }}</p>
                                <p class="text-[10px] text-[#8a938b]">{{ $rsvp->guest?->group_name ?: ($rsvp->guest ? 'Tamu terdaftar' : 'Tamu publik') }}</p>
                            </td>
                            <td class="px-4 py-3 text-[#6b746c]">{{ $rsvp->wedding?->coupleNames() }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $tone }}">{{ $rsvp->attendanceLabel() }}</span></td>
                            <td class="px-4 py-3">{{ $rsvp->attendance === 'hadir' ? $rsvp->guests_count.' orang' : '—' }}</td>
                            <td class="max-w-[220px] px-4 py-3 text-[11px] leading-5 text-[#5d675f]">{{ $rsvp->message ?: '—' }}</td>
                            <td class="px-4 py-3 text-[10px] text-[#8a938b]">{{ $rsvp->created_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-[#8a938b]">Belum ada konfirmasi kehadiran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rsvps->hasPages())<div class="border-t border-[#17211a]/10 px-4 py-3">{{ $rsvps->links() }}</div>@endif
    </section>
</div>
@endsection
