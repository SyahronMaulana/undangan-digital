@extends('layouts.auth')
@section('title', 'Daftar')
@section('content')
<h1 class="text-xl font-semibold">Buat akun baru</h1>
<form method="POST" action="{{ url('/register') }}" class="mt-6 space-y-4">
    @csrf
    @foreach ([['name','Nama lengkap','text'],['email','Email','email'],['phone','Nomor WhatsApp (opsional)','tel']] as [$f,$label,$type])
    <div>
        <label for="{{ $f }}" class="block text-sm font-medium">{{ $label }}</label>
        <input id="{{ $f }}" name="{{ $f }}" type="{{ $type }}" value="{{ old($f) }}" @if($f!=='phone') required @endif
               class="mt-1 w-full rounded-md border border-stone-300 px-3 py-2 focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
        @error($f)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    @endforeach
    <div>
        <label for="password" class="block text-sm font-medium">Kata sandi (minimal 8 karakter)</label>
        <input id="password" name="password" type="password" required
               class="mt-1 w-full rounded-md border border-stone-300 px-3 py-2 focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
        @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation" class="block text-sm font-medium">Ulangi kata sandi</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required
               class="mt-1 w-full rounded-md border border-stone-300 px-3 py-2 focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
    </div>
    <button class="w-full rounded-md bg-emerald-900 px-4 py-2.5 font-medium text-white hover:bg-emerald-800">Daftar</button>
</form>
<p class="mt-6 text-sm text-stone-600">Sudah punya akun? <a class="font-medium text-emerald-900 underline" href="{{ route('login') }}">Masuk</a></p>
@endsection
