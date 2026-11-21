<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</title><link rel="stylesheet" href="{{ asset('css/ronce.css') }}"></head>
<body class="invite {{ $design }}"><main>
<section class="invite-cover"><div class="frame"><p>UNDANGAN PERNIKAHAN</p><div class="cover-sprig">✦</div><h1>{{ $wedding->groom_name }} <i>&</i> {{ $wedding->bride_name }}</h1><small>{{ optional($wedding->event_date)->translatedFormat('d F Y') }}</small><div class="recipient">Kepada Yth.<br><b>{{ $guest?->name ?? 'Tamu Undangan' }}</b></div><a href="#pembukaan" class="open">Buka Undangan ↓</a></div></section>
<section id="pembukaan" class="intro-section"><p class="ornament">✦</p><p class="overline">ASSALAMU’ALAIKUM WARAHMATULLAHI WABARAKATUH</p><h2>Dengan segala kerendahan hati</h2><p>{{ $wedding->opening_text }}</p></section>
<section class="couple"><article><div class="portrait groom"></div><p>Putra dari {{ $wedding->groom_parents }}</p><h2>{{ $wedding->groom_name }}</h2></article><span class="amp">&</span><article><div class="portrait bride"></div><p>Putri dari {{ $wedding->bride_parents }}</p><h2>{{ $wedding->bride_name }}</h2></article></section>
<section class="quote"><p>“{{ $wedding->quote_text }}”</p></section><section class="countdown"><p class="overline">MENUJU HARI BAHAGIA</p><div><b>—</b><span>Hari</span><b>—</b><span>Jam</span><b>—</b><span>Menit</span></div></section>
<section class="events"><p class="overline">RANGKAIAN ACARA</p><h2>Mohon doa restu</h2>
@foreach($wedding->events as $event)
<article><h3>{{ $event->title }}</h3><p>{{ $event->starts_at->translatedFormat('l, d F Y') }}<br>{{ $event->starts_at->format('H.i') }} WIB</p><p>{{ $event->location }}<br>{{ $event->address }}</p>@if($event->maps_url)<a href="{{ $event->maps_url }}" target="_blank" rel="noopener">Petunjuk lokasi ↗</a>@endif</article>
@endforeach
</section>
<section class="our-story"><p class="overline">KISAH KAMI</p><h2>Berawal dari sebuah pertemuan</h2><div><article><b>01</b><h3>Awal Pertemuan</h3><p>Perjalanan kami dimulai dari pertemuan sederhana yang kemudian menjadi cerita yang begitu berarti.</p></article><article><b>02</b><h3>Menjalin Hubungan</h3><p>Dengan doa dan dukungan keluarga, kami belajar tumbuh dan melangkah bersama.</p></article><article><b>03</b><h3>Hari Bahagia</h3><p>Kini kami mengikat janji untuk saling menjaga dalam sebuah ikatan suci.</p></article></div></section>
<section class="gallery"><p class="overline">MOMEN BAHAGIA</p><div><span></span><span></span><span></span><span></span></div></section>
<section class="gift"><p class="overline">TANDA KASIH</p><h2>Kehadiran Anda adalah hadiah terbaik.</h2><p>Doa restu Anda sudah sangat berarti. Bagi yang ingin mengirimkan tanda kasih, informasi rekening akan ditampilkan di sini.</p><button type="button">Salin nomor rekening</button></section>
<section class="rsvp"><p class="overline">KONFIRMASI KEHADIRAN</p><h2>Merupakan kehormatan bagi kami bila Anda hadir.</h2>
@if(session('success'))<p class="notice">{{ session('success') }}</p>@endif
@if($wedding->exists)
<form method="post" action="{{ route('weddings.rsvp',$wedding) }}">@csrf<input name="token" type="hidden" value="{{ request('token') }}"><input name="name" placeholder="Nama Anda" value="{{ $guest?->name }}" required><select name="attendance"><option value="hadir">Akan hadir</option><option value="tidak_hadir">Tidak dapat hadir</option><option value="belum_pasti">Belum dapat memastikan</option></select><input name="guest_count" type="number" min="1" value="1"><textarea name="message" placeholder="Pesan tambahan (opsional)"></textarea><button>Kirim konfirmasi</button></form>
@else
<p class="form-demo">Form konfirmasi tersedia saat undangan dipublikasikan.</p>
@endif
</section><section class="closing"><p class="ornament">✦</p><h2>Terima kasih</h2><p>Atas doa, perhatian, dan kehadiran Bapak/Ibu/Saudara/i.</p><h3>{{ $wedding->groom_name }} <i>&</i> {{ $wedding->bride_name }}</h3></section>
</main><script>const d=new Date(@json(optional($wedding->event_date)->toIso8601String()));function tick(){let n=Math.max(0,d-Date.now()),x=document.querySelectorAll('.countdown b');[864e5,36e5,6e4].forEach((u,i)=>x[i].textContent=Math.floor(n/u)%(i?24:999));}tick();setInterval(tick,60000)</script></body></html>
