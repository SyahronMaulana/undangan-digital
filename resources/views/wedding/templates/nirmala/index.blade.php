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
    <meta name="description" content="Undangan pernikahan {{ $wedding->coupleNames() }}, {{ $displayDate }}. Sebuah film pendek tentang dua manusia dan satu janji.">
    <meta name="theme-color" content="#0c1118">
    <meta property="og:title" content="{{ $wedding->coupleNames() }} — Undangan Pernikahan">
    <meta property="og:description" content="{{ $shareText }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $coverPhoto }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/base.css') }}">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/nirmala.css') }}">
    <noscript><style>.tpl-cover{display:none!important}body.cover-open{overflow:auto!important}</style></noscript>
</head>
<body class="tpl tpl-nirmala cover-open{{ $previewMode ? ' is-preview' : '' }}">
@include('wedding.templates.partials.icons')

{{-- ============================ COVER ============================ --}}
<div class="tpl-cover nir-cover" id="tw-cover" aria-label="Sampul undangan">
    <div class="tpl-cover-media nir-cover-media">
        <img src="{{ $coverPhoto }}" alt="Foto {{ $wedding->coupleNames() }}" fetchpriority="high">
    </div>
    <div class="nir-cover-grade"></div>
    <div class="nir-grain" aria-hidden="true"></div>
    <span class="nir-letterbox nir-letterbox-top" aria-hidden="true"></span>
    <span class="nir-letterbox nir-letterbox-bottom" aria-hidden="true"></span>

    <div class="tpl-cover-inner nir-cover-inner">
        <p class="nir-credit-line"><span>A Wedding Film</span><span>MMXXVI</span></p>
        <h1 class="nir-cover-title">
            <span class="nir-cover-names">{{ $wedding->groomDisplayName() }} <em>&amp;</em> {{ $wedding->brideDisplayName() }}</span>
            <span class="nir-cover-sub">The Beginning of Forever</span>
        </h1>
        <p class="nir-cover-date">{{ $eventDate?->translatedFormat('d . m . Y') ?? 'Segera' }}</p>

        <div class="nir-guest tpl-guest">
            <span>Presented to</span>
            <strong>{{ $recipientName }}</strong>
        </div>

        <button class="nir-open" type="button" data-cover-open>
            <span>Mulai</span>
            <svg class="tpl-icon"><use href="#tw-i-play"/></svg>
        </button>
    </div>

    <p class="nir-cover-foot"><span>Sebuah persembahan</span><span>Markas Punokawan</span></p>
</div>

{{-- ============================ MAIN ============================ --}}
<main class="tpl-main nir-main">
    {{-- Pembukaan --}}
    @if($wedding->sectionEnabled('pembukaan'))
    <section class="nir-hero" id="pembukaan">
        <div class="nir-grain" aria-hidden="true"></div>
        <div class="nir-wrap nir-hero-inner">
            <p class="nir-chapter" data-reveal><span>Chapter</span><strong>01</strong><span>Pembukaan</span></p>
            @if($isIslami)
                <p class="nir-arabic" lang="ar" dir="rtl">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
            @endif
            <p class="nir-kicker" data-reveal>{{ $isIslami ? 'Assalamu’alaikum Warahmatullahi Wabarakatuh' : 'Dengan penuh sukacita' }}</p>
            <h2 class="nir-hero-names" data-reveal>
                <span>{{ $wedding->groomDisplayName() }}</span>
                <em>&amp;</em>
                <span>{{ $wedding->brideDisplayName() }}</span>
            </h2>
            <p class="nir-hero-text" data-reveal>{!! nl2br(e($wedding->openingText())) !!}</p>
            <p class="nir-hero-date" data-reveal>{{ $displayDate }}</p>
            <a class="nir-scroll" href="#mempelai">
                <span>Scroll</span>
                <svg class="tpl-icon"><use href="#tw-i-arrow-down"/></svg>
            </a>
        </div>
    </section>
    @endif

    {{-- Mempelai --}}
    @if($wedding->sectionEnabled('mempelai'))
    <section class="nir-couple" id="mempelai">
        <div class="nir-wrap nir-head" data-reveal>
            <p class="nir-chapter"><span>Chapter</span><strong>02</strong><span>Cast</span></p>
            <h2 class="nir-title">Dua Pemeran Utama</h2>
        </div>

        <div class="nir-cast">
            <article class="nir-cast-panel" data-reveal="left">
                @if($groomPhoto)
                    <img src="{{ $groomPhoto }}" alt="Foto {{ $wedding->groom_name }}" loading="lazy">
                @else
                    <span class="nir-initials" aria-hidden="true">{{ $wedding->groomInitials() }}</span>
                @endif
                <div class="nir-cast-shade"></div>
                <div class="nir-cast-info">
                    <p class="nir-cast-role">The Groom</p>
                    <h3>{{ $wedding->groom_name }}</h3>
                    <p class="nir-cast-parents">{{ $wedding->groomParents() }}</p>
                    @if($groomIg)
                        <a class="nir-social" href="{{ $groomIg }}" target="_blank" rel="noopener">
                            <svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->groom_instagram }}
                        </a>
                    @endif
                </div>
            </article>

            <div class="nir-cast-sep" aria-hidden="true"><span>&amp;</span></div>

            <article class="nir-cast-panel" data-reveal="right">
                @if($bridePhoto)
                    <img src="{{ $bridePhoto }}" alt="Foto {{ $wedding->bride_name }}" loading="lazy">
                @else
                    <span class="nir-initials" aria-hidden="true">{{ $wedding->brideInitials() }}</span>
                @endif
                <div class="nir-cast-shade"></div>
                <div class="nir-cast-info">
                    <p class="nir-cast-role">The Bride</p>
                    <h3>{{ $wedding->bride_name }}</h3>
                    <p class="nir-cast-parents">{{ $wedding->brideParents() }}</p>
                    @if($brideIg)
                        <a class="nir-social" href="{{ $brideIg }}" target="_blank" rel="noopener">
                            <svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->bride_instagram }}
                        </a>
                    @endif
                </div>
            </article>
        </div>
    </section>
    @endif

    {{-- Kutipan --}}
    @if($wedding->sectionEnabled('kutipan') && $wedding->quoteText())
    <section class="nir-quote" id="kutipan">
        <div class="nir-wrap nir-quote-inner" data-reveal>
            <p class="nir-quote-label">Interlude</p>
            <blockquote>{!! nl2br(e($wedding->quoteText())) !!}</blockquote>
            <cite>{{ $wedding->quoteSource() }}</cite>
        </div>
    </section>
    @endif

    {{-- Hitung mundur --}}
    @if($wedding->sectionEnabled('countdown') && $countdownAt)
    <section class="nir-countdown-section" id="countdown">
        <div class="nir-grain" aria-hidden="true"></div>
        <div class="nir-wrap">
            <div class="nir-head" data-reveal>
                <p class="nir-chapter"><span>Chapter</span><strong>03</strong><span>Countdown</span></p>
                <h2 class="nir-title">Menuju Hari Bahagia</h2>
                <p class="nir-sub">{{ $displayDate }}</p>
            </div>
            <div class="nir-countdown" data-countdown="{{ $countdownAt }}" data-reveal>
                <div class="nir-count"><strong data-days>00</strong><span>Hari</span></div>
                <div class="nir-count"><strong data-hours>00</strong><span>Jam</span></div>
                <div class="nir-count"><strong data-minutes>00</strong><span>Menit</span></div>
                <div class="nir-count"><strong data-seconds>00</strong><span>Detik</span></div>
            </div>
        </div>
    </section>
    @endif

    {{-- Acara --}}
    @if($wedding->sectionEnabled('acara'))
    <section class="nir-events" id="acara">
        <div class="nir-wrap">
            <div class="nir-head" data-reveal>
                <p class="nir-chapter"><span>Chapter</span><strong>04</strong><span>Programme</span></p>
                <h2 class="nir-title">Jadwal Acara</h2>
            </div>

            <div class="nir-schedule">
                @forelse($events as $event)
                    <article class="nir-event" data-reveal>
                        <div class="nir-event-time">
                            <strong>{{ $event->start_time ? \App\Models\WeddingEvent::clock($event->start_time) : '--.--' }}</strong>
                            <span>{{ $event->end_time ? 'hingga '.\App\Models\WeddingEvent::clock($event->end_time).' WIB' : 's.d. selesai' }}</span>
                        </div>
                        <div class="nir-event-body">
                            <p class="nir-event-date">{{ $event->event_date?->translatedFormat('l, d F Y') ?? $displayDate }}</p>
                            <h3>{{ $event->name }}</h3>
                            <p class="nir-event-venue">{{ $event->venue_name }}</p>
                            <p class="nir-event-address">{{ $event->address }}</p>
                            @if($event->maps_url)
                                <a class="nir-link" href="{{ $event->maps_url }}" target="_blank" rel="noopener">
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
                <div class="nir-live" data-reveal>
                    <span class="nir-live-dot" aria-hidden="true"></span>
                    <p>Live streaming tersedia pada hari acara.</p>
                    <a class="nir-link" href="{{ $wedding->live_stream_url }}" target="_blank" rel="noopener">
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
    <section class="nir-story" id="kisah">
        <div class="nir-wrap">
            <div class="nir-head" data-reveal>
                <p class="nir-chapter"><span>Chapter</span><strong>05</strong><span>Backstory</span></p>
                <h2 class="nir-title">Kisah Kami</h2>
            </div>
            <div class="nir-chapters">
                @foreach($wedding->stories->sortBy('sort_order') as $loopIndex => $story)
                    <article class="nir-chapter-item" data-reveal>
                        <div class="nir-chapter-num">{{ str_pad($loopIndex + 1, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="nir-chapter-body">
                            <p class="nir-chapter-date">{{ $story->story_date }}</p>
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
    <section class="nir-gallery-section" id="galeri">
        <div class="nir-wrap nir-head" data-reveal>
            <p class="nir-chapter"><span>Chapter</span><strong>06</strong><span>Stills</span></p>
            <h2 class="nir-title">Potret</h2>
            <p class="nir-sub">Geser untuk melihat semua</p>
        </div>
        <div class="nir-filmstrip" data-drag-scroll>
            <div class="nir-film-track">
                @foreach($photos as $index => $photo)
                    <button type="button" class="nir-frame" data-lightbox="{{ $photo }}" aria-label="Perbesar foto {{ $index + 1 }}">
                        <span class="nir-frame-perf nir-frame-perf-top" aria-hidden="true"></span>
                        <img src="{{ $photo }}" alt="Momen {{ $wedding->coupleNames() }} {{ $index + 1 }}" loading="lazy">
                        <span class="nir-frame-perf nir-frame-perf-bottom" aria-hidden="true"></span>
                        <span class="nir-frame-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    </button>
                @endforeach
            </div>
        </div>
        @if($wedding->hashtag)
            <p class="nir-hashtag" data-reveal>{{ $wedding->hashtag }}</p>
        @endif
    </section>
    @endif

    {{-- Tanda kasih --}}
    @if($wedding->sectionEnabled('tanda_kasih') && $wedding->gifts->isNotEmpty())
    <section class="nir-gifts" id="tanda-kasih">
        <div class="nir-wrap">
            <div class="nir-head" data-reveal>
                <p class="nir-chapter"><span>Chapter</span><strong>07</strong><span>Gift</span></p>
                <h2 class="nir-title">Tanda Kasih</h2>
                <p class="nir-sub">Doa restu Anda adalah hadiah yang paling berarti.</p>
            </div>
            <div class="nir-gift-grid">
                @foreach($wedding->gifts as $gift)
                    <article class="nir-gift" data-reveal>
                        <span class="nir-gift-bank">{{ $gift->bank_name }}</span>
                        <p class="nir-gift-number">{{ $gift->account_number }}</p>
                        <p class="nir-gift-name">a.n. {{ $gift->account_name }}</p>
                        <button type="button" class="nir-link" data-copy="{{ $gift->account_number }}">
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
    <section class="nir-rsvp" id="konfirmasi">
        <div class="nir-wrap nir-narrow">
            <div class="nir-head" data-reveal>
                <p class="nir-chapter"><span>Chapter</span><strong>08</strong><span>RSVP</span></p>
                <h2 class="nir-title">Konfirmasi Kehadiran</h2>
                <p class="nir-sub">Mohon konfirmasi sebelum hari acara.</p>
            </div>
            <div class="nir-form-panel" data-reveal>
                @include('wedding.templates.partials.rsvp')
            </div>
        </div>
    </section>
    @endif

    {{-- Ucapan --}}
    @if($wedding->sectionEnabled('ucapan'))
    <section class="nir-wishes" id="ucapan">
        <div class="nir-wrap nir-narrow">
            <div class="nir-head" data-reveal>
                <p class="nir-chapter"><span>Chapter</span><strong>09</strong><span>Wishes</span></p>
                <h2 class="nir-title">Ucapan &amp; Doa</h2>
                <p class="nir-sub">Tinggalkan doa untuk perjalanan kami.</p>
            </div>
            <div class="nir-form-panel" data-reveal>
                @include('wedding.templates.partials.wishes')
            </div>
        </div>
    </section>
    @endif

    {{-- Penutup --}}
    @if($wedding->sectionEnabled('penutup'))
    <footer class="nir-closing" id="penutup">
        <div class="nir-grain" aria-hidden="true"></div>
        <div class="nir-wrap nir-closing-inner">
            <p class="nir-closing-label" data-reveal>End Credits</p>
            <p class="nir-closing-text" data-reveal>{!! nl2br(e($wedding->closingText())) !!}</p>
            <h2 class="nir-closing-names" data-reveal>{{ $wedding->groomDisplayName() }} <em>&amp;</em> {{ $wedding->brideDisplayName() }}</h2>
            <p class="nir-closing-date" data-reveal>{{ $displayDate }}</p>
            <div class="nir-closing-actions" data-reveal>
                <button type="button" class="nir-link nir-link-solid" data-share="{{ $shareUrl }}" data-share-text="{{ $shareText }}" data-share-title="{{ $wedding->coupleNames() }}">
                    <svg class="tpl-icon"><use href="#tw-i-share"/></svg>
                    Bagikan Undangan
                </button>
                @if($music)
                    <button type="button" class="nir-link" data-music-toggle aria-pressed="false">
                        <svg class="tpl-icon"><use href="#tw-i-music"/></svg>
                        Musik
                    </button>
                @endif
            </div>
            <p class="nir-credit" data-reveal>Directed by love · Markas Punokawan Wedding</p>
        </div>
    </footer>
    @endif
</main>

{{-- Dock --}}
<nav class="tpl-dock nir-dock" aria-label="Navigasi undangan">
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
<script src="{{ asset('wedding-assets/assets/templates/nirmala.js') }}" defer></script>
</body>
</html>
