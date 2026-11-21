@extends('wedding.layouts.dashboard')

@section('title', 'Daftar Tamu')
@section('eyebrow', 'Manajemen Tamu')
@section('page-title', 'Daftar Tamu')

@section('actions')
    <div class="flex items-center gap-1.5" x-data="{ openImport: false }">
        <a href="{{ route('wedding.guests.export', $wedding ? ['wedding' => $wedding->id] : []) }}" class="hidden items-center gap-1.5 rounded-full border border-[#17211a]/15 px-3.5 py-2 text-[11px] font-medium text-[#4f5a51] transition hover:border-[#17211a]/35 sm:inline-flex">
            <i data-lucide="download" class="h-3.5 w-3.5"></i> Ekspor CSV
        </a>
        <button type="button" @click="openImport = true" class="inline-flex items-center gap-1.5 rounded-full border border-[#17211a]/15 px-3.5 py-2 text-[11px] font-medium text-[#4f5a51] transition hover:border-[#17211a]/35">
            <i data-lucide="upload" class="h-3.5 w-3.5"></i> Impor
        </button>
        <button type="button" onclick="document.getElementById('add-guest-panel').scrollIntoView({behavior:'smooth'})" class="inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-4 py-2 text-[11px] font-semibold text-[#f4f1ea] transition hover:bg-[#26352b]">
            <i data-lucide="user-plus" class="h-3.5 w-3.5"></i> Tambah Tamu
        </button>

        {{-- Import modal --}}
        <div x-show="openImport" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-[#0c120e]/55 p-4 backdrop-blur-sm" @click.self="openImport = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div><h3 class="font-display text-xl">Impor Tamu</h3><p class="text-[11px] text-[#8a938b]">Unggah CSV dengan header nama, telepon, grup, kuota.</p></div>
                    <button type="button" @click="openImport = false" class="rounded-lg p-1.5 text-[#8a938b] hover:bg-[#17211a]/5"><i data-lucide="x" class="h-4 w-4"></i></button>
                </div>
                <form method="POST" action="{{ route('wedding.guests.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-medium text-[#4f5a51]">Undangan tujuan</label>
                        <select name="wedding_id" required class="mt-1 w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm">
                            @foreach($weddings as $item)<option value="{{ $item->id }}" @selected($wedding?->id === $item->id)>{{ $item->coupleNames() }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-[#4f5a51]">Berkas CSV</label>
                        <input type="file" name="file" accept=".csv,.txt" required class="mt-1 w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs">
                        <p class="mt-1 text-[10px] text-[#8a938b]">Contoh: <code>nama,telepon,grup,kuota</code>. Maks 5MB.</p>
                    </div>
                    <button class="w-full rounded-full bg-[#17211a] px-4 py-2.5 text-xs font-semibold text-[#f4f1ea]">Impor Sekarang</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-5" x-data="guestPage()">
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Total Tamu', 'value' => $totalGuests, 'icon' => 'users'],
            ['label' => 'Sudah RSVP', 'value' => $guests->getCollection()->whereNotNull('rsvp')->count(), 'icon' => 'clipboard-check'],
            ['label' => 'Dengan WhatsApp', 'value' => $guests->getCollection()->whereNotNull('phone')->count(), 'icon' => 'message-circle'],
            ['label' => 'Belum RSVP', 'value' => $guests->getCollection()->whereNull('rsvp')->count(), 'icon' => 'user-x'],
        ] as $stat)
            <div class="flex items-center gap-3 rounded-xl border border-[#17211a]/10 bg-white/70 px-4 py-3.5 backdrop-blur">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-[#17211a]/5 text-[#4f5a51]"><i data-lucide="{{ $stat['icon'] }}" class="h-4 w-4"></i></span>
                <div><p class="text-[9px] uppercase tracking-[.16em] text-[#8a938b]">{{ $stat['label'] }}</p><p class="font-display text-xl">{{ $stat['value'] }}</p></div>
            </div>
        @endforeach
    </div>

    <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-4 backdrop-blur sm:p-5">
        <form method="GET" action="{{ route('wedding.guests.index') }}" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label class="sr-only">Cari tamu</label>
                <div class="flex items-center gap-2 rounded-lg border border-[#17211a]/15 bg-white px-3 py-2">
                    <i data-lucide="search" class="h-4 w-4 text-[#a8b0a9]"></i>
                    <input name="q" value="{{ request('q') }}" placeholder="Cari nama atau nomor..." class="w-full border-0 bg-transparent text-xs outline-none">
                </div>
            </div>
            <select name="wedding" class="rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                <option value="">Semua undangan</option>
                @foreach($weddings as $item)<option value="{{ $item->id }}" @selected($wedding?->id === $item->id)>{{ $item->coupleNames() }}</option>@endforeach
            </select>
            <select name="group" class="rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                <option value="">Semua grup</option>
                @foreach($groups as $group)<option value="{{ $group }}" @selected(request('group') === $group)>{{ $group }}</option>@endforeach
            </select>
            <div class="flex gap-2">
                <select name="status" class="min-w-0 flex-1 rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-xs">
                    <option value="">Semua status</option>
                    <option value="sudah_rsvp" @selected(request('status') === 'sudah_rsvp')>Sudah RSVP</option>
                    <option value="belum_rsvp" @selected(request('status') === 'belum_rsvp')>Belum RSVP</option>
                </select>
                <button class="rounded-lg bg-[#17211a] px-3 py-2 text-xs font-semibold text-[#f4f1ea]">Filter</button>
            </div>
        </form>
    </section>

    <div class="grid gap-5 xl:grid-cols-[1.55fr_.45fr]">
        <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
            <div class="flex items-center justify-between gap-3 border-b border-[#17211a]/10 px-4 py-3">
                <p class="text-[11px] font-semibold">{{ $guests->total() }} tamu</p>
                <span class="text-[10px] text-[#8a938b]">Halaman {{ $guests->currentPage() }} dari {{ $guests->lastPage() }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-xs">
                    <thead class="bg-[#f4f1ea]/70 text-[9px] uppercase tracking-[.14em] text-[#8a938b]">
                        <tr>
                            <th class="px-4 py-2.5 font-semibold">Tamu</th>
                            <th class="px-4 py-2.5 font-semibold">Grup</th>
                            <th class="px-4 py-2.5 font-semibold">Kuota</th>
                            <th class="px-4 py-2.5 font-semibold">RSVP</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#17211a]/5">
                        @forelse($guests as $guest)
                            <tr class="hover:bg-[#fbf9f3]">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-[#17211a]">{{ $guest->name }}</p>
                                    <p class="text-[10px] text-[#8a938b]">{{ $guest->phone ?: 'Tanpa nomor' }} · {{ $guest->wedding?->coupleNames() }}</p>
                                </td>
                                <td class="px-4 py-3"><span class="rounded-full bg-[#17211a]/5 px-2 py-0.5 text-[10px]">{{ $guest->group_name ?: '—' }}</span></td>
                                <td class="px-4 py-3">{{ $guest->max_pax }} orang</td>
                                <td class="px-4 py-3">
                                    @if($guest->rsvp)
                                        @php $tone = $guest->rsvp->attendance === 'hadir' ? 'bg-emerald-100 text-emerald-800' : ($guest->rsvp->attendance === 'tidak_hadir' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800'); @endphp
                                        <span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $tone }}">{{ $guest->rsvp->attendanceLabel() }}</span>
                                    @else
                                        <span class="text-[10px] text-[#a8b0a9]">Belum</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="openLink({{ $guest->id }}, @js(route('wedding.guests.link', $guest)))" class="rounded-md p-1.5 text-[#6b746c] hover:bg-[#17211a]/5 hover:text-[#17211a]" title="Link personal"><i data-lucide="link" class="h-3.5 w-3.5"></i></button>
                                        <button type="button" @click="editGuest(@js(['id'=>$guest->id,'name'=>$guest->name,'phone'=>$guest->phone,'group_name'=>$guest->group_name,'max_pax'=>$guest->max_pax,'action'=>route('wedding.guests.update',$guest)]))" class="rounded-md p-1.5 text-[#6b746c] hover:bg-[#17211a]/5 hover:text-[#17211a]" title="Edit"><i data-lucide="pen-line" class="h-3.5 w-3.5"></i></button>
                                        <form method="POST" action="{{ route('wedding.guests.destroy', $guest) }}" onsubmit="return confirm('Hapus tamu {{ addslashes($guest->name) }}?')">@csrf @method('DELETE')
                                            <button class="rounded-md p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600" title="Hapus"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-[#8a938b]">Belum ada tamu yang cocok. Tambahkan tamu pertama Anda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($guests->hasPages())
                <div class="border-t border-[#17211a]/10 px-4 py-3">{{ $guests->links() }}</div>
            @endif
        </section>

        <div class="space-y-5">
            <section id="add-guest-panel" class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Tambah Tamu</h3>
                @if($weddings->isEmpty())
                    <p class="mt-2 text-[11px] text-[#8a938b]">Buat undangan terlebih dahulu.</p>
                @else
                    <form method="POST" action="{{ route('wedding.guests.store') }}" class="mt-3 space-y-3">
                        @csrf
                        <div><label class="editor-label">Undangan</label>
                            <select name="wedding_id" required class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs">
                                @foreach($weddings as $item)<option value="{{ $item->id }}" @selected($wedding?->id === $item->id)>{{ $item->coupleNames() }}</option>@endforeach
                            </select>
                        </div>
                        <div><label class="editor-label">Nama tamu *</label><input name="name" required maxlength="100" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs" placeholder="Bapak Syahron"></div>
                        <div><label class="editor-label">Nomor WhatsApp</label><input name="phone" maxlength="20" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs" placeholder="0812..."></div>
                        <div class="grid grid-cols-2 gap-2">
                            <div><label class="editor-label">Grup</label><input name="group_name" maxlength="60" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs" placeholder="Keluarga"></div>
                            <div><label class="editor-label">Kuota</label><input type="number" name="max_pax" min="1" max="20" value="2" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs"></div>
                        </div>
                        <button class="w-full rounded-full bg-[#17211a] px-4 py-2.5 text-xs font-semibold text-[#f4f1ea]">Simpan Tamu</button>
                    </form>
                @endif
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Cara Berbagi</h3>
                <ol class="mt-2 space-y-2 text-[11px] leading-5 text-[#6b746c]">
                    <li><b class="text-[#17211a]">1.</b> Simpan nama dan nomor WhatsApp tamu.</li>
                    <li><b class="text-[#17211a]">2.</b> Klik ikon link pada baris tamu.</li>
                    <li><b class="text-[#17211a]">3.</b> Salin link atau buka WhatsApp Click to Chat.</li>
                </ol>
                <p class="mt-3 rounded-lg border border-[#17211a]/10 bg-[#f4f1ea] px-3 py-2 text-[10px] leading-4 text-[#6b746c]">Nama pada cover hanya berasal dari token tamu di database. Parameter <code>?to=</code> tidak pernah dipercaya.</p>
            </section>
        </div>
    </div>

    {{-- Link modal --}}
    <div x-show="linkOpen" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-[#0c120e]/55 p-4 backdrop-blur-sm" @click.self="linkOpen = false">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
            <div class="flex items-start justify-between">
                <div><h3 class="font-display text-xl">Link Personal Tamu</h3><p class="text-[11px] text-[#8a938b]">Link ini membuka undangan dengan nama tamu.</p></div>
                <button type="button" @click="linkOpen = false" class="rounded-lg p-1.5 text-[#8a938b] hover:bg-[#17211a]/5"><i data-lucide="x" class="h-4 w-4"></i></button>
            </div>
            <template x-if="loading"><p class="mt-4 text-xs text-[#8a938b]">Memuat...</p></template>
            <template x-if="!loading && link">
                <div class="mt-4 space-y-3">
                    <div class="break-all rounded-lg border border-[#17211a]/15 bg-[#f4f1ea] p-3 font-mono text-[10px] text-[#4f5a51]" x-text="link"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="copyLink()" class="rounded-lg border border-[#17211a]/15 px-3 py-2 text-[11px] font-semibold" x-text="copied ? 'Tersalin' : 'Salin Link'"></button>
                        <a :href="wa || '#'" :class="wa ? '' : 'pointer-events-none opacity-40'" target="_blank" rel="noopener" class="rounded-lg bg-[#17211a] px-3 py-2 text-center text-[11px] font-semibold text-[#f4f1ea]">Buka WhatsApp</a>
                    </div>
                    <p class="text-[10px] leading-4 text-[#8a938b]">WhatsApp hanya membuka jendela chat. Sistem tidak mengklaim pesan terkirim sampai Anda mengirimnya sendiri.</p>
                </div>
            </template>
        </div>
    </div>

    {{-- Edit modal --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-[#0c120e]/55 p-4 backdrop-blur-sm" @click.self="editOpen = false">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
            <div class="flex items-start justify-between">
                <h3 class="font-display text-xl">Edit Tamu</h3>
                <button type="button" @click="editOpen = false" class="rounded-lg p-1.5 text-[#8a938b] hover:bg-[#17211a]/5"><i data-lucide="x" class="h-4 w-4"></i></button>
            </div>
            <form method="POST" :action="edit.action" class="mt-4 space-y-3">
                @csrf @method('PUT')
                <div><label class="editor-label">Nama *</label><input name="name" x-model="edit.name" required maxlength="100" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs"></div>
                <div><label class="editor-label">Nomor WhatsApp</label><input name="phone" x-model="edit.phone" maxlength="20" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs"></div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="editor-label">Grup</label><input name="group_name" x-model="edit.group_name" maxlength="60" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs"></div>
                    <div><label class="editor-label">Kuota</label><input type="number" name="max_pax" x-model="edit.max_pax" min="1" max="20" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-xs"></div>
                </div>
                <button class="w-full rounded-full bg-[#17211a] px-4 py-2.5 text-xs font-semibold text-[#f4f1ea]">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function guestPage() {
    return {
        linkOpen: false, editOpen: false, loading: false, copied: false, link: '', wa: '', edit: {},
        async openLink(id, url) {
            this.linkOpen = true; this.loading = true; this.link = ''; this.wa = ''; this.copied = false;
            try {
                const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await response.json();
                this.link = data.link; this.wa = data.wa;
            } catch (e) { this.link = 'Gagal memuat link. Coba lagi.'; }
            this.loading = false;
        },
        copyLink() {
            if (!this.link) return;
            const done = () => { this.copied = true; setTimeout(() => this.copied = false, 1800); };
            if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(this.link).then(done); }
            else { const t = document.createElement('textarea'); t.value = this.link; document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t); done(); }
        },
        editGuest(data) { this.edit = { ...data }; this.editOpen = true; }
    };
}
</script>
@endpush
