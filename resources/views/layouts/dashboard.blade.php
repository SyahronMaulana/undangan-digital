@php
    $u = auth()->user();
    $menu = $u->isAdmin()
        ? [
            ['Dasbor', route('admin.dashboard'), 'admin.dashboard'],
            ['Kelola Customer', null, null], ['Kelola Template', null, null], ['Kelola Undangan', null, null],
            ['Kelola Paket', null, null], ['Kelola Pembayaran', null, null], ['Pengaturan Platform', null, null],
        ]
        : [
            ['Dasbor', route('dashboard.index'), 'dashboard.index'],
            ['Undangan Saya', route('dashboard.invitations.index'), 'dashboard.invitations.index'],
            ['Buat Undangan', route('dashboard.invitations.create'), 'dashboard.invitations.create'],
            ['Pilihan Desain', null, null], ['Daftar Tamu', null, null], ['WhatsApp', null, null],
            ['Konfirmasi Kehadiran', null, null], ['Ucapan & Doa', null, null], ['Pengaturan Akun', null, null],
        ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-stone-800 antialiased" x-data="{ open: false }">
<header class="flex items-center justify-between border-b border-stone-200 bg-white px-4 py-3 lg:hidden">
    <span class="font-semibold text-emerald-900">{{ config('app.name') }}</span>
    <button @click="open = !open" class="rounded-md border border-stone-300 px-3 py-1.5 text-sm" aria-label="Buka menu">Menu</button>
</header>
<div class="lg:flex">
    <aside :class="open ? 'block' : 'hidden'" class="w-full border-b border-stone-200 bg-white lg:block lg:min-h-screen lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r">
        <div class="hidden px-5 py-5 text-lg font-semibold text-emerald-900 lg:block">{{ config('app.name') }}</div>
        <nav class="space-y-0.5 px-3 pb-4">
            @foreach ($menu as [$label, $href, $pattern])
                @if ($href)
                    <a href="{{ $href }}" class="block rounded-md px-3 py-2 text-sm {{ request()->routeIs($pattern) ? 'bg-emerald-50 font-medium text-emerald-900' : 'text-stone-700 hover:bg-stone-100' }}">{{ $label }}</a>
                @else
                    <span class="flex items-center justify-between rounded-md px-3 py-2 text-sm text-stone-400">{{ $label }}<span class="text-xs">Segera</span></span>
                @endif
            @endforeach
        </nav>
        <div class="border-t border-stone-200 px-5 py-4 text-sm">
            <p class="truncate font-medium">{{ $u->name }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">@csrf
                <button class="text-stone-600 underline hover:text-stone-900">Keluar</button>
            </form>
        </div>
    </aside>
    <main class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-8">
        @if (session('status'))
            <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
