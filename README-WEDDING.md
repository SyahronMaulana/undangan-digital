# Markas Punokawan — Modul Wedding (Undangan Pernikahan Digital)

Modul **Wedding** adalah bagian integral dari platform ekosistem **Markas Punokawan**, dirancang secara modular dan terisolasi di bawah namespace URL `/wedding`.

---

## 1. Ringkasan Arsitektur & Isolasi Modul

- **Prefix URL**: Seluruh route (publik, auth, dashboard, admin, preview, editor, API) menggunakan prefix `/`**`wedding`** (misal: `/wedding`, `/wedding/dashboard`, `/wedding/{slug}`). Route utama `/` tetap bersih untuk landing page platform utama Markas Punokawan.
- **Pemisahan Aset**: Aset frontend, template, CSS, JS, dan gambar khusus modul diletakkan di `public/wedding-assets/` dan tidak bercampur dengan aset modul platform utama.
- **Struktur Modular**: Mengikuti pola MVC Laravel dengan service class terpisah (`TemplateRegistry`, `WhatsAppTemplate`, `DemoInvitation`), migration terdedikasi (`2026_11_14_000001_extend_wedding_module.php`), dan konfigurasi terpusat (`config/wedding.php`).

---

## 2. Delapan Template Eksklusif

Modul ini menyediakan **8 template dengan desain, tipografi, ornamen, CSS, dan animasi yang benar-benar berbeda** (bukan sekadar ganti warna):

1. **Melati** (`melati`): Classic Floral Wedding (Ivory, Cream, Gold, ornamen melati, tipografi serif elegan & script).
2. **Senja** (`senja`): Modern Minimalist Wedding (Beige, putih, cokelat muda, ruang kosong luas, layout editorial).
3. **Lestari** (`lestari`): Botanical Garden Wedding (Sage green, olive, ilustrasi tanaman & daun natural).
4. **Maharaja** (`maharaja`): Luxury Royal Wedding (Hitam, gold, dark brown, ornamen geometris klasik, nuansa eksklusif dramatis).
5. **Arum** (`arum`): Rustic Earthy Wedding (Terracotta, cream, tekstur kertas, bunga kering, nuansa hangat).
6. **Puspita** (`puspita`): Elegant Javanese Traditional Wedding (Hijau tua, cokelat, motif batik tradisional Jawa, klasik).
7. **Asmara** (`asmara`): Romantic Modern Wedding (Dusty pink, mauve, soft rose, ornamen bunga lembut & romantis).
8. **Nirmala** (`nirmala`): Dark Elegant Contemporary Wedding (Navy, hitam, silver, minimalis eksklusif cinematic).

---

## 3. Fitur Utama yang Diimplementasikan

- **Katalog & Live Preview**: Thumbnail portrait interaktif dengan iframe preview asli menggunakan view dan stylesheet produksi yang sama persis dengan undangan publik.
- **Editor Dua Panel**: Panel kiri untuk pengaturan (Identitas, Acara, Kisah, Galeri, Tanda Kasih, Tampilan, Section Toggle) dan panel kanan live preview interaktif dengan autosave AJAX & reload instan.
- **Manajemen Tamu & Link Personal**: CRUD tamu dengan normalisasi nomor HP ke format internasional (`+62`), import/export CSV/Excel dengan header Bahasa Indonesia, dan link personal bertoken aman (`?token=...`).
- **Nama Penerima dari URL (`?to=`)**: Link undangan dapat menyapa penerima tanpa membuat data tamu, mis. `https://domain.com/wedding/andi-siti?to=Budi%20Santoso`. Nama di-URL-encode (spasi & karakter Indonesia didukung), di-escape terhadap XSS, dan default **Tamu Undangan** bila kosong/tidak valid. Berlaku di kedelapan template tanpa mengubah desain. Nama **tidak** disimpan ke data undangan.
- **Bagikan Undangan Satu per Satu**: Pada halaman ringkasan, tulis nama penerima → link personal `?to=` + pesan WhatsApp otomatis dibangun ulang per penerima → **Salin Link** → **Kirim via WhatsApp** (Web Share API `navigator.share` bila tersedia, fallback `wa.me/?text=`) → **Tamu Berikutnya** untuk penerima selanjutnya. Tidak ada siaran massal, tidak mengakses kontak otomatis, dan tidak mengklaim pesan terkirim.
- **Tanggal & Waktu Indonesia (WIB)**: Seluruh tanggal/waktu pada pesan WhatsApp, ringkasan, dan template memakai format Indonesia zona `Asia/Jakarta`, mis. `Minggu, 15 November 2026 pukul 10.00 WIB` / `Pukul 10.00 – 12.00 WIB`.
- **Broadcast WhatsApp (Template Pesan)**: Bagikan satu template pesan ke kontak pilihan Anda lewat **Web Share API** (`navigator.share`) atau fallback **WhatsApp Click to Chat (`wa.me/?text=...`)**. Tanpa daftar penerima, tanpa nomor manual, dan tanpa personalisasi nama tamu; variabel undangan (`{{nama_pria}}`, `{{nama_wanita}}`, `{{tanggal_pernikahan}}`, `{{lokasi_acara}}`, `{{link_undangan}}`) sama untuk semua penerima. Tidak ada klaim status terkirim/terbaca otomatis.
- **RSVP & Ucapan**: Konfirmasi kehadiran (hadir, tidak hadir, belum), pencatatan jumlah tamu, kirim ucapan & doa dengan sistem moderasi, dan kado digital (rekening bank dengan tombol salin).
- **Foto Mempelai & Fallback Inisial**: Foto mempelai pria/wanita tersimpan andal (unggah, ganti, hapus). Bila foto tidak ada, kedelapan template menampilkan **inisial nama** (mis. `Ahmad Fauzi` → `AF`, `Siti Rahma` → `SR`, satu kata → dua huruf pertama) dengan gaya yang mengikuti desain masing-masing template — tidak ada gambar placeholder generik dan inisial selalu terisi.
- **Musik Latar**: Unggah/ganti/hapus musik di editor (mp3/wav/m4a/ogg) dan kontrol putar/jeda + indikator pemutaran yang tersedia di kedelapan template. Validasi musik berbasis **isi berkas (signature)** sehingga MP3/WAV/M4A/OGG yang sah tetap diterima meski MIME terdeteksi `application/octet-stream`, sementara berkas non-audio yang hanya diganti ekstensi `.mp3` tetap ditolak; pesan validasi berbahasa Indonesia. Pemutaran mengikuti gestur pengguna (tidak diblokir autoplay), hanya satu audio per halaman, dan kontrol disembunyikan bila berkas gagal dimuat atau tidak ada musik.
- **Galeri Geser (Slider)**: Desain slider **Senja** dan **Nirmala** dapat digeser dengan mouse **dan** swipe sentuh, dilengkapi tombol panah, tanpa mengganggu scroll vertikal halaman. Enam template lain tetap berupa grid galeri statis dengan lightbox.
- **Kunci Akses Publik**: Hanya undangan berstatus **published** (diterbitkan Admin) yang dapat dibuka publik. Draf dan semua status sebelum terbit → **404** (termasuk untuk pemilik). Pratinjau internal tetap tersedia bagi pemilik/admin melalui route berautentikasi. Menarik publikasi langsung menutup akses. Hanya Admin yang dapat mempublikasikan; konfirmasi pembayaran tidak otomatis mempublikasikan.
- **Identitas Logo Resmi (platform-wide)**: Logo resmi Markas Punokawan dipakai apa adanya (tanpa mengubah bentuk/proporsi/warna, tanpa direkayasa ulang) di navbar & footer situs Wedding, halaman login/registrasi, sidebar dasbor customer, sidebar panel Super Admin, halaman utama platform, dan pratinjau template. Sumber tunggal: `public/brand/markas-punokawan-logo.png` (568 × 221, transparan) via `App\Support\Brand` + `config/brand.php`; favicon diturunkan darinya (`public/brand/favicon.png`, `public/favicon.ico`). Ukuran responsif dengan rasio asli dijaga (40 px, 32 px di layar kecil).
- **Tombol WhatsApp Admin Terpusat pada Pengajuan Publikasi**: Nomor admin hanya didefinisikan di satu tempat dan dipakai lewat `App\Support\AdminWhatsApp`. Sumber aktifnya adalah **database** (`wedding_settings`, kunci `admin_whatsapp`) yang dapat diubah Super Admin lewat menu **Pengaturan Platform → Kontak WhatsApp Admin**; bila belum pernah diatur, nilainya jatuh ke `config/wedding.php` (`admin_whatsapp`, bersumber dari `WEDDING_ADMIN_WHATSAPP`). Setelah customer mengajukan publikasi, muncul tombol **Hubungi Admin via WhatsApp** (ikon WhatsApp, catatan baku) yang membuka `https://wa.me/<nomor>?text=<pesan-terenkode>` dengan pesan siap kirim berisi nama mempelai, nama & konsep template, link pratinjau internal yang aman, slug, dan status pengajuan. Customer mengirim manual; membuka WhatsApp **tidak** mempublikasikan undangan dan **tidak** membuat pengajuan baru. Bila nomor belum dikonfigurasi, tombol tidak dirender sebagai tautan `wa.me` kosong, melainkan menampilkan pemberitahuan "Kontak admin belum dikonfigurasi."
- **Dashboard & Panel Admin**: Dasbor statistik lengkap untuk customer serta panel Super Admin untuk manajemen template, pengajuan publikasi, konfirmasi pembayaran manual, publikasi/penarikan undangan, dan **Pengaturan Platform** (nomor WhatsApp admin). Dasbor customer menampilkan status pengajuan + status pembayaran + tombol WhatsApp selama komunikasi masih dibutuhkan.
- **Pengaturan Platform (Nomor WhatsApp Admin)**: Menu **Pengaturan Platform** di sidebar Super Admin memuat bagian **Kontak WhatsApp Admin** dengan nomor aktif saat ini, form ubah nomor, tombol **Simpan Perubahan**/**Batalkan**, dan konfirmasi sebelum menyimpan. Nomor menerima format `08xxxxxxxxxx`, `628xxxxxxxxxx`, atau `+628xxxxxxxxxx` (spasi/tanda hubung/tanda kurung diizinkan), dinormalisasi otomatis ke format internasional tanpa `+`/spasi/tanda hubung (`085155269940` → `6285155269940`), disimpan ke database secara upsert (satu nilai aktif, pengaturan lain tidak terhapus), dan langsung berlaku di seluruh tombol WhatsApp customer tanpa cache/restart. Nomor tidak valid ditolak dan tidak disimpan. Hanya Super Admin yang dapat membuka/mengubah (customer → **403**); CSRF + validasi server-side diterapkan.
- **Keamanan & Isolasi Data**: Middleware role/auth, proteksi XSS, CSRF, rate limiting pada RSVP/ucapan, serta isolasi data ketat agar customer hanya dapat mengakses undangannya sendiri. Seluruh endpoint publik (halaman undangan, RSVP, ucapan) memvalidasi status publikasi undangan.

---

## 4. Akun Demo & Akses

Jika dijalankan pada environment lokal (`APP_ENV=local`), seeder otomatis menyediakan akun uji:

- **Super Admin**: `admin@markaspunokawan.test` / `ganti-kata-sandi-ini`
- **Customer Demo**: `demo@markaspunokawan.test` / `demo12345`
- **Undangan Publik Demo**: `/wedding/rangga-ayu`

---

## 5. Instruksi Instalasi & Pengujian

1. **Migrasi & Seeding Database**:
   ```bash
   php artisan migrate --seed
   ```
2. **Build Aset Frontend**:
   ```bash
   npm run build
   ```
3. **Menjalankan Pengujian (Test Suite)**:
   Modul ini dilengkapi dengan 182 unit & feature test komprehensif (menggunakan SQLite in-memory):
   ```bash
   php artisan test
   ```
   *Hasil uji saat ini: **182 passed, 0 failed** (mencakup Auth, Role Access, Invitation Flow, Invitation Editor Persistence, Public Invitation, WhatsApp Share, Guest Management, Template Catalog, Broadcast, WhatsApp Template, Wedding Initials, Audio Format, Data Isolation, dan Brand & WhatsApp Integration).*
