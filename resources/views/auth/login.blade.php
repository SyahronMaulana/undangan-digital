@extends('layouts.auth')
@section('title', 'Masuk')
@section('content')
<h1 class="text-xl font-semibold">Masuk ke akun Anda</h1>
<form method="POST" action="{{ url('/login') }}" class="mt-6 space-y-4">
    @csrf
    <div>
        <label for="email" class="block text-sm font-medium">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
               class="mt-1 w-full rounded-md border border-stone-300 px-3 py-2 focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
        @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="block text-sm font-medium">Kata sandi</label>
        <input id="password" name="password" type="password" required
               class="mt-1 w-full rounded-md border border-stone-300 px-3 py-2 focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700">
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded border-stone-300"> Ingat saya</label>
    <button class="w-full rounded-md bg-emerald-900 px-4 py-2.5 font-medium text-white hover:bg-emerald-800">Masuk</button>
</form>
<p class="mt-6 text-sm text-stone-600">Belum punya akun? <a class="font-medium text-emerald-900 underline" href="{{ route('register') }}">Daftar gratis</a></p>
@endsection
