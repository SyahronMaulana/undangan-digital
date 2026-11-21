@extends('wedding.layouts.dashboard')

@section('title', 'Editor — '.$wedding->coupleNames())
@section('eyebrow', 'Editor Undangan')
@section('page-title', $wedding->coupleNames())

@push('head')
<style>
    [x-cloak]{display:none!important}
    .editor-shell{display:grid;gap:22px}
    @media(min-width:1280px){.editor-shell{grid-template-columns:minmax(0,1fr) minmax(390px,42%)}}
    .editor-tab{white-space:nowrap}
    .editor-input{width:100%;border:1px solid rgba(23,33,26,.15);background:#fff;border-radius:10px;padding:9px 11px;font-size:13px;outline:none;transition:.18s}
    .editor-input:focus{border-color:#c7a15a;box-shadow:0 0 0 3px rgba(199,161,90,.18)}
    .editor-label{display:block;font-size:10.5px;font-weight:600;color:#4f5a51;margin-bottom:5px;letter-spacing:.01em}
    .editor-hint{font-size:10px;color:#8a938b;margin-top:4px}
    .editor-card{border:1px solid rgba(23,33,26,.10);background:rgba(255,255,255,.78);backdrop-filter:blur(10px);border-radius:18px;padding:20px}
    .editor-preview{position:sticky;top:86px}
    .preview-box{margin-inline:auto;width:100%;max-width:420px;aspect-ratio:9/15.5;border-radius:24px;border:1px solid rgba(23,33,26,.18);overflow:hidden;background:#fff;box-shadow:0 26px 60px rgba(23,33,26,.14)}
    .preview-box.is-desktop{max-width:100%;aspect-ratio:16/10}
    .preview-box iframe{width:100%;height:100%;border:0;display:block}
    .tab-pill{display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:999px;font-size:11px;font-weight:600;border:1px solid transparent;color:#6b746c;cursor:pointer;transition:.18s}
    .tab-pill:hover{background:rgba(23,33,26,.05);color:#17211a}
    .tab-pill.is-active{background:#17211a;color:#f4f1ea}
    .tab-pill svg{width:14px;height:14px}
</style>
@endpush

@section('actions')
    <span id="save-state" class="hidden items-center gap-1.5 rounded-full border border-[#17211a]/15 px-3 py-1.5 text-[10px] font-medium text-[#6b746c] sm:inline-flex">
        <i data-lucide="cloud" class="h-3 w-3"></i> <span id="save-text">Tersimpan</span>
    </span>
    @if($wedding->isPublished())
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-4 py-2 text-[11px] font-semibold text-emerald-800">
            <i data-lucide="globe" class="h-3.5 w-3.5"></i> Terbit
        </span>
    @else
        <a id="publish-link" href="{{ route('wedding.invitations.show', $wedding) }}#publikasi" class="inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-4 py-2 text-[11px] font-semibold text-[#f4f1ea] transition hover:bg-[#26352b]">
            <i data-lucide="send" class="h-3.5 w-3.5"></i> Ajukan Publikasi
        </a>
    @endif
@endsection

@section('content')
<form id="editor-form" method="POST" action="{{ route('wedding.invitations.update', $wedding) }}" enctype="multipart/form-data" class="editor-shell" x-data="weddingEditor(@js($editorData))" @submit="submitForm($event)">
    @csrf
    @method('PUT')

    {{-- Penanda: form ini benar-benar mengirim bagian relasi berikut, sehingga
         pengosongan daftar tetap tersimpan, sementara autosave sebagian tidak
         menghapus data yang tidak disertakan. --}}
    <input type="hidden" name="sync_events" value="1">
    <input type="hidden" name="sync_stories" value="1">
    <input type="hidden" name="sync_gifts" value="1">
    <input type="hidden" name="sync_sections" value="1">

    {{-- ================= LEFT: EDITOR ================= --}}
    <div class="min-w-0 space-y-4">
        {{-- Status --}}
        <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-[#17211a]/10 bg-white/70 px-4 py-3 backdrop-blur">
            @if($wedding->isPublished())
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-emerald-800"><i data-lucide="globe" class="h-3 w-3"></i> Terbit</span>
                <a href="{{ $wedding->invitationUrl() }}" target="_blank" rel="noopener" class="truncate font-mono text-[11px] text-[#6b746c] hover:text-[#17211a]">{{ $wedding->invitationUrl() }}</a>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-amber-800"><i data-lucide="file-pen-line" class="h-3 w-3"></i> Draf</span>
                @if($wedding->hasActiveSubmission())
                    <span class="text-[11px] text-amber-800">Pengajuan aktif: {{ $wedding->submissionStatusLabel() }}. Mengubah data akan membatalkan pengajuan dan Anda harus mengajukan ulang.</span>
                @else
                    <span class="text-[11px] text-[#8a938b]">Tambahkan minimal satu acara, lalu ajukan publikasi ke Admin.</span>
                @endif
            @endif
            <div class="ml-auto flex items-center gap-1.5">
                <a href="{{ route('wedding.invitations.show', $wedding) }}" class="rounded-lg border border-[#17211a]/15 px-2.5 py-1.5 text-[10px] font-medium text-[#4f5a51] hover:border-[#17211a]/35">Ringkasan</a>
                <button type="button" onclick="document.getElementById('preview-toggle').click()" class="rounded-lg border border-[#17211a]/15 px-2.5 py-1.5 text-[10px] font-medium text-[#4f5a51] xl:hidden">Pratinjau</button>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 overflow-x-auto rounded-xl border border-[#17211a]/10 bg-white/60 p-1.5 backdrop-blur" role="tablist">
            @foreach([
                ['key' => 'identitas', 'label' => 'Identitas', 'icon' => 'users'],
                ['key' => 'acara', 'label' => 'Acara', 'icon' => 'calendar-days'],
                ['key' => 'cerita', 'label' => 'Kisah', 'icon' => 'book-heart'],
                ['key' => 'galeri', 'label' => 'Galeri', 'icon' => 'images'],
                ['key' => 'hadiah', 'label' => 'Tanda Kasih', 'icon' => 'wallet-cards'],
                ['key' => 'tampilan', 'label' => 'Tampilan', 'icon' => 'palette'],
            ] as $tab)
                <button type="button" class="tab-pill editor-tab" :class="tab === '{{ $tab['key'] }}' ? 'is-active' : ''" @click="tab = '{{ $tab['key'] }}'">
                    <i data-lucide="{{ $tab['icon'] }}"></i> {{ $tab['label'] }}
                </button>
            @endforeach
            <span class="ml-auto hidden items-center pr-2 text-[10px] text-[#a8b0a9] sm:flex" x-text="'Draf tersimpan ' + savedAt"></span>
        </div>

        {{-- TAB: IDENTITAS --}}
        <div x-show="tab === 'identitas'" x-cloak class="space-y-4">
            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Mempelai Pria</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div><label class="editor-label">Nama lengkap *</label><input class="editor-input" name="groom_name" x-model="form.groom_name" required maxlength="80"></div>
                    <div><label class="editor-label">Nama panggilan</label><input class="editor-input" name="groom_nickname" x-model="form.groom_nickname" maxlength="40"></div>
                    <div><label class="editor-label">Nama ayah</label><input class="editor-input" name="groom_father" x-model="form.groom_father" maxlength="80"></div>
                    <div><label class="editor-label">Nama ibu</label><input class="editor-input" name="groom_mother" x-model="form.groom_mother" maxlength="80"></div>
                    <div><label class="editor-label">Instagram</label><input class="editor-input" name="groom_instagram" x-model="form.groom_instagram" maxlength="80" placeholder="@rangga"></div>
                    <div>
                        <label class="editor-label">Foto mempelai pria</label>
                        <div class="flex items-center gap-3">
                            <template x-if="media.groom_photo"><img :src="media.groom_photo" alt="Foto tersimpan" class="h-12 w-12 flex-none rounded-lg border border-[#17211a]/10 object-cover"></template>
                            <input type="file" name="groom_photo" accept="image/jpeg,image/png,image/webp" class="editor-input text-[11px]">
                        </div>
                        <p class="editor-hint">JPG/PNG/WebP, maks {{ intdiv(config('wedding.limits.image_kb'), 1024) }}MB.<span x-show="media.groom_photo"> Foto tersimpan.</span></p>
                        <label x-show="media.groom_photo" x-cloak class="mt-2 flex items-center gap-2 text-[10px] text-red-600">
                            <input type="checkbox" name="remove_groom_photo" value="1" class="h-3.5 w-3.5 accent-red-500">
                            Hapus foto (tampilkan inisial nama)
                        </label>
                    </div>
                </div>
            </section>

            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Mempelai Wanita</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div><label class="editor-label">Nama lengkap *</label><input class="editor-input" name="bride_name" x-model="form.bride_name" required maxlength="80"></div>
                    <div><label class="editor-label">Nama panggilan</label><input class="editor-input" name="bride_nickname" x-model="form.bride_nickname" maxlength="40"></div>
                    <div><label class="editor-label">Nama ayah</label><input class="editor-input" name="bride_father" x-model="form.bride_father" maxlength="80"></div>
                    <div><label class="editor-label">Nama ibu</label><input class="editor-input" name="bride_mother" x-model="form.bride_mother" maxlength="80"></div>
                    <div><label class="editor-label">Instagram</label><input class="editor-input" name="bride_instagram" x-model="form.bride_instagram" maxlength="80" placeholder="@ayu"></div>
                    <div>
                        <label class="editor-label">Foto mempelai wanita</label>
                        <div class="flex items-center gap-3">
                            <template x-if="media.bride_photo"><img :src="media.bride_photo" alt="Foto tersimpan" class="h-12 w-12 flex-none rounded-lg border border-[#17211a]/10 object-cover"></template>
                            <input type="file" name="bride_photo" accept="image/jpeg,image/png,image/webp" class="editor-input text-[11px]">
                        </div>
                        <p class="editor-hint">JPG/PNG/WebP, maks {{ intdiv(config('wedding.limits.image_kb'), 1024) }}MB.<span x-show="media.bride_photo"> Foto tersimpan.</span></p>
                        <label x-show="media.bride_photo" x-cloak class="mt-2 flex items-center gap-2 text-[10px] text-red-600">
                            <input type="checkbox" name="remove_bride_photo" value="1" class="h-3.5 w-3.5 accent-red-500">
                            Hapus foto (tampilkan inisial nama)
                        </label>
                    </div>
                </div>
            </section>

            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Tanggal & Cover</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div><label class="editor-label">Tanggal pernikahan</label><input type="date" class="editor-input" name="event_date" x-model="form.event_date"></div>
                    <div>
                        <label class="editor-label">Foto cover</label>
                        <div class="flex items-center gap-3">
                            <template x-if="media.cover_photo"><img :src="media.cover_photo" alt="Cover tersimpan" class="h-12 w-12 flex-none rounded-lg border border-[#17211a]/10 object-cover"></template>
                            <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp" class="editor-input text-[11px]">
                        </div>
                        <p class="editor-hint">Rasio portrait paling ideal. Maks {{ intdiv(config('wedding.limits.image_kb'), 1024) }}MB.<span x-show="media.cover_photo"> Cover tersimpan.</span></p>
                        <label x-show="media.cover_photo" x-cloak class="mt-2 flex items-center gap-2 text-[10px] text-red-600">
                            <input type="checkbox" name="remove_cover_photo" value="1" class="h-3.5 w-3.5 accent-red-500">
                            Hapus foto cover
                        </label>
                    </div>
                    <div class="sm:col-span-2"><label class="editor-label">Caption cover</label><input class="editor-input" name="cover_caption" x-model="form.cover_caption" maxlength="160" placeholder="The beginning of our forever"></div>
                    <div><label class="editor-label">Dress code</label><input class="editor-input" name="dress_code" x-model="form.dress_code" maxlength="120" placeholder="Earth tone & formal"></div>
                    <div><label class="editor-label">Hashtag</label><input class="editor-input" name="hashtag" x-model="form.hashtag" maxlength="80" placeholder="#RanggaAyuForever"></div>
                </div>
            </section>
        </div>

        {{-- TAB: ACARA --}}
        <div x-show="tab === 'acara'" x-cloak class="space-y-4">
            <section class="editor-card">
                <div class="flex items-start justify-between gap-3">
                    <div><h3 class="font-display text-lg font-light">Rangkaian Acara</h3><p class="editor-hint">Minimal satu acara diperlukan sebelum publikasi.</p></div>
                    <button type="button" @click="addEvent()" class="inline-flex items-center gap-1 rounded-full border border-[#17281a]/15 px-3 py-1.5 text-[10px] font-semibold hover:border-[#c7a15a]"><i data-lucide="plus" class="h-3 w-3"></i> Tambah</button>
                </div>
                <div class="mt-3 space-y-3">
                    <template x-for="(event, index) in events" :key="event.uid">
                        <div class="rounded-xl border border-[#17211a]/10 bg-white p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#8e6d2f]" x-text="'Acara ' + (index + 1)"></p>
                                <button type="button" @click="removeEvent(index)" class="rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600" title="Hapus acara"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button>
                            </div>
                            <input type="hidden" :name="'events['+index+'][id]'" x-model="event.id">
                            <div class="mt-2 grid gap-2.5 sm:grid-cols-2">
                                <div class="sm:col-span-2"><label class="editor-label">Nama acara</label><input class="editor-input" :name="'events['+index+'][name]'" x-model="event.name" placeholder="Akad Nikah" maxlength="80"></div>
                                <div><label class="editor-label">Tanggal</label><input type="date" class="editor-input" :name="'events['+index+'][event_date]'" x-model="event.event_date"></div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div><label class="editor-label">Mulai</label><input type="time" class="editor-input" :name="'events['+index+'][start_time]'" x-model="event.start_time"></div>
                                    <div><label class="editor-label">Selesai</label><input type="time" class="editor-input" :name="'events['+index+'][end_time]'" x-model="event.end_time"></div>
                                </div>
                                <div><label class="editor-label">Nama tempat</label><input class="editor-input" :name="'events['+index+'][venue_name]'" x-model="event.venue_name" maxlength="120"></div>
                                <div><label class="editor-label">Link Google Maps</label><input class="editor-input" :name="'events['+index+'][maps_url]'" x-model="event.maps_url" placeholder="https://maps.google.com/..."></div>
                                <div class="sm:col-span-2"><label class="editor-label">Alamat lengkap</label><textarea class="editor-input" rows="2" :name="'events['+index+'][address]'" x-model="event.address" maxlength="500"></textarea></div>
                            </div>
                        </div>
                    </template>
                    <p x-show="!events.length" class="rounded-lg border border-dashed border-[#17211a]/20 px-3 py-6 text-center text-[11px] text-[#8a938b]">Belum ada acara. Klik Tambah.</p>
                </div>
            </section>
        </div>

        {{-- TAB: CERITA --}}
        <div x-show="tab === 'cerita'" x-cloak class="space-y-4">
            <section class="editor-card">
                <div class="flex items-start justify-between gap-3">
                    <div><h3 class="font-display text-lg font-light">Kisah Kami</h3><p class="editor-hint">Momen penting dalam perjalanan Anda. Tampil jika bagian Kisah diaktifkan.</p></div>
                    <button type="button" @click="addStory()" class="inline-flex items-center gap-1 rounded-full border border-[#17281a]/15 px-3 py-1.5 text-[10px] font-semibold hover:border-[#c7a15a]"><i data-lucide="plus" class="h-3 w-3"></i> Tambah</button>
                </div>
                <div class="mt-3 space-y-3">
                    <template x-for="(story, index) in stories" :key="story.uid">
                        <div class="rounded-xl border border-[#17211a]/10 bg-white p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#8e6d2f]" x-text="'Momen ' + (index + 1)"></p>
                                <button type="button" @click="removeStory(index)" class="rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button>
                            </div>
                            <input type="hidden" :name="'stories['+index+'][id]'" x-model="story.id">
                            <div class="mt-2 grid gap-2.5 sm:grid-cols-3">
                                <div><label class="editor-label">Judul</label><input class="editor-input" :name="'stories['+index+'][title]'" x-model="story.title" maxlength="80"></div>
                                <div><label class="editor-label">Tahun / tanggal</label><input class="editor-input" :name="'stories['+index+'][story_date]'" x-model="story.story_date" maxlength="40"></div>
                                <div class="sm:col-span-3"><label class="editor-label">Cerita</label><textarea class="editor-input" rows="2" :name="'stories['+index+'][body]'" x-model="story.body" maxlength="2000"></textarea></div>
                            </div>
                        </div>
                    </template>
                    <p x-show="!stories.length" class="rounded-lg border border-dashed border-[#17211a]/20 px-3 py-6 text-center text-[11px] text-[#8a938b]">Belum ada kisah. Klik Tambah untuk mulai.</p>
                </div>
            </section>
        </div>

        {{-- TAB: GALERI --}}
        <div x-show="tab === 'galeri'" x-cloak class="space-y-4">
            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Galeri Foto</h3>
                <p class="editor-hint">Unggah hingga 20 foto sekaligus. Saat ini {{ $wedding->photos->count() }} foto tersimpan.</p>
                <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
                    @foreach($wedding->photos as $photo)
                        <label class="group relative aspect-square cursor-pointer overflow-hidden rounded-lg border border-[#17211a]/10">
                            <img src="{{ \App\Models\Wedding::assetUrl($photo->path) }}" alt="" class="h-full w-full object-cover">
                            <span class="absolute inset-0 grid place-items-center bg-black/0 text-transparent transition group-hover:bg-black/45 group-hover:text-white">
                                <input type="checkbox" name="remove_photos[]" value="{{ $photo->id }}" class="h-4 w-4 accent-red-500">
                            </span>
                            <span class="absolute inset-x-0 bottom-0 bg-black/55 px-1.5 py-1 text-[8px] text-white/85">Hapus?</span>
                        </label>
                    @endforeach
                    <div class="grid aspect-square place-items-center rounded-lg border border-dashed border-[#17211a]/25 bg-[#f4f1ea] text-center">
                        <div>
                            <i data-lucide="image-plus" class="mx-auto h-5 w-5 text-[#8a938b]"></i>
                            <p class="mt-1 text-[9px] text-[#8a938b]">Tambah foto</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3"><label class="editor-label">Unggah foto baru</label><input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="editor-input text-[11px]"><p class="editor-hint">Pilih beberapa sekaligus. Centang foto lama untuk menghapus saat menyimpan. Foto baru tersimpan otomatis; muat ulang halaman untuk melihatnya di daftar.</p></div>
            </section>

            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Musik Latar</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div><label class="editor-label">Judul musik</label><input class="editor-input" name="music_title" x-model="form.music_title" maxlength="120" placeholder="A Thousand Years — Christina Perri"></div>
                    <div>
                        <label class="editor-label">Berkas audio</label>
                        <input type="file" name="music" accept="audio/mpeg,audio/wav,audio/mp4,audio/ogg,.mp3,.wav,.m4a,.ogg" class="editor-input text-[11px]" @change="pickMusic($event)">
                        <p class="editor-hint">MP3/WAV/M4A/OGG, maks {{ intdiv(config('wedding.limits.audio_kb'), 1024) }}MB. Musik lama tetap tersimpan bila tidak diganti.</p>
                        <template x-if="musicPreview || media.music_url">
                            <div class="mt-2 rounded-lg border border-[#17211a]/10 bg-[#f4f1ea] p-2">
                                <p class="truncate text-[10px] text-[#4f5a51]">
                                    <span x-show="musicPreview">Pratinjau berkas baru: <span x-text="musicPreviewName"></span></span>
                                    <span x-show="!musicPreview">Tersimpan: <span x-text="media.music_name"></span></span>
                                </p>
                                <audio controls preload="none" class="mt-1 h-8 w-full" :src="musicPreview || media.music_url"></audio>
                                <template x-if="media.music_url && !musicPreview">
                                    <label class="mt-2 flex items-center gap-2 text-[10px] text-red-600">
                                        <input type="checkbox" name="remove_music" value="1" class="h-3.5 w-3.5 accent-red-500">
                                        Hapus musik ini
                                    </label>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Live Streaming</h3>
                <div class="mt-3"><label class="editor-label">URL live streaming</label><input class="editor-input" name="live_stream_url" x-model="form.live_stream_url" placeholder="https://youtube.com/live/..." maxlength="500"><p class="editor-hint">Opsional. Tampil sebagai tombol di bagian acara.</p></div>
            </section>
        </div>

        {{-- TAB: HADIAH --}}
        <div x-show="tab === 'hadiah'" x-cloak class="space-y-4">
            <section class="editor-card">
                <div class="flex items-start justify-between gap-3">
                    <div><h3 class="font-display text-lg font-light">Tanda Kasih</h3><p class="editor-hint">Rekening atau amplop digital. Tampil jika bagian Tanda Kasih diaktifkan.</p></div>
                    <button type="button" @click="addGift()" class="inline-flex items-center gap-1 rounded-full border border-[#17281a]/15 px-3 py-1.5 text-[10px] font-semibold hover:border-[#c7a15a]"><i data-lucide="plus" class="h-3 w-3"></i> Tambah</button>
                </div>
                <div class="mt-3 space-y-3">
                    <template x-for="(gift, index) in gifts" :key="gift.uid">
                        <div class="rounded-xl border border-[#17211a]/10 bg-white p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#8e6d2f]" x-text="'Rekening ' + (index + 1)"></p>
                                <button type="button" @click="removeGift(index)" class="rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button>
                            </div>
                            <input type="hidden" :name="'gifts['+index+'][id]'" x-model="gift.id">
                            <div class="mt-2 grid gap-2.5 sm:grid-cols-3">
                                <div><label class="editor-label">Bank / e-wallet</label><input class="editor-input" :name="'gifts['+index+'][bank_name]'" x-model="gift.bank_name" maxlength="60" placeholder="Bank Mandiri"></div>
                                <div><label class="editor-label">Nomor rekening</label><input class="editor-input" :name="'gifts['+index+'][account_number]'" x-model="gift.account_number" maxlength="40"></div>
                                <div><label class="editor-label">Atas nama</label><input class="editor-input" :name="'gifts['+index+'][account_name]'" x-model="gift.account_name" maxlength="100"></div>
                            </div>
                        </div>
                    </template>
                    <p x-show="!gifts.length" class="rounded-lg border border-dashed border-[#17211a]/20 px-3 py-6 text-center text-[11px] text-[#8a938b]">Belum ada rekening.</p>
                </div>
            </section>
        </div>

        {{-- TAB: TAMPILAN --}}
        <div x-show="tab === 'tampilan'" x-cloak class="space-y-4">
            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Bagian Undangan</h3>
                <p class="editor-hint">Aktifkan atau sembunyikan bagian pada undangan publik.</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach($sectionLabels as $key => $label)
                        <label class="flex items-center justify-between gap-3 rounded-lg border border-[#17211a]/10 bg-white px-3 py-2.5">
                            <span class="text-xs font-medium">{{ $label }}</span>
                            <input type="hidden" name="sections[{{ $key }}]" value="0">
                            <input type="checkbox" name="sections[{{ $key }}]" value="1" class="h-4 w-4 rounded accent-[#17211a]" @checked(($wedding->sections[$key] ?? config('wedding.default_sections.'.$key, true)))>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Gaya Bahasa & Kutipan</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div><label class="editor-label">Gaya bahasa</label>
                        <select class="editor-input" name="language_style" x-model="form.language_style">
                            @foreach($languageStyles as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="editor-label">Kutipan pilihan</label>
                        <select class="editor-input" name="quote_key" x-model="form.quote_key" @change="applyQuote($event.target.value)">
                            <option value="">— Tulis manual —</option>
                            @foreach($quotes as $key => $quote)<option value="{{ $key }}">{{ $quote['source'] }}</option>@endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2"><label class="editor-label">Teks kutipan</label><textarea class="editor-input" rows="3" name="quote_text" x-model="form.quote_text" maxlength="1200"></textarea></div>
                    <div><label class="editor-label">Sumber kutipan</label><input class="editor-input" name="quote_source" x-model="form.quote_source" maxlength="120"></div>
                </div>
            </section>

            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Pembuka & Penutup</h3>
                <div class="mt-3 space-y-3">
                    <div><label class="editor-label">Teks pembukaan</label><textarea class="editor-input" rows="4" name="opening_text" x-model="form.opening_text" maxlength="3000"></textarea></div>
                    <div><label class="editor-label">Teks penutup</label><textarea class="editor-input" rows="3" name="closing_text" x-model="form.closing_text" maxlength="3000"></textarea></div>
                </div>
            </section>

            <section class="editor-card">
                <h3 class="font-display text-lg font-light">Ganti Desain</h3>
                <p class="editor-hint">Data undangan tidak berubah saat desain diganti.</p>
                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach($templates as $template)
                        <button type="submit" form="template-form" name="template_id" value="{{ $template->id }}"
                            class="rounded-lg border-2 p-2 text-left transition {{ $wedding->template_id === $template->id ? 'border-[#c7a15a] bg-[#c7a15a]/10' : 'border-[#17211a]/10 hover:border-[#17211a]/30' }}">
                            <span class="block truncate text-[11px] font-semibold">{{ $template->name }}</span>
                            <span class="block truncate text-[9px] text-[#8a938b]">{{ $template->style_label }}</span>
                            @if($wedding->template_id === $template->id)<span class="mt-1 inline-block text-[8px] font-bold uppercase tracking-wider text-[#8e6d2f]">Aktif</span>@endif
                        </button>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Footer simpan --}}
        <div class="sticky bottom-0 z-10 flex flex-wrap items-center gap-2 rounded-2xl border border-[#17211a]/10 bg-[#f4f1ea]/95 px-4 py-3 backdrop-blur">
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-4 py-2 text-[11px] font-semibold text-[#f4f1ea] hover:bg-[#26352b]">
                <i data-lucide="save" class="h-3.5 w-3.5"></i> Simpan Perubahan
            </button>
            <span class="text-[10px] text-[#8a938b]">Perubahan tersimpan otomatis saat Anda berpindah kolom.</span>
            <a href="{{ route('wedding.invitations.show', $wedding) }}" class="ml-auto text-[10px] font-medium text-[#6b746c] hover:text-[#17211a]">Ringkasan &rsaquo;</a>
        </div>
    </div>

    {{-- ================= RIGHT: PREVIEW ================= --}}
    <aside class="editor-preview min-w-0" x-data="{ desktop: false }">
        <div class="rounded-2xl border border-[#17211a]/10 bg-white/70 p-4 backdrop-blur">
            <div class="mb-3 flex items-center justify-between gap-2">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-[#8a938b]">Pratinjau Langsung</p>
                    <p class="text-[10px] text-[#a8b0a9]">Memakai view & CSS undangan asli</p>
                </div>
                <div class="flex items-center gap-1 rounded-full border border-[#17211a]/10 p-1">
                    <button type="button" @click="desktop = false" :class="!desktop ? 'bg-[#17211a] text-white' : 'text-[#6b746c]'" class="rounded-full p-1.5" title="Ponsel"><i data-lucide="smartphone" class="h-3.5 w-3.5"></i></button>
                    <button type="button" @click="desktop = true" :class="desktop ? 'bg-[#17211a] text-white' : 'text-[#6b746c]'" class="rounded-full p-1.5" title="Desktop"><i data-lucide="monitor" class="h-3.5 w-3.5"></i></button>
                </div>
            </div>
            <div class="preview-box" :class="desktop ? 'is-desktop' : ''">
                <iframe id="editor-preview-frame" src="{{ $previewUrl }}" title="Pratinjau undangan {{ $wedding->coupleNames() }}" loading="lazy"></iframe>
            </div>
            <div class="mt-3 flex items-center justify-between gap-2">
                <a href="{{ route('wedding.invitations.preview', $wedding) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-[10px] font-medium text-[#6b746c] hover:text-[#17211a]"><i data-lucide="external-link" class="h-3 w-3"></i> Buka preview penuh</a>
                <button type="button" id="preview-toggle" @click="reloadPreview()" class="inline-flex items-center gap-1 rounded-full border border-[#17211a]/15 px-2.5 py-1 text-[10px] font-medium text-[#4f5a51] hover:border-[#17211a]/35"><i data-lucide="refresh-cw" class="h-3 w-3"></i> Muat ulang</button>
            </div>
        </div>
    </aside>
</form>

{{-- Form terpisah untuk ganti template (tidak boleh nested form) --}}
<form id="template-form" method="POST" action="{{ route('wedding.invitations.template', $wedding) }}" class="hidden">
    @csrf
</form>
@endsection

@push('scripts')
<script>
function weddingEditor(data) {
    return {
        tab: 'identitas',
        form: data.form,
        events: data.events,
        stories: data.stories,
        gifts: data.gifts,
        media: data.media || {},
        musicPreview: null,
        musicPreviewName: '',
        savedAt: data.savedAt || '—',
        dirty: false,
        timer: null,
        init() {
            this.$watch('form', () => this.schedule(), { deep: true });
            this.$watch('events', () => this.schedule(), { deep: true });
            this.$watch('stories', () => this.schedule(), { deep: true });
            this.$watch('gifts', () => this.schedule(), { deep: true });
            const form = document.getElementById('editor-form');
            form.addEventListener('change', (event) => {
                if (event.target.matches('input[type=file], input[name^="remove_photos"], input[name="remove_music"], input[name="remove_groom_photo"], input[name="remove_bride_photo"], input[name="remove_cover_photo"], input[name^="sections"], select[name="language_style"]')) {
                    this.saveNow();
                }
            });
            // Tombol "Ajukan Publikasi" ada di header (di luar cakupan x-data),
            // jadi disambungkan lewat listener agar data tersimpan lebih dulu.
            const publish = document.getElementById('publish-link');
            if (publish) {
                publish.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.goToPublication();
                });
            }
        },
        uid() { return Math.random().toString(36).slice(2, 9); },
        addEvent() { this.events.push({ uid: this.uid(), id: '', name: '', event_date: this.form.event_date || '', start_time: '', end_time: '', venue_name: '', address: '', maps_url: '' }); },
        removeEvent(i) { this.events.splice(i, 1); },
        addStory() { this.stories.push({ uid: this.uid(), id: '', title: '', story_date: '', body: '' }); },
        removeStory(i) { this.stories.splice(i, 1); },
        addGift() { this.gifts.push({ uid: this.uid(), id: '', bank_name: '', account_number: '', account_name: '' }); },
        removeGift(i) { this.gifts.splice(i, 1); },
        applyQuote(key) {
            const quotes = @json($quotes);
            if (quotes[key]) { this.form.quote_text = quotes[key].text; this.form.quote_source = quotes[key].source; }
        },
        pickMusic(event) {
            // Pratinjau berkas yang baru dipilih sebelum/sesudah tersimpan.
            const file = event.target.files && event.target.files[0];
            if (this.musicPreview) { URL.revokeObjectURL(this.musicPreview); }
            this.musicPreview = file ? URL.createObjectURL(file) : null;
            this.musicPreviewName = file ? file.name : '';
        },
        schedule() {
            this.dirty = true;
            this.setState('Menyimpan...', 'spin');
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.saveNow(), 1400);
        },
        async saveNow() {
            clearTimeout(this.timer);
            const form = document.getElementById('editor-form');
            if (!form) return false;
            const fileInputs = [...form.querySelectorAll('input[type=file]')];
            const hadFiles = fileInputs.some((input) => input.files && input.files.length > 0);
            const musicInput = form.querySelector('input[name="music"]');
            const musicFileUploaded = !!(musicInput && musicInput.files && musicInput.files.length > 0);
            const removeMusic = form.querySelector('input[name="remove_music"]');
            const removingMusic = !!(removeMusic && removeMusic.checked);
            const photoRemovalNames = ['remove_groom_photo', 'remove_bride_photo', 'remove_cover_photo'];
            const removingPhotos = photoRemovalNames.some((name) => {
                const box = form.querySelector('input[name="' + name + '"]');
                return !!(box && box.checked);
            });
            const body = new FormData(form);
            body.append('_method', 'PUT');
            this.setState('Menyimpan...', 'spin');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) {
                    this.setState(result.message || 'Periksa isian', 'error');
                    return false;
                }
                this.dirty = false;
                this.savedAt = result.updated_at || new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'});
                if (hadFiles || removingMusic || removingPhotos) {
                    // Tampilkan berkas yang baru diunggah, lalu kosongkan input
                    // agar berkas yang sama tidak terunggah ulang pada autosave
                    // berikutnya (mencegah duplikasi foto).
                    fileInputs.forEach((input) => {
                        const file = input.files && input.files[0];
                        if (file) {
                            const url = URL.createObjectURL(file);
                            if (input.name === 'groom_photo') this.media.groom_photo = url;
                            if (input.name === 'bride_photo') this.media.bride_photo = url;
                            if (input.name === 'cover_photo') this.media.cover_photo = url;
                            if (input.name === 'music') { this.media.music_url = url; this.media.music_name = file.name; }
                        }
                        input.value = '';
                    });
                    // Foto yang ditandai hapus sudah benar-benar terhapus.
                    form.querySelectorAll('input[name^="remove_photos"]').forEach((cb) => { cb.checked = false; });
                    photoRemovalNames.forEach((name) => {
                        const box = form.querySelector('input[name="' + name + '"]');
                        if (box && box.checked) {
                            if (name === 'remove_groom_photo') this.media.groom_photo = null;
                            if (name === 'remove_bride_photo') this.media.bride_photo = null;
                            if (name === 'remove_cover_photo') this.media.cover_photo = null;
                            box.checked = false;
                        }
                    });
                    if (removingMusic) {
                        if (!musicFileUploaded) {
                            this.media.music_url = null;
                            this.media.music_name = null;
                            this.form.music_title = '';
                        }
                        if (this.musicPreview) { URL.revokeObjectURL(this.musicPreview); }
                        this.musicPreview = null;
                        this.musicPreviewName = '';
                        removeMusic.checked = false;
                    }
                }
                this.setState('Tersimpan', 'ok');
                this.reloadPreview();
                return true;
            } catch (error) {
                this.setState('Gagal menyimpan', 'error');
                return false;
            }
        },
        setState(text, state) {
            const box = document.getElementById('save-state');
            const label = document.getElementById('save-text');
            if (!box || !label) return;
            box.classList.remove('hidden');
            label.textContent = text;
            box.className = 'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[10px] font-medium sm:inline-flex ' +
                (state === 'error' ? 'border-red-200 bg-red-50 text-red-700' : state === 'ok' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-[#17211a]/15 text-[#6b746c]');
        },
        reloadPreview() {
            const frame = document.getElementById('editor-preview-frame');
            if (frame) frame.src = frame.src.split('?')[0] + '?t=' + Date.now();
        },
        async submitForm(event) {
            event.preventDefault();
            const ok = await this.saveNow();
            if (ok === false) return;
            window.location.reload();
        },
        async goToPublication() {
            const ok = await this.saveNow();
            if (ok === false) return;
            window.location.href = @json(route('wedding.invitations.show', $wedding).'#publikasi');
        }
    };
}
</script>
@endpush
