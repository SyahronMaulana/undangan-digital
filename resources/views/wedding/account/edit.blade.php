@extends('wedding.layouts.dashboard')

@section('title', 'Pengaturan Akun')
@section('eyebrow', 'Akun')
@section('page-title', 'Pengaturan Akun')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <div class="grid gap-5 md:grid-cols-[1fr_1fr]">
        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
            <h2 class="font-display text-xl font-light">Profil</h2>
            <p class="text-[11px] text-[#8a938b]">Nama ini tampil di dasbor dan email transaksi.</p>
            <form method="POST" action="{{ route('wedding.account.update') }}" class="mt-4 space-y-3">
                @csrf @method('PUT')
                <div><label class="editor-label">Nama *</label><input name="name" value="{{ old('name', $user->name) }}" required maxlength="100" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm"></div>
                <div><label class="editor-label">Email *</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="150" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm"></div>
                <div><label class="editor-label">Nomor WhatsApp</label><input name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm"></div>
                <button class="rounded-full bg-[#17211a] px-5 py-2.5 text-xs font-semibold text-[#f4f1ea]">Simpan Profil</button>
            </form>
        </section>

        <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
            <h2 class="font-display text-xl font-light">Kata Sandi</h2>
            <p class="text-[11px] text-[#8a938b]">Gunakan minimal 8 karakter yang tidak mudah ditebak.</p>
            <form method="POST" action="{{ route('wedding.account.password') }}" class="mt-4 space-y-3">
                @csrf @method('PUT')
                <div><label class="editor-label">Kata sandi saat ini *</label><input type="password" name="current_password" required class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm"></div>
                <div><label class="editor-label">Kata sandi baru *</label><input type="password" name="password" required class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm"></div>
                <div><label class="editor-label">Ulangi kata sandi baru *</label><input type="password" name="password_confirmation" required class="w-full rounded-lg border border-[#17211a]/15 px-3 py-2.5 text-sm"></div>
                <button class="rounded-full border border-[#17211a]/20 px-5 py-2.5 text-xs font-semibold hover:border-[#17211a]/45">Ganti Kata Sandi</button>
            </form>
        </section>
    </div>

    <section class="rounded-2xl border border-[#17211a]/10 bg-white/75 p-5 backdrop-blur">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-light">Ringkasan Akun</h2>
                <p class="text-[11px] text-[#8a938b]">Modul Wedding terpisah dari halaman platform utama.</p>
            </div>
            <span class="rounded-full border border-[#c7a15a]/50 bg-[#c7a15a]/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-[#8e6d2f]">Akses Penuh</span>
        </div>
        <dl class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg bg-[#f4f1ea] px-3 py-2.5"><dt class="text-[9px] uppercase tracking-wider text-[#8a938b]">Role</dt><dd class="mt-0.5 text-sm font-medium capitalize">{{ str_replace('_', ' ', $user->role) }}</dd></div>
            <div class="rounded-lg bg-[#f4f1ea] px-3 py-2.5"><dt class="text-[9px] uppercase tracking-wider text-[#8a938b]">Terdaftar</dt><dd class="mt-0.5 text-sm font-medium">{{ $user->created_at?->translatedFormat('d F Y') }}</dd></div>
            <div class="rounded-lg bg-[#f4f1ea] px-3 py-2.5"><dt class="text-[9px] uppercase tracking-wider text-[#8a938b]">Undangan</dt><dd class="mt-0.5 text-sm font-medium">{{ $user->weddings()->count() }} undangan</dd></div>
        </dl>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('wedding.invitations.index') }}" class="rounded-full border border-[#17211a]/15 px-4 py-2 text-[11px] font-medium hover:border-[#17211a]/35">Undangan Saya</a>
            <a href="{{ route('home') }}" class="rounded-full border border-[#17211a]/15 px-4 py-2 text-[11px] font-medium hover:border-[#17211a]/35">Markas Punokawan</a>
        </div>
    </section>
</div>
@endsection
