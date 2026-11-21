@php
    $photos = $wedding->photoUrls();
    $coverPhoto = $wedding->coverUrl() ?: asset('wedding-assets/assets/images/demo/cover.jpg');
    // Tanpa foto → tampilkan inisial nama (bukan gambar placeholder generik).
    $groomPhoto = $wedding->groomPhotoUrl();
    $bridePhoto = $wedding->bridePhotoUrl();
    $events = $wedding->events->sortBy('sort_order');
    $mainEvent = $events->first();
    $eventDate = $mainEvent?->event_date ?? $wedding->event_date;
    $displayDate = $eventDate?->translatedFormat('d F Y') ?? 'Tanggal menyusul';
    $countdownAt = $eventDate
        ? $eventDate->format('Y-m-d').'T'.substr((string) ($mainEvent?->start_time ?: '08:00'), 0, 5).':00'
        : null;
    $shareUrl = $wedding->invitationUrl($guest);
    $shareText = 'Undangan pernikahan '.$wedding->coupleNames().' — '.$displayDate;
    $music = $wedding->music_path ? asset('storage/'.$wedding->music_path) : null;
    $groomIg = $wedding->groom_instagram ? 'https://instagram.com/'.ltrim($wedding->groom_instagram, '@') : null;
    $brideIg = $wedding->bride_instagram ? 'https://instagram.com/'.ltrim($wedding->bride_instagram, '@') : null;
    $isIslami = $wedding->language_style === 'islami';
    $initials = mb_substr($wedding->groomDisplayName(), 0, 1).mb_substr($wedding->brideDisplayName(), 0, 1);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $wedding->coupleNames() }} — Undangan Pernikahan</title>
    <meta name="description" content="Undangan pernikahan {{ $wedding->coupleNames() }}, {{ $displayDate }}. Sebuah kisah cinta yang kami rayakan dengan lembut.">
    <meta name="theme-color" content="#f6e3df">
    <meta property="og:title" content="{{ $wedding->coupleNames() }} — Undangan Pernikahan">
    <meta property="og:description" content="{{ $shareText }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $coverPhoto }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Parisienne&family=Mulish:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/base.css') }}">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/asmara.css') }}">
    <noscript><style>.tpl-cover{display:none!important}body.cover-open{overflow:auto!important}</style></noscript>
</head>
<body class="tpl tpl-asmara cover-open{{ $previewMode ? ' is-preview' : '' }}">
@include('wedding.templates.partials.icons')

{{-- ============================ COVER ============================ --}}
<div class="tpl-cover asm-cover" id="tw-cover" aria-label="Sampul undangan">
    <div class="tpl-cover-media asm-cover-media">
        <img src="{{ $coverPhoto }}" alt="Foto {{ $wedding->coupleNames() }}" fetchpriority="high">
    </div>
    <div class="asm-cover-veil"></div>
    <div class="asm-cover-glow" aria-hidden="true"></div>

    <div class="tpl-cover-inner asm-cover-inner">
        <div class="asm-seal" aria-hidden="true">
            <span>{{ $initials }}</span>
        </div>
        <p class="asm-script asm-cover-script">We are getting married</p>
        <h1 class="asm-cover-names">
            <span>{{ $wedding->groomDisplayName() }}</span>
            <em>&amp;</em>
            <span>{{ $wedding->brideDisplayName() }}</span>
        </h1>
        <p class="asm-cover-date">{{ $eventDate?->translatedFormat('d . m . Y') ?? 'Segera' }}</p>

        <div class="asm-guest tpl-guest">
            <span>Kepada Bapak/Ibu/Saudara/i</span>
            <strong>{{ $recipientName }}</strong>
        </div>

        <button class="asm-open" type="button" data-cover-open>
            <svg class="tpl-icon"><use href="#tw-i-heart"/></svg>
            Buka Undangan
        </button>
    </div>

    <p class="asm-cover-foot">with love, {{ $wedding->coupleNames() }}</p>
</div>

{{-- ============================ MAIN ============================ --}}
<main class="tpl-main asm-main">
    {{-- Pembukaan --}}
    @if($wedding->sectionEnabled('pembukaan'))
    <section class="asm-hero" id="pembukaan">
        <div class="asm-hero-glow" aria-hidden="true"></div>
        <div class="asm-wrap asm-hero-inner">
            @if($isIslami)
                <p class="asm-arabic" lang="ar" dir="rtl">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
            @endif
            <p class="asm-kicker" data-reveal>{{ $isIslami ? 'Assalamu’alaikum Warahmatullahi Wabarakatuh' : 'Dengan segenap cinta' }}</p>
            <h2 class="asm-hero-names" data-reveal>
                <span>{{ $wedding->groomDisplayName() }}</span>
                <em>&amp;</em>
                <span>{{ $wedding->brideDisplayName() }}</span>
            </h2>
            <p class="asm-script asm-hero-script" data-reveal>are tying the knot</p>
            <p class="asm-hero-text" data-reveal>{!! nl2br(e($wedding->openingText())) !!}</p>
            <p class="asm-hero-date" data-reveal>{{ $displayDate }}</p>
            <a class="asm-scroll" href="#mempelai" aria-label="Gulir ke mempelai">
                <span>Mempelai</span>
                <svg class="tpl-icon"><use href="#tw-i-arrow-down"/></svg>
            </a>
        </div>
    </section>
    @endif

    {{-- Mempelai --}}
    @if($wedding->sectionEnabled('mempelai'))
    <section class="asm-section asm-couple" id="mempelai">
        <div class="asm-wrap">
            <header class="asm-head" data-reveal>
                <span class="asm-kicker">The Bride &amp; Groom</span>
                <h2 class="asm-title">Dua Hati</h2>
                <p class="asm-script asm-sub">two hearts, one home</p>
            </header>

            <div class="asm-couple-grid">
                <article class="asm-person" data-reveal="left">
                    <div class="asm-portrait">
                        @if($groomPhoto)
                            <img src="{{ $groomPhoto }}" alt="Foto {{ $wedding->groom_name }}" loading="lazy">
                        @else
                            <span class="asm-initials" aria-hidden="true">{{ $wedding->groomInitials() }}</span>
                        @endif
                    </div>
                    <p class="asm-role">The Groom</p>
                    <h3>{{ $wedding->groom_name }}</h3>
                    <p class="asm-parents">{{ $wedding->groomParents() }}</p>
                    @if($groomIg)
                        <a class="asm-social" href="{{ $groomIg }}" target="_blank" rel="noopener">
                            <svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->groom_instagram }}
                        </a>
                    @endif
                </article>

                <div class="asm-connector" data-reveal="scale" aria-hidden="true">
                    <span class="asm-connector-line"></span>
                    <svg class="tpl-icon"><use href="#tw-i-heart"/></svg>
                    <span class="asm-connector-line"></span>
                </div>

                <article class="asm-person" data-reveal="right">
                    <div class="asm-portrait">
                        @if($bridePhoto)
                            <img src="{{ $bridePhoto }}" alt="Foto {{ $wedding->bride_name }}" loading="lazy">
                        @else
                            <span class="asm-initials" aria-hidden="true">{{ $wedding->brideInitials() }}</span>
                        @endif
                    </div>
                    <p class="asm-role">The Bride</p>
                    <h3>{{ $wedding->bride_name }}</h3>
                    <p class="asm-parents">{{ $wedding->brideParents() }}</p>
                    @if($brideIg)
                        <a class="asm-social" href="{{ $brideIg }}" target="_blank" rel="noopener">
                            <svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->bride_instagram }}
                        </a>
                    @endif
                </article>
            </div>
        </div>
    </section>
    @endif

    {{-- Kutipan --}}
    @if($wedding->sectionEnabled('kutipan') && $wedding->quoteText())
    <section class="asm-quote-section" id="kutipan">
        <div class="asm-wrap">
            <div class="asm-quote-card" data-reveal>
                <span class="asm-quote-heart" aria-hidden="true"><svg class="tpl-icon"><use href="#tw-i-heart"/></svg></span>
                <blockquote>{!! nl2br(e($wedding->quoteText())) !!}</blockquote>
                <cite>{{ $wedding->quoteSource() }}</cite>
            </div>
        </div>
    </section>
    @endif

    {{-- Hitung mundur --}}
    @if($wedding->sectionEnabled('countdown') && $countdownAt)
    <section class="asm-section asm-countdown-section" id="countdown">
        <div class="asm-wrap">
            <header class="asm-head" data-reveal>
                <span class="asm-kicker">Counting Down</span>
                <h2 class="asm-title">Menuju Hari Bahagia</h2>
                <p class="asm-script asm-sub">{{ $displayDate }}</p>
            </header>
            <div class="asm-countdown" data-countdown="{{ $countdownAt }}" data-reveal>
                <div class="asm-count"><strong data-days>00</strong><span>Hari</span></div>
                <div class="asm-count"><strong data-hours>00</strong><span>Jam</span></div>
                <div class="asm-count"><strong data-minutes>00</strong><span>Menit</span></div>
                <div class="asm-count"><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
        </div>
    </section>
    @endif

    {{-- Acara --}}
    @if($wedding->sectionEnabled('acara'))
    <section class="asm-section asm-events" id="acara">
        <div class="asm-wrap">
            <header class="asm-head" data-reveal>
                <span class="asm-kicker">The Celebration</span>
                <h2 class="asm-title">Rangkaian Acara</h2>
                <p class="asm-script asm-sub">come celebrate with us</p>
            </header>

            <div class="asm-event-grid">
                @forelse($events as $event)
                    <article class="asm-event" data-reveal>
                        <span class="asm-event-badge">
                            <svg class="tpl-icon"><use href="#tw-i-calendar"/></svg>
                        </span>
                        <h3>{{ $event->name }}</h3>
                        <p class="asm-event-date">{{ $event->event_date?->translatedFormat('l, d F Y') ?? $displayDate }}</p>
                        <p class="asm-event-time">{{ $event->timeRange() ?: 'Waktu menyusul' }}</p>
                        <span class="asm-event-divider" aria-hidden="true"></span>
                        <p class="asm-event-venue">{{ $event->venue_name }}</p>
                        <p class="asm-event-address">{{ $event->address }}</p>
                        @if($event->maps_url)
                            <a class="asm-btn" href="{{ $event->maps_url }}" target="_blank" rel="noopener">
                                <svg class="tpl-icon"><use href="#tw-i-map"/></svg>
                                Lihat Lokasi
                            </a>
                        @endif
                    </article>
                @empty
                    <p class="tw-note">Rangkaian acara akan segera diumumkan.</p>
                @endforelse
            </div>

            @if($wedding->live_stream_url)
                <div class="asm-live" data-reveal>
                    <p class="asm-script">can’t make it? join us online</p>
                    <a class="asm-btn" href="{{ $wedding->live_stream_url }}" target="_blank" rel="noopener">
                        <svg class="tpl-icon"><use href="#tw-i-play"/></svg>
                        Tonton Live Streaming
                    </a>
                </div>
            @endif
        </div>
    </section>
    @endif

    {{-- Kisah --}}
    @if($wedding->sectionEnabled('kisah') && $wedding->stories->isNotEmpty())
    <section class="asm-section asm-story" id="kisah">
        <div class="asm-wrap">
            <header class="asm-head" data-reveal>
                <span class="asm-kicker">Our Love Story</span>
                <h2 class="asm-title">Kisah Kami</h2>
                <p class="asm-script asm-sub">every love has its own story</p>
            </header>
            <div class="asm-heart-line">
                @foreach($wedding->stories->sortBy('sort_order') as $story)
                    <article class="asm-story-item" data-reveal>
                        <div class="asm-story-dot" aria-hidden="true">
                            <svg class="tpl-icon"><use href="#tw-i-heart"/></svg>
                        </div>
                        <div class="asm-story-card">
                            <p class="asm-story-date">{{ $story->story_date }}</p>
                            <h3>{{ $story->title }}</h3>
                            <p>{{ $story->body }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Galeri --}}
    @if($wedding->sectionEnabled('galeri') && count($photos))
    <section class="asm-section asm-gallery-section" id="galeri">
        <div class="asm-wrap">
            <header class="asm-head" data-reveal>
                <span class="asm-kicker">Our Moments</span>
                <h2 class="asm-title">Galeri Cinta</h2>
                <p class="asm-script asm-sub">little pieces of our happiness</p>
            </header>
            <div class="asm-gallery">
                @foreach($photos as $index => $photo)
                    <button type="button" class="asm-photo{{ $index % 5 === 0 ? ' is-wide' : '' }}" data-lightbox="{{ $photo }}" data-reveal="scale" aria-label="Perbesar foto {{ $index + 1 }}">
                        <img src="{{ $photo }}" alt="Momen {{ $wedding->coupleNames() }} {{ $index + 1 }}" loading="lazy">
                    </button>
                @endforeach
            </div>
            @if($wedding->hashtag)
                <p class="asm-hashtag" data-reveal>{{ $wedding->hashtag }}</p>
            @endif
        </div>
    </section>
    @endif

    {{-- Tanda kasih --}}
    @if($wedding->sectionEnabled('tanda_kasih') && $wedding->gifts->isNotEmpty())
    <section class="asm-section asm-gifts" id="tanda-kasih">
        <div class="asm-wrap">
            <header class="asm-head" data-reveal>
                <span class="asm-kicker">Wedding Gift</span>
                <h2 class="asm-title">Tanda Kasih</h2>
                <p class="asm-script asm-sub">your presence is our greatest gift</p>
            </header>
            <div class="asm-gift-grid">
                @foreach($wedding->gifts as $gift)
                    <article class="asm-gift" data-reveal>
                        <span class="asm-gift-icon" aria-hidden="true"><svg class="tpl-icon"><use href="#tw-i-gift"/></svg></span>
                        <span class="asm-gift-bank">{{ $gift->bank_name }}</span>
                        <p class="asm-gift-number">{{ $gift->account_number }}</p>
                        <p class="asm-gift-name">a.n. {{ $gift->account_name }}</p>
                        <button type="button" class="asm-btn" data-copy="{{ $gift->account_number }}">
                            <svg class="tpl-icon"><use href="#tw-i-copy"/></svg>
                            <span data-copy-label>Salin Nomor</span>
                        </button>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- RSVP --}}
    @if($wedding->sectionEnabled('rsvp'))
    <section class="asm-section asm-rsvp" id="konfirmasi">
        <div class="asm-wrap asm-narrow">
            <header class="asm-head" data-reveal>
                <span class="asm-kicker">RSVP</span>
                <h2 class="asm-title">Konfirmasi Kehadiran</h2>
                <p class="asm-script asm-sub">we would love to have you there</p>
            </header>
            <div class="asm-form-card" data-reveal>
                @include('wedding.templates.partials.rsvp')
            </div>
        </div>
    </section>
    @endif

    {{-- Ucapan --}}
    @if($wedding->sectionEnabled('ucapan'))
    <section class="asm-section asm-wishes" id="ucapan">
        <div class="asm-wrap asm-narrow">
            <header class="asm-head" data-reveal>
                <span class="asm-kicker">Wishes</span>
                <h2 class="asm-title">Ucapan &amp; Doa</h2>
                <p class="asm-script asm-sub">leave a little love for us</p>
            </header>
            <div class="asm-form-card" data-reveal>
                @include('wedding.templates.partials.wishes')
            </div>
        </div>
    </section>
    @endif

    {{-- Penutup --}}
    @if($wedding->sectionEnabled('penutup'))
    <footer class="asm-closing" id="penutup">
        <div class="asm-closing-hearts" aria-hidden="true"></div>
        <div class="asm-wrap asm-closing-inner">
            <span class="asm-closing-icon" data-reveal aria-hidden="true"><svg class="tpl-icon"><use href="#tw-i-heart"/></svg></span>
            <p class="asm-script asm-closing-script" data-reveal>and so, our forever begins</p>
            <p class="asm-closing-text" data-reveal>{!! nl2br(e($wedding->closingText())) !!}</p>
            <h2 class="asm-closing-names" data-reveal>{{ $wedding->groomDisplayName() }} <em>&amp;</em> {{ $wedding->brideDisplayName() }}</h2>
            <p class="asm-closing-date" data-reveal>{{ $displayDate }}</p>
            <div class="asm-closing-actions" data-reveal>
                <button type="button" class="asm-btn" data-share="{{ $shareUrl }}" data-share-text="{{ $shareText }}" data-share-title="{{ $wedding->coupleNames() }}">
                    <svg class="tpl-icon"><use href="#tw-i-share"/></svg>
                    Bagikan Undangan
                </button>
                @if($music)
                    <button type="button" class="asm-btn" data-music-toggle aria-pressed="false">
                        <svg class="tpl-icon"><use href="#tw-i-music"/></svg>
                        Musik
                    </button>
                @endif
            </div>
            <p class="asm-credit" data-reveal>Markas Punokawan Wedding</p>
        </div>
    </footer>
    @endif
</main>

{{-- Dock --}}
<nav class="tpl-dock asm-dock" aria-label="Navigasi undangan">
    <a href="#pembukaan" data-dock-link aria-label="Pembukaan"><svg class="tpl-icon"><use href="#tw-i-home"/></svg><span class="dock-tip">Awal</span></a>
    <a href="#mempelai" data-dock-link aria-label="Mempelai"><svg class="tpl-icon"><use href="#tw-i-couple"/></svg><span class="dock-tip">Mempelai</span></a>
    <a href="#acara" data-dock-link aria-label="Acara"><svg class="tpl-icon"><use href="#tw-i-calendar"/></svg><span class="dock-tip">Acara</span></a>
    <a href="#galeri" data-dock-link aria-label="Galeri"><svg class="tpl-icon"><use href="#tw-i-images"/></svg><span class="dock-tip">Galeri</span></a>
    <a href="#tanda-kasih" data-dock-link aria-label="Tanda kasih"><svg class="tpl-icon"><use href="#tw-i-gift"/></svg><span class="dock-tip">Hadiah</span></a>
    <a href="#konfirmasi" data-dock-link aria-label="Konfirmasi"><svg class="tpl-icon"><use href="#tw-i-mail"/></svg><span class="dock-tip">RSVP</span></a>
    <a href="#ucapan" data-dock-link aria-label="Ucapan"><svg class="tpl-icon"><use href="#tw-i-heart"/></svg><span class="dock-tip">Ucapan</span></a>
</nav>

@if($music)
    <audio id="tw-music" src="{{ $music }}" loop preload="none"></audio>
@endif

<script src="{{ asset('wedding-assets/assets/js/invitation-core.js') }}" defer></script>
<script src="{{ asset('wedding-assets/assets/templates/asmara.js') }}" defer></script>
</body>
</html>
