@extends('wedding.layouts.dashboard')

@section('title', 'Ucapan & Doa')
@section('eyebrow', 'Moderasi Ucapan')
@section('page-title', 'Ucapan & Doa')

@section('content')
<div class="space-y-5">
    <div class="grid gap-3 sm:grid-cols-3">
        @foreach([
            ['label' => 'Semua Ucapan', 'value' => $counts['all'], 'icon' => 'message-heart'],
            ['label' => 'Ditampilkan', 'value' => $counts['approved'], 'icon' => 'eye'],
            ['label' => 'Disembunyikan', 'value' => $counts['hidden'], 'icon' => 'eye-off'],
        ] as $stat)
            <div class="flex items-center gap-3 rounded-xl border border-[#17211a]/10 bg-white/70 px-4 py-3.5 backdrop-blur">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-[#17211a]/5 text-[#4f5a51]"><i data-lucide="{{ $stat['icon'] }}" class="h-4 w-4"></i></span>
                <div><p class="text-[9px] uppercase tracking-[.16em] text-[#8a938b]">{{ $stat['label'] }}</p><p class="font-display text-xl">{{ $stat['value'] }}</p></div>
            </div>
        @endforeach
    </div>

    <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-4 backdrop-blur sm:p-5">
        <form method="GET" action="{{ route('wedding.wishes.index') }}" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <select name="wedding" class="rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                <option value="">Semua undangan</option>
                @foreach($weddings as $item)<option value="{{ $item->id }}" @selected($wedding?->id === $item->id)>{{ $item->coupleNames() }}</option>@endforeach
            </select>
            <select name="status" class="rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                <option value="">Semua status</option>
                <option value="approved" @selected(request('status') === 'approved')>Ditampilkan</option>
                <option value="hidden" @selected(request('status') === 'hidden')>Disembunyikan</option>
            </select>
            <div class="flex items-center gap-2 rounded-lg border border-[#17211a]/15 bg-white px-3 py-2">
                <i data-lucide="search" class="h-3.5 w-3.5 text-[#a8b0a9]"></i>
                <input name="q" value="{{ request('q') }}" placeholder="Cari nama atau isi..." class="w-full border-0 bg-transparent text-xs outline-none">
            </div>
            <button class="rounded-lg bg-[#17211a] px-3 py-2 text-xs font-semibold text-[#f4f1ea]">Terapkan</button>
        </form>
    </section>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($wishes as $wish)
            <article class="flex flex-col rounded-2xl border border-[#17211a]/10 bg-white/75 p-4 backdrop-blur">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold">{{ $wish->name }}</p>
                        <p class="text-[10px] text-[#8a938b]">{{ $wish->wedding?->coupleNames() }} · {{ $wish->created_at?->diffForHumans() }}</p>
                    </div>
                    <span class="flex-none rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $wish->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-[#17211a]/10 text-[#6b746c]' }}">{{ $wish->status === 'approved' ? 'Tampil' : 'Disembunyikan' }}</span>
                </div>
                <p class="mt-3 flex-1 text-[12px] leading-6 text-[#4f5a51]">{{ $wish->message }}</p>
                <div class="mt-4 flex items-center gap-2 border-t border-[#17211a]/5 pt-3">
                    <form method="POST" action="{{ route('wedding.wishes.moderate', $wish) }}" class="flex-1">@csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $wish->status === 'approved' ? 'hidden' : 'approved' }}">
                        <button class="w-full rounded-lg border border-[#17211a]/15 px-3 py-1.5 text-[10px] font-semibold hover:border-[#17211a]/35">{{ $wish->status === 'approved' ? 'Sembunyikan' : 'Tampilkan' }}</button>
                    </form>
                    <form method="POST" action="{{ route('wedding.wishes.destroy', $wish) }}" onsubmit="return confirm('Hapus ucapan ini?')">@csrf @method('DELETE')
                        <button class="rounded-lg border border-red-200 bg-red-50 p-1.5 text-red-600 hover:bg-red-100" title="Hapus"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button>
                    </form>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-[#17211a]/20 px-6 py-14 text-center text-[#8a938b]">
                <i data-lucide="message-heart" class="mx-auto h-7 w-7"></i>
                <p class="mt-2 text-sm">Belum ada ucapan dan doa.</p>
            </div>
        @endforelse
    </div>

    @if($wishes->hasPages())<div>{{ $wishes->links() }}</div>@endif
</div>
@endsection
