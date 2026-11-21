<?php

return [
    'brand' => [
        'platform' => 'Markas Punokawan',
        'module' => 'Wedding',
        'subtitle' => 'Wedding Invitation',
        'tagline' => 'Undangan pernikahan digital yang terasa seperti karya desainer.',
    ],

    // Nomor WhatsApp admin default. Diatur lewat .env (WEDDING_ADMIN_WHATSAPP)
    // dan dapat diubah dari Pengaturan admin (tersimpan di wedding_settings).
    // Format: kode negara tanpa "+", spasi, atau tanda hubung.
    'admin_whatsapp' => env('WEDDING_ADMIN_WHATSAPP', '6285155269940'),

    // Slug yang tidak boleh dipakai undangan agar tidak menimpa route modul.
    'reserved_slugs' => [
        'admin', 'dashboard', 'login', 'register', 'logout', 'up', 'api', 'storage',
        'build', 'css', 'js', 'images', 'assets', 'templates', 'template', 'preview',
        'paket', 'harga', 'account', 'pengaturan', 'settings', 'invitations', 'guests',
        'rsvp', 'wishes', 'ucapan', 'broadcast', 'whatsapp', 'pembayaran', 'langganan',
        'sitemap', 'robots', 'favicon', 'wedding', 'password', 'email', 'verifikasi',
        'help', 'bantuan', 'syarat', 'privasi', 'kebijakan', 'demo',
    ],

    'sections' => [
        'pembukaan'   => 'Pembukaan',
        'mempelai'    => 'Mempelai',
        'kutipan'     => 'Kutipan',
        'countdown'   => 'Hitung Mundur',
        'acara'       => 'Rangkaian Acara',
        'kisah'       => 'Kisah Kami',
        'galeri'      => 'Galeri',
        'rsvp'        => 'Konfirmasi Kehadiran',
        'ucapan'      => 'Ucapan & Doa',
        'tanda_kasih' => 'Tanda Kasih',
        'penutup'     => 'Penutup',
    ],

    'default_sections' => [
        'pembukaan'   => true,
        'mempelai'    => true,
        'kutipan'     => true,
        'countdown'   => true,
        'acara'       => true,
        'kisah'       => false,
        'galeri'      => true,
        'rsvp'        => true,
        'ucapan'      => true,
        'tanda_kasih' => true,
        'penutup'     => true,
    ],

    'language_styles' => [
        'islami' => 'Islami',
        'umum' => 'Umum',
        'adat_jawa' => 'Adat Jawa',
    ],

    'openings' => [
        'islami' => "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\nDengan memohon rahmat dan ridha Allah Subhanahu wa Ta'ala, kami bermaksud menyelenggarakan pernikahan putra-putri kami.",
        'umum' => "Dengan penuh sukacita dan rasa hormat, kami bermaksud mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu pada hari bahagia kami.",
        'adat_jawa' => "Sugeng rawuh, kanthi raos bingah lan pakurmatan, kula sekalian badhe ngundhang panjenengan sedaya wonten ing adicara dhauping putra-putri kula.",
    ],

    'quotes' => [
        'ar-rum-21' => [
            'text' => 'Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir.',
            'source' => 'QS. Ar-Rum: 21',
        ],
        'ar-rum-21-ringkas' => [
            'text' => 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan hidup dari jenismu sendiri, supaya kamu dapat ketenangan hati dan merasa kasih sayang.',
            'source' => 'QS. Ar-Rum: 21',
        ],
        'umum' => [
            'text' => 'Cinta bukanlah tentang menemukan seseorang yang sempurna, melainkan memilih untuk tetap bersama, tumbuh, dan saling menguatkan dalam setiap musim kehidupan.',
            'source' => 'Markas Punokawan',
        ],
        'jawa' => [
            'text' => 'Yen tresna, aja mung nganti tresna. Nanging tresnane kudu bisa dadi kekuatan kanggo nggayuh urip sing luwih becik.',
            'source' => 'Pepatah Jawa',
        ],
    ],

    /**
     * Delapan identitas desain. Metadata dipakai katalog, preview, editor, dan seeder.
     * Implementasi visual berada di resources/views/wedding/templates/{slug}.
     */
    'templates' => [
        'melati' => [
            'name' => 'Melati',
            'tagline' => 'Classic Floral Wedding',
            'description' => 'Nuansa klasik yang lembut dengan ornamen melati, bingkai dekoratif, dan tekstur kertas premium.',
            'palette' => ['#fbf6ea', '#efe1c3', '#ffffff', '#a8843b'],
            'fonts' => ['Cormorant Garamond', 'Great Vibes', 'DM Sans'],
            'demo' => ['Rangga', 'Ayu', '24 Agustus 2027', 'Yogyakarta'],
        ],
        'senja' => [
            'name' => 'Senja',
            'tagline' => 'Modern Minimalist Wedding',
            'description' => 'Komposisi editorial asimetris, ruang kosong yang lapang, dan tipografi modern yang tenang.',
            'palette' => ['#eee4d5', '#ffffff', '#b79b80', '#2d2a26'],
            'fonts' => ['Bodoni Moda', 'DM Sans'],
            'demo' => ['Bima', 'Nara', '12 September 2027', 'Bandung'],
        ],
        'lestari' => [
            'name' => 'Lestari',
            'tagline' => 'Botanical Garden Wedding',
            'description' => 'Taman botani dengan daun sage, bingkai lengkung, dan suasana natural yang menenangkan.',
            'palette' => ['#9caa8f', '#65704f', '#f6f2e8', '#ffffff'],
            'fonts' => ['Fraunces', 'DM Sans'],
            'demo' => ['Damar', 'Laras', '3 Oktober 2027', 'Bogor'],
        ],
        'maharaja' => [
            'name' => 'Maharaja',
            'tagline' => 'Luxury Royal Wedding',
            'description' => 'Hitam pekat, emas, dan garis geometris klasik untuk perayaan yang eksklusif dan dramatis.',
            'palette' => ['#0a0908', '#c9a54b', '#3a2a1a', '#f4ead1'],
            'fonts' => ['Cinzel', 'Cormorant Garamond'],
            'demo' => ['Adipati', 'Kania', '7 November 2027', 'Jakarta'],
        ],
        'arum' => [
            'name' => 'Arum',
            'tagline' => 'Rustic Earthy Wedding',
            'description' => 'Terracotta, kertas bertekstur, bunga kering, dan kehangatan nuansa bumi yang membumi.',
            'palette' => ['#b5573a', '#f1e2cb', '#6b4630', '#40332a'],
            'fonts' => ['Lora', 'Caveat'],
            'demo' => ['Galih', 'Sekar', '19 Mei 2027', 'Yogyakarta'],
        ],
        'puspita' => [
            'name' => 'Puspita',
            'tagline' => 'Elegant Javanese Traditional',
            'description' => 'Motif batik, hijau tua, dan emas dalam komposisi tradisional Jawa yang anggun.',
            'palette' => ['#1f3d2b', '#6b4630', '#c9a54b', '#f3ecd8'],
            'fonts' => ['Playfair Display', 'Cormorant Garamond'],
            'demo' => ['Bagas', 'Puspita', '2 Desember 2027', 'Surakarta'],
        ],
        'asmara' => [
            'name' => 'Asmara',
            'tagline' => 'Romantic Modern Wedding',
            'description' => 'Dusty pink, kelopak bunga, dan tipografi script yang romantis untuk kisah yang lembut.',
            'palette' => ['#d9a5ae', '#f7e9e8', '#8e5a74', '#fffaf8'],
            'fonts' => ['Italiana', 'Parisienne', 'Jost'],
            'demo' => ['Raka', 'Aluna', '26 Juni 2027', 'Semarang'],
        ],
        'nirmala' => [
            'name' => 'Nirmala',
            'tagline' => 'Dark Elegant Contemporary',
            'description' => 'Navy, hitam, dan silver dengan komposisi editorial sinematik yang modern dan berkelas.',
            'palette' => ['#101827', '#05070d', '#c0c4cc', '#e8e9ec'],
            'fonts' => ['Italiana', 'Jost'],
            'demo' => ['Arga', 'Nirmala', '14 Januari 2028', 'Jakarta'],
        ],
    ],

    'demo' => [
        'slug' => 'demo',
        'groom_name' => 'Rangga Adiputra',
        'groom_nickname' => 'Rangga',
        'groom_father' => 'Bapak H. Suryana',
        'groom_mother' => 'Ibu Hj. Ratna Dewi',
        'bride_name' => 'Ayu Larasati',
        'bride_nickname' => 'Ayu',
        'bride_father' => 'Bapak H. Muhaimin',
        'bride_mother' => 'Ibu Hj. Sri Wahyuni',
        'cover_photo' => 'wedding-assets/assets/images/demo/cover.jpg',
        'groom_photo' => 'wedding-assets/assets/images/demo/groom.jpg',
        'bride_photo' => 'wedding-assets/assets/images/demo/bride.jpg',
    ],

    'gallery' => [
        'wedding-assets/assets/images/demo/gallery-1.jpg',
        'wedding-assets/assets/images/demo/gallery-2.jpg',
        'wedding-assets/assets/images/demo/gallery-3.jpg',
        'wedding-assets/assets/images/demo/gallery-4.jpg',
        'wedding-assets/assets/images/demo/gallery-5.jpg',
        'wedding-assets/assets/images/demo/gallery-6.jpg',
    ],

    'limits' => [
        'image_kb' => 5120,
        'audio_kb' => 10240,
        'rsvp_per_minute' => 6,
        'wishes_per_minute' => 4,
        'import_rows' => 2000,
    ],
];
