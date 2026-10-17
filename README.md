# Ruang Baca CMI

Website artikel untuk Ujian Masuk IT CMI, menggunakan Laravel, MySQL, Eloquent, dan Blade. Proyek demo dengan antarmuka berbahasa Indonesia; bukan situs resmi Klinik Utama CMI.

## Fitur

- Registrasi, login menggunakan email, logout, serta lupa dan reset password.
- Dashboard dengan jumlah seluruh artikel, Published, Draft, dan 5 artikel terbaru milik pengguna.
- Tambah, lihat, edit, dan hapus artikel dengan kategori serta status Draft/Published.
- Upload, pratinjau, ganti, dan hapus gambar JPG/JPEG, PNG, atau WebP; maksimal 2 MB dan 5000 × 5000 piksel.
- Halaman publik untuk membaca artikel Published, pencarian, filter kategori, dan pagination. Dengan JavaScript aktif, hasil diperbarui tanpa reload halaman, dengan scroll halus dan dukungan Back/Forward.
- Daftar artikel dengan INNER JOIN penulis: judul, nama/email penulis, status, tanggal, urutan terbaru, pencarian, dan 10 artikel per halaman.
- Pembatasan akses: pengguna mengelola artikelnya sendiri; Draft dan gambarnya hanya dapat diakses pemilik. Direktori dengan email penulis membutuhkan login.
- Tampilan responsif, validasi formulir, dan pesan hasil tindakan.

## Kebutuhan

PHP 8.3+, Composer, dan MySQL 8.4+. Aktifkan ekstensi PHP `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `dom`, `xml`, `curl`, dan `zip`. Pengujian default membutuhkan `pdo_sqlite`. Mailpit digunakan untuk mencoba email reset password secara lokal.

## Instalasi awal

1. Pasang dependency:

   ```sh
   composer install
   ```

2. Salin `.env.example` menjadi `.env`. Buat database MySQL `ruangbacacmi`, lalu sesuaikan koneksi:

   ```dotenv
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ruangbacacmi
   DB_USERNAME=user_mysql_anda
   DB_PASSWORD="password_mysql_anda"
   ```

   Pada mesin Windows yang sudah disiapkan untuk proyek ini, service **MySQL84** memakai **port 3307**. Gunakan port yang sesuai dengan MySQL Anda. Simpan kredensial hanya di `.env`.

3. Siapkan aplikasi dan jalankan:

   ```sh
   php artisan key:generate
   php artisan migrate --seed
   composer dev
   ```

Buka [aplikasi](http://127.0.0.1:8000/), lalu [daftarkan akun](http://127.0.0.1:8000/register) untuk mencoba fitur penulis. Seeder menyediakan 12 artikel Published dan 1 Draft; password akun seed diacak, jadi gunakan akun yang Anda daftarkan sendiri.

Untuk proyek yang **sudah dikonfigurasi**, cukup nyalakan MySQL dan jalankan `composer dev`. Pertahankan `.env` dan `APP_KEY` yang sudah ada. Tidak perlu npm, build frontend, atau `storage:link`.

Setelah menerima perubahan migration, jalankan `php artisan migrate`.

## Mencoba reset password

Jalankan Mailpit dari PowerShell di folder proyek:

```powershell
& '.runtime/mailpit/mailpit.exe' --listen 127.0.0.1:8025 --smtp 127.0.0.1:1025 --quiet
```

Binary tersebut tersedia pada mesin lokal dan tidak disertakan dalam repository. Untuk mesin lain, pasang Mailpit dan sesuaikan lokasi executable pada perintah di atas. Konfigurasi `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_SCHEME=null
```

Biarkan Mailpit berjalan; **Ctrl+C** menghentikannya. Setelah mengubah `.env`, jalankan `php artisan config:clear`.

1. Logout, lalu buka [lupa password](http://127.0.0.1:8000/forgot-password).
2. Kirim permintaan menggunakan email akun yang terdaftar.
3. Buka [inbox Mailpit](http://127.0.0.1:8025/) dan ikuti tautan reset pada email.
4. Simpan password baru minimal 12 karakter, dengan huruf dan angka. Pastikan login baru berhasil dan password lama ditolak.

Email ditangkap di Mailpit, termasuk tujuan Gmail/Yahoo; tidak dikirim ke inbox email asli.

## Pengujian

```sh
php vendor/bin/phpunit --configuration phpunit.xml
node --test tests/Browser/*.test.cjs
```

Suite default memakai SQLite in-memory. Untuk pengujian MySQL, buat database terpisah **`cmi_test`** dengan akses dari kredensial pengujian Anda, lalu jalankan:

```sh
php vendor/bin/phpunit --configuration phpunit.mysql.xml
```

Tabel `cmi_test` dapat dibuat ulang oleh pengujian. Simpan pengaturan koneksi khusus test dalam `.env.testing` atau environment shell. CI tersedia di `.github/workflows/tests.yml`.

## Sumber dan atribusi

Soal asli: [Ujian Masuk IT CMI](docs/supports/Ujian%20masuk%20IT%20CMI.pdf). Tema warna mengacu pada [Klinik Utama CMI](https://www.cmihospital.com/). Logo berasal dari situs tersebut dan hak mereknya tetap milik pemiliknya, digunakan untuk konteks tes. Artikel seed merupakan konten fiktif untuk demonstrasi.
