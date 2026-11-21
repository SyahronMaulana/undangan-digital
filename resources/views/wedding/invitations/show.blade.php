@extends('wedding.layouts.dashboard')

@section('title', 'Ringkasan — '.$wedding->coupleNames())
@section('eyebrow', 'Ringkasan Undangan')
@section('page-title', $wedding->coupleNames())

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
    $adminWaUrl = \App\Support\AdminWhatsApp::publicationUrl($wedding);
    // Catatan baku yang tampil bersama tombol WhatsApp admin.
    $adminWaNote = 'Silakan hubungi admin melalui WhatsApp untuk mendapatkan informasi pembayaran dan proses publikasi undangan Anda.';
@endphp

@section('actions')
    <a href="{{ route('wedding.invitations.edit', $wedding) }}" class="inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-4 py-2 text-[11px] font-semibold text-[#f4f1ea] transition hover:bg-[#26352b]">
        <i data-lucide="pen-line" class="h-3.5 w-3.5"></i> Edit Undangan
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid gap-6 xl:grid-cols-[1.35fr_.65fr]">
        <div class="space-y-5">
            <section class="overflow-hidden rounded-2xl border border-[#17211a]/10 bg-white/75 backdrop-blur">
                <div class="relative h-44 bg-[#111b15] sm:h-52">
                    @if($wedding->coverUrl())<img src="{{ $wedding->coverUrl() }}" alt="" class="h-full w-full object-cover opacity-80">@endif
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0c120e]/85 via-[#0c120e]/20 to-transparent"></div>
                    <div class="absolute left-5 top-5 flex gap-2">
                        @if($wedding->isPublished())
                            <span class="rounded-full bg-emerald-500/90 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-white">Terbit</span>
                        @else
                            <span class="rounded-full bg-amber-400/95 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-[#4a3200]">Draf</span>
                        @endif
                        <span class="rounded-full bg-black/40 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-wider text-white/85 backdrop-blur">{{ $wedding->template?->name }}</span>
                    </div>
                    <div class="absolute inset-x-5 bottom-4 text-white">
                        <p class="text-[10px] uppercase tracking-[.22em] text-white/60">Undangan Pernikahan</p>
                        <h2 class="font-display text-3xl font-light">{{ $wedding->coupleNames() }}</h2>
                        <p class="text-[11px] text-white/70">{{ $wedding->event_date?->translatedFormat('l, d F Y') ?? 'Tanggal belum diisi' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-px border-t border-[#17211a]/10 bg-[#17211a]/10 sm:grid-cols-4">
                    @foreach([
                        ['label' => 'Hadir', 'value' => $summary['hadir'] ?? 0, 'icon' => 'check-circle-2'],
                        ['label' => 'Tidak Hadir', 'value' => $summary['tidak_hadir'] ?? 0, 'icon' => 'user-x'],
                        ['label' => 'Ragu', 'value' => $summary['ragu'] ?? 0, 'icon' => 'help-circle'],
                        ['label' => 'Total Orang', 'value' => $summary['total_orang'] ?? 0, 'icon' => 'users'],
                    ] as $item)
                        <div class="bg-white/80 px-4 py-3">
                            <div class="flex items-center gap-1.5 text-[9px] uppercase tracking-wider text-[#8a938b]"><i data-lucide="{{ $item['icon'] }}" class="h-3 w-3"></i> {{ $item['label'] }}</div>
                            <p class="mt-1 font-display text-2xl">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Rangkaian Acara</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @forelse($wedding->events as $event)
                        <div class="rounded-xl border border-[#17211a]/10 bg-[#fbf9f3] p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#8e6d2f]">{{ $event->name }}</p>
                            <p class="mt-1 text-sm font-medium">{{ $event->event_date?->translatedFormat('d F Y') ?? 'Tanggal belum diisi' }}</p>
                            <p class="text-[11px] text-[#6b746c]">{{ $event->timeRange() ?: 'Waktu menyusul' }}</p>
                            <p class="mt-2 text-[11px] leading-5 text-[#5d675f]">{{ $event->venue_name }}@if($event->address)<br>{{ $event->address }}@endif</p>
                        </div>
                    @empty
                        <p class="text-[11px] text-[#8a938b]">Belum ada acara. Tambahkan dari editor agar dapat diajukan untuk publikasi.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-lg font-light">Bagian Undangan</h3>
                    <span class="text-[10px] text-[#8a938b]">{{ collect($wedding->sections ?? [])->filter()->count() }} aktif</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach(config('wedding.sections') as $key => $label)
                        <span class="rounded-full border px-2.5 py-1 text-[10px] {{ $wedding->sectionEnabled($key) ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-[#17211a]/10 bg-[#f4f1ea] text-[#a8b0a9] line-through' }}">{{ $label }}</span>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="space-y-5">
            <section id="publikasi" class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Status & Publikasi</h3>
                <div class="mt-3 space-y-3 text-[11px]">
                    <div class="flex items-center justify-between">
                        <span class="text-[#8a938b]">Status Undangan</span>
                        <span class="font-semibold {{ $wedding->isPublished() ? 'text-emerald-700' : 'text-amber-700' }}">{{ $wedding->isPublished() ? 'Dipublikasikan' : 'Draf' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#8a938b]">Status Pengajuan</span>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $submissionTones[$wedding->submissionStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $wedding->submissionStatusLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[#8a938b]">Status Pembayaran</span>
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-semibold {{ $paymentTones[$wedding->paymentStatus()] ?? 'bg-[#17211a]/5 text-[#6b746c]' }}">{{ $wedding->paymentStatusLabel() }}</span>
                    </div>

                    @if($wedding->revision_notes)
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-800">
                            <p class="font-semibold">Catatan Revisi dari Admin:</p>
                            <p class="mt-1">{{ $wedding->revision_notes }}</p>
                        </div>
                    @endif

                    @if($wedding->isPublished())
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-emerald-800">
                            <p class="font-semibold">Undangan sudah dipublikasikan.</p>
                            <p class="mt-1">Perubahan konten publik setelah terbit hanya dapat dilakukan melalui revisi dari Admin agar tidak berubah diam-diam.</p>
                        </div>
                    @elseif($wedding->canSubmitPublication())
                        @php $issues = $wedding->publicationIssues(); @endphp
                        @if($issues !== [])
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-900">
                                <p class="font-semibold">Lengkapi dulu sebelum mengajukan:</p>
                                <ul class="mt-1 list-disc pl-4">
                                    @foreach($issues as $issue)<li>{{ $issue }}</li>@endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('wedding.invitations.submit_publication', $wedding) }}" class="space-y-2 pt-1">
                            @csrf
                            <label class="flex items-start gap-2 rounded-lg border border-[#17211a]/15 bg-[#fbf9f3] p-3 text-[10.5px] leading-4 text-[#4f5a51]">
                                <input type="checkbox" name="final_design" value="1" required class="mt-0.5 h-3.5 w-3.5 rounded">
                                <span>Saya menyatakan desain dan seluruh data undangan sudah <b>final</b>. Perubahan setelah pengajuan akan membatalkan pengajuan dan harus diajukan ulang.</span>
                            </label>
                            @error('publish')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror
                            @error('final_design')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror
                            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full bg-[#17211a] px-4 py-2.5 text-[11px] font-semibold text-[#f4f1ea] hover:bg-[#26352b]">
                                <i data-lucide="send" class="h-3.5 w-3.5"></i> Ajukan Publikasi ke Admin
                            </button>
                        </form>
                        @include('wedding.partials.admin-whatsapp-button', ['url' => $adminWaUrl, 'note' => $adminWaNote])
                    @elseif($wedding->submissionStatus() === 'menunggu_konfirmasi')
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-900">
                            <p class="font-semibold">Pengajuan terkirim.</p>
                            <p class="mt-1">Admin akan mengirim instruksi pembayaran manual melalui chat WhatsApp.</p>
                        </div>
                        @include('wedding.partials.admin-whatsapp-button', ['url' => $adminWaUrl, 'note' => $adminWaNote])
                    @elseif($wedding->submissionStatus() === 'menunggu_pembayaran')
                        <div class="rounded-lg border border-orange-200 bg-orange-50 p-3 text-orange-900">
                            <p class="font-semibold">Menunggu pembayaran manual.</p>
                            <p class="mt-1">Admin sudah mengirim instruksi pembayaran. Lakukan pembayaran melalui chat Admin, lalu tekan tombol di bawah agar Admin memverifikasi.</p>
                        </div>
                        @include('wedding.partials.admin-whatsapp-button', ['url' => $adminWaUrl, 'note' => $adminWaNote])
                        <form method="POST" action="{{ route('wedding.invitations.claim_payment', $wedding) }}">
                            @csrf
                            @error('payment')<p class="mb-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full border border-[#17211a]/20 px-4 py-2.5 text-[11px] font-semibold hover:border-[#17211a]/45">
                                <i data-lucide="badge-check" class="h-3.5 w-3.5"></i> Saya Sudah Membayar
                            </button>
                        </form>
                    @elseif($wedding->submissionStatus() === 'menunggu_verifikasi')
                        <div class="rounded-lg border border-sky-200 bg-sky-50 p-3 text-sky-900">
                            <p class="font-semibold">Menunggu verifikasi pembayaran.</p>
                            <p class="mt-1">Admin akan memeriksa pembayaran Anda. Setelah dikonfirmasi, undangan akan dipublikasikan oleh Admin.</p>
                        </div>
                        @include('wedding.partials.admin-whatsapp-button', ['url' => $adminWaUrl, 'note' => $adminWaNote])
                    @elseif($wedding->submissionStatus() === 'pembayaran_dikonfirmasi')
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-emerald-800">
                            <p class="font-semibold">Pembayaran dikonfirmasi.</p>
                            <p class="mt-1">Undangan Anda menunggu dipublikasikan oleh Admin.</p>
                        </div>
                        @include('wedding.partials.admin-whatsapp-button', ['url' => $adminWaUrl, 'note' => $adminWaNote])
                    @endif
                </div>
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur" x-data="weddingShare(@js($shareData))">
                <h3 class="font-display text-lg font-light">Bagikan Undangan</h3>
                <p class="mt-1 text-[11px] text-[#8a938b]">Bagikan satu per satu ke tamu. Tulis nama penerima, lalu salin link atau kirim lewat WhatsApp.</p>
                <div class="mt-3 flex items-center gap-2 rounded-lg border border-[#17211a]/15 bg-[#f4f1ea] p-2">
                    <span class="truncate font-mono text-[10px] text-[#4f5a51]">{{ $wedding->invitationUrl() }}</span>
                    <button type="button" data-copy="{{ $wedding->invitationUrl() }}" class="ml-auto flex-none rounded-md bg-white px-2 py-1 text-[9px] font-semibold shadow-sm">Salin</button>
                </div>
                @if($wedding->isPublished())
                    <div class="mt-4 rounded-xl border border-[#17211a]/10 bg-[#fbf9f3] p-3">
                        <label class="block text-[10.5px] font-semibold text-[#4f5a51]" for="share-recipient">Nama penerima</label>
                        <input id="share-recipient" type="text" maxlength="100" x-model="name" @input="feedback = ''" placeholder="Contoh: Budi Santoso" class="mt-1 w-full rounded-lg border border-[#17211a]/15 bg-white px-3 py-2 text-[12px] outline-none focus:border-[#c7a15a]">
                        <p class="mt-1 text-[10px] text-[#8a938b]">Hanya untuk menyapa penerima di halaman undangan, tidak disimpan sebagai tamu.</p>

                        <p class="mt-3 text-[10px] font-semibold uppercase tracking-wider text-[#8a938b]">Link personal</p>
                        <div class="mt-1 flex items-center gap-2 rounded-lg border border-[#17211a]/15 bg-white p-2">
                            <span class="truncate font-mono text-[10px] text-[#4f5a51]" x-text="link"></span>
                            <button type="button" :data-copy="link" class="ml-auto flex-none rounded-md bg-[#17211a] px-2 py-1 text-[9px] font-semibold text-white"><span data-copy-label>Salin Link</span></button>
                        </div>

                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <button type="button" @click="shareWhatsApp()" class="flex items-center justify-center gap-1.5 rounded-full bg-emerald-600 px-4 py-2.5 text-[11px] font-semibold text-white hover:bg-emerald-700">
                                <i data-lucide="message-circle" class="h-3.5 w-3.5"></i> Kirim via WhatsApp
                            </button>
                            <button type="button" @click="next()" class="flex items-center justify-center gap-1.5 rounded-full border border-[#17211a]/20 px-4 py-2.5 text-[11px] font-semibold text-[#4f5a51] hover:border-[#17211a]/45">
                                <i data-lucide="user-plus" class="h-3.5 w-3.5"></i> Tamu Berikutnya
                            </button>
                        </div>

                        <p class="mt-2 text-[10px] text-emerald-700" x-show="feedback" x-cloak x-text="feedback"></p>

                        <details class="mt-3">
                            <summary class="cursor-pointer text-[10px] font-medium text-[#6b746c]">Lihat pratinjau pesan</summary>
                            <pre class="mt-2 whitespace-pre-wrap rounded-lg border border-[#17211a]/10 bg-white p-2 text-[10px] leading-5 text-[#4f5a51]" x-text="message"></pre>
                        </details>

                        <p class="mt-2 text-[9px] leading-4 text-[#a8b0a9]">Tombol bagikan memakai berbagi perangkat bila tersedia, atau membuka WhatsApp dengan pesan siap kirim. Anda memilih kontaknya sendiri. Sistem tidak mengirim otomatis dan tidak mengakses kontak.</p>
                    </div>
                    <a href="{{ $wedding->invitationUrl() }}" target="_blank" rel="noopener" class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-full bg-[#17211a] px-4 py-2.5 text-[11px] font-semibold text-[#f4f1ea]"><i data-lucide="external-link" class="h-3.5 w-3.5"></i> Buka Undangan Publik</a>
                @else
                    <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[10px] text-amber-900">Undangan masih draf. Tombol bagikan aktif setelah undangan benar-benar dipublikasikan oleh Admin.</p>
                @endif
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('wedding.guests.index', ['wedding' => $wedding->id]) }}" class="rounded-lg border border-[#17211a]/15 px-3 py-2 text-center text-[10px] font-medium hover:border-[#17211a]/35">Daftar Tamu</a>
                    <a href="{{ route('wedding.rsvp.index', ['wedding' => $wedding->id]) }}" class="rounded-lg border border-[#17211a]/15 px-3 py-2 text-center text-[10px] font-medium hover:border-[#17211a]/35">RSVP</a>
                </div>
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Statistik</h3>
                <dl class="mt-3 space-y-2.5 text-[11px]">
                    @foreach([
                        ['Dilihat', number_format($wedding->views_count, 0, ',', '.').' kali'],
                        ['Tamu', $wedding->guests_count.' orang'],
                        ['RSVP masuk', $wedding->rsvps_count.' konfirmasi'],
                        ['Ucapan', $wedding->wishes_count.' pesan'],
                        ['Galeri', $wedding->photos->count().' foto'],
                        ['Dibuat', $wedding->created_at?->translatedFormat('d M Y')],
                        ['Diajukan', $wedding->submitted_at?->translatedFormat('d M Y H:i') ?? '—'],
                        ['Dipublikasikan', $wedding->published_at?->translatedFormat('d M Y') ?? '—'],
                    ] as [$label, $value])
                        <div class="flex items-center justify-between gap-3 border-b border-[#17211a]/5 pb-2 last:border-0">
                            <dt class="text-[#8a938b]">{{ $label }}</dt>
                            <dd class="font-medium text-[#17211a]">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
                <h3 class="font-display text-lg font-light">Tindakan</h3>
                <div class="mt-3 space-y-2">
                    <form method="POST" action="{{ route('wedding.invitations.duplicate', $wedding) }}">@csrf
                        <button class="flex w-full items-center gap-2 rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-left text-[11px] font-medium hover:border-[#17211a]/35"><i data-lucide="copy" class="h-3.5 w-3.5"></i> Duplikasi sebagai draf</button>
                    </form>
                    @if(! $wedding->isPublished())
                        <a href="{{ route('wedding.invitations.edit', $wedding) }}#template-form" class="flex w-full items-center gap-2 rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-[11px] font-medium hover:border-[#17211a]/35"><i data-lucide="palette" class="h-3.5 w-3.5"></i> Ganti desain</a>
                    @endif
                    <form method="POST" action="{{ route('wedding.invitations.destroy', $wedding) }}" onsubmit="return confirm('Hapus undangan ini secara permanen? Tindakan tidak dapat dibatalkan.')">@csrf @method('DELETE')
                        <button class="flex w-full items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-left text-[11px] font-medium text-red-700 hover:bg-red-100"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i> Hapus undangan</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
function weddingShare(config) {
    return {
        baseUrl: config.baseUrl,
        messageTemplate: config.messageTemplate,
        name: '',
        feedback: '',
        // Link personal: ?to= hanya berisi nama penerima, di-encode agar aman
        // untuk spasi dan karakter Indonesia. Kosong → link umum.
        get link() {
            const name = (this.name || '').trim();
            return name === '' ? this.baseUrl : this.baseUrl + '?to=' + encodeURIComponent(name);
        },
        get message() {
            return (this.messageTemplate || '').replace('__LINK__', this.link);
        },
        next() {
            this.name = '';
            this.feedback = 'Siap untuk tamu berikutnya.';
        },
        shareWhatsApp() {
            const text = this.message;
            const fallback = 'https://wa.me/?text=' + encodeURIComponent(text);
            if (typeof navigator.share === 'function') {
                try {
                    navigator.share({ text, url: this.link }).catch(() => window.open(fallback, '_blank', 'noopener'));
                } catch (error) {
                    window.open(fallback, '_blank', 'noopener');
                }
            } else {
                window.open(fallback, '_blank', 'noopener');
            }
        },
    };
}
</script>
@endverbatim
@endpush
