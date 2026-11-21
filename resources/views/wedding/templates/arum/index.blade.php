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
    <meta name="description" content="Undangan pernikahan {{ $wedding->coupleNames() }}, {{ $displayDate }}. Sebuah catatan cinta yang kami tulis dengan sederhana dan hangat.">
    <meta name="theme-color" content="#f3e9d7">
    <meta property="og:title" content="{{ $wedding->coupleNames() }} — Undangan Pernikahan">
    <meta property="og:description" content="{{ $shareText }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $coverPhoto }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Karla:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/base.css') }}">
    <link rel="stylesheet" href="{{ asset('wedding-assets/assets/templates/arum.css') }}">
    <noscript><style>.tpl-cover{display:none!important}body.cover-open{overflow:auto!important}</style></noscript>
</head>
<body class="tpl tpl-arum cover-open{{ $previewMode ? ' is-preview' : '' }}">
@include('wedding.templates.partials.icons')

{{-- ============================ COVER ============================ --}}
<div class="tpl-cover arm-cover" id="tw-cover" aria-label="Sampul undangan">
    <div class="arm-paper-grain" aria-hidden="true"></div>
    <span class="arm-tape arm-tape-tl" aria-hidden="true"></span>
    <span class="arm-tape arm-tape-tr" aria-hidden="true"></span>

    <div class="tpl-cover-inner arm-cover-card">
        <div class="arm-cover-photo">
            <img src="{{ $coverPhoto }}" alt="Foto {{ $wedding->coupleNames() }}" fetchpriority="high">
            <span class="arm-photo-caption">{{ $wedding->cover_caption ?: 'our little forever' }}</span>
        </div>

        <p class="arm-hand">Kami menikah!</p>
        <h1 class="arm-cover-names">
            {{ $wedding->groomDisplayName() }}
            <span class="arm-cover-amp">&amp;</span>
            {{ $wedding->brideDisplayName() }}
        </h1>
        <p class="arm-cover-date">{{ $eventDate?->translatedFormat('d . m . Y') ?? 'Segera' }}</p>

        <div class="arm-guest tpl-guest">
            <span>Untuk Bapak/Ibu/Saudara/i</span>
            <strong>{{ $recipientName }}</strong>
        </div>

        <button class="arm-open" type="button" data-cover-open>
            <svg class="tpl-icon"><use href="#tw-i-mail"/></svg>
            Buka Undangan
        </button>
    </div>

    <p class="arm-cover-foot">dibuat dengan tangan, dikirim dengan cinta</p>
</div>

{{-- ============================ MAIN ============================ --}}
<main class="tpl-main arm-main">
    {{-- Pembukaan --}}
    @if($wedding->sectionEnabled('pembukaan'))
    <section class="arm-hero" id="pembukaan">
        <div class="arm-wrap">
            <div class="arm-hero-note" data-reveal>
                @if($isIslami)
                    <p class="arm-arabic" lang="ar" dir="rtl">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p>
                @endif
                <p class="arm-hand arm-hero-hand">Halo, kami mengundang Anda</p>
                <p class="arm-hero-text">{!! nl2br(e($wedding->openingText())) !!}</p>
                <div class="arm-hero-sign">
                    <span>Dengan hangat,</span>
                    <strong>{{ $wedding->groomDisplayName() }} &amp; {{ $wedding->brideDisplayName() }}</strong>
                </div>
                <span class="arm-paperclip" aria-hidden="true"></span>
            </div>

            <div class="arm-hero-strip" data-reveal>
                <img src="{{ $coverPhoto }}" alt="Potret {{ $wedding->coupleNames() }}" loading="lazy">
                <span class="arm-strip-tape" aria-hidden="true"></span>
                <p>{{ $displayDate }} · {{ $mainEvent?->venue_name ?: 'Lokasi menyusul' }}</p>
            </div>

            <a class="arm-scroll" href="#mempelai">
                <svg class="tpl-icon"><use href="#tw-i-arrow-down"/></svg>
                <span>Kenali kami lebih dekat</span>
            </a>
        </div>
    </section>
    @endif

    {{-- Mempelai --}}
    @if($wedding->sectionEnabled('mempelai'))
    <section class="arm-section arm-couple" id="mempelai">
        <div class="arm-wrap">
            <header class="arm-head" data-reveal>
                <span class="arm-label">Halaman dua</span>
                <h2 class="arm-title">Mempelai</h2>
                <p class="arm-hand">dua orang, satu cerita</p>
            </header>

            <div class="arm-couple-board">
                <article class="arm-polaroid arm-polaroid-groom" data-reveal="left">
                    <span class="arm-tape" aria-hidden="true"></span>
                    <div class="arm-polaroid-photo">
                        @if($groomPhoto)
                            <img src="{{ $groomPhoto }}" alt="Foto {{ $wedding->groom_name }}" loading="lazy">
                        @else
                            <span class="arm-initials" aria-hidden="true">{{ $wedding->groomInitials() }}</span>
                        @endif
                    </div>
                    <p class="arm-polaroid-name">{{ $wedding->groomDisplayName() }}</p>
                    <p class="arm-polaroid-full">{{ $wedding->groom_name }}</p>
                    <p class="arm-polaroid-parents">{{ $wedding->groomParents() }}</p>
                    @if($groomIg)
                        <a class="arm-social" href="{{ $groomIg }}" target="_blank" rel="noopener">
                            <svg class="tpl-icon"><use href="#tw-i-instagram"/></svg>{{ $wedding->groom_instagram }}
                        </a>
                    @endif
                </article>

                <div class="arm-heart" data-reveal="scale" aria-hidden="true">
                    <svg class="tpl-icon"><use href="#tw-i-heart"/></svg>
                    <span>sejak dulu</span>
                </div>

                <article class="arm-polaroid arm-polaroid-bride" data-reveal="right">
                    <span class="arm-tape" aria-hidden="true"></span>
                    <div class="arm-polaroid-photo">
                        @if($bridePhoto)
                            <img src="{{ $bridePhoto }}" alt="Foto {{ $wedding->bride_name }}" loading="lazy">
                        @else
                            <span class="arm-initials" aria-hidden="true">{{ $wedding->brideInitials() }}</span>
                        @endif
                    </div>
                    <p class="arm-polaroid-name">{{ $wedding->brideDisplayName() }}</p>
                    <p class="arm-polaroid-full">{{ $wedding->bride_name }}</p>
                    <p class="arm-polaroid-parents">{{ $wedding->brideParents() }}</p>
                    @if($brideIg)
                        <a class="arm-social" href="{{ $brideIg }}" target="_blank" rel="noopener">
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
    <section class="arm-quote-section" id="kutipan">
        <div class="arm-wrap">
            <figure class="arm-quote-note" data-reveal>
                <span class="arm-tape arm-tape-quote" aria-hidden="true"></span>
                <span class="arm-quote-mark" aria-hidden="true">&ldquo;</span>
                <blockquote>{!! nl2br(e($wedding->quoteText())) !!}</blockquote>
                <figcaption>— {{ $wedding->quoteSource() }}</figcaption>
            </figure>
        </div>
    </section>
    @endif

    {{-- Hitung mundur --}}
    @if($wedding->sectionEnabled('countdown') && $countdownAt)
    <section class="arm-section arm-countdown-section" id="countdown">
        <div class="arm-wrap">
            <header class="arm-head" data-reveal>
                <span class="arm-label">Hitung hari</span>
                <h2 class="arm-title">Menuju Hari Bahagia</h2>
                <p class="arm-hand">{{ $displayDate }}</p>
            </header>
            <div class="arm-countdown" data-countdown="{{ $countdownAt }}" data-reveal>
                <div class="arm-count"><strong data-days>00</strong><span>hari</span></div>
                <div class="arm-count"><strong data-hours>00</strong><span>jam</span></div>
                <div class="arm-count"><strong data-minutes>00</strong><span>menit</span></div>
                <div class="arm-count"><strong data-seconds>00</strong><span>detik</span></div>
            </div>
        </div>
    </section>
    @endif

    {{-- Acara --}}
    @if($wedding->sectionEnabled('acara'))
    <section class="arm-section arm-events" id="acara">
        <div class="arm-wrap">
            <header class="arm-head" data-reveal>
                <span class="arm-label">Simpan tanggalnya</span>
                <h2 class="arm-title">Rangkaian Acara</h2>
                <p class="arm-hand">kami tunggu kedatangan Anda</p>
            </header>

            <div class="arm-postcards">
                @forelse($events as $event)
                    <article class="arm-postcard" data-reveal>
                        <div class="arm-postcard-stamp" aria-hidden="true">
                            <svg class="tpl-icon"><use href="#tw-i-calendar"/></svg>
                        </div>
                        <div class="arm-postcard-body">
                            <h3>{{ $event->name }}</h3>
                            <p class="arm-postcard-date">{{ $event->event_date?->translatedFormat('l, d F Y') ?? $displayDate }}</p>
                            <p class="arm-postcard-time">{{ $event->timeRange() ?: 'Waktu menyusul' }}</p>
                            <span class="arm-dash" aria-hidden="true"></span>
                            <p class="arm-postcard-venue">{{ $event->venue_name }}</p>
                            <p class="arm-postcard-address">{{ $event->address }}</p>
                            @if($event->maps_url)
                                <a class="arm-link" href="{{ $event->maps_url }}" target="_blank" rel="noopener">
                                    <svg class="tpl-icon"><use href="#tw-i-map"/></svg>
                                    Petunjuk lokasi
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="tw-note">Rangkaian acara akan segera diumumkan.</p>
                @endforelse
            </div>

            @if($wedding->live_stream_url)
                <div class="arm-live" data-reveal>
                    <p class="arm-hand">tidak bisa hadir? saksikan dari rumah</p>
                    <a class="arm-link arm-link-solid" href="{{ $wedding->live_stream_url }}" target="_blank" rel="noopener">
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
    <section class="arm-section arm-story" id="kisah">
        <div class="arm-wrap">
            <header class="arm-head" data-reveal>
                <span class="arm-label">Catatan harian</span>
                <h2 class="arm-title">Cerita Kami</h2>
                <p class="arm-hand">dari pertemuan sampai janji</p>
            </header>

            <div class="arm-journal">
                @foreach($wedding->stories->sortBy('sort_order') as $story)
                    <article class="arm-journal-entry" data-reveal>
                        <span class="arm-journal-year">{{ $story->story_date }}</span>
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
    <section class="arm-section arm-gallery-section" id="galeri">
        <div class="arm-wrap">
            <header class="arm-head" data-reveal>
                <span class="arm-label">Album kecil</span>
                <h2 class="arm-title">Momen Kami</h2>
                <p class="arm-hand">potret-potret yang kami sayang</p>
            </header>
            <div class="arm-gallery">
                @foreach($photos as $index => $photo)
                    <button type="button" class="arm-snap" data-lightbox="{{ $photo }}" data-reveal="scale" aria-label="Perbesar foto {{ $index + 1 }}">
                        <img src="{{ $photo }}" alt="Momen {{ $wedding->coupleNames() }} {{ $index + 1 }}" loading="lazy">
                    </button>
                @endforeach
            </div>
            @if($wedding->hashtag)
                <p class="arm-hashtag" data-reveal>{{ $wedding->hashtag }}</p>
            @endif
        </div>
    </section>
    @endif

    {{-- Tanda kasih --}}
    @if($wedding->sectionEnabled('tanda_kasih') && $wedding->gifts->isNotEmpty())
    <section class="arm-section arm-gifts" id="tanda-kasih">
        <div class="arm-wrap">
            <header class="arm-head" data-reveal>
                <span class="arm-label">Tanda kasih</span>
                <h2 class="arm-title">Hadiah &amp; Doa</h2>
                <p class="arm-hand">doa Anda sudah lebih dari cukup</p>
            </header>
            <div class="arm-gift-grid">
                @foreach($wedding->gifts as $gift)
                    <article class="arm-gift-tag" data-reveal>
                        <span class="arm-gift-hole" aria-hidden="true"></span>
                        <span class="arm-gift-bank">{{ $gift->bank_name }}</span>
                        <p class="arm-gift-number">{{ $gift->account_number }}</p>
                        <p class="arm-gift-name">a.n. {{ $gift->account_name }}</p>
                        <button type="button" class="arm-link" data-copy="{{ $gift->account_number }}">
                            <svg class="tpl-icon"><use href="#tw-i-copy"/></svg>
                            <span data-copy-label>Salin nomor</span>
                        </button>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- RSVP --}}
    @if($wedding->sectionEnabled('rsvp'))
    <section class="arm-section arm-rsvp" id="konfirmasi">
        <div class="arm-wrap arm-narrow">
            <header class="arm-head" data-reveal>
                <span class="arm-label">RSVP</span>
                <h2 class="arm-title">Konfirmasi Kehadiran</h2>
                <p class="arm-hand">kabari kami ya, supaya kursinya cukup</p>
            </header>
            <div class="arm-form-card" data-reveal>
                <span class="arm-tape arm-tape-form" aria-hidden="true"></span>
                @include('wedding.templates.partials.rsvp')
            </div>
        </div>
    </section>
    @endif

    {{-- Ucapan --}}
    @if($wedding->sectionEnabled('ucapan'))
    <section class="arm-section arm-wishes" id="ucapan">
        <div class="arm-wrap arm-narrow">
            <header class="arm-head" data-reveal>
                <span class="arm-label">Ucapan</span>
                <h2 class="arm-title">Tuliskan Doa</h2>
                <p class="arm-hand">satu kalimat baik berarti banyak</p>
            </header>
            <div class="arm-form-card" data-reveal>
                @include('wedding.templates.partials.wishes')
            </div>
        </div>
    </section>
    @endif

    {{-- Penutup --}}
    @if($wedding->sectionEnabled('penutup'))
    <footer class="arm-closing" id="penutup">
        <div class="arm-wrap">
            <div class="arm-closing-card" data-reveal>
                <span class="arm-tape arm-tape-closing" aria-hidden="true"></span>
                <p class="arm-hand arm-closing-hand">sampai jumpa di hari kami</p>
                <p class="arm-closing-text">{!! nl2br(e($wedding->closingText())) !!}</p>
                <h2 class="arm-closing-names">{{ $wedding->groomDisplayName() }} <span>&amp;</span> {{ $wedding->brideDisplayName() }}</h2>
                <p class="arm-closing-date">{{ $displayDate }}</p>
                <div class="arm-closing-actions">
                    <button type="button" class="arm-link arm-link-solid" data-share="{{ $shareUrl }}" data-share-text="{{ $shareText }}" data-share-title="{{ $wedding->coupleNames() }}">
                        <svg class="tpl-icon"><use href="#tw-i-share"/></svg>
                        Bagikan Undangan
                    </button>
                    @if($music)
                        <button type="button" class="arm-link" data-music-toggle aria-pressed="false">
                            <svg class="tpl-icon"><use href="#tw-i-music"/></svg>
                            Musik
                        </button>
                    @endif
                </div>
                <p class="arm-credit">Markas Punokawan Wedding · dibuat dengan tangan</p>
            </div>
        </div>
    </footer>
    @endif
</main>

{{-- Dock --}}
<nav class="tpl-dock arm-dock" aria-label="Navigasi undangan">
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
<script src="{{ asset('wedding-assets/assets/templates/arum.js') }}" defer></script>
</body>
</html>
