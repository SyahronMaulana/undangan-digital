@extends('wedding.layouts.admin')

@section('title', 'Template')
@section('eyebrow', 'Katalog Desain')
@section('page-title', 'Template')

@section('content')
<div class="space-y-5">
    <div class="rounded-xl border border-[#17211a]/10 bg-white/60 px-4 py-3 text-[11px] text-[#6b746c]">
        Delapan desain memiliki view, CSS, dan JavaScript terpisah di <code class="rounded bg-[#f4f1ea] px-1">resources/views/wedding/templates</code>. Mengubah status nonaktif menyembunyikan template dari katalog dan pembuatan undangan baru.
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        @foreach($templates as $template)
            @php $info = $meta[$template->slug] ?? []; @endphp
            <article class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
                <div class="relative aspect-[4/5] bg-[#111b15]">
                    <iframe src="{{ route('wedding.templates.preview', $template->slug) }}" title="Preview {{ $template->name }}" loading="lazy" tabindex="-1" class="h-full w-full border-0"></iframe>
                    <span class="absolute left-2 top-2 rounded-full px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider {{ $template->is_active ? 'bg-emerald-500/90 text-white' : 'bg-red-500/90 text-white' }}">{{ $template->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    <span class="absolute right-2 top-2 rounded-full bg-black/50 px-2 py-0.5 text-[8px] font-semibold text-white/85 backdrop-blur">{{ $template->weddings_count }} undangan</span>
                </div>
                <form method="POST" action="{{ route('wedding.admin.templates.update', $template) }}" class="space-y-2 p-3">
                    @csrf @method('PATCH')
                    <div><label class="editor-label">Nama</label><input name="name" value="{{ $template->name }}" required maxlength="60" class="w-full rounded-lg border border-[#17211a]/15 px-2.5 py-1.5 text-xs"></div>
                    <div><label class="editor-label">Label gaya</label><input name="style_label" value="{{ $template->style_label }}" required maxlength="80" class="w-full rounded-lg border border-[#17211a]/15 px-2.5 py-1.5 text-xs"></div>
                    <div class="flex gap-2">
                        <div class="flex-1"><label class="editor-label">Urutan</label><input type="number" name="sort_order" value="{{ $template->sort_order }}" min="0" max="999" class="w-full rounded-lg border border-[#17211a]/15 px-2.5 py-1.5 text-xs"></div>
                        <label class="flex items-end gap-1.5 pb-2 text-[10px]"><input type="checkbox" name="is_active" value="1" @checked($template->is_active) class="h-3.5 w-3.5 rounded"> Aktif</label>
                    </div>
                    <p class="line-clamp-2 text-[9px] leading-4 text-[#8a938b]">{{ $info['tagline'] ?? $template->tagline() }} · {{ implode(', ', array_slice($info['fonts'] ?? [], 0, 2)) }}</p>
                    <button class="w-full rounded-lg bg-[#17211a] px-3 py-1.5 text-[10px] font-semibold text-[#f4f1ea]">Simpan</button>
                </form>
            </article>
        @endforeach
    </div>
</div>
@endsection
