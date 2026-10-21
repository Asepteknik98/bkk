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

## Tahap 2 — Database

- Ditambahkan `database/bkk_jaya_buana.sql` untuk database baru yang kosong: delapan tabel InnoDB, utf8mb4_unicode_ci, primary key, indeks, slug unik per tabel, serta foreign key penulis/pengunggah dengan ON DELETE SET NULL.
- Role users: admin/staf_bkk; kategori program sesuai lima kategori yang diminta. Status publikasi default draft. Users dan tracer_responses tidak diberi seed.
- Seed: satu berita, satu lowongan, lima program, seluruhnya berlabel DATA DEMO dan draft. Settings memuat identitas yang diberikan pengguna dan placeholder/NULL untuk kontak yang belum tersedia.
- Tracer menyimpan persetujuan, waktu, versi teks persetujuan, dan kontak; CHECK menolak persetujuan selain 1. Data tracer harus tetap privat saat fitur diimplementasikan.
- Tidak ada password bawaan, kredensial asli, DROP, CREATE DATABASE, atau USE di berkas SQL. Impor dijelaskan di README; helper settings ditunda karena belum ada halaman yang memakainya.
- Validasi: impor berhasil pada MariaDB 10.4.32 sementara di direktori Temp dan port terpisah, tanpa mengubah database XAMPP yang ada. Delapan tabel dan jumlah seed sesuai; percobaan slug duplikat, foreign key tidak valid, kategori tidak valid, dan persetujuan 0 ditolak; ON DELETE SET NULL berhasil. Baris pengujian sementara di-rollback dan server pengujian dihentikan. Beranda tetap HTTP 200 tanpa mengakses database. `git diff --check` lulus.
- Tidak mengimplementasikan autentikasi atau CRUD. Kompatibilitas MySQL dinyatakan untuk 8.0.16+; pengujian runtime dilakukan pada MariaDB lokal, bukan MySQL.

## Tahap 3 — Autentikasi dan hak akses

- Login bersama Admin/Staf, prepared statements + password_verify, pesan gagal generik, regenerasi ID session/token CSRF, logout hanya POST + CSRF.
- Cookie HttpOnly, SameSite=Lax, Secure saat HTTPS terdeteksi server, path mengikuti base URL; session disimpan di storage/sessions dan tidak dilacak Git. Trace exception tidak menyertakan argumen fungsi.
- Pembatas atomik berbasis berkas: 5 percobaan per email/IP dan 30 per IP per 15 menit, termasuk yang berhasil; tidak menyimpan email/IP mentah, tetap berlaku lintas session/cookie, HTTP 429 + Retry-After. Storage dilindungi Apache dan diabaikan Git.
- Middleware require_auth/require_role membaca ulang akun aktif dan role dari database, untuk halaman maupun handler. Endpoint pengguna/pengaturan pada tahap berikutnya wajib require_role('admin'); belum dibuat modul CRUD.
- Dashboard dan empat layout panel tersedia; menu hanya Dashboard/Beranda, label dan keterangan hak akses sesuai role. Layout ditolak untuk akses HTTP langsung.
- app/create-admin.php hanya CLI: input interaktif, password tersembunyi dan konfirmasi, password_hash, prepared INSERT, lock untuk admin pertama; menolak jika admin sudah ada. Password tidak dicetak, ditulis ke source/log, atau dijadikan argumen proses.
- Pengujian otomatis pada salinan proyek dan MariaDB 10.4 sementara lulus: login kedua role, pesan generik, akun nonaktif, regenerasi ID, cookie HttpOnly/SameSite, CSRF, logout POST, akses tanpa login, akses halaman dan handler khusus Admin (Staf 403), penonaktifan saat session aktif, pembatas lintas cookie, dan guard CLI/browser. Server sementara dihentikan; database asli tidak diubah.
- Seluruh file PHP diperiksa sintaks. Pemeriksaan terminal password nyata dan HTTPS langsung belum dilakukan; panduan kebutuhan terminal/server tersedia di README. Tidak melanjutkan modul lain.

## Tahap 4 — Halaman publik dan desain portal

- Layout publik bersama: masthead BKK, navbar seluruh menu (lima program dalam dropdown), Login Pengelola, informasi terbaru, sidebar, footer. Tampilan navy/putih/emas memakai Bootstrap 5.3.3 lokal beserta lisensi MIT, CSS utama/mobile, dan navigasi keyboard/skip link serta fallback tanpa JavaScript.
- Beranda memuat berita utama/pendamping, berita terbaru, lowongan beserta batas lamaran dan penanda kedaluwarsa, lima kategori program, pengumuman settings, akses tracer, serta mitra aktif. Empty state tidak memakai informasi sekolah rekaan.
- Profil/kontak, daftar berita berpagination, detail berita berdasarkan slug, pencarian judul/ringkasan berita, dan tujuh tujuan menu utama tersedia. Lowongan/program baru berupa ringkasan; formulir tracer serta detail/pendaftaran menunggu tahap berikutnya. Tidak ada CRUD.
- Helper public_query/setting memakai PDO prepared statements, fallback pengaturan, serta pemberitahuan gangguan database. Beranda tetap terbuka sebelum database tersedia. Berita hanya published dengan waktu publikasi tidak di masa depan; detail draft/slug tidak dikenal 404, gangguan database 503. Program/lowongan hanya published; mitra hanya aktif. Portal tidak membaca tracer_responses.
- Konten dan settings di-escape; isi berita ditampilkan sebagai teks dengan baris baru. DATA DEMO diberi badge jelas. SQL seed tidak diubah/publikasikan. Gambar placeholder SVG geometris dibuat lokal; path gambar eksternal/tidak aman/hilang memakai placeholder. Monogram BKK adalah identitas sementara, bukan logo resmi sekolah.
- README diperbarui untuk portal, Bootstrap lokal, perilaku publikasi, key settings opsional, fallback database, dan ruang lingkup halaman tahap ini.
- Pengujian PHP/MariaDB pada salinan sementara lulus: empty state dengan seed draft, filter published, berita terjadwal/draft 404, escape XSS, pagination/pencarian literal wildcard, label demo, batas lamaran lewat, mitra aktif, 34 tautan/aset lokal, dan fallback database/detail 503. Semua halaman menu juga HTTP 200 melalui Apache /bkk; database pengguna tidak diubah. Pemeriksaan sintaks seluruh PHP dan git diff --check lulus.
- QA Chrome pada HTML hasil render PHP: delapan jenis halaman di viewport tepat 320/390/768/1440 piksel, menu serta dropdown terbuka, tidak ada horizontal overflow/gambar rusak. Pemeriksaan visual menambahkan pembatas baris judul kartu agar judul sangat panjang tetap rapi; judul lengkap tersedia pada detail. Server uji dan database sementara dihentikan.

## Keputusan pengguna — File pembaruan SQL terpisah

- Setiap pembaruan database berikutnya wajib berupa file SQL baru bernomor di database/updates/ (contoh 001_nama_perubahan.sql), agar pengguna cukup mengimpor file pembaruan. Jangan gabungkan perubahan kembali ke database/bkk_jaya_buana.sql; file tersebut dipertahankan untuk instalasi awal.
- Pembaruan harus mempertahankan data yang sudah ada; dokumentasikan urutan impor, prasyarat, verifikasi, dan ketentuan impor ulang di README. Jangan membuat file SQL pembaruan kosong jika tidak ada perubahan database.
- Pengguna melaporkan tangkapan layar impor dengan pesan server #1050: tabel users sudah ada. SQL awal bukan untuk impor ulang. Tahap 3/4 tidak mengubah skema/seed; belum diperlukan SQL pembaruan. Belum ada pemeriksaan langsung kelengkapan database pengguna atau tindakan perubahan terhadapnya.
- Perubahan kali ini hanya dokumentasi README/progres; diverifikasi dengan git diff --check.

## Tahap 5 — CRUD berita dan media

- Admin/staf_bkk dapat mengelola seluruh berita: daftar dengan pencarian/filter status dan pagination, tambah/edit/simpan/hapus POST+CSRF, draft/published, unggulan, slug ASCII unik dengan akhiran otomatis, dan gambar utama. Penulis awal dipertahankan saat edit. Waktu publikasi memakai NOW() database agar sesuai portal walau zona waktu PHP berbeda.
- Isi berita tetap teks biasa; baris kosong dirender menjadi paragraf HTML ter-escape. Input HTML tidak dieksekusi, termasuk saat validasi gagal dan textarea ditampilkan ulang. Berita published terhubung ke detail publik; draft tetap tidak dapat dibuka pengunjung.
- Helper unggahan bersama: JPG/JPEG/PNG/WebP 5 MB, PDF opsional 10 MB; ukuran aktual, ekstensi, fileinfo MIME dan dimensi gambar diperiksa. Nama acak, larangan SVG/executable/ekstensi ganda berbahaya, path/full_path traversal, dan pembatasan realpath ke uploads. Tidak menambah dependency runtime selain fileinfo yang sudah menjadi ekstensi PHP.
- Katalog media dengan thumbnail/metadata, pencarian/pagination, upload, download attachment, hapus POST+CSRF; gambar katalog dapat dipakai ulang dalam berita. Menghapus berita atau mengganti gambar tidak menghapus berkas.
- Penghapusan media memeriksa semua referensi konten/settings, termasuk draft. Named lock database dipakai bersama seluruh penulisan berita/media. Media tanpa referensi dipindah sementara ke storage terlindungi, transaksi menghapus metadata, lalu berkas dibersihkan; rollback memulihkan berkas. Upload baru dibersihkan jika transaksi berita gagal, tanpa membuang media yang sudah tercatat.
- Sidebar/dashboard/layout panel diperbarui dengan tautan modul yang tersedia; CSS panel/form/tabel/media responsif dan konfirmasi hapus melalui JS. README menjelaskan penggunaan dan kebutuhan izin upload/temporary directory serta batas php.ini.
- Tidak ada perubahan skema/seed database: tabel berita/media dari tahap 2 sudah mencukupi, sehingga tidak membuat file SQL pembaruan kosong dan tidak mengubah bkk_jaya_buana.sql. Aturan SQL pembaruan terpisah tetap berlaku.
- Pengujian HTTP/PDO/MariaDB sementara lulus untuk kedua role: CRUD lengkap, draft/published, slug unik, unggulan, paragraf/XSS, pencarian/pagination, POST/CSRF, akses tanpa login, JPG/PNG/WebP/PDF, unggahan tidak valid (SVG, PHP, PHP.jpg, MIME palsu, kosong, ukuran lebih, traversal, gambar rusak), download, perlindungan file bersama lintas berita/lowongan/program/mitra/settings, hapus fisik dan pembersihan rollback yang dipicu kegagalan INSERT. Uji ulang dengan PHP Pacific/Kiritimati juga lulus. SQL awal diverifikasi tidak berubah; server uji dihentikan, database pengguna tidak disentuh.
- Pemeriksaan Chrome pada HTML hasil render panel lulus untuk daftar berita, editor, katalog media, dan dashboard pada viewport 320/390/768/1440 piksel tanpa horizontal overflow halaman. Tabel lebar memakai area gulir sendiri. Sintaks PHP dan git diff --check lulus; tidak melanjutkan modul lain.
