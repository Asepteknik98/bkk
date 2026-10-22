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

## Tahap 6 — Modul lowongan kerja

- CRUD panel Admin/Staf: perusahaan, posisi, lokasi, jenis pekerjaan, pendidikan, persyaratan/deskripsi teks biasa, tanggal batas, URL http/https, poster unggahan/katalog, draft/published; pencarian/filter status/pagination, slug unik saat tambah dan stabil saat edit, penulis awal tetap, hapus hanya POST+CSRF. Sidebar menautkan modul lowongan.
- Publik: daftar dengan pencarian posisi/perusahaan, filter lokasi/jenis yang bersumber dari published, pagination 9 item; detail published, label dibuka/ditutup, poster, tautan daftar perusahaan tervalidasi, dan maksimal tiga lowongan terkait. Draft/closed lama tidak ditampilkan; detail tidak dikenal 404 atau gangguan database 503. Beranda memakai label dan tautan detail yang sama.
- Batas lamaran dihitung server dengan Asia/Jakarta sampai akhir hari; tepat 00:00 WIB hari berikutnya ditutup. Tidak tergantung konfigurasi zona PHP/DB atau cron. Data lama tanpa tanggal tidak dianggap dibuka. Tombol daftar disembunyikan jika kedaluwarsa/tanggal kosong/URL tidak valid.
- jobs.php berisi validasi URL/tanggal/status bersama; vacancies.php memakai transaksi dan layanan unggahan lama. with_content_lock dipindah dari news.php ke panel.php agar seluruh modul referensi media memakai lock yang sama. Menghapus/mengganti poster tidak membuang berkas bersama.
- Tidak mengubah SQL/skema/seed atau database pengguna; tabel tahap 2 sudah mencukupi sehingga tidak ada file SQL pembaruan kosong. README diperbarui, aturan SQL terpisah tetap berlaku.
- Uji CLI app/tests/jobs.php lulus untuk akhir hari WIB (termasuk mikrodetik), tengah malam, tahun kabisat, tanggal salah/kosong, draft, serta URL http/https vs skema berbahaya/kredensial/karakter kontrol.
- Integrasi pada salinan aplikasi dan MariaDB sementara lulus: CRUD kedua role, aktif/batas hari ini/kedaluwarsa/draft, related, search/filter/pagination, validasi tanggal/URL/poster, escape HTML, reuse dan perlindungan media, POST/CSRF/akses tanpa login, slug tetap/unik. Diuji dengan PHP Pacific/Kiritimati; regresi penuh berita/media lulus setelah pemindahan lock. Server uji dihentikan; hash SQL awal tetap sama.
- QA Chrome untuk daftar/detail publik dan daftar/form panel lulus pada viewport 320/390/768/1440 piksel tanpa horizontal overflow halaman. Sintaks PHP, uji CLI, dan git diff --check lulus. Artefak QA sementara di workspace dibersihkan; tidak melanjutkan modul lain.

## Tahap 7 — Modul program BKK

- Satu CRUD/form/tabel program untuk Admin dan Staf, dengan lima kategori dari program_categories() sebagai allowlist server. Daftar dengan pencarian, filter kategori/status, pagination; tambah/edit kategori, judul, slug unik global, ringkasan/isi teks biasa, gambar upload/katalog, URL pendaftaran opsional, draft/published; hapus POST+CSRF. Penulis awal tetap, transaksi/lock dan keamanan unggahan memakai helper bersama.
- Kelima index publik menggunakan program-list.php (pagination 9 item); kelima detail.php hanya menetapkan kategori lalu memuat program-detail.php. Query detail selalu membatasi slug + kategori + published, sehingga draft/lintas kategori 404. URL opsional http/https diperiksa saat simpan dan render; konten HTML di-escape menjadi teks/paragraf. Kelas Melanjutkan dapat memuat penjelasan kuliah dalam/luar negeri dan beasiswa tanpa struktur CRUD tambahan.
- Memperbaiki variabel informasi kategori pada daftar yang sebelumnya tertimpa loop navbar; setiap halaman kini mempertahankan deskripsi kategorinya sendiri. Sidebar panel menautkan Program BKK; README diperbarui dan bagian petunjuk pembaruan SQL yang terduplikasi dirapikan.
- Tidak mengubah skema/seed, SQL awal, atau database pengguna; tabel tahap 2 mencukupi. Tidak membuat file SQL pembaruan kosong.
- Integrasi PHP/PDO/MariaDB sementara lulus: CRUD kedua role, semua lima kategori, 20 percobaan detail lintas kategori ditolak 404, draft/unpublish, pindah kategori, validasi allowlist/URL/status, slug unik, paragraf/XSS, reuse/perlindungan gambar, POST/CSRF/akses tanpa login, pencarian/filter/pagination, serta rollback upload/metadata saat INSERT dipaksa gagal. Hash SQL awal tidak berubah; server sementara dihentikan.
- QA Chrome terhadap HTML hasil render daftar/detail publik dan daftar/form panel lulus pada 320/390/768/1440 piksel tanpa overflow halaman. Tidak melanjutkan modul lain.

## Tahap 8 — Tracer study

- Informasi, formulir tanpa login, simpan POST, dan konfirmasi sukses tersedia. Nama/jurusan/tahun/WhatsApp wajib; NISN/email opsional. Empat status dengan field dinamis; server memvalidasi allowlist/tahun/detail dan membuang detail status lain. Tanpa JS formulir tetap dapat dipakai. Teks tujuan, persetujuan eksplisit, privasi, dan kontak koreksi BKK tersedia tanpa kontak rekaan.
- CSRF, honeypot, 5 pengiriman valid/session dan 30/IP per 15 menit memakai prefix tracer pada pembatas berkas lama. Token formulir acak maksimal 5 aktif/2 jam; unique submission key membuat kirim ulang identik idempoten. Token sama/isi berbeda mendapat penjelasan; formulir baru dengan data serupa tetap diterima dan respons tambahan tidak dianggap pembaruan otomatis. Sukses tidak menampilkan identitas/ID respons, hanya receipt session 30 menit.
- SQL pembaruan TERPISAH `database/updates/001_tracer_submission_key.sql`, impor satu kali setelah SQL awal. Menambah submission_key nullable dan indeks unik tanpa mengubah respons lama. SQL instalasi awal tidak diubah, migrasi tidak dijalankan pada database pengguna.
- Panel Admin/Staf: daftar/pagination 15, filter jurusan/tahun/status, detail, rekap respons per status, ekspor CSV sesuai filter melalui builder yang sama. Semua endpoint termasuk export memakai middleware role; tidak ada query pembacaan respons pada halaman publik selain pencarian token session milik pengirim saat proses simpan.
- CSV streaming, UTF-8 BOM, attachment/no-store, awalan formula/tab/CR/LF dan whitespace/kontrol Unicode dinetralisir dengan apostrof; token internal tidak diekspor. Input/data detail di-escape. Rekap menjelaskan jumlah respons, bukan alumni unik.
- README menjelaskan impor 001, alur formulir, validasi, persetujuan versi tracer-v1, penanganan ulang, batas anti-spam, kontak, akses panel, dan ekspor. Uji CLI app/tests/tracer.php tersedia.
- Integrasi PHP/PDO/MariaDB sementara lulus: migrasi menjaga data lama, empat status, validasi server/CSRF/honeypot/throttle, replay identik/berbeda/pengiriman baru, tidak ada PII publik, akses kedua role, filter/rekap/pagination, ekspor terfilter dan formula injection (termasuk Unicode). SQL awal diverifikasi tidak berubah; server uji dihentikan dan database pengguna tidak disentuh.
- Uji CLI validasi/CSV, sintaks PHP, dan git diff --check lulus. QA Chrome untuk field dinamis keempat status, formulir/konfirmasi, serta daftar/detail/rekap panel lulus pada 320/390/768/1440 piksel tanpa overflow. Artefak QA workspace dibersihkan; tidak melanjutkan modul lain. Pengguna tetap perlu mengimpor pembaruan 001 ke database terpasang.

## Tahap 9 — Mitra, pengguna, pengaturan, dan akun

- CRUD mitra untuk kedua role: perusahaan/kampus, nama, logo unggahan/katalog, website http/https opsional, deskripsi teks biasa, status aktif; hapus POST+CSRF. Beranda hanya menampilkan mitra aktif (12 nama pertama), dengan deskripsi dan website valid. Logo bersama tetap di katalog, memakai transaksi/lock layanan media lama.
- Pengguna khusus Admin pada semua halaman/handler: daftar, tambah, edit nama/username/email/role, aktif/nonaktif, reset password. Username dinormalisasi huruf kecil, unik, 3–50 karakter ASCII huruf/angka/titik/garis; email unik dipertahankan untuk kompatibilitas. Tidak membuat hapus akun. Password 12–72 byte dengan konfirmasi dan password_hash/password_verify; tidak disimpan ke session/log.
- Semua perubahan akun memakai lock per database dan transaksi, memeriksa ulang kewenangan pelaku di dalam transaksi. Admin aktif terakhir terlindungi dari penonaktifan/demosi termasuk dua perubahan bersamaan. Middleware membaca ulang role, status, username, dan session_version. Nonaktif/reset membatalkan sesi lama; aktivasi ulang tidak memulihkannya. Demosi membatasi akses admin pada permintaan berikutnya.
- Akun pribadi kedua role: ubah nama/username/email akun dari sesi saja; abaikan target/role/status dari POST. Ubah password memverifikasi password lama, dibatasi 10 percobaan/akun/15 menit, meregenerasi ID sesi, mempertahankan sesi saat ini, dan membatalkan sesi perangkat lain.
- Pengaturan khusus Admin memakai allowlist key: nama BKK/sekolah, dua logo, alamat/kontak/email, tiga URL media sosial, profil teks biasa. Kontak kosong tetap placeholder; tidak mengisi kontak resmi rekaan. Header/footer/profil membaca pengaturan dan escape output. Pilihan logo sekolah/BKK memakai katalog dan perlindungan referensi media bersama.
- SQL TERPISAH `database/updates/002_users_username_session.sql`, impor satu kali setelah 001. Menambah username unik (akun lama `akun-<id>`) dan session_version tanpa mengubah password/role. Login menerima username maupun email; skrip CLI admin pertama meminta username. Sesi lama harus login ulang. SQL awal dan 001 tidak diubah; migrasi hanya diuji di MariaDB sementara, database pengguna tidak disentuh. README menjelaskan instalasi baru SQL awal → 001 → 002 → CLI dan verifikasi kolom/indeks.
- Integrasi PHP/PDO/MariaDB sementara lulus: migrasi akun lama, login email/username, CRUD/keunikan/validasi akun, admin terakhir, dua demosi admin bersamaan (satu ditolak), profil pribadi tanpa eskalasi, password lama/reset, pencabutan sesi termasuk sesi yang belum mengakses selama nonaktif lalu aktif lagi, kedua role pada mitra, aktif/nonaktif publik, URL/unggahan tidak valid, logo bersama, perlindungan katalog, rollback berkas/metadata ketika logo kedua gagal, POST/CSRF/logout, serta akses langsung Staf ke semua halaman/handler khusus Admin.
- Regresi penuh berita/media lulus dengan migrasi 002: CRUD kedua role, draft/publikasi, slug, XSS/paragraf, pencarian/pagination, unggahan JPG/PNG/WebP/PDF dan penolakan berkas tidak valid, referensi lintas konten/settings, hapus fisik dan rollback. Hash SQL awal tetap sama; server sementara dihentikan.
- QA Chrome terhadap mitra, pengguna, pengaturan, akun, beranda, dan profil lulus pada viewport 320/390/768/1440 tanpa horizontal overflow. Sintaks PHP dan git diff --check lulus. Tidak melanjutkan modul lain; pengguna tetap perlu mengimpor 002 satu kali pada database terpasang. Input password CLI interaktif/HTTPS mengikuti pengujian lingkungan pemasangan pada README.

## Tahap 10 — Integrasi dan penyempurnaan

- Pencarian publik gabungan berita/lowongan/program di helper search.php, UNION ALL dari sumber/kolom allowlist server, prepared statements native dan escape wildcard LIKE. Filter jenis/kategori, pagination 9 dengan parameter tetap; kategori membatasi hasil ke program. Input array/UTF-8/panjang/allowlist divalidasi; error 400 dijelaskan, halaman dibatasi. Hanya published; berita masa depan dan kategori program tak dikenal tidak muncul. Header mengarah ke pencarian terpadu.
- Dashboard memakai jumlah nyata status konten, lowongan dibuka berdasarkan tanggal Asia/Jakarta, mitra aktif, media, dan tracer per status; akun aktif hanya untuk Admin. Wajib login kedua role, tanpa ringkasan tracer/PII publik. Hitungan status published tetap termasuk berita terjadwal; visibilitas publik terpisah. Tracer dihitung sebagai respons.
- Judul/meta description dinamis dari detail/ringkasan/kategori/pencarian/filter, ringkasan UTF-8 maksimal 160 karakter tanpa dependency baru. Navigasi pencarian memakai aria-current; fokus keyboard diperluas ke select/textarea, grid dashboard responsif. Gambar hilang tetap placeholder lokal; kartu dekoratif alt kosong, detail/logo ber-alt.
- 404.php bersama untuk detail hilang/draft serta panel ID tidak ditemukan. Rewrite fallback Apache relatif untuk root/subfolder setelah proteksi direktori internal. Kegagalan database publik 503 dengan pesan layanan, exception tak tertangani 500 generik. Memperbaiki log exception bootstrap yang sebelumnya mencetak seluruh exception: kini hanya kelas/kode/lokasi, tanpa pesan driver/SQL/kredensial. Log tetap di storage/logs terlindungi.
- Integrasi PHP/PDO/MariaDB sementara lulus: tiga sumber, filter/kategori/pagination, wildcard/injeksi SQL, input array/panjang, draft/future 404, jumlah dashboard kedua role, privasi respons tracer, metadata, placeholder, tautan halaman utama dan alt. Error berisi token rahasia sintetis tidak bocor ke respons ataupun log; kredensial salah menghasilkan 503 tanpa detail.
- Apache terpisah dengan konfigurasi sementara lulus 404 root/subfolder dan proteksi app/database/storage/docs/.git; layanan/config XAMPP pengguna tidak diubah. Chrome pada HTML uji sintetis memeriksa 27 halaman publik/panel/detail/login/404 pada 320/390/768/1440 piksel, tanpa horizontal overflow. Audit label input/select/textarea pada halaman uji lulus.
- Tidak menambah library, tidak redesign besar, dan tidak mengubah SQL awal/001/002 ataupun database pengguna. README menjelaskan perilaku integrasi dan cara pemeriksaan pemasangan. Pembaruan SQL baru tidak diperlukan.
- Hasil pencarian lowongan menampilkan label dibuka/ditutup/batas belum ditentukan dan tanggal akhir 23:59:59 WIB. Integrasi diulang setelah penambahan ini, serta tampilan pencarian diperiksa ulang pada empat viewport dan lulus. Regresi penuh berita/media, uji CLI jobs/tracer, lint seluruh PHP berubah, dan git diff --check lulus. Seluruh layanan uji dihentikan dan artefak QA workspace dibersihkan.

## Tahap 11 — Pemeriksaan akhir (selesai, 21 Oktober 2026)

- Seluruh 107 file PHP lolos lint; app/tests/jobs.php dan tracer.php lulus. Tidak menambah dependency atau modul baru.
- Menjalankan ulang suite HTTP/PDO/MariaDB sementara untuk berita/media, lowongan, program, tracer, dan mitra/pengguna/pengaturan/akun. Semuanya lulus: CRUD kedua role, draft/publikasi, pemisahan lima kategori/20 akses lintas kategori ditolak, pagination/filter/slug, validasi URL/tanggal/unggahan, escape paragraf, media bersama/rollback, CSRF/POST, admin terakhir termasuk race, pencabutan sesi, privasi tracer dan CSV aman.
- Audit Apache terpisah lulus untuk instalasi subfolder /bkk, 404 subfolder/root, proteksi app/database/storage/docs/.git, penolakan PHP dan ekstensi ganda berbahaya dalam uploads, serta akses JPG normal. Login/logout kedua role, regenerasi ID, HttpOnly/SameSite/path cookie, pesan gagal generik, batas lintas sesi dan POST/CSRF lulus. Tidak mengubah konfigurasi/layanan Apache XAMPP pengguna.
- Temuan diperbaiki: handler exception bootstrap semula dipasang setelah konfigurasi, sehingga error konfigurasi belum melalui redaksi aman. Handler kini didaftarkan sebelum require konfigurasi. Pengujian exception dan ParseError konfigurasi dengan token sintetis menghasilkan 500 generik tanpa token di respons/log; kasus koneksi gagal menghasilkan 503 tanpa kredensial/SQL.
- Pemindaian literal kredensial nonkosong pada file Git tidak menemukan kandidat; app.local.php/.env diabaikan Git, tidak ada pada daftar file maupun riwayat path yang diperiksa. SQL tidak memuat akun/password bawaan. Hash dummy login hanya untuk pembanding, bukan akun. Konfigurasi lokal dan log nyata tidak dicetak atau dipindahkan ke repository.
- Chrome pada HTML hasil render data sintetis: 36 halaman utama/detail/form/panel/login/404, lebar 320/390/768/1440, tanpa horizontal overflow; menu mobile, navigasi Tab, label input/select/textarea, alt gambar, dan empat status tracer dinamis lulus. Penyimpanan dan otorisasi diuji terpisah melalui HTTP aplikasi hidup, bukan diklaim sebagai alur browser end-to-end penuh.
- README duplikat dirapikan; dilengkapi kebutuhan server, htdocs, urutan SQL awal→001→002, konfigurasi/base URL, CLI admin, menjalankan lokal, persiapan hosting, pembersihan demo lewat panel, backup database+uploads dan uji pemulihan, langkah manual serta tabel hasil/batas verifikasi.
- Batas verifikasi eksplisit: HTTPS/sertifikat nyata dan cookie Secure melalui TLS, pembacaan password CLI tanpa echo di terminal interaktif pemasangan, serta lingkungan shared hosting belum diuji langsung. Petunjuk manual tersedia; tidak ada deploy/unggah hosting. SQL awal/001/002 dan database pengguna tidak diubah, tidak ada migrasi tambahan. Semua layanan uji dihentikan; artefak QA workspace dibersihkan. Status tahap 1–11 selesai dalam lingkup lokal; instalasi tujuan tetap mengikuti README dan verifikasi lingkungan tersebut.

## Perubahan setelah audit

- README.md dihapus atas permintaan pengguna agar repository GitHub tidak memuat README. Catatan tahap sebelumnya merupakan riwayat; README tidak lagi tersedia.

## Logo sekolah, PPKS, dan favicon

- Setelah konfirmasi pengguna, kedua logo ditampilkan bersama pada header publik dan panel: assets/images/jb.webp (sekolah) dan ppks.jpeg (Pusat Pengembangan Karir Siswa). Helper site_logos() menyediakan bawaan lokal dan fallback bila pengaturan logo kosong/hilang; logo valid dari pengaturan tetap didukung.
- Favicon publik, panel, dan login memakai ppks.jpeg dengan MIME image/jpeg dan URL helper agar mendukung subfolder. Logo menggunakan alt yang jelas dan object-fit contain, dengan ukuran responsif tanpa mengubah berkas asli. Tidak mengubah database/SQL atau membuat README kembali.
- Lint lima PHP berubah dan git diff --check lulus. Integrasi HTTP/database sementara lulus. Chrome memverifikasi dua gambar termuat, favicon /bkk/assets/images/ppks.jpeg, serta tidak ada overflow pada 320/390/768/1440 untuk publik/panel/login. Artefak QA lokal dibersihkan.

## Menu navigasi dalam header publik

- Setelah klarifikasi dan persetujuan pengguna, menu Beranda sampai Pencarian disatukan dengan area logo/nama sekolah dalam header publik. Desktop memakai grid identitas di kiri, pencarian dan menu di kanan; navbar tidak lagi menjadi baris terpisah di luar header. Panel tidak diubah.
- Mobile/tablet tetap memakai toggle Bootstrap dan dropdown lima program, dengan navigasi aktif/tautan lama dipertahankan. Perubahan terbatas pada layout header/navbar dan CSS utama/responsif.
- Lint dua layout PHP dan integrasi HTTP/database sementara lulus. Chrome pada salinan HTML hasil render memverifikasi struktur header, posisi menu desktop, toggle/dropdown, serta tidak ada overflow pada 320/390/768/1024/1200/1440 piksel. Screenshot desktop diperiksa; artefak QA workspace dibersihkan. Tidak mengubah SQL/database atau membuat README.

## Navigasi pada bar navy paling atas

- Mengikuti gambar terbaru pengguna, seluruh menu dipindahkan dari masthead ke topbar navy, menggantikan teks Portal Bursa Kerja Khusus. Login Pengelola tetap di kanan; dua logo, nama sekolah, dan pencarian tetap pada area putih di bawah. Hanya layout publik dan CSS terkait yang berubah.
- Teks menu putih, menu aktif/hover emas, isi dropdown gelap pada latar putih. Memperkuat specificity CSS setelah pemeriksaan screenshot menemukan aturan nav lama menimpa warna. Pada ponsel, tombol Menu berada di kiri dan Login di kanan; dropdown/tautan aktif tetap tersedia.
- Lint PHP dan integrasi HTTP/database sementara lulus. Uji browser awal struktur topbar, toggle/dropdown dan overflow lulus pada 320/390/768/1024/1200/1440. Setelah koreksi warna, computed style putih/emas dan interaksi diverifikasi pada 320; pengulangan lintas viewport terganggu reset koneksi server preview, bukan error aplikasi. Tidak mengubah SQL/database. Artefak QA dibersihkan.

## Sidebar panel profesional dengan tema

- Atas instruksi pengguna mengikuti referensi sidebar, panel Admin/Staf memakai sidebar navy–emas dengan ikon SVG lokal, menu aktif, mode penuh/ikon, serta tema terang/gelap. Preferensi sidebar dan tema disimpan di localStorage, dipulihkan sebelum stylesheet tema untuk mengurangi kedipan. Tidak menambah library.
- Layout panel bersama diperbarui; logo sekolah/PPKS dan favicon dipertahankan. Akun tampil pada navbar; Keluar dipindah ke bawah sidebar dengan POST+CSRF. Menu Pengguna/Pengaturan tetap hanya Admin dan middleware/handler tidak dilonggarkan. Ikon statis berasal dari panel/layouts/icons.php, gaya baru terpisah pada assets/css/panel-theme.css.
- Sidebar desktop sticky mengikuti tinggi navbar yang diukur ResizeObserver; area menu dapat digulir, tombol tema/Keluar tetap terlihat. Mode ikon mempertahankan title/aria-label menu. Pada layar <=900px, drawer memakai backdrop, tombol tutup/Escape, focus trap dan pengembalian fokus ke tombol pembuka; konten belakang inert saat drawer terbuka. Tanpa JavaScript, daftar menu tetap dapat diakses.
- Suite HTTP/database sementara akun/mitra/pengaturan dan integrasi publik/dashboard lulus, termasuk kedua role, penolakan halaman/handler Admin bagi Staf, logout dan CSRF. Lint layout PHP dan git diff --check lulus. Tidak mengubah SQL/database atau membuat README.
- Chrome pada snapshot HTML hasil render memverifikasi dashboard/daftar/form mitra/pengaturan pada 320/390/768/1024/1440: tanpa overflow, collapse 88px, label aksesibel, tema light/dark dan penyimpanan preferensi, drawer/Escape/backdrop/focus trap, posisi Keluar dalam viewport desktop. Preferensi saat dokumen dimuat ulang dan navigasi tanpa JavaScript juga lulus. Screenshot desktop/mobile diperiksa; artefak QA dibersihkan.

## Desain halaman login sesuai referensi

- Mengikuti instruksi pengguna, halaman login memakai kartu besar dua kolom: form putih di kiri, panel biru dengan geometri coral/pink dan laptop di kanan, tombol Masuk hitam, input garis bawah, sudut membulat dan latar abu-abu. Identitas/logo sekolah dan PPKS serta favicon dipertahankan; teks Indonesia untuk login pengelola, bukan pendaftaran akun.
- Gaya terisolasi di assets/css/login.css. Ilustrasi vektor lokal assets/images/login-illustration.svg dibuat khusus tanpa foto/dependency/font eksternal atau data resmi rekaan. Mobile <=700px memakai satu kolom formulir. Tidak mengubah CSS panel/publik, handler autentikasi, database, maupun SQL.
- Form tetap POST ke proses-login.php, field username/email dan password, CSRF, autocomplete, pesan error ter-escape dan tautan beranda. Lint PHP, XML SVG dan git diff --check lulus. Suite HTTP/database sementara akun/role/login/logout lulus.
- Chrome untuk kondisi normal/error pada lebar 320/390/600/768/906/1200/1440 lulus: tanpa overflow, gambar termuat, label, native validation, urutan fokus keyboard, serta form action/method/token benar. Screenshot desktop 906x600 dan mobile diperiksa. Kontras teks pendukung diperjelas; artefak QA dibersihkan. README tidak dibuat kembali.

## Penyempurnaan login responsif HP/laptop

- Viewport memakai viewport-fit=cover dan interactive-widget=resizes-content. CSS menggunakan dynamic viewport height, padding safe-area, kolom minmax, dan tinggi kartu otomatis agar formulir/pesan error tidak terpotong ketika layar pendek. Mobile dan HP landscape pendek memakai satu kolom; laptop tetap dua kolom.
- Input 16px untuk menghindari auto-zoom fokus pada iPhone, input/tombol minimal 44px, ruang gulir fokus, serta jarak vertikal yang menyesuaikan layar. Tidak mengunci scroll atau menonaktifkan zoom pengguna. Handler login/CSRF dan database tidak diubah.
- Chrome pada 12 ukuran (320x568 sampai 1440x900), termasuk HP portrait, landscape 844x390 dan viewport pendek 390x320, lulus untuk normal/error: tidak ada overflow atau konten terpotong, password dan tombol dapat digulir ke area terlihat. Lint PHP dan diff check lulus. Keyboard/safe-area perangkat fisik Safari belum diuji langsung; viewport pendek hanya simulasi browser. Artefak QA dibersihkan.

## Palet login lebih hidup

- Atas permintaan pengguna, palet login kini biru dengan aksen coral/emas yang mengikuti ilustrasi. Latar memakai gradasi pastel biru dan peach, kartu bertint lembut dengan garis aksen tiga warna, judul navy dan indikator gradasi, input berisi biru pucat, tombol gradasi biru dengan bayangan dan feedback hover/fokus, tautan biru serta badge role krem-emas.
- Perubahan hanya assets/css/login.css; tetap mempertahankan identitas sekolah/PPKS, tata letak responsif, input 16px, target sentuh minimal 48px, safe-area dan scroll. Tidak mengubah alur/handler login atau database.
- Chrome pada sembilan ukuran 320x568 sampai 1440x900, termasuk landscape dan viewport pendek, lulus untuk normal/error: tidak ada overflow/konten terpotong, gradasi tombol dan font input benar, fokus password dapat digulir ke area terlihat. Screenshot diperiksa; git diff --check lulus dan artefak QA dibersihkan.

## Memperjelas dua logo pada login

- Logo sekolah jb.webp dan PPKS ppks.jpeg diperbesar pada bagian atas kartu login, dengan bingkai putih lembut dan alt lengkap. Ukuran 64px desktop, 56px HP, dan 48px layar kecil; object-fit contain mempertahankan proporsi.
- Pemeriksaan hasil render CLI memastikan kedua logo dan URL subfolder tersedia; sintaks PHP serta git diff --check lulus. Tidak mengubah handler login/database.

- Permintaan lanjutan: pada HP portrait maupun landscape pendek, kedua logo kini rata tengah, dengan identitas BKK/sekolah di bawahnya. Aturan hanya pada breakpoint mobile; desktop tetap memakai susunan sebelumnya. Pemeriksaan diff lulus.

## Pembuatan Admin pertama pada database lokal

- Atas instruksi eksplisit pengguna, Admin aktif dibuat langsung pada database lokal melalui CLI interaktif yang sudah tersedia. Password dibaca tersembunyi dan disimpan sebagai hash bcrypt; tidak disimpan pada source, SQL, dokumentasi, maupun log. Username/password/hash tidak dicatat di progres ini.
- Email belum diberikan, sehingga digunakan placeholder akun internal berakhiran example.invalid, yang dapat diganti pada profil. Konfigurasi koneksi XAMPP lokal dibuat pada app/config/app.local.php yang diabaikan Git; file tersebut tidak memuat password akun aplikasi.
- Verifikasi database memastikan role admin, aktif=1 dan algoritma bcrypt. Uji HTTP terhadap Apache lokal berhasil: login menggunakan akun baru, membuka dashboard, logout POST+CSRF, lalu akses panel kembali mengarah ke login. Tidak membuat kredensial bawaan atau mengubah SQL instalasi.

## Penyempurnaan seluruh halaman publik

- Setelah persetujuan pengguna, seluruh layout publik memakai assets/css/portal.css: navy–emas dengan aksen biru/coral/teal/violet lembut, header/identitas rapi, kartu membulat, hierarki judul, form/tombol mudah disentuh dan footer konsisten. Tidak mengubah halaman login/panel atau menambah library.
- Menu tetap pada topbar navy yang sticky. Pada HP/tablet, hamburger membuka menu lebar penuh dengan batas tinggi dan scroll; Escape mengembalikan fokus ke pembuka dan klik di luar menutup menu. Menu/tautan aktif tetap server-driven, termasuk kategori program; fallback tanpa JavaScript/Bootstrap tetap dalam alur halaman dan dapat diakses.
- Logo dan identitas sekolah rata tengah pada HP, search input 16px, tombol utama/login/dropdown 44px, kartu berita/program/sidebar satu kolom pada HP. Form tracer, filter, detail artikel/lowongan/program, profil, pencarian dan 404 mendapat gaya bersama. Fokus keyboard dan reduced-motion dipertahankan; jarak scroll anchor memperhitungkan topbar.
- Beranda memakai hero gradasi lembut dengan CTA Lowongan dan formulir Tracer serta anchor program. Empty state tetap menjelaskan belum ada konten published; tidak mengisi konten resmi rekaan. Kartu lima program memakai aksen berbeda. Alt mitra kini menyebut nama dan simbol tautan mitra diperbaiki. Query/visibilitas published, validasi server dan privasi tracer tidak diubah.
- Lint tiga PHP berubah, git diff --check, uji CLI jobs/tracer, suite HTTP/MariaDB pencarian/dashboard/publik dan suite tracer lengkap lulus pada lingkungan sementara. Database nyata tidak diubah; hanya membaca halaman publik untuk snapshot empty state. Tidak membuat SQL baru atau README.
- Chrome pada 19 halaman/state publik (beranda, profil, berita, pencarian, lowongan, tracer info/form, lima program, detail bersama, 404, empty state) lulus pada 320/390/600/768/1024/1200/1440: tanpa overflow, brand terpusat, label/alt, menu/dropdown/Escape, dan empat kelompok tracer dinamis. Screenshot desktop/mobile diperiksa. Setelah penyesuaian target sentuh 44px, uji tambahan home/form pada 320/1440 serta fallback tanpa JS/Bootstrap lulus. Browser memakai HTML hasil render dan aset lokal yang disertakan dalam preview; tidak diklaim sebagai pengujian perangkat fisik. Artefak QA dibersihkan.

## Membatasi kepadatan daftar berita

- Setelah persetujuan pengguna, daftar berita menampilkan maksimal 6 kartu per halaman melalui LIMIT/OFFSET, dengan urutan tanggal terbaru dan id sebagai pemecah urutan. Berita lama tetap tersedia melalui pagination dan pencarian. Bagian berita terbaru di beranda mempertahankan batas 6; bagian Sorotan tetap terpisah dan terbatas seperti sebelumnya.
- Kartu berita diberi kelas khusus news-story-card agar gaya tidak memengaruhi kartu program. Judul maksimal 2 baris, ringkasan maksimal 3 baris pada grid, gambar tetap rasio 16:10. Badan kartu memakai flex agar tautan baca sejajar pada setiap baris; teks panjang tetap membungkus. HP satu kolom, tablet/desktop dua kolom, isi lengkap tetap tersedia pada detail.
- Lint kedua PHP dan git diff --check lulus. Suite HTTP/MariaDB sementara untuk integrasi publik lulus, ditambah 13 berita published yang terbagi 6/6/1 tanpa duplikasi/kehilangan, batas halaman ekstrem, pengecualian draft/terjadwal, dan batas 6 pada bagian terbaru beranda. Fixture judul/ringkasan panjang berada dalam batas kolom database.
- Chrome pada snapshot hasil render dengan aset lokal lulus di lebar 320/390/768/1024/1440: jumlah kartu, jumlah kolom, clamp teks, rasio gambar, tinggi kartu sejajar pada setiap baris desktop, dan tanpa horizontal overflow. Bukan pengujian perangkat fisik. Tidak mengubah database nyata, SQL, README, atau modul lain; artefak QA dibersihkan.

## Menghapus Sorotan BKK dari beranda

- Atas permintaan pengguna, seluruh bagian Sorotan BKK (berita utama, pendamping, dan placeholder) dihapus dari index.php beserta query/variabel yang tidak lagi digunakan. Bagian berita terbaru tetap maksimal 6 kartu dengan tautan daftar lengkap.
- Lint index.php dan pemeriksaan HTTP lokal lulus: status 200, Sorotan/placeholder tidak muncul, bagian program/lowongan/berita terbaru tetap tersedia. git diff --check lulus. Tidak mengubah database atau SQL.

## Persiapan batik sekolah pada kartu program

- Gaya batik navy dengan aksen emas dan lapisan gelap untuk keterbacaan disiapkan pada lima kartu program. index.php mengaktifkan kelas program-section-batik hanya jika assets/images/batik-sekolah.jpg tersedia; URL CSS relatif mendukung subfolder. Hover mempertahankan motif dan teks putih.
- Status belum selesai: gambar referensi ada di chat tetapi berkas batik belum tersedia di proyek; tidak mengganti dengan motif rekaan. Tampilan awal tetap digunakan sampai berkas tersedia.
- Lint PHP, git diff --check dan HTTP lokal lulus: kelima kartu tetap tersedia dan kelas batik belum aktif saat gambar tidak ada. Tampilan bermotif belum dapat diverifikasi tanpa aset asli. Tidak mengubah database/SQL.

## Mengaktifkan aset batik PNG

- Pengguna mengonfirmasi nama batik-sekolah.png; berkas asli kini tersedia di assets/images. Referensi pemeriksaan file di index.php dan latar CSS disesuaikan ke PNG sehingga motif aktif pada lima kartu program.
- Lint PHP, git diff --check dan HTTP lokal lulus: kelas batik aktif, kelima kartu tersedia, CSS merujuk PNG, gambar merespons 200 dengan signature PNG valid. Tampilan visual browser belum diuji ulang pada perubahan ekstensi ini. Tidak mengubah database/SQL.

## Melembutkan motif batik untuk keterbacaan

- Lapisan navy pada kartu batik diperkuat dengan transisi bertahap dari 82% di atas sampai 99% di bawah; motif lebih terlihat pada area dekorasi atas dan semakin samar di belakang judul/deskripsi. Bayangan judul dihapus dan jarak baris teks diperlonggar.
- Pemeriksaan HTTP lokal memastikan lima kartu aktif dan CSS terbaru termuat. Perhitungan kontras sRGB terhadap motif putih pada lapisan paling ringan memenuhi 4.5:1 untuk teks putih, deskripsi dan nomor emas. git diff --check lulus; pemeriksaan visual browser belum dilakukan pada penyesuaian ini. Hanya portal.css dan progres berubah.

## Batik header dan footer minimalis

- Header publik memakai motif batik lokal dengan lapisan putih sangat lembut agar identitas/logo/pencarian tetap jelas; topbar dan footer memakai motif dengan lapisan navy pekat. Menggunakan aset PNG yang sama, tanpa mengubah gambar aslinya.
- Footer publik diringkas dari empat kolom menjadi identitas dan navigasi horizontal, diikuti baris copyright/login serta media sosial hanya jika URL valid sudah diisi. Tautan Profil & kontak menggantikan blok alamat/kontak panjang; placeholder media sosial dihapus dari footer. Pada HP susunan membungkus dan bertumpuk secara wajar, target tautan tetap 44px.
- Lint footer PHP dan git diff --check lulus. HTTP lokal memastikan beranda berhasil, footer ringkas dirender tanpa kolom lama/placeholder, dan semua tautan footer lokal dapat dibuka. Belum menguji ulang tampilan browser desktop/mobile pada perubahan ini. Tidak mengubah panel/login, database, SQL atau README.
