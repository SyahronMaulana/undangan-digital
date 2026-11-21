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
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $wedding->coupleNames() }} — Undangan Pernikahan</title>
    <meta name="description" content="Undangan pernikahan {{ $wedding->coupleNames() }}, {{ $displayDate }}. Sebuah perayaan di taman yang tumbuh bersama cinta.">
    <meta name="theme-color" content="#f6f2e8">
    <meta property="og:title" content="{{ $wedding->coupleNames() }} — Undangan Pernikahan">
    <meta property="og:description" content="{{ $shareText }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $coverPhoto }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,300;1,9..144,400&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/base.css') }}">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/lestari.css') }}">
    <noscript><style>.tpl-cover{display:none!important}body.cover-open{overflow:auto!important}</style></noscript>
</head>
<body class="tpl tpl-lestari cover-open{{ $previewMode ? ' is-preview' : '' }}">
@include('wedding.templates.partials.icons')

{{-- ============================ COVER ============================ --}}
<div class="tpl-cover les-cover" id="tw-cover" aria-label="Sampul undangan">
    <div class="les-cover-leaf les-leaf-a" aria-hidden="true">
        <svg viewBox="0 0 200 260"><g fill="none" stroke="currentColor" stroke-width="1"><path d="M20 250C60 200 90 140 100 20"/><path d="M100 40c-18-14-36-16-52-8 14 16 34 22 52 8zM96 78c-20-10-38-8-52 4 16 13 37 14 52-4zM88 118c-20-4-36 4-46 18 17 8 36 2 46-18zM76 160c-18 2-30 14-34 30 17 1 32-10 34-30zM64 200c-14 6-22 18-22 32 14-3 24-16 22-32z"/><path d="M104 60c20-8 38-4 50 10-18 10-38 7-50-10zM96 100c22 0 40 10 46 28-20 4-38-8-46-28zM84 142c20 6 32 20 32 40-19-3-32-19-32-40zM72 184c16 10 24 26 20 42-17-8-24-26-20-42z"/></g></svg>
    </div>
    <div class="les-cover-leaf les-leaf-b" aria-hidden="true">
        <svg viewBox="0 0 200 260"><g fill="none" stroke="currentColor" stroke-width="1"><path d="M20 250C60 200 90 140 100 20"/><path d="M100 40c-18-14-36-16-52-8 14 16 34 22 52 8zM96 78c-20-10-38-8-52 4 16 13 37 14 52-4zM88 118c-20-4-36 4-46 18 17 8 36 2 46-18zM76 160c-18 2-30 14-34 30 17 1 32-10 34-30z"/><path d="M104 60c20-8 38-4 50 10-18 10-38 7-50-10zM96 100c22 0 40 10 46 28-20 4-38-8-46-28zM84 142c20 6 32 20 32 40-19-3-32-19-32-40z"/></g></svg>
    </div>

    <div class="tpl-cover-inner les-cover-inner">
        <p class="les-kicker">Together with joy</p>
        <div class="les-arch les-cover-arch">
            <div class="les-arch-inner">
                <img src="{{ $coverPhoto }}" alt="Foto {{ $wedding->coupleNames() }}" fetchpriority="high">
            </div>
            <span class="les-arch-leaves" aria-hidden="true">
                <svg viewBox="0 0 120 60"><g fill="none" stroke="currentColor" stroke-width="1"><path d="M4 56C24 34 44 20 66 12c14-5 30-7 50-5"/><path d="M24 40c-10-2-18 0-24 6 8 4 18 2 24-6zM42 28c-8-4-16-4-22 0 7 5 16 5 22 0zM62 18c-6-6-14-8-20-6 5 7 13 9 20 6zM84 12c-3-8-10-12-17-12 2 8 9 12 17 12z"/></g></svg>
            </span>
        </div>
        <h1 class="les-cover-names">{{ $wedding->groomDisplayName() }} <span>&amp;</span> {{ $wedding->brideDisplayName() }}</h1>
        <p class="les-cover-date">{{ $eventDate?->translatedFormat('d . m . Y') ?? 'Segera' }}</p>
        <div class="les-guest tpl-guest">
            <span>Dear</span>
            <strong>{{ $recipientName }}</strong>
        </div>
        <button class="tpl-cover-open les-open" type="button" data-cover-open>
            <svg class="tpl-icon"><use href="#tw-i-arrow-down"/></svg>
            Buka Undangan
        </button>
    </div>
    <span class="les-cover-foot" aria-hidden="true">A garden celebration</span>
</div>

{{-- ============================ MAIN ============================ --}}
<main class="tpl-main les-main">
    {{-- Pembukaan --}}
    @if($wedding->sectionEnabled('pembukaan'))
    <section class="les-hero" id="pembukaan">
        <div class="les-wrap">
            <span class="les-monogram" data-reveal>{{ mb_substr($wedding->groomDisplayName(), 0, 1) }}<i>&amp;</i>{{ mb_substr($wedding->brideDisplayName(), 0, 1) }}</span>
            <p class="les-hero-kicker" data-reveal>We are getting married</p>
            <h2 class="les-hero-names" data-reveal>{{ $wedding->groomDisplayName() }} <em>&amp;</em> {{ $wedding->brideDisplayName() }}</h2>
            <p class="les-hero-date" data-reveal>{{ $displayDate }} · {{ $mainEvent?->venue_name ?? 'Indonesia' }}</p>
            <div class="les-leaf-rule" data-reveal><span></span><svg viewBox="0 0 40 24" aria-hidden="true"><path d="M2 22C10 14 18 8 28 4c4-2 8-2 10-2-3 8-10 14-18 16-6 2-12 3-18 4z" fill="none" stroke="currentColor"/></svg><span></span></div>
            <p class="les-hero-text" data-reveal>{!! nl2br(e($wedding->openingText())) !!}</p>
            <a class="les-scroll" href="#mempelai" aria-label="Gulir ke mempelai">
                <span>Kenali kami</span>
                <svg class="tpl-icon"><use href="#tw-i-arrow-down"/></svg>
            </a>
        </div>
    </section>
    @endif

    {{-- Mempelai --}}
    @if($wedding->sectionEnabled('mempelai'))
    <section class="les-couple" id="mempelai">
        <div class="les-wrap">
            <header class="les-head" data-reveal>
                <span class="les-kicker">The Bride &amp; Groom</span>
                <h2>Dua hati, satu taman</h2>
            </header>

            <div class="les-couple-grid">
                <article class="les-person" data-reveal>
                    <div class="les-portrait">
                        @if($groomPhoto)
                            <img src="{{ $groomPhoto }}" alt="Foto {{ $wedding->groom_name }}" loading="lazy">
                        @else
                            <span class="les-initials" aria-hidden="true">{{ $wedding->groomInitials() }}</span>
                        @endif
                        <span class="les-badge">Groom</span>
                    </div>
                    <h3>{{ $wedding->groom_name }}</h3>
                    <p class="les-parents">{{ $wedding->groomParents() }}</p>
                    @if($groomIg)
                        <a class="les-social" href="{{ $groomIg }}" target="_blank" rel="noopener"><svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->groom_instagram }}</a>
                    @endif
                </article>

                <article class="les-person" data-reveal>
                    <div class="les-portrait">
                        @if($bridePhoto)
                            <img src="{{ $bridePhoto }}" alt="Foto {{ $wedding->bride_name }}" loading="lazy">
                        @else
                            <span class="les-initials" aria-hidden="true">{{ $wedding->brideInitials() }}</span>
                        @endif
                        <span class="les-badge">Bride</span>
                    </div>
                    <h3>{{ $wedding->bride_name }}</h3>
                    <p class="les-parents">{{ $wedding->brideParents() }}</p>
                    @if($brideIg)
                        <a class="les-social" href="{{ $brideIg }}" target="_blank" rel="noopener"><svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->bride_instagram }}</a>
                    @endif
                </article>
            </div>
        </div>
    </section>
    @endif

    {{-- Kutipan --}}
    @if($wedding->sectionEnabled('kutipan') && $wedding->quoteText())
    <section class="les-quote" id="kutipan">
        <div class="les-wrap les-quote-inner" data-reveal>
            <span class="les-quote-leaf" aria-hidden="true">
                <svg viewBox="0 0 60 60"><g fill="none" stroke="currentColor" stroke-width="1"><path d="M8 54C20 40 30 25 52 6"/><path d="M18 40c-8 0-14 4-16 10 8 1 14-3 16-10zM28 28c-7-3-14-1-18 4 6 3 13 2 18-4zM38 18c-5-5-12-6-17-3 4 6 11 7 17 3z"/></g></svg>
            </span>
            <blockquote>{!! nl2br(e($wedding->quoteText())) !!}</blockquote>
            <cite>{{ $wedding->quoteSource() }}</cite>
        </div>
    </section>
    @endif

    {{-- Countdown --}}
    @if($wedding->sectionEnabled('countdown') && $countdownAt)
    <section class="les-countdown-section" id="countdown">
        <div class="les-wrap">
            <header class="les-head les-head-center" data-reveal>
                <span class="les-kicker">Save the date</span>
                <h2>Sampai berjumpa di hari bahagia</h2>
                <p class="les-countdown-date">{{ $displayDate }}</p>
            </header>
            <div class="les-countdown" data-countdown="{{ $countdownAt }}" data-reveal>
                <div class="les-count"><strong data-days>00</strong><span>Hari</span></div>
                <div class="les-count"><strong data-hours>00</strong><span>Jam</span></div>
                <div class="les-count"><strong data-minutes>00</strong><span>Menit</span></div>
                <div class="les-count"><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
        </div>
    </section>
    @endif

    {{-- Acara --}}
    @if($wedding->sectionEnabled('acara'))
    <section class="les-events" id="acara">
        <div class="les-wrap">
            <header class="les-head" data-reveal>
                <span class="les-kicker">The celebration</span>
                <h2>Rangkaian acara</h2>
            </header>

            <div class="les-event-grid">
                @forelse($events as $event)
                    <article class="les-event" data-reveal>
                        <div class="les-event-arch">
                            <span class="les-event-icon"><svg class="tpl-icon"><use href="#tw-i-calendar"/></svg></span>
                            <p class="les-event-day">{{ $event->event_date?->format('d') ?? '—' }}</p>
                            <p class="les-event-month">{{ $event->event_date?->translatedFormat('F Y') ?? '' }}</p>
                        </div>
                        <div class="les-event-body">
                            <h3>{{ $event->name }}</h3>
                            <p class="les-event-time">{{ $event->timeRange() ?: 'Waktu menyusul' }}</p>
                            <p class="les-event-venue">{{ $event->venue_name }}</p>
                            <p class="les-event-address">{{ $event->address }}</p>
                            @if($event->maps_url)
                                <a class="les-btn" href="{{ $event->maps_url }}" target="_blank" rel="noopener">
                                    <svg class="tpl-icon"><use href="#tw-i-map"/></svg>
                                    Petunjuk Lokasi
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="tw-note">Rangkaian acara akan segera diumumkan.</p>
                @endforelse
            </div>

            @if($wedding->live_stream_url)
                <div class="les-live" data-reveal>
                    <span class="les-kicker">Live streaming</span>
                    <a class="les-btn" href="{{ $wedding->live_stream_url }}" target="_blank" rel="noopener">
                        <svg class="tpl-icon"><use href="#tw-i-play"/></svg>
                        Tonton Acara
                    </a>
                </div>
            @endif
        </div>
    </section>
    @endif

    {{-- Kisah --}}
    @if($wedding->sectionEnabled('kisah') && $wedding->stories->isNotEmpty())
    <section class="les-story" id="kisah">
        <div class="les-wrap">
            <header class="les-head les-head-center" data-reveal>
                <span class="les-kicker">Our story</span>
                <h2>Yang tumbuh perlahan</h2>
            </header>
            <div class="les-vine">
                @foreach($wedding->stories->sortBy('sort_order') as $story)
                    <article class="les-vine-item" data-reveal>
                        <span class="les-vine-dot" aria-hidden="true"></span>
                        <p class="les-vine-date">{{ $story->story_date }}</p>
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
    <section class="les-gallery-section" id="galeri">
        <div class="les-wrap">
            <header class="les-head" data-reveal>
                <span class="les-kicker">Gallery</span>
                <h2>Momen di taman</h2>
            </header>
            <div class="les-gallery">
                @foreach($photos as $index => $photo)
                    <button type="button" class="les-photo{{ $index % 4 === 0 ? ' is-arch' : '' }}{{ $index % 5 === 2 ? ' is-wide' : '' }}" data-lightbox="{{ $photo }}" data-reveal="scale" aria-label="Perbesar foto {{ $index + 1 }}">
                        <img src="{{ $photo }}" alt="Momen {{ $wedding->coupleNames() }} {{ $index + 1 }}" loading="lazy">
                    </button>
                @endforeach
            </div>
            @if($wedding->hashtag)
                <p class="les-hashtag" data-reveal>{{ $wedding->hashtag }}</p>
            @endif
        </div>
    </section>
    @endif

    {{-- Tanda kasih --}}
    @if($wedding->sectionEnabled('tanda_kasih') && $wedding->gifts->isNotEmpty())
    <section class="les-gifts" id="tanda-kasih">
        <div class="les-wrap">
            <header class="les-head les-head-center" data-reveal>
                <span class="les-kicker">Wedding gift</span>
                <h2>Tanda kasih</h2>
                <p class="les-gift-lead">Kehadiran dan doa Anda adalah hadiah terindah. Bila berkenan, kami sediakan kanal berikut.</p>
            </header>
            <div class="les-gift-grid">
                @foreach($wedding->gifts as $gift)
                    <article class="les-gift" data-reveal>
                        <span class="les-gift-leaf" aria-hidden="true">
                            <svg viewBox="0 0 40 24"><path d="M2 22C10 14 18 8 28 4c4-2 8-2 10-2-3 8-10 14-18 16-6 2-12 3-18 4z" fill="none" stroke="currentColor"/></svg>
                        </span>
                        <span class="les-gift-bank">{{ $gift->bank_name }}</span>
                        <p class="les-gift-number">{{ $gift->account_number }}</p>
                        <p class="les-gift-name">a.n. {{ $gift->account_name }}</p>
                        <button type="button" class="les-btn les-copy" data-copy="{{ $gift->account_number }}">
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
    <section class="les-rsvp" id="konfirmasi">
        <div class="les-wrap les-form-grid">
            <div class="les-form-aside" data-reveal>
                <span class="les-kicker">RSVP</span>
                <h2>Datang dan<br>berbahagia bersama</h2>
                <p>Mohon konfirmasi kehadiran Anda agar kami dapat mempersiapkan tempat terbaik di taman kami.</p>
            </div>
            <div class="les-form-card" data-reveal>
                @include('wedding.templates.partials.rsvp')
            </div>
        </div>
    </section>
    @endif

    {{-- Ucapan --}}
    @if($wedding->sectionEnabled('ucapan'))
    <section class="les-wishes" id="ucapan">
        <div class="les-wrap les-form-grid">
            <div class="les-form-aside" data-reveal>
                <span class="les-kicker">Wishes</span>
                <h2>Tanamkan doa<br>di taman kami</h2>
                <p>Setiap doa adalah benih yang akan tumbuh bersama perjalanan kami.</p>
            </div>
            <div class="les-form-card" data-reveal>
                @include('wedding.templates.partials.wishes')
            </div>
        </div>
    </section>
    @endif

    {{-- Penutup --}}
    @if($wedding->sectionEnabled('penutup'))
    <footer class="les-closing" id="penutup">
        <div class="les-closing-leaf" aria-hidden="true">
            <svg viewBox="0 0 300 200"><g fill="none" stroke="currentColor" stroke-width="1"><path d="M4 196C70 150 140 100 250 20"/><path d="M40 160c-20-4-36 2-44 16 20 4 36-4 44-16zM78 130c-18-8-34-4-44 8 18 8 34 2 44-8zM120 98c-16-12-32-12-44-2 14 11 32 12 44 2zM168 66c-12-15-28-18-42-11 10 14 27 17 42 11zM218 38c-8-16-22-22-37-18 7 16 21 22 37 18z"/><path d="M60 148c18 0 32 10 38 26-19 3-34-8-38-26zM104 112c20 3 34 16 36 34-20-1-35-15-36-34zM152 78c20 6 30 21 29 39-19-5-31-21-29-39zM202 46c18 8 27 24 24 41-18-7-28-24-24-41z"/></g></svg>
        </div>
        <div class="les-closing-inner" data-reveal>
            <span class="les-monogram les-monogram-light">{{ mb_substr($wedding->groomDisplayName(), 0, 1) }}<i>&amp;</i>{{ mb_substr($wedding->brideDisplayName(), 0, 1) }}</span>
            <p class="les-closing-text">{!! nl2br(e($wedding->closingText())) !!}</p>
            <h2>{{ $wedding->groomDisplayName() }} <em>&amp;</em> {{ $wedding->brideDisplayName() }}</h2>
            <p class="les-closing-date">{{ $displayDate }}</p>
            <div class="les-closing-actions">
                <button type="button" class="les-btn les-btn-light" data-share="{{ $shareUrl }}" data-share-text="{{ $shareText }}" data-share-title="{{ $wedding->coupleNames() }}">
                    <svg class="tpl-icon"><use href="#tw-i-share"/></svg>
                    Bagikan Undangan
                </button>
                @if($music)
                    <button type="button" class="les-btn les-btn-light" data-music-toggle aria-pressed="false">
                        <svg class="tpl-icon"><use href="#tw-i-music"/></svg>
                        Musik
                    </button>
                @endif
            </div>
            <p class="les-credit">Tumbuh bersama · Markas Punokawan Wedding</p>
        </div>
    </footer>
    @endif
</main>

{{-- Dock --}}
<nav class="tpl-dock les-dock" aria-label="Navigasi undangan">
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
<script src="{{ asset('wedding-assets/assets/templates/lestari.js') }}" defer></script>
</body>
</html>
