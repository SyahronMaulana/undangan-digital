<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Markas Punokawan — Rumah Digital untuk Karya dan Perayaan</title>
    <meta name="description" content="Markas Punokawan adalah platform digital multifungsi. Temukan modul undangan pernikahan, dan modul lain yang segera hadir.">
    <link rel="icon" href="{{ \App\Support\Brand::favicon() }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ \App\Support\Brand::faviconPng() }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\Brand::faviconPng() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#111713;--ink-2:#1b241e;--cream:#f3eee3;--gold:#c8a45c;--muted:#a6ada6;--line:rgba(255,255,255,.12)}
        *{box-sizing:border-box}html,body{margin:0;padding:0}
        body{background:var(--ink);color:var(--cream);font-family:'DM Sans',sans-serif;-webkit-font-smoothing:antialiased}
        a{color:inherit;text-decoration:none}
        .wrap{max-width:1180px;margin:0 auto;padding:0 24px}
        .nav{display:flex;align-items:center;justify-content:space-between;padding:26px 0;border-bottom:1px solid var(--line)}
        .brand{display:flex;align-items:center;gap:12px}
        .brand-logo{height:42px;width:auto;display:block}
        .navlinks{display:flex;align-items:center;gap:26px;font-size:13px;color:#cbd1cb}
        .navlinks a:hover{color:var(--gold)}
        .btn{display:inline-flex;align-items:center;gap:9px;padding:13px 20px;border-radius:999px;font-size:13px;font-weight:600;transition:.25s;border:1px solid transparent}
        .btn-gold{background:var(--gold);color:#16150f}.btn-gold:hover{background:#dcbd7d;transform:translateY(-1px)}
        .btn-ghost{border-color:var(--line);color:var(--cream)}.btn-ghost:hover{border-color:rgba(200,164,92,.6);color:var(--gold)}
        .hero{padding:86px 0 70px;display:grid;grid-template-columns:1.15fr .85fr;gap:60px;align-items:center;position:relative}
        .eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:11px;letter-spacing:.26em;text-transform:uppercase;color:var(--gold);margin-bottom:26px}
        .eyebrow:before{content:'';width:28px;height:1px;background:var(--gold)}
        h1{font-family:'Fraunces',serif;font-weight:400;font-size:clamp(44px,6vw,78px);line-height:.98;letter-spacing:-.03em;margin:0}
        h1 em{color:var(--gold);font-style:italic}
        .hero p{font-size:17px;line-height:1.75;color:#b9c0b9;max-width:540px;margin:28px 0 34px}
        .actions{display:flex;gap:12px;flex-wrap:wrap}
        .meta{display:flex;gap:36px;margin-top:54px;padding-top:28px;border-top:1px solid var(--line)}
        .meta b{display:block;font-family:'Fraunces',serif;font-size:27px;color:var(--cream);font-weight:400}
        .meta span{font-size:11px;color:var(--muted);letter-spacing:.14em;text-transform:uppercase}
        .orb{position:relative;aspect-ratio:1;border:1px solid rgba(200,164,92,.22);border-radius:50%;display:grid;place-items:center;padding:30px}
        .orb:before{content:'';position:absolute;inset:9%;border:1px dashed rgba(200,164,92,.25);border-radius:50%;animation:spin 50s linear infinite}
        .orb:after{content:'';position:absolute;inset:0;border-radius:50%;background:radial-gradient(circle at 35% 30%,rgba(200,164,92,.16),transparent 55%)}
        @keyframes spin{to{transform:rotate(360deg)}}
        .orb-card{position:relative;z-index:2;width:72%;aspect-ratio:3/4;border-radius:170px 170px 18px 18px;overflow:hidden;border:1px solid rgba(200,164,92,.45);box-shadow:0 30px 80px rgba(0,0,0,.55)}
        .orb-card img{width:100%;height:100%;object-fit:cover;filter:saturate(.82)}
        .orb-card .cap{position:absolute;inset:auto 0 0;padding:24px 18px 20px;text-align:center;background:linear-gradient(transparent,rgba(17,23,19,.92))}
        .orb-card .cap small{font-size:9px;letter-spacing:.3em;color:var(--gold)}
        .orb-card .cap b{display:block;font-family:'Fraunces',serif;font-size:21px;font-weight:400;margin-top:4px}
        .chip{position:absolute;z-index:3;background:rgba(27,36,30,.9);backdrop-filter:blur(12px);border:1px solid var(--line);border-radius:999px;padding:9px 15px;font-size:11px;color:#d7dbd7;display:flex;gap:8px;align-items:center}
        .chip i{width:6px;height:6px;border-radius:50%;background:var(--gold);display:block}
        .chip.one{top:9%;right:-4%;animation:float 6s ease-in-out infinite}
        .chip.two{bottom:12%;left:-8%;animation:float 7s ease-in-out infinite reverse}
        @keyframes float{50%{transform:translateY(-10px)}}
        .section{padding:90px 0;border-top:1px solid var(--line)}
        .section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:30px;margin-bottom:44px}
        .section-head h2{font-family:'Fraunces',serif;font-size:clamp(34px,4.5vw,54px);font-weight:400;line-height:1.05;margin:0;letter-spacing:-.02em}
        .section-head h2 em{color:var(--gold);font-style:italic}
        .section-head p{max-width:430px;color:var(--muted);line-height:1.75;font-size:14px;margin:0}
        .modules{display:grid;grid-template-columns:1.4fr 1fr;gap:18px}
        .module{position:relative;border:1px solid var(--line);border-radius:22px;padding:36px;overflow:hidden;min-height:300px;display:flex;flex-direction:column;justify-content:space-between;transition:.3s;background:linear-gradient(145deg,rgba(255,255,255,.03),transparent)}
        .module:hover{transform:translateY(-4px);border-color:rgba(200,164,92,.45)}
        .module.active{background:linear-gradient(145deg,#233328,#17221b);}
        .module .tag{font-size:10px;letter-spacing:.24em;text-transform:uppercase;color:var(--gold);margin-bottom:auto}
        .module h3{font-family:'Fraunces',serif;font-weight:400;font-size:clamp(30px,4vw,46px);margin:60px 0 12px}
        .module p{color:#aeb6ae;font-size:14px;line-height:1.7;max-width:480px;margin:0 0 24px}
        .module .go{display:inline-flex;align-items:center;gap:8px;color:var(--cream);font-size:12px;font-weight:600;letter-spacing:.04em}
        .module .go:hover{color:var(--gold)}
        .module .art{position:absolute;right:-20px;top:-20px;width:45%;opacity:.35;pointer-events:none}
        .module .art img{width:100%;height:100%;object-fit:cover;mask-image:linear-gradient(to left,black,transparent)}
        .soon{display:grid;gap:12px}
        .soon-item{border:1px solid var(--line);border-radius:16px;padding:22px 24px;display:flex;justify-content:space-between;align-items:center;color:#aeb6ae;font-size:13px}
        .soon-item span{font-size:9px;letter-spacing:.2em;text-transform:uppercase;border:1px solid var(--line);border-radius:999px;padding:5px 9px;color:#7f887f}
        .footer{border-top:1px solid var(--line);padding:34px 0;display:flex;justify-content:space-between;gap:20px;font-size:12px;color:#7f887f;flex-wrap:wrap}
        @media(max-width:900px){.hero{grid-template-columns:1fr;padding-top:60px}.orb{max-width:420px;margin:10px auto 0;width:100%}.modules{grid-template-columns:1fr}.navlinks{display:none}.section-head{display:block}.section-head p{margin-top:18px}}
        @media(max-width:600px){.wrap{padding:0 18px}.brand-logo{height:34px}.meta{gap:22px}.meta b{font-size:22px}.module{padding:26px;min-height:270px}.chip{display:none}.hero{padding:48px 0 54px}.section{padding:64px 0}}
    </style>
</head>
<body>
<div class="wrap">
    <nav class="nav">
        <a href="{{ route('home') }}" class="brand" aria-label="Markas Punokawan">
            <img src="{{ \App\Support\Brand::logo() }}" alt="{{ \App\Support\Brand::logoAlt() }}" class="brand-logo">
        </a>
        <div class="navlinks">
            <a href="#modul">Modul</a>
            <a href="#tentang">Tentang</a>
            <a href="{{ route('wedding.home') }}">Wedding</a>
            <a href="{{ auth()->check() ? route('wedding.dashboard') : route('wedding.login') }}" class="btn btn-gold">
                {{ auth()->check() ? 'Buka Dasbor' : 'Masuk ke Wedding' }}
            </a>
        </div>
    </nav>

    <header class="hero">
        <div>
            <span class="eyebrow">Markas Punokawan · Ekosistem Digital</span>
            <h1>Satu markas untuk <em>karya</em>, perayaan, dan pertumbuhan.</h1>
            <p>Markas Punokawan tumbuh sebagai rumah digital multifungsi. Modul pertama yang siap digunakan adalah Wedding — undangan pernikahan digital dengan delapan desain yang dirancang seperti karya desainer profesional.</p>
            <div class="actions">
                <a href="{{ route('wedding.home') }}" class="btn btn-gold">Jelajahi Wedding <span aria-hidden="true">→</span></a>
                <a href="#modul" class="btn btn-ghost">Lihat Modul</a>
            </div>
            <div class="meta">
                <div><b>8</b><span>Desain Undangan</span></div>
                <div><b>100%</b><span>Mobile First</span></div>
                <div><b>1</b><span>Modul Aktif</span></div>
            </div>
        </div>
        <div class="orb" aria-hidden="true">
            <div class="chip one"><i></i> Undangan digital premium</div>
            <div class="orb-card">
                <img src="{{ asset('wedding-assets/assets/images/demo/cover.jpg') }}" alt="Pratinjau desain undangan Markas Punokawan Wedding">
                <div class="cap"><small>The Wedding Of</small><b>Rangga &amp; Ayu</b></div>
            </div>
            <div class="chip two"><i></i> 8 template siap pakai</div>
        </div>
    </header>

    <section class="section" id="modul">
        <div class="section-head">
            <h2>Modul yang tumbuh <em>bersama</em>.</h2>
            <p>Setiap modul memiliki ruang, identitas, dan route sendiri. Wedding bukan aplikasi terpisah — ia bagian dari ekosistem Markas Punokawan.</p>
        </div>
        <div class="modules">
            <a href="{{ route('wedding.home') }}" class="module active">
                <span class="tag">Modul Aktif · 01</span>
                <div class="art"><img src="{{ asset('wedding-assets/assets/images/demo/couple-1.jpg') }}" alt=""></div>
                <div>
                    <h3>Wedding</h3>
                    <p>Undangan pernikahan digital dengan delapan template berbeda, RSVP, ucapan, tamu personal, dan broadcast WhatsApp.</p>
                    <span class="go">Masuk ke Wedding →</span>
                </div>
            </a>
            <div class="soon">
                <div class="soon-item">Marketplace <span>Segera</span></div>
                <div class="soon-item">Desain Grafis <span>Segera</span></div>
                <div class="soon-item">Manajemen Bisnis <span>Segera</span></div>
                <div class="soon-item">Sistem Informasi <span>Segera</span></div>
            </div>
        </div>
    </section>

    <section class="section" id="tentang">
        <div class="section-head">
            <h2>Dibangun untuk <em>bertahan</em>.</h2>
            <p>Arsitektur modular, keamanan data, dan kualitas visual menjadi fondasi setiap modul. Root platform tetap milik Markas Punokawan; setiap modul memiliki namespace sendiri.</p>
        </div>
        <div class="modules" style="grid-template-columns:repeat(3,1fr)">
            <div class="module" style="min-height:230px">
                <span class="tag">Terisolasi</span>
                <div><h3 style="font-size:28px;margin:40px 0 8px">Namespace /wedding</h3><p style="margin:0;font-size:13px">Semua route, aset, dan tampilan modul tidak mengambil alih halaman utama.</p></div>
            </div>
            <div class="module" style="min-height:230px">
                <span class="tag">Aman</span>
                <div><h3 style="font-size:28px;margin:40px 0 8px">Data terisolasi</h3><p style="margin:0;font-size:13px">Setiap customer hanya dapat mengakses dan mengubah undangannya sendiri.</p></div>
            </div>
            <div class="module" style="min-height:230px">
                <span class="tag">Berkarakter</span>
                <div><h3 style="font-size:28px;margin:40px 0 8px">8 dunia visual</h3><p style="margin:0;font-size:13px">Bukan sekadar ganti warna. Setiap template punya layout, ornamen, dan animasi sendiri.</p></div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <span>© {{ date('Y') }} Markas Punokawan. Seluruh hak cipta dilindungi.</span>
        <span>Dirancang dan dibangun dengan teliti di Indonesia.</span>
    </footer>
</div>
</body>
</html>
