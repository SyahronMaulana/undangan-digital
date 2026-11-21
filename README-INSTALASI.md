# Platform SaaS Undangan Pernikahan — Tahap 1–2

Paket ini adalah **file tambahan (overlay)** untuk proyek Laravel 12 yang baru.
Belum diuji karena lingkungan penyusun tidak memiliki PHP. Jalankan `php artisan test` di komputer Anda.

## Persyaratan
PHP 8.2+, Composer, Node.js, MySQL, ekstensi `pdo_mysql` dan `pdo_sqlite` (untuk test).

## Langkah
```bash
composer create-project laravel/laravel undangan "^12.0"
cd undangan
# Ekstrak ZIP ini ke folder undangan dan setujui penimpaan (bootstrap/app.php, routes/web.php,
# app/Models/User.php, database/seeders/DatabaseSeeder.php, resources/js/app.js)
npm install alpinejs
```
Ubah `.env`:
```
APP_NAME="Undangan Digital"
APP_URL=http://localhost:8000
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=undangan
DB_USERNAME=root
DB_PASSWORD=
ADMIN_EMAIL=admin@undangan.test
ADMIN_PASSWORD=isi-kata-sandi-kuat
```
Buat database `undangan` di MySQL, lalu:
```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run dev      # terminal 1
php artisan serve               # terminal 2
php artisan test                # pastikan semua lulus
```
Jika ada tes yang gagal, kirim pesan error-nya ke saya agar saya perbaiki.

## Yang sudah ada (Tahap 1–2)
- Migration semua tabel (users + role/phone, templates, packages, weddings, foto, acara, kisah, tamu, RSVP, ucapan, kado, broadcast, langganan, pembayaran).
- Model dan relasi Eloquent. `role` tidak bisa diisi lewat mass assignment.
- Registrasi, login, logout (rate limit), middleware `role`.
- `WeddingPolicy`: undangan customer lain dijawab 404.
- Dashboard customer (Bahasa Indonesia) dan dasbor admin dasar.
- Buat undangan: validasi slug (format + daftar slug cadangan), pembatasan jumlah undangan menurut paket, publikasi/draf, hapus.
- Route publik `/{slug}` (paling akhir) dengan validasi token tamu: nama hanya dari database; `?to=` tidak dipercaya.
- Token tamu acak 40 karakter, normalisasi nomor ke +62.
- Seeder: 3 paket, 8 template (data saja), akun Super Admin.
- Tes: auth, role, isolasi data, alur undangan, link umum/personal/token, normalisasi telepon.

## Belum ada (tahap berikutnya)
Tahap 3 landing dan katalog · Tahap 4 delapan desain undangan (saat ini memakai halaman `placeholder`) ·
Tahap 5 editor dan live preview · Tahap 6 manajemen tamu/import/export · Tahap 7 RSVP, ucapan, WhatsApp ·
Tahap 8 paket/pembayaran/admin lengkap · Tahap 9 hardening dan Lucide Icons.
