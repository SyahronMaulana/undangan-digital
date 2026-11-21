<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Wedding Admin</title>
    <link rel="icon" href="{{ \App\Support\Brand::favicon() }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ \App\Support\Brand::faviconPng() }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\Brand::faviconPng() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;1,9..144,300&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
    <style>
        body{font-family:'DM Sans',sans-serif}.font-display{font-family:'Fraunces',serif}
        [x-cloak]{display:none!important}
        .admin-grid{background-image:linear-gradient(rgba(23,33,26,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(23,33,26,.03) 1px,transparent 1px);background-size:40px 40px}
    </style>
    @stack('head')
</head>
<body class="bg-[#f4f1ea] text-[#17211a] antialiased" x-data="{ sidebar: false }">
<div class="min-h-screen lg:flex">
    <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-40 bg-[#0c120e]/55 backdrop-blur-sm lg:hidden"></div>
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 flex w-[264px] flex-col bg-[#0c120e] text-[#f1eee6] transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen">
        <div class="flex items-center justify-between px-5 pb-5 pt-6">
            <a href="{{ route('wedding.admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::logoAlt() }}" class="h-9 w-auto flex-none">
                <span><strong class="block font-display text-[12px] font-medium uppercase tracking-[.17em]">Wedding Admin</strong><em class="block text-[8px] not-italic uppercase tracking-[.25em] text-white/45">Markas Punokawan</em></span>
            </a>
            <button type="button" @click="sidebar = false" class="rounded-lg p-2 text-white/50 hover:bg-white/10 lg:hidden" aria-label="Tutup menu"><i data-lucide="x" class="h-5 w-5"></i></button>
        </div>
        <div class="mx-4 mb-4 rounded-xl border border-white/10 bg-white/[.035] px-4 py-3">
            <p class="text-[9px] uppercase tracking-[.2em] text-white/40">Masuk sebagai</p>
            <p class="mt-1 truncate text-xs font-medium">{{ auth()->user()->name }}</p>
            <p class="truncate text-[10px] text-white/40">{{ auth()->user()->email }}</p>
        </div>
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 pb-6">
            @foreach([
                ['route' => 'wedding.admin.dashboard', 'pattern' => 'wedding.admin.dashboard', 'label' => 'Ringkasan', 'icon' => 'layout-dashboard'],
                ['route' => 'wedding.admin.publications.index', 'pattern' => 'wedding.admin.publications.*', 'label' => 'Pengajuan Publikasi', 'icon' => 'send'],
                ['route' => 'wedding.admin.customers.index', 'pattern' => 'wedding.admin.customers.*', 'label' => 'Customer', 'icon' => 'users'],
                ['route' => 'wedding.admin.weddings.index', 'pattern' => 'wedding.admin.weddings.*', 'label' => 'Undangan', 'icon' => 'mail'],
                ['route' => 'wedding.admin.templates.index', 'pattern' => 'wedding.admin.templates.*', 'label' => 'Template', 'icon' => 'palette'],
                ['route' => 'wedding.admin.settings.edit', 'pattern' => 'wedding.admin.settings.*', 'label' => 'Pengaturan Platform', 'icon' => 'settings'],
            ] as $nav)
                <a href="{{ route($nav['route']) }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition {{ request()->routeIs($nav['pattern']) ? 'bg-[#c7a15a]/15 font-medium text-[#e8d5aa]' : 'text-white/65 hover:bg-white/[.06] hover:text-white' }}">
                    <i data-lucide="{{ $nav['icon'] }}" class="h-4 w-4"></i> {{ $nav['label'] }}
                </a>
            @endforeach
            <div class="my-3 border-t border-white/10"></div>
            <a href="{{ route('wedding.home') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] text-white/65 transition hover:bg-white/[.06] hover:text-white"><i data-lucide="external-link" class="h-4 w-4"></i> Situs Wedding</a>
            <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] text-white/65 transition hover:bg-white/[.06] hover:text-white"><i data-lucide="arrow-up-left" class="h-4 w-4"></i> Platform Utama</a>
        </nav>
        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-full bg-[#c7a15a]/20 font-display text-sm text-[#e8d5aa]">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                <span class="min-w-0 flex-1 truncate text-[11px] text-white/60">Super Admin</span>
                <form method="POST" action="{{ route('wedding.logout') }}">@csrf<button type="submit" class="rounded-lg p-2 text-white/40 hover:bg-white/10 hover:text-white" title="Keluar"><i data-lucide="log-out" class="h-4 w-4"></i></button></form>
            </div>
        </div>
    </aside>

    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-30 border-b border-[#17211a]/10 bg-[#f4f1ea]/90 backdrop-blur-xl">
            <div class="flex min-h-[70px] flex-wrap items-center gap-4 px-5 py-3 sm:px-7 lg:px-9">
                <button type="button" @click="sidebar = true" class="rounded-lg border border-[#17211a]/15 p-2 lg:hidden" aria-label="Buka menu"><i data-lucide="menu" class="h-5 w-5"></i></button>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[10px] font-semibold uppercase tracking-[.18em] text-[#8a938b]">@yield('eyebrow', 'Wedding Administration')</p>
                    <h1 class="truncate font-display text-[22px] font-light leading-tight">@yield('page-title', 'Admin')</h1>
                </div>
                @yield('actions')
            </div>
        </header>
        <main class="admin-grid min-h-[calc(100vh-70px)] px-5 py-6 sm:px-7 lg:px-9 lg:py-8">
            @if(session('status'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status"><i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 flex-none"></i><span class="flex-1">{{ session('status') }}</span></div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert"><p class="font-medium">Periksa kembali data.</p><ul class="mt-1 list-inside list-disc text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons())</script>
@stack('scripts')
</body>
</html>
