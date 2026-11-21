@php
    $photos = $wedding->photoUrls();
    $coverPhoto = $wedding->coverUrl() ?: asset('wedding-assets/assets/images/demo/cover.jpg');
    // Tanpa foto → tampilkan inisial nama (bukan gambar placeholder generik).
    $groomPhoto = $wedding->groomPhotoUrl();
    $bridePhoto = $wedding->bridePhotoUrl();
    $bandPhoto = $photos[0] ?? $coverPhoto;
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
    <meta name="description" content="Undangan pernikahan {{ $wedding->coupleNames() }}, {{ $displayDate }}. Sebuah perayaan cinta yang tenang dan modern.">
    <meta name="theme-color" content="#eee4d5">
    <meta property="og:title" content="{{ $wedding->coupleNames() }} — Undangan Pernikahan">
    <meta property="og:description" content="{{ $shareText }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $coverPhoto }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400;0,6..96,500;0,6..96,600;1,6..96,400;1,6..96,500&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/base.css') }}">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/senja.css') }}">
    <noscript><style>.tpl-cover{display:none!important}body.cover-open{overflow:auto!important}</style></noscript>
</head>
<body class="tpl tpl-senja cover-open{{ $previewMode ? ' is-preview' : '' }}">
@include('wedding.templates.partials.icons')

{{-- ============================ COVER ============================ --}}
<div class="tpl-cover sen-cover" id="tw-cover" aria-label="Sampul undangan">
    <div class="tpl-cover-media sen-cover-media">
        <img src="{{ $coverPhoto }}" alt="Foto {{ $wedding->coupleNames() }}" fetchpriority="high">
    </div>
    <div class="sen-cover-shade"></div>

    <div class="sen-cover-top">
        <span>Markas Punokawan</span>
        <span>Invitation / {{ $eventDate?->format('Y') ?? '2027' }}</span>
    </div>

    <span class="sen-cover-side" aria-hidden="true">Save the date — {{ $displayDate }}</span>

    <div class="tpl-cover-inner sen-cover-body">
        <p class="sen-cover-kicker">The Wedding Of</p>
        <h1 class="sen-cover-names">
            <span>{{ $wedding->groomDisplayName() }}</span>
            <em>and</em>
            <span>{{ $wedding->brideDisplayName() }}</span>
        </h1>
        <p class="sen-cover-place">{{ $eventDate?->translatedFormat('d . m . Y') }} &nbsp;·&nbsp; {{ $mainEvent?->venue_name ?? 'Indonesia' }}</p>

        <div class="sen-cover-footer">
            <div class="tpl-guest sen-guest">
                <span>Kepada</span>
                <strong>{{ $recipientName }}</strong>
            </div>
            <button class="sen-open" type="button" data-cover-open>
                Buka Undangan
                <svg class="tpl-icon"><use href="#tw-i-arrow-right"/></svg>
            </button>
        </div>
    </div>

    <span class="sen-cover-index" aria-hidden="true">01</span>
</div>

{{-- ============================ MAIN ============================ --}}
<main class="tpl-main sen-main">
    <div class="sen-topbar">
        <span>{{ $wedding->groomDisplayName() }} <i>&amp;</i> {{ $wedding->brideDisplayName() }}</span>
        <span>{{ $eventDate?->format('d.m.Y') ?? '—' }}</span>
    </div>

    {{-- Pembukaan --}}
    @if($wedding->sectionEnabled('pembukaan'))
    <section class="sen-intro" id="pembukaan">
        <div class="sen-wrap">
            <div class="sen-intro-grid">
                <div class="sen-intro-aside" data-reveal>
                    <span class="sen-index">01</span>
                    <span class="sen-label">The Beginning</span>
                </div>
                <div class="sen-intro-main">
                    <p class="sen-lead" data-reveal>{!! nl2br(e($wedding->openingText())) !!}</p>
                    <div class="sen-intro-sign" data-reveal>
                        <span class="sen-sign-names">{{ $wedding->groomDisplayName() }}</span>
                        <span class="sen-sign-amp">&amp;</span>
                        <span class="sen-sign-names">{{ $wedding->brideDisplayName() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="sen-marquee" aria-hidden="true">
            <div class="sen-marquee-track">
                @for($i = 0; $i < 3; $i++)
                    <span>Save the date</span><i>&#10022;</i><span>{{ $displayDate }}</span><i>&#10022;</i>
                @endfor
            </div>
        </div>
    </section>
    @endif

    {{-- Mempelai --}}
    @if($wedding->sectionEnabled('mempelai'))
    <section class="sen-couple" id="mempelai">
        <div class="sen-wrap">
            <div class="sen-section-head" data-reveal>
                <span class="sen-index">02</span>
                <div>
                    <span class="sen-label">Bride &amp; Groom</span>
                    <h2 class="sen-title">Two hearts,<br><em>one story.</em></h2>
                </div>
            </div>

            <div class="sen-couple-grid">
                <article class="sen-person" data-reveal="left">
                    <figure class="sen-person-photo">
                        @if($groomPhoto)
                            <img src="{{ $groomPhoto }}" alt="Foto {{ $wedding->groom_name }}" loading="lazy">
                        @else
                            <span class="sen-initials" aria-hidden="true">{{ $wedding->groomInitials() }}</span>
                        @endif
                        <figcaption>The Groom</figcaption>
                    </figure>
                    <h3>{{ $wedding->groom_name }}</h3>
                    <p class="sen-person-parent">{{ $wedding->groomParents() }}</p>
                    @if($groomIg)
                        <a class="sen-link" href="{{ $groomIg }}" target="_blank" rel="noopener">
                            {{ $wedding->groom_instagram }}
                            <svg class="tpl-icon"><use href="#tw-i-arrow-right"/></svg>
                        </a>
                    @endif
                </article>

                <div class="sen-couple-gap" aria-hidden="true">
                    <span>&amp;</span>
                    <small>Est. {{ $eventDate?->format('Y') ?? '2027' }}</small>
                </div>

                <article class="sen-person sen-person-right" data-reveal="right">
                    <figure class="sen-person-photo">
                        @if($bridePhoto)
                            <img src="{{ $bridePhoto }}" alt="Foto {{ $wedding->bride_name }}" loading="lazy">
                        @else
                            <span class="sen-initials" aria-hidden="true">{{ $wedding->brideInitials() }}</span>
                        @endif
                        <figcaption>The Bride</figcaption>
                    </figure>
                    <h3>{{ $wedding->bride_name }}</h3>
                    <p class="sen-person-parent">{{ $wedding->brideParents() }}</p>
                    @if($brideIg)
                        <a class="sen-link" href="{{ $brideIg }}" target="_blank" rel="noopener">
                            {{ $wedding->bride_instagram }}
                            <svg class="tpl-icon"><use href="#tw-i-arrow-right"/></svg>
                        </a>
                    @endif
                </article>
            </div>
        </div>
    </section>
    @endif

    {{-- Photo band --}}
    <div class="sen-band" data-parallax>
        <img src="{{ $bandPhoto }}" alt="Potret {{ $wedding->coupleNames() }}" loading="lazy">
        <span>{{ $wedding->coupleNames() }} — {{ $eventDate?->format('Y') ?? '' }}</span>
    </div>

    {{-- Kutipan --}}
    @if($wedding->sectionEnabled('kutipan') && $wedding->quoteText())
    <section class="sen-quote" id="kutipan">
        <div class="sen-wrap">
            <div class="sen-quote-grid" data-reveal>
                <span class="sen-index">03</span>
                <blockquote>
                    <p>{!! nl2br(e($wedding->quoteText())) !!}</p>
                    <cite>{{ $wedding->quoteSource() }}</cite>
                </blockquote>
            </div>
        </div>
    </section>
    @endif

    {{-- Countdown --}}
    @if($wedding->sectionEnabled('countdown') && $countdownAt)
    <section class="sen-countdown-section" id="countdown">
        <div class="sen-wrap">
            <div class="sen-countdown-head" data-reveal>
                <span class="sen-label">Counting down to</span>
                <p>{{ $displayDate }}</p>
            </div>
            <div class="sen-countdown" data-countdown="{{ $countdownAt }}" data-reveal>
                <div class="sen-count"><strong data-days>00</strong><span>Days</span></div>
                <div class="sen-count"><strong data-hours>00</strong><span>Hours</span></div>
                <div class="sen-count"><strong data-minutes>00</strong><span>Minutes</span></div>
                <div class="sen-count"><strong data-seconds>00</strong><span>Seconds</span></div>
            </div>
        </div>
    </section>
    @endif

    {{-- Acara --}}
    @if($wedding->sectionEnabled('acara'))
    <section class="sen-events" id="acara">
        <div class="sen-wrap">
            <div class="sen-section-head" data-reveal>
                <span class="sen-index">04</span>
                <div>
                    <span class="sen-label">The Celebration</span>
                    <h2 class="sen-title">When &amp; where</h2>
                </div>
            </div>

            <div class="sen-event-list">
                @forelse($events as $loopIndex => $event)
                    <article class="sen-event" data-reveal>
                        <div class="sen-event-number">{{ str_pad($loopIndex + 1, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="sen-event-date">
                            <strong>{{ $event->event_date?->format('d') ?? '—' }}</strong>
                            <span>{{ $event->event_date?->translatedFormat('M Y') ?? '' }}</span>
                        </div>
                        <div class="sen-event-body">
                            <h3>{{ $event->name }}</h3>
                            <p class="sen-event-time">{{ $event->timeRange() ?: 'Waktu menyusul' }}</p>
                            <p class="sen-event-venue">{{ $event->venue_name }}</p>
                            <p class="sen-event-address">{{ $event->address }}</p>
                        </div>
                        @if($event->maps_url)
                            <a class="sen-map" href="{{ $event->maps_url }}" target="_blank" rel="noopener" aria-label="Lokasi {{ $event->name }}">
                                <svg class="tpl-icon"><use href="#tw-i-map"/></svg>
                                <span>Peta</span>
                            </a>
                        @endif
                    </article>
                @empty
                    <p class="tw-note">Rangkaian acara akan segera diumumkan.</p>
                @endforelse
            </div>

            @if($wedding->live_stream_url)
                <div class="sen-live" data-reveal>
                    <span class="sen-label">Live streaming</span>
                    <a class="sen-link" href="{{ $wedding->live_stream_url }}" target="_blank" rel="noopener">
                        Saksikan momen kami secara langsung
                        <svg class="tpl-icon"><use href="#tw-i-arrow-right"/></svg>
                    </a>
                </div>
            @endif
        </div>
    </section>
    @endif

    {{-- Kisah --}}
    @if($wedding->sectionEnabled('kisah') && $wedding->stories->isNotEmpty())
    <section class="sen-story" id="kisah">
        <div class="sen-wrap">
            <div class="sen-section-head" data-reveal>
                <span class="sen-index">05</span>
                <div>
                    <span class="sen-label">Our Story</span>
                    <h2 class="sen-title">How it began</h2>
                </div>
            </div>
            <div class="sen-story-list">
                @foreach($wedding->stories->sortBy('sort_order') as $story)
                    <article class="sen-story-item" data-reveal>
                        <span class="sen-story-year">{{ $story->story_date }}</span>
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
    <section class="sen-gallery" id="galeri">
        <div class="sen-wrap sen-gallery-head" data-reveal>
            <div>
                <span class="sen-index">06</span>
                <span class="sen-label">Gallery</span>
                <h2 class="sen-title">Fragments of us</h2>
            </div>
            <div class="sen-slider-nav" data-slider>
                <button type="button" data-slide="prev" aria-label="Foto sebelumnya"><svg class="tpl-icon"><use href="#tw-i-chevron-left"/></svg></button>
                <button type="button" data-slide="next" aria-label="Foto berikutnya"><svg class="tpl-icon"><use href="#tw-i-chevron-right"/></svg></button>
            </div>
        </div>
        <div class="sen-gallery-track" data-slide-track>
            @foreach($photos as $index => $photo)
                <button type="button" class="sen-photo{{ $index % 3 === 1 ? ' is-tall' : '' }}" data-lightbox="{{ $photo }}" aria-label="Perbesar foto {{ $index + 1 }}">
                    <img src="{{ $photo }}" alt="Momen {{ $wedding->coupleNames() }} {{ $index + 1 }}" loading="lazy">
                    <span>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                </button>
            @endforeach
        </div>
        @if($wedding->hashtag)
            <div class="sen-wrap"><p class="sen-hashtag">{{ $wedding->hashtag }}</p></div>
        @endif
    </section>
    @endif

    {{-- Tanda kasih --}}
    @if($wedding->sectionEnabled('tanda_kasih') && $wedding->gifts->isNotEmpty())
    <section class="sen-gifts" id="tanda-kasih">
        <div class="sen-wrap">
            <div class="sen-section-head" data-reveal>
                <span class="sen-index">07</span>
                <div>
                    <span class="sen-label">Wedding Gift</span>
                    <h2 class="sen-title">With gratitude</h2>
                </div>
            </div>
            <p class="sen-gift-lead" data-reveal>Kehadiran dan doa Anda adalah hadiah terbaik. Bila berkenan memberi tanda kasih, berikut kanal yang kami sediakan.</p>
            <div class="sen-gift-list">
                @foreach($wedding->gifts as $gift)
                    <article class="sen-gift" data-reveal>
                        <span class="sen-gift-bank">{{ $gift->bank_name }}</span>
                        <p class="sen-gift-number">{{ $gift->account_number }}</p>
                        <span class="sen-gift-name">{{ $gift->account_name }}</span>
                        <button type="button" class="sen-gift-copy" data-copy="{{ $gift->account_number }}" aria-label="Salin nomor rekening">
                            <svg class="tpl-icon"><use href="#tw-i-copy"/></svg>
                            <span data-copy-label>Salin</span>
                        </button>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- RSVP --}}
    @if($wedding->sectionEnabled('rsvp'))
    <section class="sen-rsvp" id="konfirmasi">
        <div class="sen-wrap sen-rsvp-grid">
            <div class="sen-rsvp-aside" data-reveal>
                <span class="sen-index">08</span>
                <span class="sen-label">RSVP</span>
                <h2 class="sen-title">Will you<br><em>join us?</em></h2>
                <p>Mohon konfirmasi kehadiran Anda agar kami dapat menyambut dengan sebaik-baiknya.</p>
            </div>
            <div class="sen-rsvp-form" data-reveal>
                @include('wedding.templates.partials.rsvp')
            </div>
        </div>
    </section>
    @endif

    {{-- Ucapan --}}
    @if($wedding->sectionEnabled('ucapan'))
    <section class="sen-wishes" id="ucapan">
        <div class="sen-wrap sen-wishes-grid">
            <div class="sen-wishes-aside" data-reveal>
                <span class="sen-index">09</span>
                <span class="sen-label">Wishes</span>
                <h2 class="sen-title">Leave a<br><em>kind word</em></h2>
                <p>Setiap doa yang Anda tuliskan akan menjadi kenangan yang kami simpan.</p>
            </div>
            <div class="sen-wishes-form" data-reveal>
                @include('wedding.templates.partials.wishes')
            </div>
        </div>
    </section>
    @endif

    {{-- Penutup --}}
    @if($wedding->sectionEnabled('penutup'))
    <footer class="sen-closing" id="penutup">
        <div class="sen-closing-top">
            <span class="sen-label">With love</span>
            <p>{!! nl2br(e($wedding->closingText())) !!}</p>
        </div>
        <h2 class="sen-closing-names" data-reveal>
            <span>{{ $wedding->groomDisplayName() }}</span>
            <em>&amp;</em>
            <span>{{ $wedding->brideDisplayName() }}</span>
        </h2>
        <div class="sen-closing-meta">
            <span>{{ $displayDate }}</span>
            <span>{{ $mainEvent?->venue_name ?? 'Indonesia' }}</span>
        </div>
        <div class="sen-closing-actions">
            <button type="button" class="sen-open sen-open-dark" data-share="{{ $shareUrl }}" data-share-text="{{ $shareText }}" data-share-title="{{ $wedding->coupleNames() }}">
                Bagikan Undangan
                <svg class="tpl-icon"><use href="#tw-i-share"/></svg>
            </button>
            @if($music)
                <button type="button" class="sen-open sen-open-ghost" data-music-toggle aria-pressed="false">
                    <svg class="tpl-icon"><use href="#tw-i-music"/></svg>
                    Musik
                </button>
            @endif
        </div>
        <p class="sen-credit">&copy; <span data-year>{{ date('Y') }}</span> Markas Punokawan Wedding</p>
    </footer>
    @endif
</main>

{{-- Dock --}}
<nav class="tpl-dock sen-dock" aria-label="Navigasi undangan">
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
<script src="{{ asset('wedding-assets/assets/templates/senja.js') }}" defer></script>
</body>
</html>
