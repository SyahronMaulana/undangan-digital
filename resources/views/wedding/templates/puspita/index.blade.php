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
    <meta name="description" content="Undangan pernikahan {{ $wedding->coupleNames() }}, {{ $displayDate }}. Sebuah perayaan penuh adat dan doa.">
    <meta name="theme-color" content="#123c2e">
    <meta property="og:title" content="{{ $wedding->coupleNames() }} — Undangan Pernikahan">
    <meta property="og:description" content="{{ $shareText }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $coverPhoto }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&family=Lora:ital,wght@0,400;0,500;0,600;1,400&family=Noto+Serif+Javanese:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/base.css') }}">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/puspita.css') }}">
    <noscript><style>.tpl-cover{display:none!important}body.cover-open{overflow:auto!important}</style></noscript>
</head>
<body class="tpl tpl-puspita cover-open{{ $previewMode ? ' is-preview' : '' }}">
@include('wedding.templates.partials.icons')

{{-- ============================ COVER ============================ --}}
<div class="tpl-cover pus-cover" id="tw-cover" aria-label="Sampul undangan">
    <div class="pus-batik pus-batik-cover" aria-hidden="true"></div>
    <div class="pus-gunungan" aria-hidden="true">
        <svg viewBox="0 0 200 150" preserveAspectRatio="xMidYMax meet">
            <path d="M100 4C84 34 60 52 40 70c-16 14-26 34-30 60h180c-4-26-14-46-30-60-20-18-44-36-60-66z" fill="none" stroke="currentColor" stroke-width="1"/>
            <path d="M100 22c-12 24-31 39-47 54C40 88 31 104 27 122h146c-4-18-13-34-26-46-16-15-35-30-47-54z" fill="none" stroke="currentColor" stroke-width=".7" opacity=".6"/>
            <path d="M100 46v66M74 112h52M86 96c8-7 20-7 28 0" fill="none" stroke="currentColor" stroke-width=".8"/>
            <circle cx="100" cy="78" r="9" fill="none" stroke="currentColor" stroke-width=".8"/>
        </svg>
    </div>

    <div class="tpl-cover-inner pus-cover-inner">
        <p class="pus-javanese" lang="jv">ꦥꦸꦱ꧀ꦥꦶꦠ</p>
        <p class="pus-eyebrow">The Wedding Of</p>
        <h1 class="pus-cover-names">
            <span>{{ $wedding->groomDisplayName() }}</span>
            <em>&amp;</em>
            <span>{{ $wedding->brideDisplayName() }}</span>
        </h1>
        <div class="pus-cover-divider" aria-hidden="true">
            <span></span><svg viewBox="0 0 24 24"><path d="M12 2l3 7 7 3-7 3-3 7-3-7-7-3 7-3 3-7z" fill="none" stroke="currentColor" stroke-width="1"/></svg><span></span>
        </div>
        <p class="pus-cover-date">{{ $eventDate?->translatedFormat('l, d F Y') ?? 'Segera' }}</p>
        <p class="pus-cover-venue">{{ $mainEvent?->venue_name ?: 'Lokasi akan diumumkan' }}</p>

        <div class="pus-guest tpl-guest">
            <span>Katur dhumateng Bapak/Ibu/Saudara/i</span>
            <strong>{{ $recipientName }}</strong>
        </div>

        <button class="pus-open" type="button" data-cover-open>
            <svg class="tpl-icon"><use href="#tw-i-mail"/></svg>
            Buka Undangan
        </button>
    </div>

    <p class="pus-cover-foot">Ngunduh Mantu · {{ $eventDate?->translatedFormat('Y') ?? '' }}</p>
</div>

{{-- ============================ MAIN ============================ --}}
<main class="tpl-main pus-main">
    {{-- Pembukaan --}}
    @if($wedding->sectionEnabled('pembukaan'))
    <section class="pus-hero" id="pembukaan">
        <div class="pus-batik pus-batik-hero" aria-hidden="true"></div>
        <div class="pus-wrap pus-hero-inner">
            @if($isIslami)
                <p class="pus-arabic" lang="ar" dir="rtl">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
            @endif
            <p class="pus-kicker" data-reveal>Sugeng Rawuh</p>
            <h2 class="pus-hero-names" data-reveal>
                <span>{{ $wedding->groomDisplayName() }}</span>
                <em>lan</em>
                <span>{{ $wedding->brideDisplayName() }}</span>
            </h2>
            <div class="pus-hero-frame" data-reveal>
                <p>{!! nl2br(e($wedding->openingText())) !!}</p>
            </div>
            <p class="pus-hero-date" data-reveal>{{ $displayDate }}</p>
            <a class="pus-scroll" href="#mempelai" aria-label="Gulir ke mempelai">
                <span>Mempelai</span>
                <svg class="tpl-icon"><use href="#tw-i-arrow-down"/></svg>
            </a>
        </div>
    </section>
    @endif

    {{-- Mempelai --}}
    @if($wedding->sectionEnabled('mempelai'))
    <section class="pus-section pus-couple" id="mempelai">
        <div class="pus-wrap">
            <header class="pus-head" data-reveal>
                <span class="pus-kicker">Bride &amp; Groom</span>
                <h2 class="pus-title">Mempelai</h2>
                <p class="pus-sub">Pinanganten Kakung lan Putri</p>
            </header>

            <div class="pus-couple-grid">
                <article class="pus-person" data-reveal="left">
                    <div class="pus-person-frame">
                        <span class="pus-corner pus-corner-tl" aria-hidden="true"></span>
                        <span class="pus-corner pus-corner-tr" aria-hidden="true"></span>
                        <span class="pus-corner pus-corner-bl" aria-hidden="true"></span>
                        <span class="pus-corner pus-corner-br" aria-hidden="true"></span>
                        @if($groomPhoto)
                            <img src="{{ $groomPhoto }}" alt="Foto {{ $wedding->groom_name }}" loading="lazy">
                        @else
                            <span class="pus-initials" aria-hidden="true">{{ $wedding->groomInitials() }}</span>
                        @endif
                    </div>
                    <p class="pus-role">Putra</p>
                    <h3>{{ $wedding->groom_name }}</h3>
                    <p class="pus-parents">{{ $wedding->groomParents() }}</p>
                    @if($groomIg)
                        <a class="pus-social" href="{{ $groomIg }}" target="_blank" rel="noopener">
                            <svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->groom_instagram }}
                        </a>
                    @endif
                </article>

                <div class="pus-amp" data-reveal="scale" aria-hidden="true">
                    <span class="pus-amp-line"></span>
                    <em>&amp;</em>
                    <span class="pus-amp-line"></span>
                </div>

                <article class="pus-person" data-reveal="right">
                    <div class="pus-person-frame">
                        <span class="pus-corner pus-corner-tl" aria-hidden="true"></span>
                        <span class="pus-corner pus-corner-tr" aria-hidden="true"></span>
                        <span class="pus-corner pus-corner-bl" aria-hidden="true"></span>
                        <span class="pus-corner pus-corner-br" aria-hidden="true"></span>
                        @if($bridePhoto)
                            <img src="{{ $bridePhoto }}" alt="Foto {{ $wedding->bride_name }}" loading="lazy">
                        @else
                            <span class="pus-initials" aria-hidden="true">{{ $wedding->brideInitials() }}</span>
                        @endif
                    </div>
                    <p class="pus-role">Putri</p>
                    <h3>{{ $wedding->bride_name }}</h3>
                    <p class="pus-parents">{{ $wedding->brideParents() }}</p>
                    @if($brideIg)
                        <a class="pus-social" href="{{ $brideIg }}" target="_blank" rel="noopener">
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
    <section class="pus-quote" id="kutipan">
        <div class="pus-wrap">
            <div class="pus-quote-panel" data-reveal>
                <span class="pus-quote-ornament" aria-hidden="true">&#10022;</span>
                <blockquote>{!! nl2br(e($wedding->quoteText())) !!}</blockquote>
                <cite>{{ $wedding->quoteSource() }}</cite>
            </div>
        </div>
    </section>
    @endif

    {{-- Hitung mundur --}}
    @if($wedding->sectionEnabled('countdown') && $countdownAt)
    <section class="pus-section pus-countdown-section" id="countdown">
        <div class="pus-wrap">
            <header class="pus-head" data-reveal>
                <span class="pus-kicker">Wilujengan</span>
                <h2 class="pus-title">Menuju Hari Bahagia</h2>
                <p class="pus-sub">{{ $displayDate }}</p>
            </header>
            <div class="pus-countdown" data-countdown="{{ $countdownAt }}" data-reveal>
                <div class="pus-count"><strong data-days>00</strong><span>Dina</span></div>
                <div class="pus-count"><strong data-hours>00</strong><span>Jam</span></div>
                <div class="pus-count"><strong data-minutes>00</strong><span>Menit</span></div>
                <div class="pus-count"><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
        </div>
    </section>
    @endif

    {{-- Acara --}}
    @if($wedding->sectionEnabled('acara'))
    <section class="pus-section pus-events" id="acara">
        <div class="pus-wrap">
            <header class="pus-head" data-reveal>
                <span class="pus-kicker">Rangkaian Adat</span>
                <h2 class="pus-title">Acara Pernikahan</h2>
                <p class="pus-sub">Peparing rawuh lan donga pangestu</p>
            </header>

            <div class="pus-event-list">
                @forelse($events as $loopIndex => $event)
                    <article class="pus-event" data-reveal>
                        <div class="pus-event-marker" aria-hidden="true">
                            <span>{{ $loopIndex + 1 }}</span>
                        </div>
                        <div class="pus-event-card">
                            <p class="pus-event-kicker">{{ $loopIndex === 0 ? 'Upacara' : 'Resepsi' }}</p>
                            <h3>{{ $event->name }}</h3>
                            <p class="pus-event-date">{{ $event->event_date?->translatedFormat('l, d F Y') ?? $displayDate }}</p>
                            <p class="pus-event-time">{{ $event->timeRange() ?: 'Waktu menyusul' }}</p>
                            <span class="pus-event-line" aria-hidden="true"></span>
                            <p class="pus-event-venue">{{ $event->venue_name }}</p>
                            <p class="pus-event-address">{{ $event->address }}</p>
                            @if($event->maps_url)
                                <a class="pus-btn" href="{{ $event->maps_url }}" target="_blank" rel="noopener">
                                    <svg class="tpl-icon"><use href="#tw-i-map"/></svg>
                                    Buka Peta
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="tw-note">Rangkaian acara akan segera diumumkan.</p>
                @endforelse
            </div>

            @if($wedding->live_stream_url)
                <div class="pus-live" data-reveal>
                    <p>Bagi yang berhalangan hadir, saksikan momen kami secara langsung.</p>
                    <a class="pus-btn pus-btn-solid" href="{{ $wedding->live_stream_url }}" target="_blank" rel="noopener">
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
    <section class="pus-section pus-story" id="kisah">
        <div class="pus-wrap">
            <header class="pus-head" data-reveal>
                <span class="pus-kicker">Lelakon</span>
                <h2 class="pus-title">Kisah Kami</h2>
                <p class="pus-sub">Saka patemon nganti janji suci</p>
            </header>
            <div class="pus-story-grid">
                @foreach($wedding->stories->sortBy('sort_order') as $story)
                    <article class="pus-story-card" data-reveal>
                        <span class="pus-story-batik" aria-hidden="true"></span>
                        <p class="pus-story-date">{{ $story->story_date }}</p>
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
    <section class="pus-section pus-gallery-section" id="galeri">
        <div class="pus-wrap">
            <header class="pus-head" data-reveal>
                <span class="pus-kicker">Album</span>
                <h2 class="pus-title">Momen Kami</h2>
                <p class="pus-sub">Potret ingkang kita tresnani</p>
            </header>
            <div class="pus-gallery">
                @foreach($photos as $index => $photo)
                    <button type="button" class="pus-photo{{ $index % 5 === 0 ? ' is-feature' : '' }}" data-lightbox="{{ $photo }}" data-reveal="scale" aria-label="Perbesar foto {{ $index + 1 }}">
                        <img src="{{ $photo }}" alt="Momen {{ $wedding->coupleNames() }} {{ $index + 1 }}" loading="lazy">
                        <span class="pus-photo-frame" aria-hidden="true"></span>
                    </button>
                @endforeach
            </div>
            @if($wedding->hashtag)
                <p class="pus-hashtag" data-reveal>{{ $wedding->hashtag }}</p>
            @endif
        </div>
    </section>
    @endif

    {{-- Tanda kasih --}}
    @if($wedding->sectionEnabled('tanda_kasih') && $wedding->gifts->isNotEmpty())
    <section class="pus-section pus-gifts" id="tanda-kasih">
        <div class="pus-wrap">
            <header class="pus-head" data-reveal>
                <span class="pus-kicker">Tanda Kasih</span>
                <h2 class="pus-title">Kado &amp; Doa</h2>
                <p class="pus-sub">Donga pangestu panjenengan punika peparing ingkang paling larang</p>
            </header>
            <div class="pus-gift-grid">
                @foreach($wedding->gifts as $gift)
                    <article class="pus-gift" data-reveal>
                        <span class="pus-gift-flap" aria-hidden="true"></span>
                        <span class="pus-gift-bank">{{ $gift->bank_name }}</span>
                        <p class="pus-gift-number">{{ $gift->account_number }}</p>
                        <p class="pus-gift-name">a.n. {{ $gift->account_name }}</p>
                        <button type="button" class="pus-btn" data-copy="{{ $gift->account_number }}">
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
    <section class="pus-section pus-rsvp" id="konfirmasi">
        <div class="pus-wrap pus-narrow">
            <header class="pus-head" data-reveal>
                <span class="pus-kicker">RSVP</span>
                <h2 class="pus-title">Konfirmasi Rawuh</h2>
                <p class="pus-sub">Keparenga ngabari kehadiran panjenengan</p>
            </header>
            <div class="pus-form-panel" data-reveal>
                @include('wedding.templates.partials.rsvp')
            </div>
        </div>
    </section>
    @endif

    {{-- Ucapan --}}
    @if($wedding->sectionEnabled('ucapan'))
    <section class="pus-section pus-wishes" id="ucapan">
        <div class="pus-wrap pus-narrow">
            <header class="pus-head" data-reveal>
                <span class="pus-kicker">Donga</span>
                <h2 class="pus-title">Ucapan &amp; Doa</h2>
                <p class="pus-sub">Aturaken donga saha pangestu</p>
            </header>
            <div class="pus-form-panel" data-reveal>
                @include('wedding.templates.partials.wishes')
            </div>
        </div>
    </section>
    @endif

    {{-- Penutup --}}
    @if($wedding->sectionEnabled('penutup'))
    <footer class="pus-closing" id="penutup">
        <div class="pus-batik pus-batik-closing" aria-hidden="true"></div>
        <div class="pus-wrap pus-closing-inner">
            <span class="pus-closing-ornament" data-reveal aria-hidden="true">&#10022;</span>
            <p class="pus-closing-kicker" data-reveal>Matur Nuwun</p>
            <p class="pus-closing-text" data-reveal>{!! nl2br(e($wedding->closingText())) !!}</p>
            <h2 class="pus-closing-names" data-reveal>{{ $wedding->groomDisplayName() }} <em>&amp;</em> {{ $wedding->brideDisplayName() }}</h2>
            <p class="pus-closing-date" data-reveal>{{ $displayDate }}</p>
            <div class="pus-closing-actions" data-reveal>
                <button type="button" class="pus-btn pus-btn-light" data-share="{{ $shareUrl }}" data-share-text="{{ $shareText }}" data-share-title="{{ $wedding->coupleNames() }}">
                    <svg class="tpl-icon"><use href="#tw-i-share"/></svg>
                    Bagikan Undangan
                </button>
                @if($music)
                    <button type="button" class="pus-btn pus-btn-light" data-music-toggle aria-pressed="false">
                        <svg class="tpl-icon"><use href="#tw-i-music"/></svg>
                        Musik
                    </button>
                @endif
            </div>
            <p class="pus-credit" data-reveal>Markas Punokawan Wedding</p>
        </div>
    </footer>
    @endif
</main>

{{-- Dock --}}
<nav class="tpl-dock pus-dock" aria-label="Navigasi undangan">
    <a href="#pembukaan" data-dock-link aria-label="Pembukaan"><svg class="tpl-icon"><use href="#tw-i-home"/></svg><span class="dock-tip">Awal</span></a>
    <a href="#mempelai" data-dock-link aria-label="Mempelai"><svg class="tpl-icon"><use href="#tw-i-couple"/></svg><span class="dock-tip">Mempelai</span></a>
    <a href="#acara" data-dock-link aria-label="Acara"><svg class="tpl-icon"><use href="#tw-i-calendar"/></svg><span class="dock-tip">Acara</span></a>
    <a href="#galeri" data-dock-link aria-label="Galeri"><svg class="tpl-icon"><use href="#tw-i-images"/></svg><span class="dock-tip">Galeri</span></a>
    <a href="#tanda-kasih" data-dock-link aria-label="Tanda kasih"><svg class="tpl-icon"><use href="#tw-i-gift"/></svg><span class="dock-tip">Kado</span></a>
    <a href="#konfirmasi" data-dock-link aria-label="Konfirmasi"><svg class="tpl-icon"><use href="#tw-i-mail"/></svg><span class="dock-tip">RSVP</span></a>
    <a href="#ucapan" data-dock-link aria-label="Ucapan"><svg class="tpl-icon"><use href="#tw-i-heart"/></svg><span class="dock-tip">Doa</span></a>
</nav>

@if($music)
    <audio id="tw-music" src="{{ $music }}" loop preload="none"></audio>
@endif

<script src="{{ asset('wedding-assets/assets/js/invitation-core.js') }}" defer></script>
<script src="{{ asset('wedding-assets/assets/templates/puspita.js') }}" defer></script>
</body>
</html>
