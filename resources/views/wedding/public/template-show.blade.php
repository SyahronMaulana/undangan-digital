<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview {{ $template->name }} — Markas Punokawan Wedding</title>
    <meta name="description" content="{{ $meta['description'] ?? $template->description() }}">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ \App\Support\Brand::favicon() }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ \App\Support\Brand::faviconPng() }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\Brand::faviconPng() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,300;1,9..144,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/css/site.css') }}">
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
</head>
<body class="site-body preview-page">
<header class="preview-top">
    <div class="site-wrap preview-top-inner">
        <div class="preview-title">
            <a href="{{ route('wedding.templates.index') }}" class="brand" aria-label="Kembali ke katalog">
                <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::logoAlt() }}" class="brand-logo">
            </a>
            <div>
                <h1>{{ $template->name }}</h1>
                <p>{{ $meta['tagline'] ?? $template->tagline() }}</p>
            </div>
        </div>
        <div class="preview-controls">
            <div class="device-switch" aria-label="Pilih tampilan">
                <button type="button" class="is-active" data-device="mobile" title="Tampilan ponsel" aria-label="Tampilan ponsel"><i data-lucide="smartphone"></i></button>
                <button type="button" data-device="desktop" title="Tampilan desktop" aria-label="Tampilan desktop"><i data-lucide="monitor"></i></button>
            </div>
            <a href="{{ route('wedding.templates.index') }}" class="btn btn-small btn-line">Kembali</a>
            <a href="{{ route('wedding.templates.use', $template->slug) }}" class="btn btn-small btn-gold">Gunakan Template</a>
        </div>
    </div>
</header>

<main class="preview-stage">
    <aside class="preview-side">
        <span class="eyebrow">Live Preview</span>
        <h2>{{ $template->name }}</h2>
        <p>{{ $meta['description'] ?? $template->description() }}</p>
        <div class="palette" aria-hidden="true">
            @foreach(($template->palette ?: ($meta['palette'] ?? [])) as $color)
                <i style="background: {{ $color }}; width: 16px; height: 16px;"></i>
            @endforeach
        </div>
        <div class="preview-facts">
            <span>Konsep <b>{{ $meta['tagline'] ?? $template->style_label }}</b></span>
            <span>Tipografi <b>{{ implode(' · ', array_slice($meta['fonts'] ?? [], 0, 2)) }}</b></span>
            <span>Preview <b>View & CSS asli</b></span>
            <span>Demo <b>{{ implode(' & ', array_slice($meta['demo'] ?? ['Rangga', 'Ayu'], 0, 2)) }}</b></span>
        </div>
        <div style="margin-top:auto; display:grid; gap:10px">
            <a href="{{ route('wedding.templates.use', $template->slug) }}" class="btn btn-gold btn-block">Gunakan Template Ini</a>
            <a href="{{ route('wedding.templates.index') }}" class="btn btn-line btn-block">Bandingkan Desain Lain</a>
        </div>
        <p style="font-size:10px;color:#6f786f;margin:22px 0 0;line-height:1.6">Preview menampilkan data demo. Setelah Anda membuat undangan, preview memakai data milik Anda.</p>
    </aside>
    <section class="preview-canvas">
        <div class="device-frame" data-preview-frame>
            <iframe src="{{ route('wedding.templates.preview', $template->slug) }}" title="Undangan {{ $template->name }}" data-preview-iframe></iframe>
        </div>
    </section>
</main>
<script src="{{ asset('wedding-assets/assets/js/site.js') }}" defer></script>
</body>
</html>
