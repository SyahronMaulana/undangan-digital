@extends('wedding.layouts.admin')

@section('title', 'Pengaturan Platform')
@section('eyebrow', 'Konfigurasi Platform')
@section('page-title', 'Pengaturan Platform')

@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    {{-- Kontak WhatsApp Admin --}}
    <section id="whatsapp-admin" class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-light">Kontak WhatsApp Admin</h2>
                <p class="mt-1 text-[11px] text-[#8a938b]">Nomor ini menjadi tujuan komunikasi customer, dipakai pada tombol <b>Hubungi Admin via WhatsApp</b> setelah pengajuan publikasi dan pada pesan otomatis pengajuan.</p>
            </div>
            @if($adminWhatsAppConfigured)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-semibold text-emerald-700"><i data-lucide="check-circle-2" class="h-3.5 w-3.5"></i> Aktif</span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-[10px] font-semibold text-amber-800"><i data-lucide="alert-triangle" class="h-3.5 w-3.5"></i> Belum diatur</span>
            @endif
        </div>

        <div class="mt-4 rounded-xl border border-[#17211a]/10 bg-[#fbf9f3] p-4">
            <p class="text-[10px] font-semibold uppercase tracking-[.16em] text-[#8a938b]">Nomor WhatsApp admin saat ini</p>
            @if($adminWhatsAppConfigured)
                <p class="mt-1 font-display text-2xl font-light text-[#17211a]">{{ $adminWhatsAppDisplay }}</p>
                <p class="mt-1 font-mono text-[10px] text-[#8a938b]">Format tautan: https://wa.me/{{ $adminWhatsApp }}</p>
            @else
                <p class="mt-1 text-sm font-medium text-amber-800">Belum dikonfigurasi.</p>
                <p class="mt-1 text-[11px] leading-5 text-amber-900">Selama nomor belum diatur, tombol WhatsApp customer tidak ditampilkan sebagai tautan. Segera atur nomor agar customer dapat menghubungi admin.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('wedding.admin.settings.whatsapp') }}" class="mt-4 space-y-3"
            onsubmit="return confirm('Simpan perubahan nomor WhatsApp admin? Nomor ini akan langsung dipakai seluruh tombol WhatsApp customer.')">
            @csrf @method('PUT')
            <div>
                <label for="admin_whatsapp" class="editor-label">Nomor WhatsApp Admin</label>
                <input id="admin_whatsapp" name="admin_whatsapp" value="{{ old('admin_whatsapp', $adminWhatsAppDisplay) }}" required maxlength="30"
                    inputmode="tel" autocomplete="off" placeholder="085155269940"
                    class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm">
                <p class="mt-1 text-[10px] text-[#8a938b]">Boleh dalam format <span class="font-mono">08xxxxxxxxxx</span>, <span class="font-mono">628xxxxxxxxxx</span>, atau <span class="font-mono">+628xxxxxxxxxx</span>. Nomor otomatis dinormalisasi ke format internasional tanpa tanda +, spasi, atau tanda hubung.</p>
                @error('admin_whatsapp')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2">
                <a href="{{ route('wedding.admin.settings.edit') }}" class="rounded-full border border-[#17211a]/15 px-5 py-2.5 text-xs font-semibold text-[#4f5a51] hover:border-[#17211a]/35">Batalkan</a>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-full bg-[#17211a] px-6 py-2.5 text-xs font-semibold text-[#f4f1ea]"><i data-lucide="save" class="h-3.5 w-3.5"></i> Simpan Perubahan</button>
            </div>
        </form>
    </section>

    {{-- Pengaturan modul Wedding lainnya --}}
    <form method="POST" action="{{ route('wedding.admin.settings.update') }}" class="space-y-5">
        @csrf @method('PUT')
        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
            <h2 class="font-display text-xl font-light">Dukungan</h2>
            <p class="text-[11px] text-[#8a938b]">Kanal bantuan tambahan di luar WhatsApp admin publikasi.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div><label class="editor-label">WhatsApp dukungan *</label><input name="support_whatsapp" value="{{ old('support_whatsapp', $values['support_whatsapp']) }}" required maxlength="30" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm"></div>
                <div><label class="editor-label">Email dukungan *</label><input type="email" name="support_email" value="{{ old('support_email', $values['support_email']) }}" required maxlength="120" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm"></div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
            <h2 class="font-display text-xl font-light">Pembayaran Manual</h2>
            <p class="text-[11px] text-[#8a938b]">Tidak ada payment gateway. Pembayaran dilakukan manual dan diverifikasi Admin sebelum undangan dipublikasikan.</p>
            <div class="mt-4"><label class="editor-label">Catatan pembayaran manual *</label><textarea name="manual_payment_note" rows="4" required maxlength="2000" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2 text-sm">{{ old('manual_payment_note', $values['manual_payment_note']) }}</textarea></div>
        </section>

        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
            <h2 class="font-display text-xl font-light">Ucapan & Tamu</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="editor-label">Moderasi ucapan *</label>
                    <select name="wish_moderation" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm">
                        <option value="post" @selected($values['wish_moderation'] === 'post')>Langsung tampil</option>
                        <option value="pre" @selected($values['wish_moderation'] === 'pre')>Menunggu moderasi</option>
                    </select>
                </div>
            </div>
        </section>

        <div class="flex justify-end"><button class="rounded-full bg-[#17211a] px-6 py-2.5 text-xs font-semibold text-[#f4f1ea]">Simpan Pengaturan Modul</button></div>
    </form>

    <section class="rounded-2xl border border-[#17211a]/10 bg-white/60 p-5">
        <h2 class="font-display text-lg font-light">Informasi Sistem</h2>
        <dl class="mt-3 space-y-2 text-[11px]">
            @foreach([['Laravel', app()->version()], ['PHP', PHP_VERSION], ['Lingkungan', app()->environment()], ['Prefix modul', '/wedding'], ['Bahasa', 'Bahasa Indonesia'], ['Template', count(config('wedding.templates')).' desain']] as [$label, $value])
                <div class="flex justify-between gap-3 border-b border-[#17211a]/5 pb-2"><dt class="text-[#8a938b]">{{ $label }}</dt><dd class="font-medium">{{ $value }}</dd></div>
            @endforeach
        </dl>
    </section>
</div>
@endsection
