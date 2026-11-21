<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Markas Punokawan Wedding') — @yield('page', 'Undangan Pernikahan Digital')</title>
    <meta name="description" content="@yield('description', 'Undangan pernikahan digital premium dari Markas Punokawan. Delapan desain berbeda, RSVP, ucapan, dan tamu personal.')">
    <meta name="theme-color" content="#101914">
    <meta property="og:title" content="@yield('title', 'Markas Punokawan Wedding')">
    <meta property="og:description" content="@yield('description', 'Undangan pernikahan digital premium dari Markas Punokawan.')">
    <meta property="og:type" content="website">
    <link rel="icon" href="{{ \App\Support\Brand::favicon() }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ \App\Support\Brand::faviconPng() }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\Brand::faviconPng() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,300;1,9..144,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/css/site.css') }}">
    @stack('styles')
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
</head>
<body class="@yield('body-class', 'site-body')">
<a href="#main" class="skip-link">Lompat ke konten</a>
<header class="site-nav" data-nav>
    <div class="site-wrap nav-inner">
        <a href="{{ route('wedding.home') }}" class="brand" aria-label="Markas Punokawan Wedding">
            <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::logoAlt() }}" class="brand-logo">
        </a>
        <nav class="nav-links" aria-label="Navigasi utama">
            <a href="{{ route('wedding.home') }}" class="{{ request()->routeIs('wedding.home') ? 'is-active' : '' }}">Beranda</a>
            <a href="{{ route('wedding.templates.index') }}" class="{{ request()->routeIs('wedding.templates.*') ? 'is-active' : '' }}">Desain</a>
            <a href="{{ route('wedding.home') }}#cara-kerja">Cara Kerja</a>
            <a href="{{ route('wedding.home') }}#publikasi">Publikasi</a>
        </nav>
        <div class="nav-actions">
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('wedding.admin.dashboard') }}" class="btn btn-small btn-line">Dasbor Admin</a>
                @else
                    <a href="{{ route('wedding.dashboard') }}" class="btn btn-small btn-line">Dasbor</a>
                @endif
            @else
                <a href="{{ route('wedding.login') }}" class="nav-login">Masuk</a>
                <a href="{{ route('wedding.register') }}" class="btn btn-small btn-gold">Buat Undangan</a>
            @endauth
        </div>
        <button class="menu-toggle" type="button" aria-label="Buka menu" data-menu-toggle>
            <span></span><span></span>
        </button>
    </div>
    <div class="mobile-menu" data-mobile-menu>
        <a href="{{ route('wedding.home') }}">Beranda</a>
        <a href="{{ route('wedding.templates.index') }}">Desain</a>
        <a href="{{ route('wedding.home') }}#cara-kerja">Cara Kerja</a>
        <a href="{{ route('wedding.home') }}#publikasi">Publikasi</a>
        @guest
            <a href="{{ route('wedding.login') }}">Masuk</a>
            <a href="{{ route('wedding.register') }}" class="is-gold">Buat Undangan</a>
        @else
            <a href="{{ auth()->user()->isAdmin() ? route('wedding.admin.dashboard') : route('wedding.dashboard') }}">Dasbor</a>
        @endguest
    </div>
</header>

<main id="main">
    @yield('content')
</main>

<footer class="site-footer">
    <div class="site-wrap footer-grid">
        <div class="footer-brand">
            <a href="{{ route('wedding.home') }}" class="brand">
                <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::logoAlt() }}" class="brand-logo">
            </a>
            <p>Undangan pernikahan digital yang dirancang dengan rasa, untuk momen yang hanya terjadi sekali.</p>
            <div class="footer-badge">Bagian dari ekosistem Markas Punokawan</div>
        </div>
        <div class="footer-col">
            <h4>Jelajahi</h4>
            <a href="{{ route('wedding.templates.index') }}">Katalog Desain</a>
            <a href="{{ route('wedding.home') }}#publikasi">Alur Publikasi</a>
            <a href="{{ route('wedding.home') }}#fitur">Fitur</a>
            <a href="{{ route('wedding.home') }}#cara-kerja">Cara Kerja</a>
        </div>
        <div class="footer-col">
            <h4>Mulai</h4>
            <a href="{{ route('wedding.register') }}">Buat Undangan</a>
            <a href="{{ route('wedding.login') }}">Masuk ke Dasbor</a>
            <a href="{{ route('wedding.home') }}">Tentang Wedding</a>
            <a href="{{ route('home') }}">Markas Punokawan</a>
        </div>
        <div class="footer-col footer-note">
            <h4>Catatan</h4>
            <p>Semua URL modul berada di bawah <code>/wedding</code>. Halaman utama platform tetap milik Markas Punokawan.</p>
            <span class="footer-year">© {{ date('Y') }} Markas Punokawan</span>
        </div>
    </div>
</footer>
<script src="{{ asset('wedding-assets/assets/js/site.js') }}" defer></script>
@stack('scripts')
</body>
</html>
