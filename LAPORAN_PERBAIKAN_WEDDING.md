# Laporan Perbaikan Modul Wedding — Markas Punokawan

Tanggal: 15–16 November 2026
Lingkup: Modul undangan pernikahan di bawah prefix `/wedding`

## 1. Ringkasan

Modul Wedding direvisi tanpa membangun ulang dari nol. Semua fitur yang sudah
berjalan (8 template, editor, manajemen tamu, RSVP, ucapan, preview, draf)
dipertahankan. Empat perubahan besar:

1. **Semua konsep paket/langganan dihapus.** Setiap customer mendapat akses
   penuh tanpa batas.
2. **Publikasi dipindahkan ke Super Admin** melalui menu **PENGAJUAN PUBLIKASI**
   dengan pembayaran manual. Customer hanya bisa **mengajukan** publikasi.
3. **Validasi slug diperbaiki** agar mendukung tanda hubung dengan pola yang
   ketat dan tetap unik.
4. **Broadcast WhatsApp disederhanakan** menjadi alat berbagi **template pesan**
   (tanpa daftar penerima, tanpa nomor manual, tanpa personalisasi nama tamu).
   Lihat bagian 11.

Seluruh URL tetap berada di bawah `/wedding`. Route platform (`/`) tidak
tersentuh.

## 2. Status Akhir

- `php artisan test`: **182 lulus (853 asersi)** (termasuk revisi bagian 12, 13, 14, dan 15).
- Semua route modul berada di bawah `wedding` (62 route), kecuali `/` platform.
- Tidak ada route/`route()` yang menunjuk ke paket, langganan, atau pembayaran
  otomatis.
- Beranda platform (`/`) dan beranda modul (`/wedding`) terpisah dan keduanya
  berjalan tanpa error konsol.
- Broadcast WhatsApp tidak lagi memuat daftar penerima, nomor manual, maupun
  personalisasi `{{nama_tamu}}`; verifikasi browser bersih tanpa error konsol.
- Data editor (foto, musik, rekening, kisah) tersimpan pada **Simpan** maupun
  **Ajukan Publikasi** dan tetap ada saat editor dibuka kembali (bagian 12).
- Tanggal & waktu WhatsApp memakai format Indonesia zona `Asia/Jakarta` (WIB).
- Nama penerima dari parameter `?to=` tampil di kedelapan template (bagian 12).
- Tersedia berbagi undangan **satu per satu** dengan link personal per penerima.
- Foto mempelai tersimpan andal; bila tidak ada foto, kedelapan template
  menampilkan **inisial nama** (mis. `Ahmad Fauzi` → `AF`) yang selalu terisi
  dan mengikuti perubahan nama (bagian 13).
- **Musik** dapat diunggah/diganti/dihapus di editor dan tersedia di kedelapan
  template dengan kontrol putar/jeda + indikator, tanpa autoplay yang diblokir
  dan tanpa audio ganda (bagian 13). Validasi musik kini berbasis **isi berkas**
  (signature) sehingga MP3/WAV/M4A/OGG yang sah tidak lagi ditolak hanya karena
  MIME terdeteksi sebagai `application/octet-stream` (bagian 14).
- **Galeri** desain slider (senja, nirmala) dapat digeser dengan mouse **dan**
  swipe sentuh, tombol panah berfungsi, tanpa mengganggu scroll halaman
  (bagian 13).
- **Akses publik dikunci:** hanya undangan berstatus `published` (oleh Admin)
  yang dapat dibuka; draf dan semua status sebelum terbit → 404, tarik publikasi
  langsung menutup akses, sedangkan pratinjau internal tetap tersedia bagi
  pemilik/admin (bagian 13).

## 3. Penghapusan Paket / Langganan

Yang dihapus dari jalur aktif:

- Gating paket pada `InvitationController`, `GuestController`,
  `BroadcastController`, `DashboardController`, `AccountController`.
- Method/relasi paket pada model `User` (`activePackage()`, `subscriptions()`,
  `payments()`, dll.).
- Route paket/langganan/pembayaran/paket-admin.
- Halaman & controller: `Wedding\PackagePageController`,
  `Wedding\PaymentController`, `Admin\PackageController`,
  `Admin\PaymentController`.
- View: `wedding/subscription/index`, `admin/packages`, `admin/payments`,
  `public/packages`.
- Bagian "Paket" pada navigasi/footer situs publik dan seksi paket pada
  `landing.blade.php` (diganti seksi alur `#publikasi`).
- Seeder paket/langganan.

Catatan: tabel `packages`, `subscriptions`, `payments` dan model
`Package`/`Subscription`/`Payment` **tetap ada secara fisik** untuk keamanan
data, tetapi tidak dipakai lagi oleh alur Wedding. Controller platform lama
(`App\Http\Controllers\Admin\*`, `App\Http\Controllers\Customer\*`) yang masih
mereferensikan model tersebut **tidak dirutekan** sehingga tidak pernah
dieksekusi.

## 4. Alur Publikasi (Customer → Admin)

Status pengajuan: `draft`, `menunggu_konfirmasi`, `menunggu_pembayaran`,
`menunggu_verifikasi`, `pembayaran_dikonfirmasi`, `dipublikasikan`, `revisi`.
Status pembayaran: `belum_bayar`, `menunggu_verifikasi`, `dikonfirmasi`.

Alur:

1. Customer melengkapi undangan lalu menekan **AJUKAN PUBLIKASI** dengan
   konfirmasi bahwa desain sudah final → status `menunggu_konfirmasi`.
2. Admin menerima pengajuan di menu **PENGAJUAN PUBLIKASI** dan menekan
   **Tandai Instruksi Terkirim** → `menunggu_pembayaran`.
3. Customer menekan **Saya Sudah Bayar** (`claim-payment`) →
   `menunggu_verifikasi` + pembayaran `menunggu_verifikasi`.
4. Admin **wajib mencentang** "sudah benar-benar memeriksa pembayaran" sebelum
   **Pembayaran Dikonfirmasi** → `pembayaran_dikonfirmasi` +
   `pembayaran_dikonfirmasi`.
5. Admin **Publikasikan Undangan** → `dipublikasikan` + status undangan
   `published`.

Pengaman server-side:

- Tidak ada route `publish` untuk customer; tombol admin bukan satu-satunya
  pengaman (aksi tetap divalidasi di backend).
- `publish()` menolak bila: pengajuan tidak aktif, pembayaran belum
  dikonfirmasi, `publicationIssues()` tidak kosong (mis. belum ada acara),
  atau slug tidak valid / sudah dipakai. Publikasi memakai transaksi DB.
- `confirmPayment()` menolak tanpa `payment_checked` yang diterima.
- Undangan yang sudah terbit tidak bisa diubah diam-diam: `update()` membalas
  **423** untuk autosave JSON dan redirect berisi error untuk submit biasa;
  halaman `edit()` juga mengalihkan ke ringkasan.
- Mengubah data saat pengajuan aktif otomatis mengembalikan status ke `revisi`
  sehingga customer harus mengajukan ulang.

### Nomor WhatsApp Admin

Nomor admin **tidak hardcoded**. Sumber aktifnya adalah **database**:
tabel `wedding_settings` (kunci `admin_whatsapp`) yang diubah Super Admin
lewat **Pengaturan Platform → Kontak WhatsApp Admin**. Bila belum pernah
diatur, nilainya jatuh ke `config('wedding.admin_whatsapp')`
(`WEDDING_ADMIN_WHATSAPP`). Dipakai untuk tombol **Chat Admin** (customer)
dan pesan otomatis pengajuan publikasi. `App\Support\AdminWhatsApp`
menormalkan nomor lewat `App\Support\Phone` sehingga `0812-9999-8888`
menjadi `6281299998888` (valid untuk `wa.me`). Tombol **WhatsApp Customer**
di sisi admin memakai nomor customer yang juga dinormalkan. Bila nomor
belum dikonfigurasi, tautan `wa.me` tanpa tujuan tidak dibuat dan customer
melihat pemberitahuan "Kontak admin belum dikonfigurasi."

## 5. Validasi Slug (Perbaikan Tanda Hubung)

Pola yang diberlakukan: `^[a-z0-9]+(?:-[a-z0-9]+)*$`.

- Menerima: huruf kecil, angka, tanda hubung tunggal antar kata.
- Menolak: tanda hubung ganda (`--`), tanda hubung di awal/akhir, spasi, garis
  miring `/`, underscore, titik, dan karakter khusus.
- Normalisasi input: huruf besar → kecil, spasi → tanda hubung (beberapa spasi
  berturut disatukan jadi satu tanda hubung).
- Unik dan tidak boleh memakai slug yang dicadangkan.

### Penyebab masalah (root cause)

Backend (validasi `regex`, `unique`, `notIn`, constraint route, `normalizeSlug`,
`invitationUrl`, tombol salin & WhatsApp) sebenarnya sudah benar. Bug ada di
**frontend**: fungsi `slugify()` pada
`resources/views/wedding/invitations/create.blade.php` menjalankan
`.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')` **pada setiap ketikan**.
Akibatnya tanda hubung yang baru saja diketik langsung dihapus di ujung string,
sehingga `rangga-ayu` berubah menjadi `ranggaayu` — pengguna secara praktis
tidak bisa mengetik tanda hubung. (`rangga ayu` juga rusak karena `.trim()`
membuang spasi sebelum dikonversi.)

### Perbaikan (hanya file yang relevan)

- `resources/views/wedding/invitations/create.blade.php`
  - `slugify()` diubah menjadi hanya: buang spasi di awal, huruf besar → kecil,
    dan rentetan spasi → satu tanda hubung (`String(value).replace(/^\s+/, '').toLowerCase().replace(/\s+/g, '-').slice(0, 60)`).
    Tanda hubung **tidak lagi** dihapus/digabung, sehingga pola tidak valid
    (`a--b`, `-a`, `a-`) lolos ke validasi dan ditolak dengan benar.
  - Ditambah atribut HTML `pattern="[a-z0-9]+(?:-[a-z0-9]+)*"` + `title`, getter
    Alpine `slugValid`, dan pesan bantuan merah inline. Desain form tidak diubah.
- `tests/Feature/InvitationFlowTest.php`
  - Data provider `slugValidProvider` (rangga-ayu, ahmad-siti, undangan-2026,
    undangan-pernikahan-2026, budi123-putri456) → diterima.
  - `slugTidakValidProvider` dilengkapi `rangga--ayu`, `-rangga`, `rangga-`,
    `rangga/ayu`, `rangga@ayu`.
  - Tes `Rangga-Ayu` → `rangga-ayu`, pesan duplikat
    "Alamat ini sudah dipakai. Coba yang lain.", atribut `pattern` pada form,
    dan URL publik `/wedding/rangga-ayu` dapat dibuka + `invitationUrl()` benar.

Tidak ada perubahan pada Controller, route, model, atau proses publikasi — semua
sudah sesuai dan hanya diverifikasi ulang.

## 6. Menu Admin PENGAJUAN PUBLIKASI

`Admin\PublicationController` menyediakan: daftar pengajuan (filter status +
pencarian), detail (customer, mempelai, judul, template, alamat `/wedding/{slug}`,
waktu pengajuan, status pengajuan, status pembayaran), preview semua status,
tombol WhatsApp customer, instruksi pembayaran, konfirmasi pembayaran, minta
revisi, dan publikasi.

## 7. Daftar Undangan Customer

Menampilkan: mempelai, template, slug, status undangan, status pengajuan,
status pembayaran, tanggal dibuat, tanggal publikasi. Aksi menyesuaikan status.
Tombol **Salin** dan **Bagikan WhatsApp** hanya muncul setelah undangan benar-
benar terbit.

## 8. Matriks Pengujian

Dijalankan lewat `php artisan test` (SQLite in-memory). Ringkasan cakupan:

- **Slug**: matriks lengkap — `rangga-ayu`, `ahmad-siti`, `undangan-2026`,
  `undangan-pernikahan-2026`, `budi123-putri456` diterima; `Rangga-Ayu` →
  `rangga-ayu`; `rangga--ayu`, `-rangga`, `rangga-`, `rangga/ayu`, `rangga@ayu`,
  underscore, titik, dan spasi-tanda-baca ditolak; slug duplikat ditolak dengan
  pesan "Alamat ini sudah dipakai. Coba yang lain."; form memuat `pattern`;
  `/wedding/rangga-ayu` dapat dibuka.
- **Publikasi/pembayaran**: admin tidak bisa publikasi sebelum pembayaran
  dikonfirmasi; admin wajib memeriksa pembayaran; alur instruksi → klaim
  customer; customer tidak bisa menandai bayar sebelum instruksi; konfirmasi →
  publikasi berhasil; publikasi diblokir bila data belum lengkap; slug tidak
  valid memblokir publikasi; revisi → customer mengajukan ulang; customer tidak
  bisa mengakses menu publikasi admin; customer tidak punya route konfirmasi
  pembayaran.
- **Konten terbit**: undangan terbit tidak berubah diam-diam (423).
- **Nomor WhatsApp admin**: diatur di pengaturan, dinormalisasi, dan dipakai di
  halaman customer.
- **Halaman admin**: seluruh halaman admin (termasuk detail & preview pengajuan)
  dapat dirender.
- **Regresi**: autentikasi, isolasi data antar customer, katalog 8 template,
  manajemen tamu, RSVP/ucapan, dan pemisahan beranda platform vs modul.

Smoke test browser (SQLite sementara) memverifikasi end-to-end: customer membuat
draf (`Uji Coba` → `uji-coba`), mengajukan publikasi, admin memberi instruksi,
mencoba konfirmasi tanpa centang (ditolak), mengonfirmasi dengan centang,
mempublikasikan, lalu membuka `/wedding/uji-coba` (undangan publik live).
Halaman edit undangan terbit dialihkan ke ringkasan.

Smoke test browser khusus slug (mengetik karakter per karakter) memverifikasi:
`rangga-ayu`, `ahmad-siti`, `undangan-2026`, `budi123-putri456` valid;
`Rangga-Ayu` dan `rangga ayu` otomatis menjadi `rangga-ayu`;
`rangga--ayu`, `-rangga`, `rangga-`, `rangga/ayu` ditolak (pesan inline merah);
mengirim form `Rangga-Ayu` menyimpan slug `rangga-ayu` dan membuka editor;
slug `rangga-ayu` yang sudah dipakai ditolak dengan pesan
"Alamat ini sudah dipakai. Coba yang lain."; `/wedding/rangga-ayu` menampilkan
undangan; dan atribut `data-copy` serta tautan WhatsApp berisi
`.../wedding/rangga-ayu`.

## 9. Catatan Lingkungan

- MySQL (`127.0.0.1:3306`, database `ujicoba`) **tidak tersedia** saat
  pengerjaan (`connection refused`), sehingga verifikasi smoke memakai SQLite
  sementara (di folder temp) dan pengujian memakai SQLite in-memory. File SQLite
  sementara sudah dihapus dan server sementara sudah dihentikan.
- Jalankan migrasi pada MySQL saat sudah tersedia:
  `php artisan migrate`
  (termasuk `2026_11_14_000002_add_publication_workflow_to_weddings`).
- Revisi Broadcast **tidak menambah migrasi baru**: riwayat berbagi memakai tabel
  `wedding_activity_logs` yang sudah ada (lihat bagian 11).
- Revisi bagian 12 **juga tidak menambah migrasi baru**: seluruh perbaikan
  memakai kolom/tabel yang sudah ada (`weddings`, `wedding_events`,
  `wedding_stories`, `wedding_gifts`, `wedding_photos`).
- Bahasa aplikasi default kini **Indonesia** (`APP_LOCALE=id` di `.env`,
  `.env.example`, dan `phpunit.xml`), sehingga `translatedFormat()` menghasilkan
  nama hari/bulan Indonesia. Carbon mengikuti locale lewat listener
  `LocaleUpdated` bawaan `nesbot/carbon`. Zona waktu aplikasi `Asia/Jakarta`
  (`config/app.php`), jadi tidak ada penambahan jam manual.
- Karena tidak ada direktori `lang/`, pesan validasi bawaan tetap berbahasa
  Inggris; pesan validasi kustom pada form editor/publikasi tetap Indonesia.

## 10. Cara Menjalankan

```bash
composer install
npm install && npm run build
php artisan migrate --seed
php artisan serve
```

Akun demo (dari seeder): customer `demo@markaspunokawan.test` / `demo12345`,
admin `admin@markaspunokawan.test` / `ganti-kata-sandi-ini` (ganti sebelum
produksi).

## 11. Revisi Broadcast WhatsApp (Template Pesan)

Konsep baru: Broadcast WhatsApp = **kumpulan template pesan** yang dibagikan
satu kali ke kontak yang dipilih pengguna **di dalam WhatsApp**. Tidak ada
daftar penerima, input nomor manual, import kontak, maupun personalisasi nama
tamu. Fitur **Guest Management & RSVP tetap utuh**.

### 11.1 Alur baru

1. Buka menu **WhatsApp**.
2. Buat / pilih template pesan.
3. Lihat pratinjau (variabel undangan langsung terisi).
4. Tekan **Bagikan melalui WhatsApp**.
5. Perangkat memakai **Web Share API** (`navigator.share`) bila tersedia;
   jika tidak, membuka **`https://wa.me/?text=...`** (URL-encoded).
6. Pengguna memilih sendiri kontak penerimanya; pesan sama untuk semua.
7. Aktivitas berbagi dicatat (bukan klaim "terkirim").

### 11.2 Yang dihapus / disederhanakan

- Variabel `{{nama_tamu}}` dari daftar variabel broadcast.
- Personalisasi per penerima, `buildMessages()`, dan model `BroadcastMessage`.
- Route `broadcast.generate` & `broadcast.mark`, aksi "tandai terkirim",
  serta tabel penerima/nomor di UI.
- Relasi `BroadcastCampaign::messages()`.

Yang **dipertahankan**: `render()` & `waUrl()` (dipakai Guest Management untuk
link personal per tamu), tabel `broadcast_campaigns` (sebagai penyimpan
template), dan tabel `broadcast_messages` secara fisik (non-destruktif, tidak
dipakai lagi).

### 11.3 Berkas yang diubah

- `app/Services/Wedding/WhatsAppTemplate.php`
  - `VARIABLES` kini tanpa `{{nama_tamu}}`.
  - `broadcastMap(Wedding)`: peta nilai variabel tingkat undangan.
  - `broadcastMessage(Wedding, string)`: pesan final broadcast — variabel
    diisi lalu sisa placeholder (termasuk sisa `{{nama_tamu}}`) dibuang.
  - `shareUrl(string)`: `https://wa.me/?text=` + `rawurlencode`, tanpa nomor.
  - `preview()` mengembalikan `['message', 'url']` tanpa data tamu.
  - `render()`/`waUrl()` dipertahankan untuk Guest Management.
  - `buildMessages()` dihapus.
- `app/Http/Controllers/Wedding/BroadcastController.php`
  - `index`, `store`, `show`, `update`, `share`, `destroy`.
  - `share()` mencatat aktivitas ke `WeddingActivityLog`
    (`event = broadcast_share`) dan mengembalikan `{ok, url}`; server tidak
    mengirim pesan apa pun.
  - `recentShares()` membaca riwayat dari `wedding_activity_logs`.
- `app/Models/BroadcastCampaign.php` — disederhanakan (tanpa `messages()`).
- `app/Models/BroadcastMessage.php` — **dihapus**.
- `routes/wedding.php` — `broadcast.index/store/show/update/share/destroy`
  (menggantikan `generate`/`mark`).
- `resources/views/wedding/broadcast/index.blade.php` — daftar template, form
  buat template, riwayat berbagi.
- `resources/views/wedding/broadcast/show.blade.php` — editor, pratinjau live,
  tombol **Salin** & **Bagikan melalui WhatsApp**, riwayat, hapus.
- `resources/views/wedding/dashboard/index.blade.php` — teks kartu pintasan
  WhatsApp.
- `tests/Unit/WhatsAppTemplateTest.php` — disesuaikan + tes `broadcastMessage`.
- `tests/Feature/BroadcastTest.php` — **baru** (buat/edit/hapus/pratinjau,
  berbagi + riwayat + URL `wa.me/?text=`, tanpa nama tamu, kepemilikan 404).

### 11.4 Mekanisme berbagi & privasi

- `navigator.share({ text })` dipanggil sinkron saat tombol ditekan (menjaga
  *user activation*); jika gagal/tidak ada → `window.open('https://wa.me/?text=...')`.
- Website **tidak** membaca/meminta kontak, tidak memakai WhatsApp Business API
  berbayar, tidak mengirim otomatis, dan tidak membuat mekanisme spam.
- Riwayat hanya menyimpan: nama template, isi pesan, waktu tombol bagikan
  ditekan, pengguna, dan kanal (`web_share`/`whatsapp`) — tanpa penerima dan
  tanpa klaim terkirim.

### 11.5 Hasil pengujian

- `php artisan test`: **121 lulus (496 asersi)**, termasuk 8 tes unit
  `WhatsAppTemplateTest` dan 8 tes fitur `BroadcastTest`.
- Verifikasi browser (SQLite sementara, login customer demo):
  - Buat template → diarahkan ke halaman editor; pratinjau mengisi
    `{{nama_pria}}`/`{{nama_wanita}}`/`{{tanggal_pernikahan}}`/`{{lokasi_acara}}`/`{{link_undangan}}`.
  - Template lama yang memuat `{{nama_tamu}}` → pratinjau menjadi
    `Assalamu'alaikum ,` (placeholder dibuang, **bukan** diisi nama tamu);
    daftar tombol variabel tidak menawarkan `{{nama_tamu}}`.
  - **Fallback** (tanpa `navigator.share`): membuka
    `https://wa.me/?text=...` (tanpa nomor tujuan, URL-encoded) dan mengirim
    `{channel:"whatsapp"}` ke endpoint share.
  - **Web Share API** (di-mock): memanggil `navigator.share({ text })` dengan
    pesan yang sama dan mengirim `{channel:"web_share"}`.
  - **Salin Pesan**: menyalin pesan hasil pratinjau; label berubah "Tersalin".
  - **Edit** menyimpan perubahan + flash "Template pesan diperbarui".
  - **Hapus** mengembalikan ke daftar + flash "Template pesan dihapus".
  - **Riwayat** menampilkan pengguna, waktu, dan kanal; baris
    `wedding_activity_logs` berisi `event=broadcast_share`, nama template, isi
    pesan, dan kanal.
  - Halaman **Daftar Tamu** dan **Konfirmasi Kehadiran** tetap terbuka tanpa
    error konsol.

## 12. Perbaikan Editor, Tanggal WIB, Nama Tamu `?to=`, dan Bagikan Satu per Satu

Revisi ini memperbaiki empat hal tanpa mengubah struktur modul, tanpa menghapus
data, dan tanpa mengubah alur publikasi/pembayaran yang sudah berjalan.

### 12.1 Penyebab masalah (root cause)

**Data editor hilang.** Ketika menyimpan satu bagian (mis. autosave identitas),
`syncEvents()`/`syncStories()`/`syncGifts()` di `InvitationController` selalu
memanggil `whereNotIn('id', [])->delete()`. Bila request tidak memuat array
`events`/`stories`/`gifts`, daftar kosong itu dianggap "hapus semua", sehingga
foto/rekening/kisah yang tidak ikut terkirim **terhapus**. Selain itu, tombol
**Ajukan Publikasi** tidak menyimpan data editor terlebih dahulu dan foto
ganda dapat terunggah ulang karena input berkas tidak dikosongkan setelah
autosave.

**Tanggal tidak konsisten.** Pesan WhatsApp memakai `translatedFormat` dengan
locale `en` (hari/bulan Inggris) dan tanpa penanda WIB.

**Nama tamu dari URL tidak dipakai.** Halaman publik hanya membaca nama dari
`?token=` yang valid; `?to=` (link tanpa membuat data tamu) tidak didukung.

**Berbagi hanya massal.** Belum ada cara membagikan undangan ke **satu**
penerima dengan link personal tanpa membuat data tamu.

### 12.2 Perbaikan penyimpanan editor

- `app/Http/Controllers/Wedding/InvitationController.php`
  - `validateEditor()` + `persistEditor()` dipakai bersama oleh **Simpan**,
    autosave, dan **Ajukan Publikasi**.
  - Relasi hanya disinkronkan bila request memang mengirim bagiannya, ditandai
    `sync_events`/`sync_stories`/`sync_gifts` (atau ada key array-nya). Editor
    selalu mengirim ketiga penanda, sehingga **mengosongkan** daftar tetap
    tersimpan, sedangkan autosave sebagian **tidak** menghapus data lain.
  - Seluruh penyimpanan dibungkus `DB::transaction()`.
  - Berkas baru disimpan **lebih dulu**, berkas lama baru dihapus; bila
    penyimpanan gagal, berkas lama tidak hilang.
  - `submitPublication()` menyimpan payload editor di dalam transaksi
    **sebelum** `markSubmitted()`; bila validasi/penyimpanan gagal, pengajuan
    tidak ditandai berhasil.
- `resources/views/wedding/invitations/edit.blade.php`
  - Penanda `sync_events`/`sync_stories`/`sync_gifts`.
  - `saveNow()` mengembalikan status berhasil/gagal; setelah unggah berhasil,
    input berkas dikosongkan dan `remove_photos` di-uncheck (mencegah foto
    ganda), thumbnail baru ditampilkan lewat `URL.createObjectURL`.
  - Tombol **Ajukan Publikasi** (di header, di luar cakupan Alpine) disambungkan
    lewat listener `#publish-link` → simpan dulu, lalu buka ringkasan.
  - Thumbnail foto mempelai/cover dan pemutar audio musik yang sudah tersimpan.

### 12.3 Tanggal & waktu Indonesia (WIB)

- `app/Models/WeddingEvent.php`: `timeRange()` → `Pukul 10.00 WIB` /
  `Pukul 10.00 – 12.00 WIB`; ditambah `WeddingEvent::clock()` (10.00).
- `app/Services/Wedding/WhatsAppTemplate.php`: `broadcastMap()` menghasilkan
  mis. `Minggu, 15 November 2026 pukul 10.00 WIB`.
- `app/Support/AdminWhatsApp.php`: `publicationMessage()` memakai
  `l, d F Y` + `H.i WIB`.
- `resources/views/wedding/templates/nirmala/index.blade.php`: jam `10.00`,
  "hingga", dan WIB.
- `resources/views/wedding/invitations/show.blade.php`: baris waktu acara
  memakai `timeRange()`.
- Locale `id` di `.env`/`.env.example`/`phpunit.xml`. Tidak ada penambahan jam
  manual dan tidak ada konversi zona waktu ganda.

### 12.4 Nama penerima via `?to=`

- `app/Http/Controllers/Wedding/PublicInvitationController.php`
  - `recipientName` dihitung dari `?token=` (bila valid, menang) atau `?to=`.
  - `recipientFromQuery()`: buang karakter kontrol, rapikan spasi, batasi 100
    karakter, default `Tamu Undangan`.
  - Header `X-Robots-Tag: noindex, nofollow` saat ada nama tamu/`?to=`.
  - Nama penerima **tidak** disimpan ke data undangan.
- Kedelapan template (`melati`, `senja`, `lestari`, `maharaja`, `arum`,
  `puspita`, `asmara`, `nirmala`) memakai `{{ $recipientName }}` dengan desain
  masing-masing tetap utuh.
- Contoh: `https://domain.com/wedding/andi-siti?to=Budi%20Santoso`.

### 12.5 Bagikan undangan satu per satu

- `resources/views/wedding/invitations/show.blade.php`: komponen Alpine
  `weddingShare(@js($shareData))` + fungsi `weddingShare()` di `@push('scripts')`.
- Alur: tulis nama penerima → link personal (`?to=`, URL-encoded) + pesan
  otomatis dibangun ulang per penerima → **Salin Link** → **Kirim via WhatsApp**
  (Web Share API `navigator.share` bila ada, fallback `wa.me/?text=`) → **Tamu
  Berikutnya** mengosongkan nama untuk penerima selanjutnya.
- Tidak mengirim otomatis, tidak mengakses kontak, tidak mengklaim "terkirim",
  dan tidak membuat data tamu. Tombol hanya muncul saat undangan sudah terbit.

### 12.6 Hasil pengujian

- `php artisan test`: **143 lulus (601 asersi)**.
  - **Unit** `tests/Unit/WhatsAppTemplateTest.php`: `broadcastMap()` menghasilkan
    `Selasa, 24 Agustus 2027 pukul 08.00 WIB`; `timeRange()` →
    `Pukul 10.00 WIB` / `Pukul 10.00 – 12.00 WIB`; `clock()` → `08.00`.
  - **Feature** `tests/Feature/InvitationEditorPersistenceTest.php` (baru):
    simpan lengkap (berkas + rekening + kisah), autosave sebagian tidak menghapus
    relasi lain, mengosongkan daftar dengan penanda sync tetap tersimpan, editor
    menampilkan kembali data tersimpan, Ajukan Publikasi menyimpan lebih dulu,
    gagal validasi tidak menandai terkirim, berkas lama tidak hilang saat gagal,
    mengganti foto menghapus berkas lama.
  - **Feature** `tests/Feature/PublicInvitationTest.php`: `?to=` tampil; token
    tidak valid → `?to=` dipakai; kosong/spasi → `Tamu Undangan`; multi kata +
    karakter Indonesia; escape XSS; tidak disimpan; berlaku di **8 template**.
  - **Feature** `tests/Feature/WhatsAppShareTest.php` (baru): UI bagikan satu per
    satu (termasuk `navigator.share`, `wa.me/?text=`, `encodeURIComponent`),
    draf menyembunyikan kontrol, dan pesan pengajuan ke admin memakai
    `Minggu, 15 November 2026 pukul 10.00 WIB`.
- Verifikasi browser (SQLite sementara, login customer demo):
  - Editor: mengubah caption + judul musik → **Simpan** → reload → nilai tetap
    ada; tombol **Ajukan Publikasi** menyimpan dulu lalu membuka ringkasan.
  - Galeri: mengunggah 1 foto → input berkas dikosongkan → reload → jumlah foto
    naik tepat 1 (tidak ganda).
  - Halaman publik `?to=Budi%20Santoso` menampilkan
    "Kepada ... Budi Santoso"; tanpa `?to=` menampilkan "Tamu Undangan";
    `?to=<script>...` ditampilkan sebagai teks (tanpa tag script).
  - Ringkasan: `weddingShare` aktif; link personal `...?to=Budi%20Santoso`;
    karakter Indonesia ter-encode; pesan mengganti `__LINK__`; fallback
    `https://wa.me/?text=...`; `navigator.share({text,url})` terpanggil;
    **Tamu Berikutnya** mengosongkan nama.
  - Template: jam acara tampil `Pukul 08.00 – 10.00 WIB`; Nirmala menampilkan
    `08.00 HINGGA 10.00 WIB`.

### 12.7 Yang belum diuji / catatan

- Pengiriman **nyata** ke WhatsApp (membuka aplikasi WhatsApp/berbagi perangkat)
  tidak dapat diotomasi; yang diuji hanya pembentukan link/pesan dan pemanggilan
  API-nya.
- Berbagi satu per satu **tidak** mencatat riwayat (tidak ada route/log baru);
  riwayat berbagi tetap hanya milik fitur Broadcast (bagian 11).
- Validasi memakai SQLite (bukan MySQL) karena MySQL lokal tidak tersedia;
  perilaku kolom `NOT NULL` sudah dicocokkan dengan skema migrasi.
- Pesan validasi bawaan Laravel tetap Inggris (tidak ada direktori `lang/`).

## 13. Foto Mempelai + Inisial, Musik di Semua Template, Geser Galeri, dan Kunci Akses Publik

Revisi ini mengerjakan tiga prioritas: **penyimpanan foto mempelai + fallback
inisial**, **musik di kedelapan template**, dan **mengunci akses publik hanya
untuk undangan yang sudah dipublikasikan Admin**. Semua fitur yang sudah
berjalan dipertahankan; tidak ada pembangunan ulang modul, tidak ada perubahan
prefix `/wedding`, dan tidak ada perubahan alur pembayaran/persetujuan manual.

### 13.1 Penyebab masalah foto mempelai (root cause)

- **Placeholder demo menutupi hasil unggahan.** Saat `groom_photo`/`bride_photo`
  kosong, template memakai gambar demo generik. Akibatnya foto yang gagal/tidak
  ikut tersimpan tetap terlihat "ada" (memakai gambar demo), sehingga kesannya
  foto tidak benar-benar tersimpan. Selain itu tidak ada indikator bahwa foto
  sudah tersimpan maupun cara menghapusnya.
- **Autosave sebagian menghapus bagian lain.** `persistEditor()` selalu menulis
  ulang daftar `sections`; bila form tidak mengirim `sections`, seluruh section
  menjadi nonaktif sehingga blok mempelai/galeri bisa hilang saat menyimpan
  bagian lain.
- **Foto tidak bisa dihapus.** Belum ada aksi menghapus foto; sekali terunggah,
  satu-satunya cara mengganti adalah mengunggah ulang.

### 13.2 Perbaikan foto & mekanisme inisial

- `app/Models/Wedding.php`
  - `groomInitials()`, `brideInitials()`, dan `initials(?string $name)` statis.
  - Aturan inisial: ambil **kata pertama + kata terakhir**; satu kata → **dua
    huruf bermakna pertama**; lebih dari dua kata → **kata pertama + terakhir**;
    buang tanda baca/spasi berlebih; `mb_strtoupper`; **tidak pernah kosong**
    (nama kosong/tanpa huruf → `?`). Contoh: `Ahmad Fauzi` → `AF`,
    `Siti Rahma` → `SR`, `Siti` → `SI`, `R. Ahmad Fauzi` → `RF`.
  - `groomPhotoUrl()`/`bridePhotoUrl()` mengembalikan `null` bila tidak ada foto
    (tidak lagi memakai gambar demo).
- Kedelapan template (`melati`, `senja`, `lestari`, `maharaja`, `arum`,
  `puspita`, `asmara`, `nirmala`): variabel foto mempelai **nullable**, dan
  markup memakai `@if($foto) <img …> @else <span class="{tpl}-initials"
  aria-hidden="true">{{ …Initials() }}</span> @endif`. Setiap template punya
  kelas + gaya inisial sendiri (bentuk, ukuran, border, warna mengikuti desain
  masing-masing) sehingga kedelapan template **tidak identik**. Inisial selalu
  mengikuti nama tersimpan dan berubah bila nama diubah.
- `app/Http/Controllers/Wedding/InvitationController.php`
  - Validasi `remove_groom_photo`/`remove_bride_photo`/`remove_cover_photo`.
  - `persistEditor()`: unggahan baru disimpan lebih dulu (menang atas permintaan
    hapus); permintaan hapus menghapus berkas fisik lalu mengosongkan kolom;
    foto mempelai yang kosong otomatis tampil sebagai inisial.
  - Section hanya ditimpa bila form mengirim `sync_sections`/`sections`
    (penanda `sync_sections` ditambahkan pada form editor), sehingga autosave
    sebagian tidak mematikan blok mempelai/galeri.
- `resources/views/wedding/invitations/edit.blade.php`
  - Checkbox **Hapus foto (tampilkan inisial nama)** untuk mempelai pria/wanita
    dan **Hapus foto cover**; muncul hanya bila foto tersimpan.
  - `saveNow()` mengosongkan input berkas, meng-uncheck penghapusan, dan
    memperbarui thumbnail agar tidak ada unggahan ganda.

### 13.3 Musik di kedelapan template

- Audit: kedelapan template **sudah** memiliki `<audio id="tw-music">` +
  `data-music-toggle` + tombol dengan kelas per template. Yang ditambahkan:
  - Editor: hapus musik (`remove_music`) — referensi DB dibersihkan **dan**
    berkas fisik dihapus; musik lama tetap tersimpan saat menyimpan bagian lain;
    mengganti musik menghapus berkas lama **setelah** berkas baru berhasil
    disimpan.
  - `invitation-core.js`: indikator `is-playing` + animasi per template,
    `aria-pressed`, dan **penanganan gagal muat** (event `error` → kontrol
    disembunyikan agar tidak menyesatkan).
  - Autoplay tidak diblokir: pemutaran dipicu oleh **gestur pengguna** saat
    cover dibuka (`safePlay` menelan penolakan autoplay). Hanya **satu** elemen
    audio per halaman, jadi tidak ada suara ganda. Kontrol tidak dirender bila
    tidak ada musik. Aman pada perangkat seluler maupun desktop.

### 13.4 Geser/galeri (drag & swipe)

- Hanya **senja** (track `[data-slide-track]` + tombol `[data-slide]`) dan
  **nirmala** (filmstrip `[data-drag-scroll]`) yang berupa slider; enam template
  lain berupa grid statis dengan lightbox (tidak diubah).
- `invitation-core.js` menyatukan logika dalam satu helper `enableDragScroll`
  (dipakai untuk `[data-slide-track]` dan `[data-drag-scroll]`); handler ganda
  di `senja.js`/`nirmala.js` dihapus.
  - Ambang **6 px** sebelum pointer di-capture → klik biasa tetap membuka
    lightbox; geser mulus (momentum) saat dilepas; klik tepat setelah geser
    ditelan agar lightbox tidak terbuka tak sengaja.
  - `touch-action: pan-y` + `overscroll-behavior-x: contain` → scroll vertikal
    halaman **tidak** terganggu; roda vertikal menggulir halaman, roda horizontal
    menggeser galeri.
  - Saat menggeser, `scroll-snap-type` dinonaktifkan agar tidak "menarik" balik;
    jaring pengaman melepas kelas drag bila animasi dihentikan (tab tidak aktif).
- **Bug tombol panah senja ditemukan & diperbaiki:** tombol `[data-slide]` berada
  di `.sen-slider-nav` sedangkan track-nya **bersaudara** (bukan di dalamnya),
  sehingga `$('[data-slide-track]', slider)` selalu gagal dan panah tidak pernah
  berfungsi. Kini track dicari ke atas sampai wadah yang memuatnya
  (`findSlideTrack`).

### 13.5 Mengunci akses publik untuk undangan belum terbit

- **Penyebab masalah:** `PublicInvitationController::show()` dulu memberi
  pengecualian bagi pemilik (`owner bypass`) dan `isPreview` tidak dipaksa,
  sehingga tautan draf bisa terbuka. Kini **setiap** permintaan ke
  `/wedding/{slug}` dicek: hanya `status === published` yang dilayani, selain itu
  `abort(404)` — termasuk pemilik. `isPreview`/`previewMode` pada jalur publik
  di-*hardcode* `false`.
- Seluruh endpoint publik memakai penjaga status yang sama:
  - `POST /wedding/{slug}/rsvp` dan `POST /wedding/{slug}/ucapan` memakai
    `Wedding::where('slug',$slug)->where('status', STATUS_PUBLISHED)->firstOrFail()`
    → draf/revisi/`pembayaran_dikonfirmasi` menghasilkan **404** dan tidak
    membuat RSVP/ucapan.
  - Tidak ada route publik lain yang menyentuh data undangan (data tamu, ucapan,
    RSVP) selain ketiga route di atas.
- **Pratinjau internal** dibedakan dari halaman publik:
  - Pemilik: `GET /wedding/invitations/{wedding}/preview` (middleware
    `auth`+`role:customer` + `Gate::authorize('view', $wedding)`); boleh
    menampilkan draf terbaru, memakai header `noindex`.
  - Admin: `GET /wedding/admin/publications/{wedding}/preview` untuk verifikasi.
  - Tamu anonim diarahkan ke `/wedding/login`.
- **Publikasi hanya oleh Admin.** Tidak ada route `publish` untuk customer.
  Konfirmasi pembayaran **tidak** otomatis mempublikasikan. Mempublikasikan
  membuka akses; **menarik publikasi** (`markUnpublished()` +
  `Admin\PublicationController::unpublish()` + route
  `wedding.admin.publications.unpublish` + UI di `publication-show.blade.php`)
  langsung menutup akses (status → `draft`, `published_at` dikosongkan). Pengecekan
  memakai kolom status terkini (bukan slug/pembayaran), sehingga cache lama pun
  tidak bisa menahan draf tetap publik.
- Halaman publik tetap tanpa login dan mendukung `?to=` setelah terbit.

### 13.6 Berkas yang diubah

- `app/Models/Wedding.php` — `groomInitials()`/`brideInitials()`/`initials()`,
  `markUnpublished()`, foto nullable.
- `app/Http/Controllers/Wedding/InvitationController.php` — penghapusan foto,
  penjagaan `sync_sections`, validasi baru.
- `app/Http/Controllers/Wedding/PublicInvitationController.php` — publik hanya
  `published`.
- `app/Http/Controllers/Wedding/Admin/PublicationController.php` — `unpublish()`.
- `routes/wedding.php` — route `publications.unpublish`.
- `resources/views/wedding/invitations/edit.blade.php` — checkbox hapus foto,
  `sync_sections`, penanganan `saveNow`.
- `resources/views/wedding/admin/publication-show.blade.php` — tombol tarik
  publikasi.
- `resources/views/wedding/templates/{melati,senja,lestari,maharaja,arum,puspita,asmara,nirmala}/index.blade.php`
  — fallback inisial + foto nullable.
- `public/wedding-assets/assets/js/invitation-core.js` — `enableDragScroll`,
  `findSlideTrack`, penanganan error audio.
- `public/wedding-assets/assets/templates/senja.js`, `nirmala.js` — handler drag
  ganda dihapus.
- `public/wedding-assets/assets/templates/{…8…}.css` — gaya inisial per template,
  animasi `is-playing`, `touch-action`/snap untuk senja & nirmala.
- **Tidak ada migrasi baru** pada revisi ini (semua memakai kolom/tabel yang
  sudah ada).

### 13.7 Hasil pengujian

- `php artisan test`: **159 lulus (721 asersi)**.
  - **Unit** `tests/Unit/WeddingInitialsTest.php` (baru): dua kata → `AF`/`SR`,
    satu kata → dua huruf, >2 kata → kata pertama+terakhir, pembersihan
    spasi/tanda baca, dan **tidak pernah kosong** (`?`).
  - **Feature** `tests/Feature/PublicInvitationTest.php`: draf 404 untuk publik
    **dan** pemilik; pratinjau pemilik OK, customer lain 404, tamu → login; draf
    menolak RSVP & ucapan (tidak membuat baris); `pembayaran_dikonfirmasi` belum
    tentu publik; publikasi membuka akses; tarik publikasi menutup akses;
    customer tidak bisa mempublikasikan; status `revisi` ditolak; **inisial
    tampil di kedelapan template** (`>AF<`/`>SR<`, tanpa gambar demo); inisial
    mengikuti perubahan nama.
  - **Feature** `tests/Feature/InvitationEditorPersistenceTest.php`: menghapus
    satu foto mempelai → inisial untuk yang dihapus, foto lain **tetap**;
    musik unggah → ganti (berkas lama terhapus) → hapus (berkas fisik terhapus);
    musik lama tetap ada saat menyimpan bagian lain.
- **Verifikasi browser** (server SQLite sementara):
  - **Senja**: geser mouse (scrollLeft 24 → 624/1158), swipe sentuh
    (24 → 1128), tombol panah (`scrollBy` +820 / −820), klik biasa membuka
    lightbox, klik **setelah** geser **tidak** membuka lightbox, `is-dragging`
    melepas `scroll-snap` dan pulih otomatis, `touch-action: pan-y`.
  - **Nirmala**: filmstrip digeser (0 → 550), klik setelah geser ditelan,
    `touch-action: pan-y`.
  - **Musik** (template senja): hanya **satu** `<audio>`, `loop`, tombol
    play → `is-playing`+`aria-pressed=true`, klik lagi → pause, klik lagi → play;
    event `error` menyembunyikan kontrol; membuka cover memicu upaya `play`.
  - **Inisial** (nirmala, foto dikosongkan): tampil `RA`/`AL`, tanpa gambar demo.
  - **Akses publik** (HTTP langsung): `/wedding/uji-draf` → **404**;
    `/wedding/rangga-ayu` → **200**; `/wedding/invitations/2/preview` tanpa login
    → **302** ke `/wedding/login`; `?to=Bapak Budi` tampil di halaman publik.
  - Tidak ada error konsol pada halaman senja maupun nirmala.

### 13.8 Yang belum diuji / catatan

- Unggahan berkas melalui UI browser (pemilih berkas OS) tidak diotomasi; alur
  unggah/ganti/hapus foto & musik diuji lewat feature test yang menembus
  controller asli (menyimpan ke disk `public` palsu) dan diverifikasi tampil di
  halaman publik.
- Pengiriman RSVP/ucapan ke draf diuji lewat feature test (CSRF dinonaktifkan);
  di server langsung, request tanpa token CSRF lebih dulu ditolak `419`.
- MySQL lokal tetap tidak tersedia; pengujian memakai SQLite (in-memory untuk
  test, file sementara untuk smoke browser).

## 14. Perbaikan Validasi Unggah Musik (MP3 Tertentu Ditolak)

Revisi ini memperbaiki satu masalah: sebagian berkas **MP3 yang sah** ditolak
dengan pesan `The music field must be a file of type: mp3, wav, m4a, ogg.`,
padahal MP3 lain berhasil diunggah. Tidak ada perubahan pada alur publikasi,
prefix `/wedding`, template, maupun proses pembayaran.

### 14.1 Penyebab sebenarnya (root cause)

Validasi lama memakai aturan bawaan Laravel `mimes:mp3,wav,m4a,ogg`. Aturan ini
**tidak memeriksa ekstensi nama berkas**, melainkan menebak ekstensi dari MIME
hasil deteksi server (finfo/libmagic):

```
UploadedFile::guessExtension()
  = MimeTypes::getExtensions( finfo->file($path) )[0]
```

Untuk `mimes`, Laravel mengambil ekstensi **pertama** dari peta Symfony
`MimeTypes` berdasarkan MIME yang terdeteksi. Sebagian berkas MP3 yang sah
dideteksi libmagic sebagai `application/octet-stream` (mis. tag ID3v2 dengan
ukuran *synchsafe* yang bit tingginya menyimpang, ada padding/BOM di awal, atau
header Xing/VBR tertentu). `application/octet-stream` tidak ada di peta → hasil
`guessExtension()` menjadi `bin` (atau MIME `video/mpeg` → `mpeg`) → tidak ada
di daftar `mp3,wav,m4a,ogg` → **ditolak**, meskipun isi berkasnya MP3 valid.

Perbandingan hasil pemeriksaan (berkas gagal vs berhasil):

| Pemeriksaan | MP3 gagal (Westlife) | MP3 berhasil |
|---|---|---|
| `getClientOriginalName()` | `Westlife - Beautiful in white (Lyrics).mp3` | `lagu.mp3` |
| `getClientOriginalExtension()` | `mp3` | `mp3` |
| `extension()` | **`bin`** | `mp3` |
| `getMimeType()` (finfo) | **`application/octet-stream`** | `audio/mpeg` |
| `getClientMimeType()` (dari browser) | `audio/mpeg` | `audio/mpeg` |
| `guessExtension()` | **`bin`** | `mp3` |
| Signature isi berkas | **MP3 (ID3/frame MPEG)** | MP3 (ID3/frame MPEG) |

Kesimpulan: perbedaannya **bukan** pada nama/ekstensi (keduanya `.mp3`),
melainkan pada **hasil deteksi MIME server** — berkas valid dideteksi
`application/octet-stream` sehingga ekstensi tebakan menjadi `bin` dan ditolak.

Bug turunan yang ikut ditemukan: `store()` juga memakai `guessExtension()`,
sehingga MP3 `application/octet-stream` yang lolos akan tersimpan sebagai
`*.bin` dan dilayani `Content-Type: application/octet-stream` — berkas tidak
dapat diputar meski lolos validasi.

### 14.2 Aturan validasi yang diubah

- **Bukan lagi** `mimes:mp3,wav,m4a,ogg` (berbasis MIME server).
- **Baru**: aturan kustom `App\Rules\AudioFile` dengan urutan:
  1. Berkas unggahan sah (`instanceof UploadedFile` && `isValid()`).
  2. Ukuran ≤ batas `config('wedding.limits.audio_kb')` (10 MB) — pesan
     Indonesia, batas ditampilkan manusiawi (`10 MB`).
  3. Ekstensi **nama** berkas harus `mp3`/`wav`/`m4a`/`ogg` (syarat pertama;
     tidak dipercaya sendirian).
  4. **Verifikasi isi berkas** lewat `App\Support\AudioFormat::detect()`:
     - Deteksi **signature** dari byte awal: `RIFF`+`WAVE`/`RF64` → `wav`;
       `OggS` → `ogg`; kotak `ftyp` dengan brand audio (`M4A `, `mp42`, `isom`,
       …) → `m4a`; `ID3` → `mp3`; atau minimal **dua frame MPEG berurutan** yang
       konsisten (versi/layer/bitrate/sample-rate + panjang frame dihitung) →
       `mp3`.
     - Bila signature tidak dikenali, baru jatuh ke MIME `audio/*` (+
       `application/ogg`) **dengan syarat** ekstensi nama didukung.
  5. Berkas non-audio yang hanya diganti ekstensi menjadi `.mp3` **tetap
     ditolak** (isi tidak dikenali signature maupun MIME audio). MIME
     `application/octet-stream` **tidak** diterima tanpa bukti signature/MIME
     audio.
- Pesan (Bahasa Indonesia):
  - Format: `Format musik tidak didukung. Silakan gunakan MP3, WAV, M4A, atau OGG.`
  - Ukuran: `Ukuran file musik terlalu besar. Maksimal ukuran file adalah 10 MB.`
- Penyimpanan: `persistEditor()` menyimpan berkas dengan **ekstensi hasil
  deteksi isi** (`Str::random(40).'.'.AudioFormat::detect($file)`), bukan
  `guessExtension()`, sehingga MP3 `application/octet-stream` tetap tersimpan
  sebagai `.mp3` dan dilayani `Content-Type: audio/mpeg`. Urutan aman
  dipertahankan: berkas **baru** disimpan lebih dulu, berkas lama baru dihapus
  setelah berhasil.

### 14.3 Berkas source code yang diperbaiki

- `app/Support/AudioFormat.php` — **baru**: detektor audio bersama
  (`EXTENSIONS`, `mimeLooksLikeAudio()`, `signatureFormat()`, `detect()`,
  validasi frame MPEG + tabel bitrate/sample-rate).
- `app/Rules/AudioFile.php` — **baru**: aturan validasi kustom (`ValidationRule`)
  dengan pesan Indonesia + batas ukuran; memakai `AudioFormat`.
- `app/Http/Controllers/Wedding/InvitationController.php`
  - `validateEditor()`: `'music' => ['nullable', new AudioFile((int) config('wedding.limits.audio_kb'))]`
    (menggantikan `mimes:…` + `max`).
  - `persistEditor()`: simpan musik memakai ekstensi hasil `AudioFormat::detect()`.
  - Import `App\Support\AudioFormat`, `App\Rules\AudioFile`, `Illuminate\Support\Str`.
- `tests/Feature/InvitationEditorPersistenceTest.php` — 4 tes musik baru:
  MP3 `application/octet-stream` diterima (+ tersimpan `.mp3` + byte identik),
  empat format diterima (ekstensi benar), `.mp3` palsu & file melebihi batas
  ditolak dengan pesan Indonesia persis, musik lama tetap ada saat gagal, dan
  undangan publik memuat `id="tw-music"`.
- `tests/Unit/AudioFormatTest.php` — **baru**: deteksi signature mp3/wav/m4a/ogg,
  menolak teks & PNG, serta whitelist MIME.
- Tidak ada migrasi baru (memakai kolom `weddings.music_path`/`music_title` yang
  sudah ada).

### 14.4 Hasil pengujian

`php artisan test`: **168 lulus (775 asersi)**.

Uji langsung di browser (server SQLite sementara, login customer demo,
mengunggah berkas nyata lewat form editor):

| # | Berkas uji | Hasil | Bukti |
|---|---|---|---|
| 1 | `lagu-octet.mp3` (MP3 sah, MIME `application/octet-stream`, **sebelumnya gagal**) | **Diterima** | HTTP 200 `{"ok":true}`; tersimpan `…mp3`; sha1 byte-identik `f976a77b…`; `Content-Type: audio/mpeg` |
| 2 | `lagu-normal.mp3` (MP3 `audio/mpeg`, sebelumnya berhasil) | **Diterima** | HTTP 200; tersimpan `…mp3`; `Content-Type: audio/mpeg` |
| 3 | `lagu.wav` (WAV) | **Diterima** | tersimpan `…wav`; `Content-Type: audio/x-wav` |
| 4 | `lagu.m4a` (M4A) | **Diterima** | tersimpan `…m4a` |
| 5 | `lagu.ogg` (OGG) | **Diterima** | tersimpan `…ogg`; `Content-Type: audio/ogg` |
| 6 | `lagu-palsu.mp3` (bukan audio, hanya diganti ekstensi) | **Ditolak** | HTTP 422, pesan `Format musik tidak didukung. Silakan gunakan MP3, WAV, M4A, atau OGG.`; musik lama (OGG) tetap utuh |
| 7 | File melebihi batas (11.000 KB) | **Ditolak** | pesan `Ukuran file musik terlalu besar. Maksimal ukuran file adalah 10 MB.` (via feature test; unggahan browser dibatasi 5 MiB oleh server) |

- Halaman **pratinjau** (`/wedding/invitations/2/preview`) memuat
  `<audio id="tw-music" src="…/storage/wedding/2/music/…ogg">` — berkas tersimpan
  benar-benar terhubung ke pemutar.
- Verifikasi bahwa berkas `application/octet-stream` **tidak** tersimpan sebagai
  `.bin` (bug lama) dan dapat diputar (`Content-Type: audio/mpeg`).

### 14.5 Yang belum diuji / catatan

- Uji berkas melebihi batas lewat browser tidak dapat dilakukan karena batas
  total unggahan server 5 MiB lebih kecil dari batas musik 10 MB; kasus ini
  diuji lewat feature test yang menembus controller asli.
- Tidak ada direktori `lang/`, sehingga pesan validasi bawaan Laravel lain tetap
  berbahasa Inggris; hanya pesan musik yang dipastikan Indonesia (sesuai lingkup
  permintaan).

## 15. Logo Resmi Markas Punokawan & Tombol WhatsApp Admin pada Pengajuan Publikasi

Revisi ini memakai **logo resmi Markas Punokawan** sebagai identitas platform di
seluruh tampilan utama, dan menghubungkan **nomor WhatsApp admin**
(`085155269940` → `6285155269940`) ke alur pengajuan publikasi undangan lewat
tombol **Hubungi Admin via WhatsApp**. Alur publikasi manual, 8 template, prefix
`/wedding`, dan aturan "hanya Admin yang mempublikasikan" **tidak diubah**.

### 15.1 Lingkup

1. **Identitas logo (platform-wide, bukan hanya Wedding).** Logo resmi dipakai
   apa adanya: bentuk, proporsi, warna, dan komposisinya tidak diubah, tidak
   direkayasa ulang dengan CSS/teks/ikon/generatif, dan tidak diregangkan.
2. **Nomor WhatsApp admin terpusat.** Nomor hanya didefinisikan di satu tempat
   (`config/wedding.php` → `admin_whatsapp`, bersumber dari `env`), tidak
   ditulis ulang di banyak berkas Blade/Controller/JS.
3. **Alur pengajuan + tombol WhatsApp.** Setelah customer mengajukan publikasi,
   tampil tombol WhatsApp dengan pesan siap kirim. Membuka WhatsApp **tidak**
   mengubah status menjadi terbit dan **tidak** membuat pengajuan baru.

### 15.2 Aset logo (lokasi berkas & spesifikasi)

Berkas sumber: `D:\Downloads\WhatsApp Image 2026-11-17 at 21.12.04.jpeg`
(foto/JPEG logo resmi berbagi lewat WhatsApp).

Aset turunan yang dipakai aplikasi (semuanya PNG transparan):

| Berkas | Ukuran | Keterangan |
|---|---|---|
| `public/brand/markas-punokawan-logo.png` | 568 × 221 px (RGBA, alpha) | **Satu-satunya sumber logo utama.** Dipakai semua navbar/footer/dasbor/auth. |
| `public/brand/favicon.png` | 256 × 256 px (RGBA) | Turunan favicon (letterbox logo utuh di kanvas persegi, latar transparan). |
| `public/favicon.ico` | 64 × 64 px (ICO berbasis PNG) | Favicon standar browser. Menggantikan berkas `public/favicon.ico` lama (0 byte). |

Cara pembuatan aset (tanpa GD — lingkungan tidak punya ekstensi GD):

1. **Crop** ke kotak logo saja: `x=356..923`, `y=509..729` → 568 × 221 px
   (rasio ~2.57:1). Kotak ini memuat satu komposisi utuh (emblem + wordmark +
   subjudul) tanpa memotong bagian mana pun.
2. **Latar hitam → transparan** dengan *un-premultiply*: `alpha = max(R,G,B)`,
   `color = asli × 255 / alpha`, dan `alpha ≤ 8` dibuat sepenuhnya transparan.
   Hasilnya logo transparan **tanpa halo** dan warna asli tetap utuh.
3. Logo bukan dua elemen terpisah (tidak ada celah vertikal bersih antara emblem
   dan wordmark), sehingga favicon dibuat sebagai **letterbox dari logo utuh**,
   bukan potongan emblem saja.

Proporsi asli (568:221) dijaga di semua tempat lewat CSS `height: …; width:auto`
— tidak ada `width`/`height` paksa yang membuat logo gepeng.

### 15.3 Bagian aplikasi yang memakai logo

Semua tampilan yang benar-benar dirutekan sudah memakai logo resmi (tidak ada
sisa placeholder `MP` / `.brand-mark` / `.mark`):

| Bagian | Berkas | Ukuran tampil |
|---|---|---|
| Beranda platform (`/`) navbar | `resources/views/platform/home.blade.php` | `.brand-logo` (40 px; 32 px di mobile ≤560 px) |
| Beranda modul Wedding (`/wedding`) navbar + footer | `resources/views/wedding/layouts/site.blade.php` | `.brand-logo` 40 px / 32 px mobile |
| Login & registrasi (`/wedding/login`) panel kiri + mobile | `resources/views/wedding/layouts/auth.blade.php` | `h-10 w-auto` |
| Dasbor customer sidebar | `resources/views/wedding/layouts/dashboard.blade.php` | `h-9 w-auto` (label "Wedding Dashboard" tetap) |
| Panel Super Admin sidebar | `resources/views/wedding/layouts/admin.blade.php` | `h-9 w-auto` (label "Wedding Admin" tetap) |
| Pratinjau template (`/wedding/templates/{slug}/preview`) | `resources/views/wedding/public/template-show.blade.php` | `.brand-logo` 40 px / 32 px mobile |
| Favicon (semua halaman di atas) | semua layout | `favicon.ico` + `brand/favicon.png` + `apple-touch-icon` |

CSS: `public/wedding-assets/assets/css/site.css` mengganti `.brand-mark` menjadi
`.brand-logo { height:40px; width:auto; display:block }` dan menambahkan
`.brand-logo { height:32px }` pada `@media (max-width:560px)`.

Pemusatan identitas: `config/brand.php` + `app/Support/Brand.php`
(`name`, `logo()`, `logoAlt()`, `logoRatio()`, `favicon()`, `faviconPng()`),
sehingga hanya ada **satu sumber logo** untuk seluruh aplikasi.

### 15.4 Nomor WhatsApp admin terpusat

- `config/wedding.php`:
  `'admin_whatsapp' => env('WEDDING_ADMIN_WHATSAPP', '6285155269940')`.
- `.env` dan `.env.example`: `WEDDING_ADMIN_WHATSAPP=6285155269940`.
- `app/Support/AdminWhatsApp.php`: satu tempat pembentukan pesan & URL
  (`number()`, `publicationMessage()`, `publicationUrl()`, `generalMessage()`,
  `generalUrl()`, `customerUrl()`), memakai `App\Support\Phone` untuk
  normalisasi dan `rawurlencode` untuk pesan.
- Nomor admin yang bisa diubah admin tetap diprioritaskan lewat
  `WeddingSetting::get('admin_whatsapp', config('wedding.admin_whatsapp'))`;
  bila pengaturan admin belum diisi, nilai `env` `6285155269940` yang dipakai.
  `SettingController` memakai `config('wedding.admin_whatsapp')` sebagai nilai
  bawaan form (tidak ada lagi nomor hardcoded).
- URL selalu berformat `https://wa.me/6285155269940?text=…` — **tanpa** `+`,
  spasi, atau tanda hubung di nomor.

### 15.5 Alur pengajuan + tombol WhatsApp

1. Customer melengkapi undangan lalu menekan **AJUKAN PUBLIKASI**. Data
   **disimpan lebih dulu** ke database; bila penyimpanan gagal, tidak ada pesan
   sukses dan pengajuan tidak dibuat.
2. Setelah pengajuan tersimpan (status `menunggu_konfirmasi`), halaman ringkasan
   dan dasbor menampilkan tombol **Hubungi Admin via WhatsApp** dengan catatan:
   > Silakan hubungi admin melalui WhatsApp untuk mendapatkan informasi
   > pembayaran dan proses publikasi undangan Anda.
3. Tombol membuka `https://wa.me/6285155269940?text=…` (WhatsApp / WhatsApp Web)
   dengan pesan yang sudah terisi. **Customer mengirim pesan secara manual** —
   tidak ada pengiriman otomatis dan tidak ada klaim "pesan terkirim".
4. **Membuka WhatsApp tidak mengubah status.** Status tetap menunggu konfirmasi
   admin; hanya Admin yang memverifikasi pembayaran dan **mempublikasikan**
   undangan. Karena tombol hanyalah tautan keluar (bukan aksi server), mengklik
   berulang kali **tidak** membuat pengajuan baru.
5. Di **dasbor customer**, selama komunikasi masih dibutuhkan
   (`submission_status` bukan `draft`/`dipublikasikan`), ditampilkan status
   pengajuan + status pembayaran + tombol WhatsApp. Bila tidak ada pengajuan
   aktif, ditampilkan tombol WhatsApp umum. **Dasbor admin tidak diubah** — info
   desain/template tetap terlihat dan wewenang admin atas konfirmasi pembayaran
   serta publikasi tetap sama.

### 15.6 Contoh pesan WhatsApp yang dihasilkan

Contoh nyata (undangan demo `rangga-ayu`, template Melati, status
`menunggu_pembayaran`; URL di-encode dengan `rawurlencode`):

```
Assalamu'alaikum Admin Markas Punokawan.

Saya ingin mengajukan publikasi undangan pernikahan melalui Markas Punokawan.

Berikut informasi pengajuan saya:

Nama Mempelai:
Rangga Adiputra & Ayu Larasati

Desain Undangan:
Melati

Kategori Desain:
Classic Floral Wedding — Nuansa klasik yang lembut dengan ornamen melati,
bingkai dekoratif, dan tekstur kertas premium.

Link Preview Undangan:
http://<APP_URL>/wedding/admin/publications/1/preview

Slug Undangan:
rangga-ayu

Status Pengajuan:
Menunggu Pembayaran

Waktu pengajuan: Rabu, 18 November 2026 pukul 03.19 WIB

Mohon informasi mengenai proses pembayaran dan tahapan selanjutnya agar
undangan saya dapat segera diterbitkan.

Terima kasih.
```

Catatan pesan:

- Memuat **nama mempelai aktual**, **nama template**, **konsep/deskripsi
  template** (bila ada), **link pratinjau internal yang aman** (route admin
  berautentikasi `wedding.admin.publications.preview`), **slug**, dan **status
  pengajuan saat ini**.
- Field opsional yang kosong **dilewati** (tidak dicetak sebagai baris kosong).
- Pesan berbahasa Indonesia, karakter khusus & baris baru di-encode
  (`&`→`%26`, baris baru→`%0A`).
- **Tidak pernah** memuat kata sandi, token, atau API key.

### 15.7 Berkas yang diubah / ditambah

**Baru:**

- `config/brand.php`, `app/Support/Brand.php`
- `resources/views/wedding/partials/whatsapp-icon.blade.php` (ikon WhatsApp
  inline-SVG; Lucide tidak menyediakan glyph WhatsApp)
- `public/brand/markas-punokawan-logo.png`, `public/brand/favicon.png`
- `tests/Feature/BrandAndWhatsAppIntegrationTest.php` (14 tes)

**Diubah:**

- `app/Support/AdminWhatsApp.php` (pesan & URL pengajuan/umum terpusat)
- `config/wedding.php` (`admin_whatsapp` dari `env`), `.env`, `.env.example`
- `app/Http/Controllers/Wedding/Admin/SettingController.php` (bawaan dari config)
- `app/Http/Controllers/Wedding/InvitationController.php`
  (`submitPublication` → `AdminWhatsApp::publicationUrl(...)`)
- `app/Http/Controllers/Wedding/DashboardController.php` (mengoper `$publication`)
- `resources/views/wedding/invitations/show.blade.php` (tombol + catatan + ikon)
- `resources/views/wedding/dashboard/index.blade.php` (status + tombol WhatsApp)
- `resources/views/platform/home.blade.php`
- `resources/views/wedding/layouts/{site,dashboard,admin,auth}.blade.php`
- `resources/views/wedding/public/template-show.blade.php`
- `public/wedding-assets/assets/css/site.css` (`.brand-mark` → `.brand-logo`)
- `public/favicon.ico` (menggantikan berkas 0 byte)

### 15.8 Hasil pengujian

`php artisan test`: **182 lulus (853 asersi)** (naik dari 168 lulus / 775
asersi). 14 tes baru di `BrandAndWhatsAppIntegrationTest` mencakup:

- **Logo:** aset ada & valid (PNG RGBA 568 × 221, favicon persegi), helper
  `Brand` menunjuk satu aset utama dengan rasio asli, logo tampil di beranda
  platform, navbar + footer Wedding, halaman login, dasbor customer, dan dasbor
  admin; tidak ada sisa `.brand-mark`/`.mark`.
- **WhatsApp:** pesan memuat data aktual (nama mempelai, template, link
  pratinjau, slug, status) dan **tanpa** rahasia; URL memakai nomor terpusat
  `6285155269940` tanpa `+`/spasi dengan pesan ter-encode (`%0A`, `%26`); URL
  identik di dasbor dan halaman ringkasan; membuka tautan **tidak**
  mempublikasikan undangan; pengajuan ulang **tidak** membuat undangan baru;
  dasbor menampilkan status + tombol + catatan; tidak ada nomor hardcoded.

Verifikasi browser (server SQLite sementara, `migrate:fresh --seed`):

| Halaman | Logo (natural 568 × 221) | Favicon | Error konsol |
|---|---|---|---|
| `/` beranda platform | 108 × 42 (rasio terjaga) | ada | tidak ada |
| `/wedding` navbar + footer | 103 × 40 (×2) | ada | tidak ada |
| `/wedding/templates` | 103 × 40 | ada | tidak ada |
| `/wedding/login` | 103 × 40 | ada | tidak ada |
| `/wedding/dashboard` (customer) | 93 × 36 | ada | tidak ada |
| `/wedding/invitations/1` | 93 × 36 | ada | tidak ada |

Uji alur nyata (set undangan demo → `menunggu_pembayaran`): dasbor dan halaman
ringkasan menampilkan tombol **Hubungi Admin via WhatsApp** + catatan baku +
badge status; tautan menunjuk ke
`https://wa.me/6285155269940?text=…` dengan nama mempelai, template, link
pratinjau, slug, dan status yang benar; tidak ada sisa placeholder `MP`; tidak
ada error konsol.

### 15.9 Yang belum diuji / catatan

- Lingkungan tidak memiliki ekstensi GD, sehingga pengolahan gambar dilakukan
  di luar PHP (.NET `System.Drawing`); aset akhir sudah diverifikasi valid
  (header PNG, dimensi, kanal alpha) lewat feature test.
- Layout lama yang **tidak dirutekan** (`resources/views/wedding/layouts/auth.blade.php`
  versi lama, `layouts/dashboard.blade.php`, `dashboard/layout.blade.php`,
  `platform.blade.php`, `landing.blade.php`, `welcome.blade.php`,
  `catalog.blade.php`, `admin/*`, `dashboard/*`, `auth/*`) sengaja tidak
  disentuh karena tidak pernah dieksekusi. Hanya route yang aktif
  (`Route::view('/', 'platform.home')` + `routes/wedding.php`) yang memakai logo.
- Nomor WhatsApp ditampilkan pada pesan hanya sebagai tujuan `wa.me`; tombol
  tidak mengirim otomatis dan tidak menandai status apa pun di database.

## 16. Pengaturan Nomor WhatsApp Admin melalui Dashboard Super Admin

Fitur ini memindahkan pengelolaan **nomor WhatsApp admin** ke UI Super Admin,
sehingga nomor dapat diubah kapan saja tanpa mengedit `.env`, konfigurasi, atau
source code, dan tanpa deployment ulang. Database menjadi **sumber aktif**;
`config`/`.env` hanya menjadi nilai cadangan (fallback) saat nomor belum
pernah diatur. Alur pembayaran & publikasi, 8 template, prefix `/wedding`, dan
aturan "hanya Admin yang mempublikasikan" **tidak diubah**.

### 16.1 Menu dashboard tempat pengaturan berada

- **Lokasi:** sidebar **Super Admin** → menu **Pengaturan Platform**
  (sebelumnya berlabel "Pengaturan"), route `wedding.admin.settings.edit`
  (`GET /wedding/admin/settings`). Tidak ada dashboard admin baru.
- **Bagian:** **Kontak WhatsApp Admin** (`#whatsapp-admin`), berisi nomor aktif
  saat ini (mis. `+62 851-5526-9940`), badge status **Aktif/Belum diatur**,
  form ubah nomor, tombol **Simpan Perubahan** dan **Batalkan**, serta
  penjelasan bahwa nomor ini adalah tujuan komunikasi customer.
- Form memakai `onsubmit="return confirm(...)"` sebagai konfirmasi sebelum
  menyimpan. Setelah sukses, muncul notifikasi persis:
  **`Nomor WhatsApp admin berhasil diperbarui.`**
- Bila nomor belum diatur, dasbor admin (`/wedding/admin`) menampilkan banner
  peringatan **"Nomor WhatsApp admin belum dikonfigurasi."** dengan tombol
  **Atur Nomor** yang menuju bagian tersebut.

### 16.2 Struktur database yang digunakan

Tidak perlu migration baru — tabel sudah ada dan **dipakai ulang**:

```
wedding_settings
  id          bigint PK
  key         varchar(80) UNIQUE   ← 'admin_whatsapp'
  value       text NULL            ← nomor ternormalisasi, mis. 6285155269940
  created_at  timestamp
  updated_at  timestamp
```

- Dibuat oleh `database/migrations/2026_11_14_000001_extend_wedding_module.php`.
- Penulisan lewat `WeddingSetting::put()` → `updateOrCreate(['key' => ...])`,
  jadi **hanya ada satu baris** untuk `admin_whatsapp` (upsert, tanpa duplikat).
- Key lain (`support_email`, `wish_moderation`, dll.) **tidak dihapus** saat
  nomor diubah — form WhatsApp hanya menulis satu key.
- Nomor **tidak** disimpan di session/browser; sumber aktifnya DB.

### 16.3 Mekanisme normalisasi nomor

`App\Support\Phone::normalize()` membersihkan semua karakter non-digit lalu
menyeragamkan awalan (`0…` → `+62…`, `620…` → `+62…`, `8…` → `+62…`).
`App\Rules\AdminWhatsAppNumber` (baru) memvalidasi input:

1. wajib diisi, hanya boleh berisi angka dan pemisah umum
   (`spasi - . ( ) +`) — huruf/simbol lain ditolak;
2. hasil normalisasi harus cocok `^\+628[0-9]{7,11}$` (nomor seluler Indonesia).

`SettingController::updateWhatsApp()` menyimpan hasil normalisasi tanpa `+`:

| Input | Tersimpan di DB |
|---|---|
| `085155269940` | `6285155269940` |
| `6285155269940` | `6285155269940` |
| `+6285155269940` | `6285155269940` |
| `+62 851-5526-9940` | `6285155269940` |
| `0812-9999-8888` | `6281299998888` |

Nomor tidak valid (huruf, terlalu pendek/panjang, bukan nomor Indonesia) →
error validasi ditampilkan dan **tidak disimpan** (nilai lama dipertahankan).

### 16.4 Cara seluruh fitur WhatsApp mengambil nomor terbaru

- **Satu helper terpusat:** `App\Support\AdminWhatsApp`. `number()` membaca
  `WeddingSetting::get('admin_whatsapp', config('wedding.admin_whatsapp'))`
  setiap request lalu menormalkannya. Tidak ada cache, jadi perubahan langsung
  berlaku tanpa restart/deploy.
- Semua tombol/pesan dibangun dari helper ini:
  `publicationUrl()` (tombol setelah pengajuan), `generalUrl()` (tombol umum di
  dasbor customer), `customerUrl()` (tombol admin → customer), serta
  `publicationMessage()`/`generalMessage()` untuk isi pesan.
- **Tidak ada nomor hardcoded** di Blade/Controller/JS/service/`wa.me`/template
  pesan. Satu-satunya definisi default ada di `config/wedding.php`
  (`WEDDING_ADMIN_WHATSAPP`), dan itu hanya fallback.
- **Bila nomor belum diatur:** `url()`/`publicationUrl()`/`generalUrl()`
  mengembalikan `null` sehingga **tidak ada tautan `wa.me` kosong**. Partial
  `resources/views/wedding/partials/admin-whatsapp-button.blade.php` merender
  pemberitahuan **"Kontak admin belum dikonfigurasi."** sebagai gantinya.
- **Template pesan tetap utuh:** perubahan nomor hanya mengubah target URL,
  bukan badan pesan. Pesan pengajuan tetap memuat nama mempelai, nama & konsep
  template, slug undangan, status, dan link pratinjau internal (aman) — dibuktikan
  test `test_template_pesan_tidak_berubah_saat_nomor_diperbarui`.

### 16.5 Berkas yang dibuat / diubah

**Dibuat:**
- `app/Rules/AdminWhatsAppNumber.php` — aturan validasi nomor.
- `resources/views/wedding/partials/admin-whatsapp-button.blade.php` — tombol
  WhatsApp / pemberitahuan "belum dikonfigurasi".
- `tests/Feature/AdminWhatsAppSettingTest.php` — 28 test (tampilan, normalisasi,
  validasi, hak akses, integrasi, DB, kondisi belum diatur).

**Diubah:**
- `app/Support/AdminWhatsApp.php` — `isConfigured()`, `display()`, URL nullable.
- `app/Http/Controllers/Wedding/Admin/SettingController.php` — `updateWhatsApp()`
  baru; `update()` kini hanya pengaturan modul.
- `app/Http/Controllers/Wedding/Admin/DashboardController.php` — kirim
  `adminWhatsAppConfigured`.
- `routes/wedding.php` — `PUT /wedding/admin/settings/whatsapp`
  (`wedding.admin.settings.whatsapp`).
- `resources/views/wedding/admin/settings.blade.php` — halaman **Pengaturan
  Platform** + bagian **Kontak WhatsApp Admin**.
- `resources/views/wedding/layouts/admin.blade.php` — label sidebar
  "Pengaturan Platform".
- `resources/views/wedding/admin/dashboard.blade.php` — banner peringatan.
- `resources/views/wedding/invitations/show.blade.php` (5 tombol) &
  `resources/views/wedding/dashboard/index.blade.php` (2 tombol) — memakai partial.
- `tests/Feature/PublicationWorkflowTest.php` — memakai endpoint baru.
- `README-WEDDING.md`, `LAPORAN_PERBAIKAN_WEDDING.md` — dokumentasi.

### 16.6 Hasil pengujian hak akses Super Admin vs Customer

| Skenario | Hasil |
|---|---|
| Super Admin buka `/wedding/admin/settings` | **200**, melihat nomor saat ini + form |
| Super Admin simpan nomor baru (08/62/+62) | Tersimpan ternormalisasi, notifikasi tepat |
| Customer buka `/wedding/admin/settings` | **403 Forbidden** |
| Customer buka `/wedding/admin` | **403 Forbidden** |
| Customer `PUT /wedding/admin/settings/whatsapp` (URL langsung) | **403**, DB tidak berubah |
| Tamu (belum login) | dialihkan ke halaman login |
| Nomor tidak valid (9 varian) | error validasi, DB tidak berubah |
| Ubah nomor → halaman customer | tombol langsung memakai nomor baru (tanpa cache) |
| Nomor belum diatur | tidak ada `wa.me` kosong; muncul pemberitahuan + banner admin |

Total suite: **210 test lulus (982 assertion)** — naik dari baseline 182 test,
tanpa regresi pada alur pembayaran/publikasi.


