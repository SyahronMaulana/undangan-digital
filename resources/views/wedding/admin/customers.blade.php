@extends('wedding.layouts.admin')

@section('title', 'Customer')
@section('eyebrow', 'Manajemen Pengguna')
@section('page-title', 'Customer')

@section('content')
<div class="space-y-5">
    <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-4 backdrop-blur">
        <form method="GET" action="{{ route('wedding.admin.customers.index') }}" class="flex flex-wrap gap-2">
            <div class="flex min-w-[240px] flex-1 items-center gap-2 rounded-lg border border-[#17211a]/15 bg-white px-3 py-2">
                <i data-lucide="search" class="h-4 w-4 text-[#a8b0a9]"></i>
                <input name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau telepon..." class="w-full border-0 bg-transparent text-xs outline-none">
            </div>
            <button class="rounded-lg bg-[#17211a] px-4 py-2 text-xs font-semibold text-[#f4f1ea]">Cari</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
        <div class="flex items-center justify-between border-b border-[#17211a]/10 px-4 py-3"><p class="text-[11px] font-semibold">{{ $customers->total() }} customer</p><span class="text-[10px] text-[#8a938b]">Role customer only</span></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-xs">
                <thead class="bg-[#f4f1ea]/70 text-[9px] uppercase tracking-[.14em] text-[#8a938b]"><tr><th class="px-4 py-2.5">Customer</th><th class="px-4 py-2.5">Kontak</th><th class="px-4 py-2.5">Undangan</th><th class="px-4 py-2.5">Terbit</th><th class="px-4 py-2.5">Terdaftar</th><th class="px-4 py-2.5"></th></tr></thead>
                <tbody class="divide-y divide-[#17211a]/5">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-[#fbf9f3]">
                            <td class="px-4 py-3"><div class="flex items-center gap-2.5"><span class="grid h-8 w-8 place-items-center rounded-full bg-[#17211a]/5 font-display text-xs">{{ strtoupper(mb_substr($customer->name,0,1)) }}</span><div><p class="font-semibold">{{ $customer->name }}</p><p class="text-[10px] text-[#8a938b]">{{ $customer->email }}</p></div></div></td>
                            <td class="px-4 py-3 text-[#6b746c]">{{ $customer->phone ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $customer->weddings_count }}</td>
                            <td class="px-4 py-3">{{ $customer->published_weddings_count }}</td>
                            <td class="px-4 py-3 text-[#6b746c]">{{ $customer->created_at?->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('wedding.admin.customers.show', $customer) }}" class="rounded-lg border border-[#17211a]/15 px-2.5 py-1.5 text-[10px] font-semibold hover:border-[#17211a]/35">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-[#8a938b]">Belum ada customer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())<div class="border-t border-[#17211a]/10 px-4 py-3">{{ $customers->links() }}</div>@endif
    </section>
</div>
@endsection
