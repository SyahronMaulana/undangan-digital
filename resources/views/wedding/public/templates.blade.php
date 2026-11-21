@extends('wedding.layouts.site')

@section('title', 'Katalog Desain Undangan')
@section('page', 'Koleksi Template')
@section('description', 'Bandingkan delapan desain undangan Markas Punokawan Wedding: Melati, Senja, Lestari, Maharaja, Arum, Puspita, Asmara, dan Nirmala.')

@section('content')
<section class="site-wrap catalog-hero">
    <div data-reveal>
        <span class="eyebrow">Koleksi Undangan · {{ $templates->count() }} Desain</span>
        <h1>Temukan desain yang <em>paling terasa kalian.</em></h1>
    </div>
    <p data-reveal data-reveal-delay="1">Setiap gambar di bawah adalah preview hidup dari template sebenarnya — bukan thumbnail statis. Gulir di dalam preview setelah membuka desain.</p>
</section>

<div class="site-wrap catalog-tools">
    <span>Semua template aktif</span>
    <span class="pill">Preview asli · Mobile-first</span>
</div>

<section class="site-wrap catalog-section">
    <div class="template-grid large">
        @foreach($templates as $template)
            @include('wedding.public.partials.template-card', ['template' => $template])
        @endforeach
    </div>
</section>

<section class="section section-tight">
    <div class="site-wrap">
        <div class="cta-band" data-reveal>
            <div>
                <h2>Masih bingung memilih? <em>Mulai dari yang paling Anda sukai.</em></h2>
                <p>Template dapat diganti kapan saja sebelum atau sesudah publikasi tanpa kehilangan data undangan.</p>
            </div>
            <div class="cta-actions">
                <a href="{{ route('wedding.register') }}" class="btn btn-gold">Buat Undangan</a>
                <a href="{{ route('wedding.home') }}" class="btn btn-line">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</section>
@endsection
