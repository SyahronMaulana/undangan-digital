<?php

return [
    // Slug yang tidak boleh dipakai undangan agar tidak bentrok dengan route aplikasi.
    'reserved_slugs' => [
        'admin', 'dashboard', 'login', 'register', 'logout', 'up', 'api', 'storage',
        'build', 'css', 'js', 'images', 'assets', 'preview', 'katalog', 'desain',
        'harga', 'paket', 'password', 'email', 'settings', 'pengaturan', 'null',
    ],

    // Section undangan; true = aktif secara bawaan. Customer dapat mengubahnya.
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

    'language_styles' => ['islami' => 'Islami', 'umum' => 'Umum', 'adat_jawa' => 'Adat Jawa'],
];
