# BKK SMK JAYA BUANA

Fondasi portal berbasis PHP Native 8.2 dan MySQL/MariaDB. Tahap 1 hanya menyediakan konfigurasi, helper, proteksi direktori, dan halaman pemeriksaan. Fitur publik, autentikasi, Bootstrap 5, serta CRUD belum diimplementasikan.

## Instalasi XAMPP

1. Gunakan Apache 2.4, PHP 8.2 dengan ekstensi `pdo_mysql`, dan MySQL/MariaDB.
2. Letakkan proyek di `C:/xampp/htdocs/bkk`.
3. Salin `app/config/app.example.php` menjadi `app/config/app.local.php`. Atur `base_url` menjadi `/bkk`, lalu isi konfigurasi database lokal sesuai lingkungan. Jangan commit file lokal ini.
4. Jalankan Apache dan buka `http://localhost/bkk/`. Halaman pemeriksaan harus tampil meskipun MySQL belum dijalankan atau database belum diimpor.
5. Pastikan stylesheet dimuat melalui `http://localhost/bkk/assets/css/style.css`.

Untuk hosting shared, unggah isi proyek ke direktori tujuan, lalu atur `base_url` menjadi `/nama-subfolder`, string kosong untuk root domain, atau URL lengkap seperti `https://contoh.sch.id/bkk`. Gunakan HTTPS pada hosting. Zona waktu default adalah `Asia/Jakarta` dan dapat diubah di konfigurasi lokal.

## Konfigurasi server

Apache harus mengizinkan `.htaccess` (`AllowOverride All`) dan mengaktifkan `mod_rewrite` serta `mod_authz_core`. Direktori `app`, `database`, `storage`, dan `docs` menolak akses HTTP. Root memblokir berkas tersembunyi dan listing direktori; `uploads` menolak eksekusi skrip. Jangan gunakan server bawaan PHP untuk menilai proteksi Apache karena server tersebut tidak membaca `.htaccess`.

Pastikan proses PHP dapat menulis ke `storage/logs` dan, saat fitur unggahan tersedia, `uploads`. Jangan gunakan izin `777`. Kesalahan dicatat ke `storage/logs/php-error.log`; detail kesalahan tidak ditampilkan kepada pengunjung.

## Database dan helper

Belum ada skema atau proses impor database pada tahap ini. Koneksi PDO baru dibuat saat `db()` dipanggil. PDO memakai `utf8mb4`, exception, dan prepared statements native; setiap query berparameter pada tahap berikutnya wajib memakai `prepare()` dan `execute()`.

Muat `app/bootstrap.php` sekali dari setiap entry point PHP. Helper tersedia: `app_config()`, `base_url()`, `asset_url()`, `redirect()` untuk path internal, dan `e()` untuk escape teks/atribut HTML. Gunakan helper URL untuk mendukung instalasi di subfolder.

Folder fitur yang belum berisi implementasi dipertahankan dengan `.gitkeep`. Tidak ada akun pengelola atau kredensial bawaan.
