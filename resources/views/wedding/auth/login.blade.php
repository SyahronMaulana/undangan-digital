@extends('wedding.layouts.auth')
@section('title', 'Masuk')
@section('form')
<div>
    <span class="text-[10px] font-semibold uppercase tracking-[.26em] text-[#9c7838]">Selamat datang kembali</span>
    <h1 class="mt-4 font-display text-[42px] font-light leading-none tracking-tight">Masuk ke <em class="italic text-[#9c7838]">dasbor.</em></h1>
    <p class="mt-4 text-sm leading-7 text-[#667068]">Kelola undangan, tamu, RSVP, dan ucapan dari satu tempat.</p>
</div>

<form method="POST" action="{{ route('wedding.login') }}" class="mt-9 space-y-5" x-data="{ show: false }">
    @csrf
    <div>
        <label for="email" class="mb-2 block text-[11px] font-semibold uppercase tracking-[.12em] text-[#4f5a51]">Alamat email</label>
        <div class="relative">
            <i data-lucide="mail" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9aa39b]"></i>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                class="h-[52px] w-full rounded-xl border border-[#dcd5c7] bg-white/70 pl-11 pr-4 text-sm outline-none transition placeholder:text-[#a7afa8] focus:border-[#b99a5e] focus:bg-white focus:ring-4 focus:ring-[#c7a15a]/10"
                placeholder="nama@email.com">
        </div>
        @error('email')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <div class="mb-2 flex items-center justify-between">
            <label for="password" class="block text-[11px] font-semibold uppercase tracking-[.12em] text-[#4f5a51]">Kata sandi</label>
        </div>
        <div class="relative">
            <i data-lucide="lock" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#9aa39b]"></i>
            <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                class="h-[52px] w-full rounded-xl border border-[#dcd5c7] bg-white/70 pl-11 pr-12 text-sm outline-none transition placeholder:text-[#a7afa8] focus:border-[#b99a5e] focus:bg-white focus:ring-4 focus:ring-[#c7a15a]/10"
                placeholder="••••••••">
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-[#8a938b] hover:bg-[#f0ebe0] hover:text-[#17211a]" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                <i data-lucide="eye" class="h-4 w-4" x-show="!show"></i>
                <i data-lucide="eye-off" class="h-4 w-4" x-show="show" x-cloak></i>
            </button>
        </div>
        @error('password')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <label class="flex items-center gap-2.5 text-xs text-[#667068]">
        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-[#dcd5c7] text-[#1f3d2b] focus:ring-[#c7a15a]">
        Ingat saya di perangkat ini
    </label>
    <button type="submit" class="flex h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-[#17211a] text-sm font-semibold text-[#f3eee2] transition hover:bg-[#25352a] focus:outline-none focus:ring-4 focus:ring-[#17211a]/15">
        Masuk ke Dasbor <i data-lucide="arrow-right" class="h-4 w-4"></i>
    </button>
</form>

<p class="mt-7 text-center text-sm text-[#667068]">Belum punya akun? <a href="{{ route('wedding.register') }}" class="font-semibold text-[#8e6d2f] underline decoration-[#c7a15a]/40 underline-offset-4 hover:text-[#17211a]">Daftar gratis</a></p>

@if(app()->environment('local'))
<div class="mt-8 rounded-xl border border-dashed border-[#cfc6b5] bg-white/40 p-4">
    <p class="text-[10px] font-semibold uppercase tracking-[.16em] text-[#8a938b]">Akun demo lokal</p>
    <p class="mt-2 text-xs text-[#667068]">Customer: <strong>demo@markaspunokawan.test</strong> · demo12345</p>
    <p class="mt-1 text-xs text-[#667068]">Admin: <strong>admin@markaspunokawan.test</strong> · ganti-kata-sandi-ini</p>
</div>
@endif
@endsection
