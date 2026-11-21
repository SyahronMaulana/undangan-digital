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
    $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $wedding->coupleNames() }} — Undangan Pernikahan</title>
    <meta name="description" content="Undangan pernikahan {{ $wedding->coupleNames() }}, {{ $displayDate }}. Sebuah perayaan agung yang penuh kehormatan.">
    <meta name="theme-color" content="#0a0908">
    <meta property="og:title" content="{{ $wedding->coupleNames() }} — Undangan Pernikahan">
    <meta property="og:description" content="{{ $shareText }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $coverPhoto }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/base.css') }}">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/maharaja.css') }}">
    <noscript><style>.tpl-cover{display:none!important}body.cover-open{overflow:auto!important}</style></noscript>
</head>
<body class="tpl tpl-maharaja cover-open{{ $previewMode ? ' is-preview' : '' }}">
@include('wedding.templates.partials.icons')

{{-- ============================ COVER ============================ --}}
<div class="tpl-cover mhr-cover" id="tw-cover" aria-label="Sampul undangan">
    <div class="mhr-cover-texture" aria-hidden="true"></div>
    <div class="mhr-cover-frame" aria-hidden="true"></div>
    <div class="mhr-corner mhr-corner-tl" aria-hidden="true"></div>
    <div class="mhr-corner mhr-corner-tr" aria-hidden="true"></div>
    <div class="mhr-corner mhr-corner-bl" aria-hidden="true"></div>
    <div class="mhr-corner mhr-corner-br" aria-hidden="true"></div>

    <div class="tpl-cover-inner mhr-cover-inner">
        <p class="mhr-eyebrow">The Royal Wedding Of</p>

        <div class="mhr-crest" aria-hidden="true">
            <svg viewBox="0 0 120 120">
                <g fill="none" stroke="currentColor" stroke-width=".9">
                    <circle cx="60" cy="60" r="55"/>
                    <circle cx="60" cy="60" r="48"/>
                    <path d="M60 12l8 14-8 14-8-14 8-14zM60 80l8 14-8 14-8-14 8-14zM12 60l14-8 14 8-14 8-14-8zM80 60l14-8 14 8-14 8-14-8z"/>
                    <path d="M35 42l8-3 3-8 3 8 8 3-8 3-3 8-3-8-8-3zM71 42l8-3 3-8 3 8 8 3-8 3-3 8-3-8-8-3z"/>
                </g>
                <text x="60" y="70" text-anchor="middle" class="mhr-crest-letters">{{ mb_substr($wedding->groomDisplayName(), 0, 1) }} · {{ mb_substr($wedding->brideDisplayName(), 0, 1) }}</text>
            </svg>
        </div>

        <h1 class="mhr-cover-names">
            <span>{{ $wedding->groomDisplayName() }}</span>
            <em>&amp;</em>
            <span>{{ $wedding->brideDisplayName() }}</span>
        </h1>

        <div class="mhr-cover-date">
            <span></span>
            <p>{{ $eventDate?->translatedFormat('d . m . Y') ?? 'Segera' }}</p>
            <span></span>
        </div>

        <div class="mhr-guest tpl-guest">
            <span>Kepada Yang Terhormat</span>
            <strong>{{ $recipientName }}</strong>
        </div>

        <button class="mhr-open" type="button" data-cover-open>
            <span>Buka Undangan</span>
            <svg class="tpl-icon"><use href="#tw-i-arrow-right"/></svg>
        </button>
    </div>

    <p class="mhr-cover-foot">Undangan Pernikahan</p>
</div>

{{-- ============================ MAIN ============================ --}}
<main class="tpl-main mhr-main">
    {{-- Pembukaan --}}
    @if($wedding->sectionEnabled('pembukaan'))
    <section class="mhr-hero" id="pembukaan">
        <div class="mhr-wrap">
            <div class="mhr-emblem" data-reveal aria-hidden="true">
                <svg viewBox="0 0 120 44"><g fill="none" stroke="currentColor" stroke-width=".9"><path d="M2 22h34M84 22h34"/><path d="M36 22l12-9 12 9-12 9-12-9z"/><circle cx="60" cy="22" r="6"/><path d="M72 22l-6-9M72 22l-6 9"/></g></svg>
            </div>
            <p class="mhr-kicker" data-reveal>Undangan Pernikahan</p>
            <h2 class="mhr-hero-names" data-reveal>
                <span>{{ $wedding->groomDisplayName() }}</span>
                <em>and</em>
                <span>{{ $wedding->brideDisplayName() }}</span>
            </h2>
            <p class="mhr-hero-date" data-reveal>{{ $displayDate }}</p>
            <p class="mhr-hero-text" data-reveal>{!! nl2br(e($wedding->openingText())) !!}</p>
            <a class="mhr-scroll" href="#mempelai" data-reveal>
                <span>Mempelai</span>
                <svg class="tpl-icon"><use href="#tw-i-arrow-down"/></svg>
            </a>
        </div>
    </section>
    @endif

    {{-- Mempelai --}}
    @if($wedding->sectionEnabled('mempelai'))
    <section class="mhr-couple" id="mempelai">
        <div class="mhr-wrap">
            <header class="mhr-head" data-reveal>
                <span class="mhr-kicker">The Bride &amp; Groom</span>
                <h2 class="mhr-title">Dua Insan Terpilih</h2>
            </header>

            <div class="mhr-couple-grid">
                <article class="mhr-person" data-reveal="left">
                    <div class="mhr-octagon">
                        @if($groomPhoto)
                            <img src="{{ $groomPhoto }}" alt="Foto {{ $wedding->groom_name }}" loading="lazy">
                        @else
                            <span class="mhr-initials" aria-hidden="true">{{ $wedding->groomInitials() }}</span>
                        @endif
                        <span class="mhr-octagon-line" aria-hidden="true"></span>
                    </div>
                    <p class="mhr-person-role">The Groom</p>
                    <h3>{{ $wedding->groom_name }}</h3>
                    <p class="mhr-parents">{{ $wedding->groomParents() }}</p>
                    @if($groomIg)
                        <a class="mhr-social" href="{{ $groomIg }}" target="_blank" rel="noopener"><svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->groom_instagram }}</a>
                    @endif
                </article>

                <div class="mhr-amp" data-reveal="scale" aria-hidden="true">
                    <span>&amp;</span>
                </div>

                <article class="mhr-person" data-reveal="right">
                    <div class="mhr-octagon">
                        @if($bridePhoto)
                            <img src="{{ $bridePhoto }}" alt="Foto {{ $wedding->bride_name }}" loading="lazy">
                        @else
                            <span class="mhr-initials" aria-hidden="true">{{ $wedding->brideInitials() }}</span>
                        @endif
                        <span class="mhr-octagon-line" aria-hidden="true"></span>
                    </div>
                    <p class="mhr-person-role">The Bride</p>
                    <h3>{{ $wedding->bride_name }}</h3>
                    <p class="mhr-parents">{{ $wedding->brideParents() }}</p>
                    @if($brideIg)
                        <a class="mhr-social" href="{{ $brideIg }}" target="_blank" rel="noopener"><svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->bride_instagram }}</a>
                    @endif
                </article>
            </div>
        </div>
    </section>
    @endif

    {{-- Kutipan --}}
    @if($wedding->sectionEnabled('kutipan') && $wedding->quoteText())
    <section class="mhr-quote" id="kutipan">
        <div class="mhr-wrap">
            <div class="mhr-quote-panel" data-reveal>
                <span class="mhr-quote-mark" aria-hidden="true">&ldquo;</span>
                <blockquote>{!! nl2br(e($wedding->quoteText())) !!}</blockquote>
                <cite>{{ $wedding->quoteSource() }}</cite>
            </div>
        </div>
    </section>
    @endif

    {{-- Countdown --}}
    @if($wedding->sectionEnabled('countdown') && $countdownAt)
    <section class="mhr-countdown-section" id="countdown">
        <div class="mhr-wrap">
            <header class="mhr-head" data-reveal>
                <span class="mhr-kicker">The Countdown</span>
                <h2 class="mhr-title">Menuju Hari Agung</h2>
                <p class="mhr-countdown-date">{{ $displayDate }}</p>
            </header>
            <div class="mhr-countdown" data-countdown="{{ $countdownAt }}" data-reveal>
                <div class="mhr-count"><strong data-days>00</strong><span>Hari</span></div>
                <div class="mhr-count"><strong data-hours>00</strong><span>Jam</span></div>
                <div class="mhr-count"><strong data-minutes>00</strong><span>Menit</span></div>
                <div class="mhr-count"><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
        </div>
    </section>
    @endif

    {{-- Acara --}}
    @if($wedding->sectionEnabled('acara'))
    <section class="mhr-events" id="acara">
        <div class="mhr-wrap">
            <header class="mhr-head" data-reveal>
                <span class="mhr-kicker">The Royal Programme</span>
                <h2 class="mhr-title">Rangkaian Acara</h2>
            </header>

            <div class="mhr-event-grid">
                @forelse($events as $loopIndex => $event)
                    <article class="mhr-event" data-reveal>
                        <span class="mhr-event-num">{{ $romans[$loopIndex] ?? ($loopIndex + 1) }}</span>
                        <h3>{{ $event->name }}</h3>
                        <p class="mhr-event-date">{{ $event->event_date?->translatedFormat('l, d F Y') ?? $displayDate }}</p>
                        <p class="mhr-event-time">{{ $event->timeRange() ?: 'Waktu menyusul' }}</p>
                        <span class="mhr-event-line"></span>
                        <p class="mhr-event-venue">{{ $event->venue_name }}</p>
                        <p class="mhr-event-address">{{ $event->address }}</p>
                        @if($event->maps_url)
                            <a class="mhr-btn" href="{{ $event->maps_url }}" target="_blank" rel="noopener">
                                <svg class="tpl-icon"><use href="#tw-i-map"/></svg>
                                Buka Peta
                            </a>
                        @endif
                    </article>
                @empty
                    <p class="tw-note">Rangkaian acara akan segera diumumkan.</p>
                @endforelse
            </div>

            @if($wedding->live_stream_url)
                <div class="mhr-live" data-reveal>
                    <a class="mhr-btn" href="{{ $wedding->live_stream_url }}" target="_blank" rel="noopener">
                        <svg class="tpl-icon"><use href="#tw-i-play"/></svg>
                        Saksikan Live Streaming
                    </a>
                </div>
            @endif
        </div>
    </section>
    @endif

    {{-- Kisah --}}
    @if($wedding->sectionEnabled('kisah') && $wedding->stories->isNotEmpty())
    <section class="mhr-story" id="kisah">
        <div class="mhr-wrap">
            <header class="mhr-head" data-reveal>
                <span class="mhr-kicker">The Chronicle</span>
                <h2 class="mhr-title">Kisah Kami</h2>
            </header>
            <div class="mhr-chronicle">
                @foreach($wedding->stories->sortBy('sort_order') as $loopIndex => $story)
                    <article class="mhr-chronicle-item" data-reveal>
                        <span class="mhr-chronicle-year">{{ $story->story_date }}</span>
                        <span class="mhr-chronicle-dot" aria-hidden="true"></span>
                        <div>
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
    <section class="mhr-gallery-section" id="galeri">
        <div class="mhr-wrap">
            <header class="mhr-head" data-reveal>
                <span class="mhr-kicker">The Gallery</span>
                <h2 class="mhr-title">Potret Kehormatan</h2>
            </header>
            <div class="mhr-gallery">
                @foreach($photos as $index => $photo)
                    <button type="button" class="mhr-photo" data-lightbox="{{ $photo }}" data-reveal="scale" aria-label="Perbesar foto {{ $index + 1 }}">
                        <img src="{{ $photo }}" alt="Momen {{ $wedding->coupleNames() }} {{ $index + 1 }}" loading="lazy">
                        <span aria-hidden="true"></span>
                    </button>
                @endforeach
            </div>
            @if($wedding->hashtag)
                <p class="mhr-hashtag" data-reveal>{{ $wedding->hashtag }}</p>
            @endif
        </div>
    </section>
    @endif

    {{-- Tanda kasih --}}
    @if($wedding->sectionEnabled('tanda_kasih') && $wedding->gifts->isNotEmpty())
    <section class="mhr-gifts" id="tanda-kasih">
        <div class="mhr-wrap">
            <header class="mhr-head" data-reveal>
                <span class="mhr-kicker">The Wedding Gift</span>
                <h2 class="mhr-title">Tanda Kasih</h2>
                <p class="mhr-gift-lead">Doa restu Anda adalah kehormatan terbesar. Bila berkenan, kami sediakan kanal tanda kasih berikut.</p>
            </header>
            <div class="mhr-gift-grid">
                @foreach($wedding->gifts as $gift)
                    <article class="mhr-gift" data-reveal>
                        <span class="mhr-gift-ornament" aria-hidden="true">&#10022;</span>
                        <span class="mhr-gift-bank">{{ $gift->bank_name }}</span>
                        <p class="mhr-gift-number">{{ $gift->account_number }}</p>
                        <p class="mhr-gift-name">a.n. {{ $gift->account_name }}</p>
                        <button type="button" class="mhr-btn" data-copy="{{ $gift->account_number }}">
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
    <section class="mhr-rsvp" id="konfirmasi">
        <div class="mhr-wrap">
            <header class="mhr-head" data-reveal>
                <span class="mhr-kicker">RSVP</span>
                <h2 class="mhr-title">Konfirmasi Kehadiran</h2>
                <p class="mhr-form-copy">Kehormatan bagi kami apabila Anda berkenan hadir. Mohon konfirmasi sebelum hari acara.</p>
            </header>
            <div class="mhr-form-panel" data-reveal>
                @include('wedding.templates.partials.rsvp')
            </div>
        </div>
    </section>
    @endif

    {{-- Ucapan --}}
    @if($wedding->sectionEnabled('ucapan'))
    <section class="mhr-wishes" id="ucapan">
        <div class="mhr-wrap">
            <header class="mhr-head" data-reveal>
                <span class="mhr-kicker">Wishes &amp; Prayers</span>
                <h2 class="mhr-title">Ucapan &amp; Doa</h2>
                <p class="mhr-form-copy">Sampaikan doa dan harapan terbaik Anda untuk kami.</p>
            </header>
            <div class="mhr-form-panel" data-reveal>
                @include('wedding.templates.partials.wishes')
            </div>
        </div>
    </section>
    @endif

    {{-- Penutup --}}
    @if($wedding->sectionEnabled('penutup'))
    <footer class="mhr-closing" id="penutup">
        <div class="mhr-wrap">
            <div class="mhr-closing-crest" data-reveal aria-hidden="true">
                <svg viewBox="0 0 120 120"><g fill="none" stroke="currentColor" stroke-width=".8"><circle cx="60" cy="60" r="52"/><circle cx="60" cy="60" r="44"/><path d="M60 8v16M60 96v16M8 60h16M96 60h16"/><path d="M60 44l6 10 10 6-10 6-6 10-6-10-10-6 10-6 6-10z"/></g></svg>
            </div>
            <p class="mhr-closing-text" data-reveal>{!! nl2br(e($wedding->closingText())) !!}</p>
            <h2 class="mhr-closing-names" data-reveal>{{ $wedding->groomDisplayName() }} <em>&amp;</em> {{ $wedding->brideDisplayName() }}</h2>
            <p class="mhr-closing-date" data-reveal>{{ $displayDate }}</p>
            <div class="mhr-closing-actions" data-reveal>
                <button type="button" class="mhr-btn" data-share="{{ $shareUrl }}" data-share-text="{{ $shareText }}" data-share-title="{{ $wedding->coupleNames() }}">
                    <svg class="tpl-icon"><use href="#tw-i-share"/></svg>
                    Bagikan Undangan
                </button>
                @if($music)
                    <button type="button" class="mhr-btn" data-music-toggle aria-pressed="false">
                        <svg class="tpl-icon"><use href="#tw-i-music"/></svg>
                        Musik
                    </button>
                @endif
            </div>
            <p class="mhr-credit">Markas Punokawan Wedding</p>
        </div>
    </footer>
    @endif
</main>

{{-- Dock --}}
<nav class="tpl-dock mhr-dock" aria-label="Navigasi undangan">
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
<script src="{{ asset('wedding-assets/assets/templates/maharaja.js') }}" defer></script>
</body>
</html>
