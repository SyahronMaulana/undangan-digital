<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dasbor') — Markas Punokawan Wedding</title>
    <link rel="icon" href="{{ \App\Support\Brand::favicon() }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ \App\Support\Brand::faviconPng() }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\Brand::faviconPng() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,300;1,9..144,400&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
    <style>
        body{font-family:'DM Sans',sans-serif}.font-display{font-family:'Fraunces',serif}
        [x-cloak]{display:none!important}
        .sidebar-scroll::-webkit-scrollbar{width:5px}.sidebar-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:99px}
        .dash-grid{background-image:linear-gradient(rgba(23,33,26,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(23,33,26,.035) 1px,transparent 1px);background-size:38px 38px}
    </style>
    @stack('head')
</head>
<body class="bg-[#f4f1ea] text-[#17211a] antialiased" x-data="{ sidebar: false, profile: false }">
<div class="min-h-screen lg:flex">
    <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-40 bg-[#0c120e]/55 backdrop-blur-sm lg:hidden"></div>

    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 flex w-[270px] flex-col bg-[#111b15] text-[#f1eee6] transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen">
        <div class="flex items-center justify-between px-5 pb-5 pt-6">
            <a href="{{ route('wedding.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::logoAlt() }}" class="h-9 w-auto flex-none">
                <span>
                    <strong class="block font-display text-[12px] font-medium uppercase tracking-[.17em]">Markas Punokawan</strong>
                    <em class="block text-[8px] not-italic uppercase tracking-[.25em] text-white/45">Wedding Dashboard</em>
                </span>
            </a>
            <button type="button" @click="sidebar = false" class="rounded-lg p-2 text-white/50 hover:bg-white/10 lg:hidden" aria-label="Tutup menu"><i data-lucide="x" class="h-5 w-5"></i></button>
        </div>

        <div class="mx-4 mb-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3 text-center">
            <span class="text-[9px] uppercase tracking-[.2em] font-semibold text-emerald-400">Akses Penuh Tanpa Batas</span>
        </div>

        <nav class="sidebar-scroll flex-1 space-y-6 overflow-y-auto px-3 pb-6">
            <div>
                <p class="px-3 pb-2 text-[9px] font-semibold uppercase tracking-[.22em] text-white/30">Undangan</p>
                <div class="space-y-0.5">
                    <a href="{{ route('wedding.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition {{ request()->routeIs('wedding.dashboard') ? 'bg-[#c7a15a]/15 font-medium text-[#e8d5aa]' : 'text-white/65 hover:bg-white/[.06] hover:text-white' }}">
                        <i data-lucide="layout-dashboard" class="h-4 w-4"></i> Dasbor
                    </a>
                    <a href="{{ route('wedding.invitations.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition {{ request()->routeIs('wedding.invitations.*') ? 'bg-[#c7a15a]/15 font-medium text-[#e8d5aa]' : 'text-white/65 hover:bg-white/[.06] hover:text-white' }}">
                        <i data-lucide="mail" class="h-4 w-4"></i> Undangan Saya
                    </a>
                    <a href="{{ route('wedding.invitations.create') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition {{ request()->routeIs('wedding.invitations.create') ? 'bg-[#c7a15a]/15 font-medium text-[#e8d5aa]' : 'text-white/65 hover:bg-white/[.06] hover:text-white' }}">
                        <i data-lucide="plus-circle" class="h-4 w-4"></i> Buat Undangan
                    </a>
                    <a href="{{ route('wedding.templates.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] text-white/65 transition hover:bg-white/[.06] hover:text-white">
                        <i data-lucide="palette" class="h-4 w-4"></i> Pilihan Desain
                        <i data-lucide="external-link" class="ml-auto h-3 w-3 text-white/25"></i>
                    </a>
                </div>
            </div>
            <div>
                <p class="px-3 pb-2 text-[9px] font-semibold uppercase tracking-[.22em] text-white/30">Interaksi</p>
                <div class="space-y-0.5">
                    <a href="{{ route('wedding.guests.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition {{ request()->routeIs('wedding.guests.*') ? 'bg-[#c7a15a]/15 font-medium text-[#e8d5aa]' : 'text-white/65 hover:bg-white/[.06] hover:text-white' }}">
                        <i data-lucide="users" class="h-4 w-4"></i> Daftar Tamu
                    </a>
                    <a href="{{ route('wedding.broadcast.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition {{ request()->routeIs('wedding.broadcast.*') ? 'bg-[#c7a15a]/15 font-medium text-[#e8d5aa]' : 'text-white/65 hover:bg-white/[.06] hover:text-white' }}">
                        <i data-lucide="message-circle" class="h-4 w-4"></i> WhatsApp
                    </a>
                    <a href="{{ route('wedding.rsvp.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition {{ request()->routeIs('wedding.rsvp.*') ? 'bg-[#c7a15a]/15 font-medium text-[#e8d5aa]' : 'text-white/65 hover:bg-white/[.06] hover:text-white' }}">
                        <i data-lucide="clipboard-check" class="h-4 w-4"></i> Konfirmasi Kehadiran
                    </a>
                    <a href="{{ route('wedding.wishes.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition {{ request()->routeIs('wedding.wishes.*') ? 'bg-[#c7a15a]/15 font-medium text-[#e8d5aa]' : 'text-white/65 hover:bg-white/[.06] hover:text-white' }}">
                        <i data-lucide="message-heart" class="h-4 w-4"></i> Ucapan & Doa
                    </a>
                </div>
            </div>
            <div>
                <p class="px-3 pb-2 text-[9px] font-semibold uppercase tracking-[.22em] text-white/30">Akun</p>
                <div class="space-y-0.5">
                    <a href="{{ route('wedding.account.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition {{ request()->routeIs('wedding.account.*') ? 'bg-[#c7a15a]/15 font-medium text-[#e8d5aa]' : 'text-white/65 hover:bg-white/[.06] hover:text-white' }}">
                        <i data-lucide="settings" class="h-4 w-4"></i> Pengaturan Akun
                    </a>
                </div>
            </div>
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-full bg-[#c7a15a]/20 font-display text-sm text-[#e8d5aa]">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-medium text-white/90">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[10px] text-white/40">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('wedding.logout') }}">@csrf<button type="submit" class="rounded-lg p-2 text-white/40 hover:bg-white/10 hover:text-white" title="Keluar"><i data-lucide="log-out" class="h-4 w-4"></i></button></form>
            </div>
        </div>
    </aside>

    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-30 border-b border-[#17211a]/10 bg-[#f4f1ea]/90 backdrop-blur-xl">
            <div class="flex min-h-[72px] flex-wrap items-center gap-4 px-5 py-3 sm:px-7 lg:px-9">
                <button type="button" @click="sidebar = true" class="rounded-lg border border-[#17211a]/15 p-2 text-[#17211a] lg:hidden" aria-label="Buka menu"><i data-lucide="menu" class="h-5 w-5"></i></button>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[10px] font-semibold uppercase tracking-[.18em] text-[#8a938b]">@yield('eyebrow', 'Markas Punokawan Wedding')</p>
                    <h1 class="truncate font-display text-[22px] font-light leading-tight tracking-tight">@yield('page-title', 'Dasbor')</h1>
                </div>
                <div class="flex items-center gap-2">
                    @yield('actions')
                    <a href="{{ route('wedding.home') }}" class="hidden items-center gap-1.5 rounded-full border border-[#17211a]/15 px-3.5 py-2 text-[11px] font-medium text-[#4f5a51] transition hover:border-[#17211a]/35 hover:text-[#17211a] sm:inline-flex">
                        <i data-lucide="external-link" class="h-3.5 w-3.5"></i> Situs Wedding
                    </a>
                </div>
            </div>
        </header>

        <main class="dash-grid min-h-[calc(100vh-72px)] px-5 py-6 sm:px-7 lg:px-9 lg:py-8">
            @if (session('status'))
                <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                    <i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 flex-none"></i>
                    <span class="flex-1">{{ session('status') }}</span>
                    <button type="button" @click="show = false" class="text-emerald-700/60 hover:text-emerald-900" aria-label="Tutup"><i data-lucide="x" class="h-4 w-4"></i></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="flex items-center gap-2 font-medium"><i data-lucide="alert-circle" class="h-4 w-4"></i> Periksa kembali data Anda.</p>
                    <ul class="mt-1.5 list-inside list-disc text-xs">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons())</script>
@stack('scripts')
</body>
</html>
