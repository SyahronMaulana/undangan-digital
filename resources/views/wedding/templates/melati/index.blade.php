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
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $wedding->coupleNames() }} — Undangan Pernikahan</title>
    <meta name="description" content="Undangan pernikahan {{ $wedding->coupleNames() }}, {{ $displayDate }}. Kami menantikan doa dan kehadiran Anda.">
    <meta name="theme-color" content="#fbf6ea">
    <meta property="og:title" content="{{ $wedding->coupleNames() }} — Undangan Pernikahan">
    <meta property="og:description" content="{{ $shareText }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $coverPhoto }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Great+Vibes&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/base.css') }}">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/melati.css') }}">
    <noscript><style>.tpl-cover{display:none!important}body.cover-open{overflow:auto!important}</style></noscript>
</head>
<body class="tpl tpl-melati cover-open{{ $previewMode ? ' is-preview' : '' }}">
@include('wedding.templates.partials.icons')

{{-- ============================ COVER ============================ --}}
<div class="tpl-cover mel-cover" id="tw-cover" aria-label="Sampul undangan">
    <div class="tpl-cover-media mel-cover-media">
        <img src="{{ $coverPhoto }}" alt="Foto {{ $wedding->coupleNames() }}" fetchpriority="high">
    </div>
    <div class="mel-cover-veil"></div>
    <div class="mel-floral mel-floral-tl" aria-hidden="true">
        <svg viewBox="0 0 140 140"><g fill="none" stroke="currentColor" stroke-width=".9"><path d="M4 136C34 110 52 82 58 44c2-14 0-26-4-38"/><path d="M58 44c-12-8-24-10-34-6 8 10 20 16 34 6zM48 66c-14-4-24-1-32 6 12 6 24 2 32-6zM38 90c-12 0-20 5-25 14 12 2 21-4 25-14z"/><path d="M58 44c14-6 24-16 28-30-14 3-24 15-28 30zM50 68c14 0 25 5 33 15-14 2-25-4-33-15z"/></g></svg>
    </div>
    <div class="mel-floral mel-floral-br" aria-hidden="true">
        <svg viewBox="0 0 140 140"><g fill="none" stroke="currentColor" stroke-width=".9"><path d="M4 136C34 110 52 82 58 44c2-14 0-26-4-38"/><path d="M58 44c-12-8-24-10-34-6 8 10 20 16 34 6zM48 66c-14-4-24-1-32 6 12 6 24 2 32-6zM38 90c-12 0-20 5-25 14 12 2 21-4 25-14z"/><path d="M58 44c14-6 24-16 28-30-14 3-24 15-28 30zM50 68c14 0 25 5 33 15-14 2-25-4-33-15z"/></g></svg>
    </div>

    <div class="tpl-cover-inner mel-cover-frame">
        <span class="mel-frame-corner tl" aria-hidden="true"></span>
        <span class="mel-frame-corner tr" aria-hidden="true"></span>
        <span class="mel-frame-corner bl" aria-hidden="true"></span>
        <span class="mel-frame-corner br" aria-hidden="true"></span>

        <p class="mel-cover-eyebrow">The Wedding Celebration Of</p>
        <p class="mel-cover-ornament" aria-hidden="true">&#10047;</p>
        <h1 class="mel-cover-names">
            <span>{{ $wedding->groomDisplayName() }}</span>
            <em>&amp;</em>
            <span>{{ $wedding->brideDisplayName() }}</span>
        </h1>
        <p class="mel-cover-date">{{ $eventDate?->translatedFormat('d . m . Y') ?? 'Segera' }}</p>

        <div class="mel-guest tpl-guest">
            <span>Kepada Bapak/Ibu/Saudara/i</span>
            <strong>{{ $recipientName }}</strong>
        </div>

        <button class="tpl-cover-open mel-open" type="button" data-cover-open>
            <svg class="tpl-icon"><use href="#tw-i-mail"/></svg>
            Buka Undangan
        </button>
    </div>
    <span class="tpl-cover-scroll mel-cover-scroll" aria-hidden="true">Undangan Pernikahan</span>
</div>

{{-- ============================ MAIN ============================ --}}
<main class="tpl-main mel-main">
    {{-- Pembukaan --}}
    @if($wedding->sectionEnabled('pembukaan'))
    <section class="mel-hero" id="pembukaan">
        <div class="mel-hero-inner" data-reveal>
            @if($isIslami)
                <p class="mel-arabic" lang="ar" dir="rtl">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
            @endif
            <p class="mel-eyebrow">{{ $isIslami ? 'Assalamu’alaikum Warahmatullahi Wabarakatuh' : 'Dengan penuh sukacita' }}</p>
            <p class="mel-hero-text">{!! nl2br(e($wedding->openingText())) !!}</p>
            <span class="tpl-rule"></span>
            <p class="mel-hero-label">Kami yang berbahagia</p>
            <h2 class="mel-hero-names">{{ $wedding->groomDisplayName() }} <em>&amp;</em> {{ $wedding->brideDisplayName() }}</h2>
            <p class="mel-hero-date">{{ $displayDate }}</p>
            <a class="mel-scroll-hint" href="#mempelai" aria-label="Gulir ke mempelai">
                <svg class="tpl-icon"><use href="#tw-i-arrow-down"/></svg>
                <span>Mempelai</span>
            </a>
        </div>
    </section>
    @endif

    {{-- Mempelai --}}
    @if($wedding->sectionEnabled('mempelai'))
    <section class="tpl-section mel-couple" id="mempelai">
        <div class="tpl-wrap">
            <header class="mel-head tpl-center" data-reveal>
                <span class="tpl-kicker">Bride &amp; Groom</span>
                <h2 class="tpl-title">Kedua Mempelai</h2>
                <span class="tpl-rule"></span>
            </header>

            <div class="mel-couple-grid">
                <article class="mel-person" data-reveal="left">
                    <div class="mel-portrait">
                        @if($groomPhoto)
                            <img src="{{ $groomPhoto }}" alt="Foto {{ $wedding->groom_name }}" loading="lazy">
                        @else
                            <span class="mel-initials" aria-hidden="true">{{ $wedding->groomInitials() }}</span>
                        @endif
                    </div>
                    <h3>{{ $wedding->groom_name }}</h3>
                    <p class="mel-nick">{{ $wedding->groomDisplayName() }}</p>
                    <p class="mel-parents">{{ $wedding->groomParents() }}</p>
                    @if($groomIg)
                        <a class="mel-social" href="{{ $groomIg }}" target="_blank" rel="noopener">
                            <svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>
                            {{ $wedding->groom_instagram }}
                        </a>
                    @endif
                </article>

                <div class="mel-amp" data-reveal="scale" aria-hidden="true">&amp;</div>

                <article class="mel-person" data-reveal="right">
                    <div class="mel-portrait">
                        @if($bridePhoto)
                            <img src="{{ $bridePhoto }}" alt="Foto {{ $wedding->bride_name }}" loading="lazy">
                        @else
                            <span class="mel-initials" aria-hidden="true">{{ $wedding->brideInitials() }}</span>
                        @endif
                    </div>
                    <h3>{{ $wedding->bride_name }}</h3>
                    <p class="mel-nick">{{ $wedding->brideDisplayName() }}</p>
                    <p class="mel-parents">{{ $wedding->brideParents() }}</p>
                    @if($brideIg)
                        <a class="mel-social" href="{{ $brideIg }}" target="_blank" rel="noopener">
                            <svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>
                            {{ $wedding->bride_instagram }}
                        </a>
                    @endif
                </article>
            </div>
        </div>
    </section>
    @endif

    {{-- Kutipan --}}
    @if($wedding->sectionEnabled('kutipan') && $wedding->quoteText())
    <section class="tpl-section mel-quote" id="kutipan">
        <div class="tpl-wrap mel-quote-inner" data-reveal>
            <span class="mel-quote-mark" aria-hidden="true">&ldquo;</span>
            <blockquote>{!! nl2br(e($wedding->quoteText())) !!}</blockquote>
            <cite>— {{ $wedding->quoteSource() }}</cite>
        </div>
    </section>
    @endif

    {{-- Hitung mundur --}}
    @if($wedding->sectionEnabled('countdown') && $countdownAt)
    <section class="tpl-section mel-countdown-section" id="countdown">
        <div class="tpl-wrap">
            <header class="mel-head tpl-center" data-reveal>
                <span class="tpl-kicker">Save The Date</span>
                <h2 class="tpl-title">Menuju Hari Bahagia</h2>
                <p class="mel-countdown-date">{{ $displayDate }}</p>
            </header>
            <div class="mel-countdown" data-countdown="{{ $countdownAt }}" data-reveal>
                <div class="mel-count"><strong data-days>00</strong><span>Hari</span></div>
                <div class="mel-count"><strong data-hours>00</strong><span>Jam</span></div>
                <div class="mel-count"><strong data-minutes>00</strong><span>Menit</span></div>
                <div class="mel-count"><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
        </div>
    </section>
    @endif

    {{-- Acara --}}
    @if($wedding->sectionEnabled('acara'))
    <section class="tpl-section mel-events" id="acara">
        <div class="tpl-wrap">
            <header class="mel-head tpl-center" data-reveal>
                <span class="tpl-kicker">Rangkaian Acara</span>
                <h2 class="tpl-title">Dengan Penuh Cinta</h2>
                <span class="tpl-rule"></span>
            </header>

            <div class="mel-event-grid">
                @forelse($events as $event)
                    <article class="mel-event" data-reveal>
                        <span class="mel-event-icon"><svg class="tpl-icon"><use href="#tw-i-calendar"/></svg></span>
                        <h3>{{ $event->name }}</h3>
                        <p class="mel-event-date">
                            {{ $event->event_date?->translatedFormat('l, d F Y') ?? $displayDate }}
                        </p>
                        <p class="mel-event-time">{{ $event->timeRange() ?: 'Waktu menyusul' }}</p>
                        <span class="mel-event-line"></span>
                        <p class="mel-event-venue">{{ $event->venue_name }}</p>
                        <p class="mel-event-address">{{ $event->address }}</p>
                        @if($event->maps_url)
                            <a class="tpl-cta mel-map-link" href="{{ $event->maps_url }}" target="_blank" rel="noopener">
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
                <div class="mel-live" data-reveal>
                    <p>Anda juga dapat menyaksikan momen bahagia kami secara langsung.</p>
                    <a class="tpl-cta" href="{{ $wedding->live_stream_url }}" target="_blank" rel="noopener">
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
    <section class="tpl-section mel-story" id="kisah">
        <div class="tpl-wrap">
            <header class="mel-head tpl-center" data-reveal>
                <span class="tpl-kicker">Our Story</span>
                <h2 class="tpl-title">Kisah Kami</h2>
                <span class="tpl-rule"></span>
            </header>
            <div class="mel-timeline">
                @foreach($wedding->stories->sortBy('sort_order') as $story)
                    <article class="mel-timeline-item" data-reveal>
                        <span class="mel-timeline-dot" aria-hidden="true"></span>
                        <p class="mel-timeline-date">{{ $story->story_date }}</p>
                        <h3>{{ $story->title }}</h3>
                        <p>{{ $story->body }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Galeri --}}
    @if($wedding->sectionEnabled('galeri') && count($photos))
    <section class="tpl-section mel-gallery-section" id="galeri">
        <div class="tpl-wrap">
            <header class="mel-head tpl-center" data-reveal>
                <span class="tpl-kicker">Gallery</span>
                <h2 class="tpl-title">Momen Kami</h2>
                <span class="tpl-rule"></span>
            </header>
            <div class="mel-gallery">
                @foreach($photos as $index => $photo)
                    <button type="button" class="mel-shot{{ $index % 5 === 0 ? ' is-tall' : '' }}" data-lightbox="{{ $photo }}" data-reveal="scale" aria-label="Perbesar foto {{ $index + 1 }}">
                        <img src="{{ $photo }}" alt="Momen {{ $wedding->coupleNames() }} {{ $index + 1 }}" loading="lazy">
                    </button>
                @endforeach
            </div>
            @if($wedding->hashtag)
                <p class="mel-hashtag" data-reveal>{{ $wedding->hashtag }}</p>
            @endif
        </div>
    </section>
    @endif

    {{-- Tanda kasih --}}
    @if($wedding->sectionEnabled('tanda_kasih') && $wedding->gifts->isNotEmpty())
    <section class="tpl-section mel-gifts" id="tanda-kasih">
        <div class="tpl-wrap">
            <header class="mel-head tpl-center" data-reveal>
                <span class="tpl-kicker">Wedding Gift</span>
                <h2 class="tpl-title">Tanda Kasih</h2>
                <p class="mel-gift-copy">Doa restu Anda adalah hadiah terindah. Bila ingin memberi tanda kasih, kami sediakan kanal berikut.</p>
            </header>
            <div class="mel-gift-grid">
                @foreach($wedding->gifts as $gift)
                    <article class="mel-gift" data-reveal>
                        <span class="mel-gift-bank">{{ $gift->bank_name }}</span>
                        <p class="mel-gift-number">{{ $gift->account_number }}</p>
                        <p class="mel-gift-name">a.n. {{ $gift->account_name }}</p>
                        <button type="button" class="tpl-cta mel-copy" data-copy="{{ $gift->account_number }}">
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
    <section class="tpl-section mel-rsvp" id="konfirmasi">
        <div class="tpl-wrap mel-form-wrap">
            <header class="mel-head tpl-center" data-reveal>
                <span class="tpl-kicker">RSVP</span>
                <h2 class="tpl-title">Konfirmasi Kehadiran</h2>
                <p class="mel-form-copy">Kehadiran Anda sangat berarti bagi kami. Mohon konfirmasi sebelum hari acara.</p>
            </header>
            <div class="mel-form-card" data-reveal>
                @include('wedding.templates.partials.rsvp')
            </div>
        </div>
    </section>
    @endif

    {{-- Ucapan --}}
    @if($wedding->sectionEnabled('ucapan'))
    <section class="tpl-section mel-wishes" id="ucapan">
        <div class="tpl-wrap mel-form-wrap">
            <header class="mel-head tpl-center" data-reveal>
                <span class="tpl-kicker">Wishes</span>
                <h2 class="tpl-title">Ucapan &amp; Doa</h2>
                <p class="mel-form-copy">Tuliskan doa dan harapan terbaik Anda untuk perjalanan kami.</p>
            </header>
            <div class="mel-form-card" data-reveal>
                @include('wedding.templates.partials.wishes')
            </div>
        </div>
    </section>
    @endif

    {{-- Penutup --}}
    @if($wedding->sectionEnabled('penutup'))
    <footer class="mel-closing" id="penutup">
        <div class="mel-closing-inner" data-reveal>
            <span class="mel-closing-crest">&#10047;</span>
            <p class="mel-closing-text">{!! nl2br(e($wedding->closingText())) !!}</p>
            <p class="mel-closing-label">Wassalamu’alaikum Warahmatullahi Wabarakatuh</p>
            <h2 class="mel-closing-names">{{ $wedding->groomDisplayName() }} <em>&amp;</em> {{ $wedding->brideDisplayName() }}</h2>
            <p class="mel-closing-date">{{ $displayDate }}</p>

            <div class="mel-closing-actions">
                <button type="button" class="tpl-cta" data-share="{{ $shareUrl }}" data-share-text="{{ $shareText }}" data-share-title="{{ $wedding->coupleNames() }}">
                    <svg class="tpl-icon"><use href="#tw-i-share"/></svg>
                    Bagikan Undangan
                </button>
                @if($music)
                    <button type="button" class="tpl-cta" data-music-toggle aria-pressed="false">
                        <svg class="tpl-icon"><use href="#tw-i-music"/></svg>
                        Musik
                    </button>
                @endif
            </div>

            <p class="mel-credit">Dibuat dengan cinta bersama <strong>Markas Punokawan Wedding</strong></p>
        </div>
    </footer>
    @endif
</main>

{{-- Dock --}}
<nav class="tpl-dock mel-dock" aria-label="Navigasi undangan">
    <a href="#pembukaan" data-dock-link aria-label="Pembukaan"><svg class="tpl-icon"><use href="#tw-i-home"/></svg><span class="dock-tip">Pembukaan</span></a>
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
<script src="{{ asset('wedding-assets/assets/templates/melati.js') }}" defer></script>
</body>
</html>
