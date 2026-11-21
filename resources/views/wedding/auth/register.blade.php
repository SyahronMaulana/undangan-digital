@extends('wedding.layouts.auth')
@section('title', 'Daftar')
@section('form')
<div>
    <span class="text-[10px] font-semibold uppercase tracking-[.26em] text-[#9c7838]">Mulai tanpa biaya</span>
    <h1 class="mt-4 font-display text-[42px] font-light leading-none tracking-tight">Buat akun <em class="italic text-[#9c7838]">Anda.</em></h1>
    <p class="mt-4 text-sm leading-7 text-[#667068]">Satu akun untuk membuat, mengelola, dan membagikan undangan pernikahan digital.</p>
</div>

<form method="POST" action="{{ route('wedding.register') }}" class="mt-9 space-y-4" x-data="{ show: false }">
    @csrf
    <div>
        <label for="name" class="mb-2 block text-[11px] font-semibold uppercase tracking-[.12em] text-[#4f5a51]">Nama lengkap</label>
        <div class="relative">
            <i data-lucide="user" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9aa39b]"></i>
            <input id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="h-[52px] w-full rounded-xl border border-[#dcd5c7] bg-white/70 pl-11 pr-4 text-sm outline-none transition placeholder:text-[#a7afa8] focus:border-[#b99a5e] focus:bg-white focus:ring-4 focus:ring-[#c7a15a]/10" placeholder="Nama Anda">
        </div>
        @error('name')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" class="mb-2 block text-[11px] font-semibold uppercase tracking-[.12em] text-[#4f5a51]">Alamat email</label>
        <div class="relative">
            <i data-lucide="mail" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9aa39b]"></i>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                class="h-[52px] w-full rounded-xl border border-[#dcd5c7] bg-white/70 pl-11 pr-4 text-sm outline-none transition placeholder:text-[#a7afa8] focus:border-[#b99a5e] focus:bg-white focus:ring-4 focus:ring-[#c7a15a]/10" placeholder="nama@email.com">
        </div>
        @error('email')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="phone" class="mb-2 block text-[11px] font-semibold uppercase tracking-[.12em] text-[#4f5a51]">Nomor WhatsApp <span class="font-normal normal-case tracking-normal text-[#9aa39b]">(opsional)</span></label>
        <div class="relative">
            <i data-lucide="phone" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9aa39b]"></i>
            <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                class="h-[52px] w-full rounded-xl border border-[#dcd5c7] bg-white/70 pl-11 pr-4 text-sm outline-none transition placeholder:text-[#a7afa8] focus:border-[#b99a5e] focus:bg-white focus:ring-4 focus:ring-[#c7a15a]/10" placeholder="0812 3456 7890">
        </div>
        @error('phone')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="mb-2 block text-[11px] font-semibold uppercase tracking-[.12em] text-[#4f5a51]">Kata sandi</label>
        <div class="relative">
            <i data-lucide="lock" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9aa39b]"></i>
            <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                class="h-[52px] w-full rounded-xl border border-[#dcd5c7] bg-white/70 pl-11 pr-12 text-sm outline-none transition placeholder:text-[#a7afa8] focus:border-[#b99a5e] focus:bg-white focus:ring-4 focus:ring-[#c7a15a]/10" placeholder="Minimal 8 karakter">
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-[#8a938b] hover:bg-[#f0ebe0] hover:text-[#17211a]" aria-label="Tampilkan kata sandi">
                <i data-lucide="eye" class="h-4 w-4" x-show="!show"></i>
                <i data-lucide="eye-off" class="h-4 w-4" x-show="show" x-cloak></i>
            </button>
        </div>
        @error('password')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation" class="mb-2 block text-[11px] font-semibold uppercase tracking-[.12em] text-[#4f5a51]">Ulangi kata sandi</label>
        <div class="relative">
            <i data-lucide="shield-check" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9aa39b]"></i>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="h-[52px] w-full rounded-xl border border-[#dcd5c7] bg-white/70 pl-11 pr-4 text-sm outline-none transition placeholder:text-[#a7afa8] focus:border-[#b99a5e] focus:bg-white focus:ring-4 focus:ring-[#c7a15a]/10" placeholder="Ketik ulang kata sandi">
        </div>
    </div>
    <p class="pt-1 text-[11px] leading-5 text-[#8a938b]">Dengan mendaftar, Anda menyetujui penggunaan data sesuai kebutuhan layanan undangan. Kata sandi disimpan dalam bentuk hash.</p>
    <button type="submit" class="flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-[#17211a] text-sm font-semibold text-[#f3eee2] transition hover:bg-[#25352a] focus:outline-none focus:ring-4 focus:ring-[#17211a]/15">
        Buat Akun <i data-lucide="arrow-right" class="h-4 w-4"></i>
    </button>
</form>

<p class="mt-7 text-center text-sm text-[#667068]">Sudah punya akun? <a href="{{ route('wedding.login') }}" class="font-semibold text-[#8e6d2f] underline decoration-[#c7a15a]/40 underline-offset-4 hover:text-[#17211a]">Masuk di sini</a></p>
@endsection
