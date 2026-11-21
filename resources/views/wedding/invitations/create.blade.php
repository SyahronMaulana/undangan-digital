@extends('wedding.layouts.dashboard')

@section('title', 'Buat Undangan')
@section('eyebrow', 'Langkah 1 dari 2')
@section('page-title', 'Buat Undangan Baru')

@section('content')
<div class="mx-auto max-w-5xl space-y-6" x-data="createInvitation()">
    <div class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="font-display text-2xl font-light">Mulai dari data utama</h2>
                <p class="mt-1 max-w-xl text-sm leading-6 text-[#6b746c]">Isi nama mempelai dan alamat undangan. Setelah dibuat, Anda akan diarahkan ke editor dua panel untuk melengkapi cerita, acara, galeri, dan tanda kasih.</p>
            </div>
            <span class="rounded-full border border-[#c7a15a]/50 bg-[#c7a15a]/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-[#8e6d2f]">Semua Fitur Terbuka</span>
        </div>
    </div>

    <form method="POST" action="{{ route('wedding.invitations.store') }}" class="space-y-6" @submit="submitting = true">
        @csrf

        {{-- Template --}}
        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-display text-lg font-light">Pilih Desain</h3>
                    <p class="text-[11px] text-[#8a938b]">Semua desain dapat diganti nanti tanpa kehilangan data.</p>
                </div>
                <a href="{{ route('wedding.templates.index') }}" target="_blank" class="text-[10px] font-semibold uppercase tracking-wider text-[#8e6d2f] hover:text-[#17211a]">Bandingkan</a>
            </div>
            @error('template_id')<p class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($templates as $template)
                    @php $checked = old('template_id', $selectedTemplate ? $templates->firstWhere('slug', $selectedTemplate)?->id : $templates->first()?->id) == $template->id; @endphp
                    <label class="group relative cursor-pointer overflow-hidden rounded-xl border-2 bg-[#f4f1ea] transition {{ $checked ? 'border-[#c7a15a] shadow-lg shadow-[#c7a15a]/15' : 'border-transparent hover:border-[#17211a]/20' }}">
                        <input type="radio" name="template_id" value="{{ $template->id }}" class="sr-only" @checked($checked) @change="selected = {{ $template->id }}">
                        <div class="relative aspect-[4/5] overflow-hidden">
                            <iframe src="{{ route('wedding.templates.preview', $template->slug) }}" title="Preview {{ $template->name }}" loading="lazy" tabindex="-1" class="h-full w-full border-0 bg-white"></iframe>
                            <span x-show="selected === {{ $template->id }}" class="absolute right-2 top-2 grid h-6 w-6 place-items-center rounded-full bg-[#c7a15a] text-[#14130e] shadow" x-cloak><i data-lucide="check" class="h-3.5 w-3.5"></i></span>
                        </div>
                        <div class="flex items-center justify-between gap-2 px-3 py-2.5">
                            <div>
                                <p class="text-xs font-semibold">{{ $template->name }}</p>
                                <p class="text-[9px] uppercase tracking-wider text-[#8a938b]">{{ $template->style_label }}</p>
                            </div>
                            <div class="flex gap-1">
                                @foreach(array_slice($template->palette ?? [], 0, 3) as $color)
                                    <i class="h-2.5 w-2.5 rounded-full border border-black/10" style="background: {{ $color }}"></i>
                                @endforeach
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- Data mempelai --}}
        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur sm:p-6">
            <h3 class="font-display text-lg font-light">Nama Mempelai</h3>
            <div class="mt-4 grid gap-5 md:grid-cols-2">
                <div class="rounded-xl border border-[#17211a]/10 bg-[#fbf9f3] p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-[#8e6d2f]">Mempelai Pria</p>
                    <label class="mt-3 block text-[11px] font-medium text-[#4f5a51]">Nama lengkap <span class="text-red-600">*</span></label>
                    <input type="text" name="groom_name" value="{{ old('groom_name') }}" required maxlength="80" placeholder="Contoh: Rangga Adiputra"
                        class="mt-1 w-full rounded-lg border border-[#17211a]/15 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#c7a15a] focus:ring-2 focus:ring-[#c7a15a]/20">
                    @error('groom_name')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-xl border border-[#17211a]/10 bg-[#fbf9f3] p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-[#8e6d2f]">Mempelai Wanita</p>
                    <label class="mt-3 block text-[11px] font-medium text-[#4f5a51]">Nama lengkap <span class="text-red-600">*</span></label>
                    <input type="text" name="bride_name" value="{{ old('bride_name') }}" required maxlength="80" placeholder="Contoh: Ayu Larasati"
                        class="mt-1 w-full rounded-lg border border-[#17211a]/15 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#c7a15a] focus:ring-2 focus:ring-[#c7a15a]/20">
                    @error('bride_name')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- Detail undangan --}}
        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur sm:p-6">
            <h3 class="font-display text-lg font-light">Detail Undangan</h3>
            <div class="mt-4 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="block text-[11px] font-medium text-[#4f5a51]">Alamat undangan <span class="text-red-600">*</span></label>
                    <div class="mt-1 flex overflow-hidden rounded-lg border border-[#17211a]/15 bg-white focus-within:border-[#c7a15a] focus-within:ring-2 focus-within:ring-[#c7a15a]/20">
                        <span class="flex items-center border-r border-[#17211a]/10 bg-[#f4f1ea] px-3 text-[11px] text-[#6b746c]">/wedding/</span>
                        <input type="text" name="slug" x-model="slug" @input="slug = slugify(slug)" required minlength="3" maxlength="60"
                            pattern="[a-z0-9]+(?:-[a-z0-9]+)*" title="Gunakan huruf kecil, angka, dan tanda hubung tanpa tanda hubung ganda atau di awal/akhir. Contoh: rangga-ayu" placeholder="rangga-ayu"
                            class="w-full border-0 bg-transparent px-3 py-2.5 text-sm outline-none">
                    </div>
                    <p class="mt-1.5 text-[10px] text-[#8a938b]">Huruf kecil, angka, dan tanda hubung. Tidak boleh sama dengan alamat yang sudah ada.</p>
                    <p x-show="slug.length > 0 && !slugValid" x-cloak class="mt-1 text-[11px] text-red-600">Alamat belum valid. Gunakan huruf kecil, angka, dan tanda hubung tanpa tanda hubung ganda atau di awal/akhir (contoh: rangga-ayu).</p>
                    @error('slug')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-[#4f5a51]">Tanggal pernikahan</label>
                    <input type="date" name="event_date" value="{{ old('event_date') }}"
                        class="mt-1 w-full rounded-lg border border-[#17211a]/15 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#c7a15a] focus:ring-2 focus:ring-[#c7a15a]/20">
                    <p class="mt-1.5 text-[10px] text-[#8a938b]">Bisa diubah kapan saja dari editor.</p>
                    @error('event_date')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-[#4f5a51]">Gaya bahasa</label>
                    <select name="language_style" class="mt-1 w-full rounded-lg border border-[#17211a]/15 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#c7a15a] focus:ring-2 focus:ring-[#c7a15a]/20">
                        @foreach(config('wedding.language_styles') as $key => $label)
                            <option value="{{ $key }}" @selected(old('language_style', 'islami') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('language_style')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-start gap-2.5 rounded-lg border border-[#17211a]/10 bg-[#f4f1ea] p-3 text-[11px] leading-5 text-[#5d675f]">
                    <i data-lucide="info" class="mt-0.5 h-4 w-4 flex-none text-[#8e6d2f]"></i>
                    <span>Setelah dibuat, undangan tersimpan sebagai <b>draf</b>. Undangan baru tampil publik setelah Anda menambahkan minimal satu acara, lalu mengajukan publikasi ke Admin.</span>
                </div>
            </div>
        </section>

        @error('limit')<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>@enderror

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('wedding.invitations.index') }}" class="text-[11px] font-medium text-[#6b746c] hover:text-[#17211a]">Batal</a>
            <button type="submit" :disabled="submitting" class="inline-flex items-center gap-2 rounded-full bg-[#17211a] px-6 py-3 text-xs font-semibold text-[#f4f1ea] transition hover:bg-[#26352b] disabled:cursor-not-allowed disabled:opacity-60">
                <span x-show="!submitting">Buat dan Lanjut ke Editor</span>
                <span x-show="submitting" x-cloak>Menyimpan...</span>
                <i data-lucide="arrow-right" class="h-4 w-4" x-show="!submitting"></i>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function createInvitation() {
    return {
        selected: {{ (int) old('template_id', $selectedTemplate ? $templates->firstWhere('slug', $selectedTemplate)?->id : $templates->first()?->id) }},
        submitting: false,
        slug: @json(old('slug', '')),
        // Hanya ubah huruf besar → kecil dan spasi → tanda hubung. Tanda hubung
        // tidak dihapus/digabung di sini agar pengguna bisa mengetiknya; pola
        // tidak valid (mis. `a--b`, `-a`, `a-`) ditolak oleh regex/pattern.
        // Spasi di awal dibuang agar tidak menghasilkan tanda hubung di awal.
        slugify(value) {
            return String(value)
                .replace(/^\s+/, '')
                .toLowerCase()
                .replace(/\s+/g, '-')
                .slice(0, 60);
        },
        get slugValid() {
            return /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(this.slug);
        },
        init() {
            this.$watch('slug', (value) => {
                const clean = this.slugify(value);
                if (clean !== value) this.slug = clean;
            });
        }
    };
}
</script>
@endpush
