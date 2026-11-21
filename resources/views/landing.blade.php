<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Undangan pernikahan digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-stone-800 antialiased">
<main class="mx-auto flex min-h-screen max-w-2xl flex-col justify-center px-6 py-16">
    <h1 class="text-4xl font-semibold leading-tight text-emerald-950">Undangan pernikahan digital untuk Anda berdua</h1>
    <p class="mt-4 text-lg text-stone-600">Pilih desain, isi data, lalu bagikan lewat WhatsApp.</p>
    <div class="mt-8 flex gap-3">
        <a href="{{ route('register') }}" class="rounded-md bg-emerald-900 px-5 py-3 font-medium text-white hover:bg-emerald-800">Buat undangan</a>
        <a href="{{ route('login') }}" class="rounded-md border border-stone-300 px-5 py-3 font-medium hover:bg-white">Masuk</a>
    </div>
    <p class="mt-12 text-sm text-stone-500">Halaman ini sementara. Landing page dan katalog desain dibuat pada Tahap 3.</p>
</main>
</body>
</html>
