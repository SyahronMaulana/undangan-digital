<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Markas Punokawan Wedding</title>
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
        .auth-grid{background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:44px 44px}
    </style>
</head>
<body class="min-h-screen bg-[#f4efe5] text-[#17211a] antialiased">
<div class="grid min-h-screen lg:grid-cols-[1.03fr_.97fr]">
    <aside class="relative hidden overflow-hidden bg-[#101914] lg:flex lg:flex-col lg:justify-between">
        <img src="{{ asset('wedding-assets/assets/images/demo/couple-1.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-45">
        <div class="absolute inset-0 bg-gradient-to-b from-[#101914]/55 via-[#101914]/55 to-[#101914]"></div>
        <div class="auth-grid absolute inset-0 opacity-30"></div>
        <div class="relative z-10 p-10 xl:p-14">
            <a href="{{ route('wedding.home') }}" class="inline-flex items-center gap-3 text-[#f3eee2]">
                <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::logoAlt() }}" class="h-10 w-auto flex-none">
                <span>
                    <strong class="block font-display text-[13px] font-medium uppercase tracking-[.17em]">Markas Punokawan</strong>
                    <em class="block text-[9px] not-italic uppercase tracking-[.3em] text-[#a8b0a9]">Wedding Invitation</em>
                </span>
            </a>
        </div>
        <div class="relative z-10 p-10 xl:p-14">
            <span class="text-[10px] font-semibold uppercase tracking-[.28em] text-[#c7a15a]">Satu undangan, satu cerita</span>
            <blockquote class="mt-6 max-w-lg font-display text-4xl font-light leading-[1.08] tracking-tight text-[#f3eee2] xl:text-5xl">
                “Hari yang indah dimulai dari cara ia <em class="italic text-[#c7a15a]">diceritakan.</em>”
            </blockquote>
            <p class="mt-6 max-w-md text-sm leading-7 text-[#a8b0a9]">Delapan desain yang dirancang satu per satu. RSVP, ucapan, tamu personal, dan tanda kasih dalam satu dasbor.</p>
            <div class="mt-10 flex flex-wrap gap-2">
                @foreach(['Melati','Senja','Lestari','Maharaja','Arum','Puspita','Asmara','Nirmala'] as $name)
                    <span class="rounded-full border border-white/15 px-3 py-1.5 text-[10px] tracking-wide text-white/65">{{ $name }}</span>
                @endforeach
            </div>
        </div>
        <div class="relative z-10 flex items-center justify-between border-t border-white/10 p-10 pt-6 text-[10px] uppercase tracking-[.18em] text-white/40 xl:p-14 xl:pt-6">
            <span>Modul /wedding</span>
            <span>© {{ date('Y') }} Markas Punokawan</span>
        </div>
    </aside>

    <main class="flex flex-col">
        <div class="flex items-center justify-between px-6 py-6 sm:px-10">
            <a href="{{ route('wedding.home') }}" class="inline-flex items-center gap-2 text-xs font-medium text-[#5d675f] transition hover:text-[#17211a]">
                <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Kembali ke Wedding
            </a>
            <a href="{{ route('home') }}" class="text-[10px] uppercase tracking-[.18em] text-[#8a938b] hover:text-[#17211a]">Markas Punokawan</a>
        </div>
        <div class="flex flex-1 items-center justify-center px-6 py-8 sm:px-10">
            <div class="w-full max-w-[420px]">
                <div class="mb-8 lg:hidden">
                    <a href="{{ route('wedding.home') }}" class="inline-flex items-center gap-3">
                        <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::logoAlt() }}" class="h-10 w-auto flex-none">
                        <span><strong class="block font-display text-[13px] uppercase tracking-[.17em]">Markas Punokawan</strong><em class="block text-[9px] not-italic uppercase tracking-[.3em] text-[#8a938b]">Wedding Invitation</em></span>
                    </a>
                </div>
                @if (session('status'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <p class="font-medium">Periksa kembali data Anda.</p>
                        <ul class="mt-1 list-inside list-disc text-xs">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                @yield('form')
            </div>
        </div>
    </main>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons())</script>
</body>
</html>
