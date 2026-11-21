<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
<main class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-5 py-10">
    <a href="{{ route('home') }}" class="mb-8 text-lg font-semibold text-emerald-900">{{ config('app.name') }}</a>
    <div class="rounded-lg border border-stone-200 bg-white p-6 sm:p-8">
        @yield('content')
    </div>
</main>
</body>
</html>
