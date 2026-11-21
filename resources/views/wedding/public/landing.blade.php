@extends('wedding.layouts.site')

@section('title', 'Undangan Pernikahan Digital Premium')
@section('page', 'Markas Punokawan Wedding')
@section('description', 'Delapan desain undangan pernikahan digital yang benar-benar berbeda. Pilih desain, isi cerita Anda, dan bagikan dengan personal.')

@section('content')
<section class="site-wrap hero-wedding">
    <div class="hero-copy" data-reveal>
        <span class="hero-eyebrow">Markas Punokawan · Wedding</span>
        <h1 class="hero-title">Undangan yang terasa seperti <em>karya desainer.</em></h1>
        <p class="hero-lead">Bukan sekadar tautan dan teks. Delapan dunia visual dengan tipografi, ornamen, dan animasi yang dirancang satu per satu — untuk momen yang hanya terjadi sekali.</p>
        <div class="hero-actions">
            <a href="{{ route('wedding.register') }}" class="btn btn-gold">Buat Undangan <span aria-hidden="true">→</span></a>
            <a href="{{ route('wedding.templates.index') }}" class="btn btn-line">Lihat 8 Desain</a>
        </div>
        <div class="hero-trust">
            <div class="avatars" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
            <div><b>Dipercaya untuk hari bahagia</b><br>RSVP real-time · Tamu personal · WhatsApp</div>
        </div>
    </div>
    <div class="hero-stack" data-reveal data-reveal-delay="2" aria-label="Pratinjau desain undangan">
        <span class="hero-chip one"><i></i> Live preview asli</span>
        <span class="hero-chip two"><i></i> 8 template · 8 karakter</span>
        <div class="hero-mini one">
            <iframe src="{{ route('wedding.templates.preview', 'maharaja') }}" title="Pratinjau Maharaja" loading="lazy" tabindex="-1"></iframe>
        </div>
        <div class="hero-frame">
            <iframe src="{{ route('wedding.templates.preview', 'melati') }}" title="Pratinjau Melati" loading="eager" tabindex="-1"></iframe>
        </div>
        <div class="hero-mini two">
            <iframe src="{{ route('wedding.templates.preview', 'senja') }}" title="Pratinjau Senja" loading="lazy" tabindex="-1"></iframe>
        </div>
    </div>
</section>

<div class="trust-strip" aria-label="Keunggulan">
    <div class="site-wrap trust-inner">
        <span>Delapan desain orisinal</span>
        <span>Mobile-first</span>
        <span>RSVP & ucapan</span>
        <span>Link tamu personal</span>
        <span>Broadcast WhatsApp</span>
        <span>Data terisolasi</span>
    </div>
</div>

<section class="section" id="desain">
    <div class="site-wrap">
        <div class="section-head">
            <div>
                <span class="eyebrow">Koleksi Undangan</span>
                <h2 class="section-title">Delapan desain.<br><em>Delapan jiwa.</em></h2>
            </div>
            <p class="section-copy">Setiap template memiliki layout, sistem tipografi, ornamen, dan bahasa animasi sendiri. Tidak ada satu layout universal yang hanya diganti warna.</p>
        </div>
        <div class="template-grid">
            @foreach($templates as $template)
                @include('wedding.public.partials.template-card', ['template' => $template])
            @endforeach
        </div>
        <div class="section-foot">
            <a href="{{ route('wedding.templates.index') }}" class="btn btn-line">Jelajahi Semua Desain <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>

<section class="section" id="fitur">
    <div class="site-wrap showcase">
        <div class="showcase-visual" data-reveal>
            <div class="showcase-frame one"><iframe src="{{ route('wedding.templates.preview', 'lestari') }}" title="Pratinjau Lestari" loading="lazy" tabindex="-1"></iframe></div>
            <div class="showcase-frame two"><iframe src="{{ route('wedding.templates.preview', 'asmara') }}" title="Pratinjau Asmara" loading="lazy" tabindex="-1"></iframe></div>
            <div class="showcase-frame three"><iframe src="{{ route('wedding.templates.preview', 'puspita') }}" title="Pratinjau Puspita" loading="lazy" tabindex="-1"></iframe></div>
        </div>
        <div data-reveal data-reveal-delay="2">
            <span class="eyebrow">Bukan Template Biasa</span>
            <h2 class="section-title">Dirancang untuk <em>dibaca</em>, bukan sekadar dibuka.</h2>
            <p class="section-copy" style="margin-top:22px">Kami memikirkan ritme gulir, kontras foto, ukuran sentuh, dan transisi antarbagian. Undangan tetap elegan di layar kecil, dan terasa mewah di layar besar.</p>
            <ul class="showcase-list">
                <li><span class="num">01</span><div><h3>Tipografi yang punya karakter</h3><p>Serif klasik, editorial modern, script romantis, hingga geometris kontemporer — dipilih sesuai konsep desain.</p></div></li>
                <li><span class="num">02</span><div><h3>Cover yang membuat berhenti</h3><p>Komposisi cover, bingkai, foto, dan ornamen disusun berbeda untuk setiap template.</p></div></li>
                <li><span class="num">03</span><div><h3>Animasi yang menghormati pembaca</h3><p>Halus, tidak berlebihan, dan otomatis meredam diri saat perangkat meminta reduced motion.</p></div></li>
            </ul>
        </div>
    </div>
</section>

<section class="section">
    <div class="site-wrap">
        <div class="section-head center">
            <span class="eyebrow">Semua yang Anda Butuhkan</span>
            <h2 class="section-title">Satu undangan, <em>lengkap</em>.</h2>
            <p class="section-copy">Dari RSVP sampai tanda kasih, semuanya terhubung dan dapat diatur dari satu dasbor.</p>
        </div>
        <div class="feature-grid">
            <div class="feature-item" data-reveal>
                <span class="feature-icon"><i data-lucide="layout-template"></i></span>
                <h3>8 template orisinal</h3>
                <p>Preview memakai view dan stylesheet yang sama dengan undangan publik. Yang Anda lihat adalah yang tamu lihat.</p>
            </div>
            <div class="feature-item" data-reveal data-reveal-delay="1">
                <span class="feature-icon"><i data-lucide="users"></i></span>
                <h3>Tamu personal</h3>
                <p>Setiap tamu mendapat token unik. Nama di cover hanya berasal dari database, bukan parameter URL yang bisa dipalsukan.</p>
            </div>
            <div class="feature-item" data-reveal data-reveal-delay="2">
                <span class="feature-icon"><i data-lucide="clipboard-check"></i></span>
                <h3>RSVP real-time</h3>
                <p>Konfirmasi kehadiran, jumlah orang, dan pesan tamu langsung masuk ke dasbor Anda.</p>
            </div>
            <div class="feature-item" data-reveal>
                <span class="feature-icon"><i data-lucide="message-heart"></i></span>
                <h3>Ucapan & doa</h3>
                <p>Tamu dapat mengirim doa. Anda dapat menampilkan atau menyembunyikan setiap ucapan.</p>
            </div>
            <div class="feature-item" data-reveal data-reveal-delay="1">
                <span class="feature-icon"><i data-lucide="wallet-cards"></i></span>
                <h3>Tanda kasih</h3>
                <p>Beberapa rekening bank dengan tombol salin, tanpa mengarahkan tamu ke halaman yang membingungkan.</p>
            </div>
            <div class="feature-item" data-reveal data-reveal-delay="2">
                <span class="feature-icon"><i data-lucide="message-circle"></i></span>
                <h3>WhatsApp Click to Chat</h3>
                <p>Buat pesan personal dengan variabel, lalu buka WhatsApp. Tidak ada klaim terkirim tanpa bukti.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="cara-kerja">
    <div class="site-wrap">
        <div class="section-head">
            <div>
                <span class="eyebrow">Cara Kerja</span>
                <h2 class="section-title">Tiga langkah, <em>satu cerita.</em></h2>
            </div>
            <p class="section-copy">Anda tidak perlu memahami desain atau kode. Pilih, isi, lalu bagikan.</p>
        </div>
        <div class="steps">
            <article class="step" data-reveal><h3>Pilih desain Anda</h3><p>Bandingkan delapan konsep melalui preview interaktif yang menggunakan undangan asli.</p></article>
            <article class="step" data-reveal data-reveal-delay="1"><h3>Isi cerita Anda</h3><p>Lengkapi identitas mempelai, acara, kisah, galeri, dan tanda kasih dari editor dua panel.</p></article>
            <article class="step" data-reveal data-reveal-delay="2"><h3>Ajukan & terbitkan</h3><p>Ajukan publikasi, selesaikan pembayaran manual, lalu Admin menerbitkan undangan Anda.</p></article>
        </div>
    </div>
</section>

<section class="section" id="publikasi">
    <div class="site-wrap">
        <div class="section-head center">
            <span class="eyebrow">Alur Publikasi</span>
            <h2 class="section-title">Anda menyusun. <em>Admin menerbitkan.</em></h2>
            <p class="section-copy">Tidak ada paket atau langganan. Semua fitur terbuka penuh untuk setiap customer. Publikasi undangan dilakukan oleh Admin Markas Punokawan setelah pembayaran manual dikonfirmasi.</p>
        </div>
        <div class="steps">
            <article class="step" data-reveal><span class="num">01</span><h3>Susun & finalkan</h3><p>Lengkapi seluruh data undangan, pilih desain, lalu tekan <b>Ajukan Publikasi</b> saat semuanya sudah final.</p></article>
            <article class="step" data-reveal data-reveal-delay="1"><span class="num">02</span><h3>Pembayaran manual</h3><p>Admin mengirim instruksi pembayaran melalui chat WhatsApp pribadi. Tidak ada payment gateway otomatis.</p></article>
            <article class="step" data-reveal data-reveal-delay="2"><span class="num">03</span><h3>Admin menerbitkan</h3><p>Setelah pembayaran diverifikasi, Admin mempublikasikan undangan Anda. Barulah tautan aktif dan dapat dibagikan.</p></article>
        </div>
        <div class="section-foot">
            <a href="{{ auth()->check() ? route('wedding.dashboard') : route('wedding.register') }}" class="btn btn-gold">Mulai Sekarang <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="site-wrap">
        <div class="cta-band" data-reveal>
            <div>
                <h2>Hari bahagia Anda layak <em>dikenang dengan indah.</em></h2>
                <p>Semua desain dan fitur terbuka penuh. Susun cerita Anda, ajukan publikasi, dan Admin akan menerbitkannya untuk Anda.</p>
            </div>
            <div class="cta-actions">
                <a href="{{ route('wedding.register') }}" class="btn btn-gold">Buat Undangan Sekarang</a>
                <a href="{{ route('wedding.templates.index') }}" class="btn btn-line">Bandingkan Desain</a>
            </div>
        </div>
    </div>
</section>
@endsection
