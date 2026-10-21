# Progres BKK SMK JAYA BUANA

## Tahap 1 — Fondasi proyek

- Dibuat struktur direktori utama tanpa file CRUD kosong.
- Konfigurasi lokal memakai `app/config/app.local.php` yang diabaikan Git; contoh tanpa kredensial asli tersedia di `app.example.php`.
- Base URL eksplisit, default `/bkk`; semua aset memakai helper URL.
- Bootstrap, helper escape/redirect, pencatatan kesalahan, dan koneksi PDO tertunda tersedia. Beranda tidak memanggil database.
- Proteksi Apache untuk direktori internal, berkas tersembunyi, dan skrip dalam uploads tersedia.
- Halaman publik sementara hanya pemeriksaan aplikasi; informasi sekolah yang belum tersedia tidak direka.
- Autentikasi, session, CSRF, validasi unggahan, Bootstrap 5, skema database, dan fitur portal menunggu tahap terkait.

Validasi: semua 6 file PHP lulus `php -l`; render beranda tanpa konfigurasi lokal/database berhasil. Pemeriksaan helper URL (subfolder, root, URL lengkap), escape HTML, dan penolakan redirect eksternal lulus. HTTP Apache lokal: beranda dan CSS 200; app/config, database, storage/logs, docs, dan .git/config 403. Konfigurasi lokal terbukti diabaikan Git.

File halaman publik selain index.php ditunda sampai tahap implementasinya agar tidak membuat halaman kosong. Tidak ada skema database atau CRUD pada tahap ini. Jangan lanjut tahap berikutnya tanpa instruksi pengguna.
