# Progres BKK SMK JAYA BUANA

## Pengembangan lanjutan — Tahap 7 Lowongan dan hasil alumni (6 November 2026)

- Memperbaiki URL lowongan yang semula dikosongkan. Lowongan baru/edit wajib URL rekrutmen dan konfirmasi pengelola, dengan jenis hubungan kerja terkelola. Jenis awal Harian Lepas/PKWT/PKWTT tanpa uraian hukum rekaan; penjelasan/rujukan diisi sekolah.
- Tautan alumni Tahap 1 dipakai untuk pencatatan lamaran, hasil seleksi dan rincian kerja; tidak membuat akun/token terpisah. Seleksi selesai wajib hasil Lolos/Tidak Lolos. Belum Mengisi dan Dalam Proses dibedakan.
- Dashboard internal menampilkan nama/hasil dan rekap otomatis. Keterserapan hanya dari laporan sudah mulai bekerja lengkap, terpisah dari jumlah alumni Lolos. Deduplikasi NISN dan batas populasi tracer dijelaskan di dashboard; data lama tidak ditebak sebagai pekerjaan aktual.
- Migrasi tambahan 024 disiapkan dan diuji impor ulang; database asli belum dimigrasikan. 86 pemeriksaan HTTP Tahap 7, 86 regresi Tahap 6, 73 regresi Tahap 5, regresi CLI, sintaks 221 PHP dan diff check lulus. Panduan/batas pengujian: docs/LOWONGAN-HASIL-ALUMNI.md. Tidak melanjutkan tahap lain.

## Pengembangan lanjutan — Tahap 6 Sarasehan dan Kotak Ide Industri (6 November 2026)

- Jadwal Sarasehan memuat nama, tanggal/jam WIB, lokasi/tautan, mitra undangan, PIC sekolah dan PIC per mitra; data mitra/profil dipakai kembali. Izin sarasehan+mitra diterapkan termasuk simpan dan Excel.
- Kotak Ide privat memakai izin ide-industri+mitra. Tautan perusahaan aktif memakai token hash, batas waktu, pencabutan/rotasi, sekali kirim dan binding formulir agar tidak berpindah perusahaan. Tidak memberikan login/dashboard atau akses riwayat kepada mitra.
- Isi/kontak hanya dibaca pengelola berizin; konfirmasi mitra tanpa data usulan, input di-escape, CSRF dan pembatas pengiriman tersedia. Perubahan status tidak menimpa isi/kontak asli. Data tidak ditambahkan ke publik atau ekspor Sarasehan.
- Migrasi tambahan 023 disiapkan dan diuji impor ulang; database asli belum dimigrasikan. 86 pemeriksaan HTTP Tahap 6, 73 regresi HTTP Tahap 5, 77 regresi HTTP Tahap 4, regresi CLI, sintaks 212 PHP dan diff check lulus. Panduan/batas pengujian: docs/SARASEHAN-KOTAK-IDE.md. Tidak melanjutkan tahap lain.

## Pengembangan lanjutan — Tahap 5 Mitra, Kampus dan MoU (6 November 2026)

- Mempertahankan mitra sebagai data induk; profil tambahan memuat klaster industri dalam/luar negeri, bidang, alamat, negara, dan PIC privat. Direktori Kampus Dalam Negeri memakai negara Indonesia yang sudah diisi; data lama tidak diklasifikasi otomatis.
- Riwayat MoU menyimpan nomor, tanggal, dan PDF privat pada storage/mou. Status Belum Berlaku/Aktif/Kedaluwarsa dihitung per hari WIB; perpanjangan menambah dokumen baru yang merujuk dokumen lama. Riwayat tidak ditimpa.
- Izin mou+mitra diperiksa untuk URL/unggah/unduh. PDF tidak dimasukkan ke media publik; kegagalan penyimpanan membersihkan unggahan baru dengan perlindungan dokumen tersimpan. Data PIC dan MoU tidak masuk beranda publik.
- Migrasi tambahan 022 disiapkan dan diuji impor ulang; database asli belum dimigrasikan. Panduan: docs/MITRA-MOU.md. 73 pemeriksaan HTTP Tahap 5, 77 regresi Tahap 4, 70 regresi Tahap 3, regresi CLI, sintaks 199 PHP dan diff check lulus. Tidak melanjutkan tahap lain.

## Pengembangan lanjutan — Tahap 4 BMW Sakti dan Kelas Industri (6 November 2026)

- Deskripsi publik BMW Sakti memakai settings lama dengan draft/publikasi, editor berizin Program, CSRF dan versi. Navigasi publik/panel ditambahkan tanpa mengganti katalog program lama.
- Operasional Kelas Industri memakai mitra, pemateri dan siswa lama. Rencana 1–500 menghasilkan slot pertemuan bernomor, jadwal/status dan rekap Terlaksana dibanding rencana. Pengurangan rencana melindungi pertemuan yang sudah diisi. Peserta/jadwal tetap privat; izin kelas-industri+peserta diwajibkan termasuk unduhan.
- Migrasi tambahan 021 disiapkan dan diuji impor ulang; database asli tidak dimigrasikan. Tidak membuat akun atau menimpa data lama.
- 77 pemeriksaan HTTP Tahap 4, 70 regresi HTTP Tahap 3, 63 regresi HTTP Tahap 2, dan regresi CLI lulus pada lingkungan sementara. Panduan file/penerapan dan batas pengujian: docs/BMW-KELAS-INDUSTRI.md. Tidak melanjutkan tahap lain.

## Pengembangan lanjutan — Tahap 3 Jadwal PKL dan perjalanan dinas (6 November 2026)

- Menambahkan jadwal monitoring mingguan, pertemuan orang tua/wali, dan sidang berdasarkan penempatan/periode PKL, dengan penugasan guru, tanggal/jam WIB, lokasi/alamat, status, hasil monitoring, dan tindak lanjut.
- Perjalanan dinas dapat dikaitkan ke monitoring dari penempatan yang sama. Tujuan/petugas/tanggal, nominal DECIMAL dan sumber sekolah/pribadi disimpan terpisah. Izin biaya-perjalanan+pkl+peserta diwajibkan, termasuk URL/POST/unduhan. Ekspor jadwal tidak memuat biaya.
- Migrasi tambahan 020 disiapkan dan diuji impor ulang; database asli tidak dimigrasikan. Jadwal lama dilindungi saat periode PKL diedit; konflik versi dan CSRF diperiksa. Tidak membuat akun guru/wali/siswa.
- 70 pemeriksaan HTTP Tahap 3 dan 63 regresi HTTP Tahap 2 lulus pada salinan aplikasi/database; regresi CLI, sintaks 181 PHP dan diff check lulus. Panduan dan batas pengujian: docs/PKL-JADWAL-PERJALANAN.md. Tidak melanjutkan tahap lain.

## Pengembangan lanjutan — Tahap 2 PKL (6 November 2026)

- Modul penempatan PKL memakai identitas dari tabel siswa lama dan tabel penempatan/jurnal/laporan tambahan. Kolom tempat_pkl/tahun_pkl lama tidak ditimpa; beberapa periode per siswa didukung.
- Penugasan guru dan pembimbing industri berupa data penempatan, tanpa akun baru. Operasional hanya Admin atau Staf dengan izin pkl dan peserta, termasuk unduhan Excel/HTML.
- Jurnal tanggal/kegiatan/catatan, rekap Senin–Minggu ISO, laporan akhir teks dan dokumen cetak, validasi periode, CSRF, pengikatan jurnal ke penempatan, dan pemeriksaan versi tersedia.
- Migrasi 019 bersifat tambahan dan diuji impor ulang. Database asli belum dimigrasikan atau diisi data uji. Panduan penerapan, file, penggunaan, dan batas pengujian terdapat di docs/PKL.md.
- 63 pemeriksaan HTTP pada salinan aplikasi/database dan data sintetis lulus; tidak ada warning/error PHP. Regresi CLI dan sintaks PHP juga diperiksa. Tidak melanjutkan tahap lain.

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
- Kelima index publik menggunakan program-list.php (pagination 9 item); kelima detail.php hanya menetapkan kategori lalu memuat program-detail.php. Query detail selalu membatasi slug + kategori + published, sehingga draft/lintas kategori 404. URL opsional http/https diperiksa saat simpan dan render; konten HTML di-escape menjadi teks/paragraf. Kelas Melanjutkan dapat memuat penjelasan kuliah dalam/luar negeri dan beapeserta tanpa struktur CRUD tambahan.
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

- Setelah konfirmasi pengguna, kedua logo ditampilkan bersama pada header publik dan panel: assets/images/jb.webp (sekolah) dan ppks.jpeg (Pusat Pengembangan Karir Peserta). Helper site_logos() menyediakan bawaan lokal dan fallback bila pengaturan logo kosong/hilang; logo valid dari pengaturan tetap didukung.
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

## Opsi B ? Penyempurnaan tampilan dan aset (18 November 2026)

- Fokus pada tampilan publik dan optimasi aset tanpa migrasi database atau perubahan alur bisnis. Motif asli batik-sekolah.png dipertahankan; versi tampilan WebP 768x432 kualitas 84 dibuat melalui GD. Ukuran turun dari 1.972.683 menjadi 52.206 byte (97,4%); dua referensi CSS batik pada hero/topbar/footer memakai WebP lokal dengan URL relatif yang mendukung subfolder.
- Footer tetap minimalis: tautan dibungkus secara wajar dengan font minimal 12px dan target tinggi 44px, copyright 11,2px. Teks profil/kontak, deskripsi hero HP, label/penjelasan tracer diperjelas. Input tracer 16px dan minimal 44px pada HP/laptop; tombol hero dan kontrol slider 44px.
- Menemukan overflow halaman HP 320px dari label visually-hidden pada tautan berita di dalam slider. Tautan dijadikan containing block melalui position:relative agar label tetap terikat kartu; label aksesibilitas tidak dihapus dan slider tetap berfungsi.
- Chrome headless pada Apache lokal menguji 15 halaman publik/login di lebar 320/390/768/1024/1440: tanpa horizontal overflow halaman atau gambar selesai dimuat yang gagal. Lazy-loading yang belum dimuat tidak dihitung sebagai gambar rusak. Interaksi tambahan home/profil/form tracer pada 320/390/1440 lulus: hamburger, Escape dan pengembalian fokus, tombol slider, empat kelompok status tracer, input 16px dan target footer 44px. Screenshot desktop/HP diperiksa. Footer terukur sekitar 113px desktop dan 196px HP.
- HTTP WebP 200 dan signature valid; CSS memakai aset baru; git diff --check lulus. Perangkat fisik Safari, jaringan seluler nyata, halaman panel dan detail dengan konten panjang belum diuji ulang dalam tahap ini. Tidak mengubah database/SQL, mengisi data, membuat README atau deploy. Artefak QA sementara dibersihkan.

## Menghapus rekap hasil alumni dari dashboard

- Atas permintaan pengguna, komponen Hasil seleksi dan keterserapan alumni dihapus dari dashboard Admin dan Staf. Filter tahun, tiga kartu angka, keterangan dan tabel komponen tersebut tidak lagi dirender di dashboard. Modul panel/hasil-alumni tetap memakai komponen yang sama dan hak akses tetap berlaku. Tidak menghapus data atau fungsi pengelolaan alumni.
- Lint kedua PHP dan git diff --check lulus. Pemeriksaan sumber memastikan include komponen tidak ada di kedua dashboard dan tetap ada pada halaman modul. Belum melakukan login/browser ulang untuk verifikasi visual dashboard. Tidak mengubah database/SQL.

## Revisi visual modul Hasil Alumni

- Komponen career-dashboard.php memakai header berikon, tombol kelola, filter berlabel eksplisit, dan tiga kartu statistik biru/teal/ungu lembut. Angka lebih menonjol dengan ikon lokal peserta/pekerjaan/sertifikat; tanpa library tambahan. Penjelasan perhitungan tetap tersedia melalui details/summary yang dapat dibuka.
- Rincian alumni diberi identitas, badge tahun/status, dan tombol tindakan. Tabel memakai scope header/caption; pada layar kecil setiap baris tersusun vertikal dengan label kolom. Empty state, pagination dan parameter tahun dipertahankan. CSS terisolasi pada #career-results serta mendukung tema gelap. Tidak menambahkan komponen kembali ke dashboard Admin/Staf.
- Query, hitungan keterserapan, izin dan database tidak diubah. Lint PHP, regresi CLI careers.php dan diff check lulus. Chrome CDP memeriksa hasil render komponen dengan data sintetis, kondisi kosong/terisi dan jurusan panjang pada 320/390/768/1440 dalam tema terang/gelap: tanpa overflow, tiga statistik, jumlah kolom sesuai, filter terpilih dan details dapat dibuka. Escaping nama uji dan pelestarian filter pagination diperiksa. Screenshot HP/desktop diperiksa. Pengujian ini memakai komponen terisolasi, bukan login atau transaksi database nyata; perangkat fisik belum diuji. Artefak QA dibersihkan.

## Sidebar berkelompok dengan submenu

- Menu panel dikelompokkan menjadi Publikasi, Karier & Alumni, Program BMW SAKTI, Siswa & Pembelajaran, PKL & Perjalanan, Mitra & Kerja Sama, serta Administrasi khusus Admin. Dashboard dan Akun Pribadi tetap tautan langsung. Semua tujuan menu lama dipertahankan; Daftar program ditambahkan sebagai tautan katalog program yang sudah tersedia.
- Setiap kelompok memakai details/summary native dengan ikon, indikator buka/tutup dan aria-expanded saat JS aktif. Kelompok halaman aktif terbuka dari server; satu kelompok dibuka pada satu waktu dengan JS. Pada mode ikon desktop, memilih kelompok memperluas sidebar dan membuka submenu. Tanpa JS, details tetap berfungsi. Summary dimasukkan dalam kontrol focus trap drawer HP.
- Filter izin dan dependensi tetap diterapkan per item; kelompok tanpa item yang diizinkan disembunyikan. Administrasi tidak dirender bagi Staf. Pengecekan izin memakai cache lokal per render, bukan mengubah middleware. Badge lamaran dan CSRF logout dipertahankan. Memperbaiki flex-wrap warisan agar menu panjang menggulir vertikal, bukan membentuk kolom horizontal di HP.
- Lint PHP, regresi CLI access.php dan diff check lulus. Render sintetis delapan kondisi (Admin, halaman aktif lowongan/kategori/kampus, Staf tanpa izin/berita saja/izin tanpa dependensi/semua izin) memverifikasi kelompok kosong, 32 tujuan Admin tanpa duplikasi, 30 tujuan Staf lengkap tanpa administrasi, dan satu tautan aktif.
- Chrome CDP pada fixture sidebar dengan CSS/JS asli lulus pada 320/390/768/1024/1440 dalam dua tema: accordion, penanda aktif, Enter, aria-expanded, mode ikon, drawer/Escape/pengembalian fokus dan tanpa overflow halaman/menu. Navigasi tanpa JavaScript serta screenshot HP/desktop diperiksa. Ini pengujian layout terisolasi dengan izin sintetis; tidak login ulang ke database nyata atau mengubah database/SQL. Artefak QA dibersihkan.

## Garis tabel Lowongan seperti spreadsheet

- Atas permintaan pengguna, tabel daftar Lowongan diberi kelas vacancy-sheet dan garis vertikal/horizontal pada setiap sel, termasuk header. Garis lebih tegas, header biru abu-abu, sudut luar diperkecil, dan baris mendapat sorotan hover/fokus. Tema gelap memakai warna garis/header tersendiri. Pada layar kecil tabel tetap berbentuk kolom dan dapat digulir horizontal di kontainer.
- Perubahan hanya kelas pembungkus tabel di panel/lowongan/index.php dan CSS terisolasi di panel-theme.css. Filter, pagination, data, tautan dan POST/CSRF hapus tidak diubah. Lint PHP dan git diff --check lulus; belum melakukan verifikasi visual browser ulang pada perubahan ini. Tidak mengubah database/SQL.

## Garis spreadsheet pada Lamaran Masuk

- Tabel Lamaran Masuk memakai garis setiap baris/kolom seperti Lowongan. Gaya bersama diberi nama panel-sheet dan dipakai kedua daftar, tanpa menggandakan CSS; header biru abu-abu dan tema gelap tetap tersedia.
- Kelas kontainer gulir vertikal, atribut aksesibilitas, ID, kontrol naik/turun, filter/pagination, hak akses dan isi data Lamaran dipertahankan. Perubahan PHP hanya kelas pembungkus tabel. Lint kedua halaman dan git diff --check lulus; tampilan browser belum diuji ulang. Tidak mengubah database/SQL.

## Pemberitahuan WhatsApp dari Lamaran Masuk

- Menambahkan tombol WhatsApp per lamaran bagi Admin/Staf dengan izin whatsapp+lamaran+tracer-study. Halaman panel/whatsapp/pelamar.php membaca lamaran dan memeriksa career_application_guard sebelum menampilkan pesan. Empat template mengikuti status internal Baru/Diproses/Diterima/Ditolak, tanpa mengarang keputusan perusahaan; pesan dapat diedit sebelum disiapkan.
- Memakai helper nomor, deduplikasi, opt-out, daftar pesan, submission key dan handler POST+CSRF WhatsApp yang sudah ada. Form manual hanya memilih satu sumber penerima yang sesuai lamaran/lowongan; nomor tidak valid, opt-out atau lamaran lama yang terwakili nomor lebih baru mendapat penjelasan dan tautan periksa penerima. Tidak menimpa keranjang/draft WhatsApp sesi yang lain.
- Handler buat pesan kini memeriksa kembali hak akses lamaran pelamar melalui career_application_guard sebelum membuat daftar. Alur menyiapkan pesan, lalu Buka WhatsApp dan Kirim manual; tidak ada pengiriman otomatis atau klaim pesan telah diterima. CSS tombol WhatsApp dan form dibatasi pada komponen terkait. Tidak membuat SQL atau mengubah skema.
- Lint empat PHP dan git diff --check lulus. CLI menguji empat template, penolakan status tidak sah, personalisasi nama, normalisasi nomor dan pemetaan izin endpoint; regresi access.php lulus. HTTP tanpa login ke endpoint baru mengarah ke login. Belum menguji alur authenticated simpan daftar pada database sementara atau tampilan browser; tidak mengirim pesan nyata dan tidak mengubah data pelamar saat pengujian.

## Pemberitahuan WhatsApp lebih minimalis

- Form pemberitahuan dipersempit menjadi maksimal 720px dengan kartu datar, identitas/nomor penerima satu area, konteks lowongan singkat, textarea delapan baris dan satu keterangan pengiriman manual. Label Pesan terhubung dengan bantuan melalui aria-describedby; tombol menjadi Siapkan pesan.
- Tombol WhatsApp pada tabel Lamaran menjadi outline netral yang lebih ringan, dengan ikon dan target sentuh 44px tetap tersedia. CSS memakai variabel tema. Tidak mengubah penerima, template, handler, hak akses, CSRF atau database. Lint PHP dan git diff --check lulus; belum memeriksa ulang tampilan browser.

## Merapikan tombol tindakan Lamaran

- Tombol Lihat dan WhatsApp dibungkus application-actions dalam satu baris flex tanpa wrap, jarak 8px, margin nol, tinggi target 44px dan ukuran ikon konsisten. Lihat tetap hijau lembut, WhatsApp outline mengikuti tema. Menghapus aturan margin WhatsApp lama yang menyebabkan posisi tidak sejajar. Tabel tetap dapat digulir pada layar kecil.
- Hanya markup pembungkus dan CSS tindakan berubah; URL/izin/notifikasi tetap sama. Lint PHP dan git diff --check lulus; belum memeriksa ulang tampilan browser.

## Tindakan Lamaran menjadi ikon ringkas

- Lihat dan WhatsApp kini berupa tombol ikon outline 44x44px dengan jarak 6px, title dan aria-label yang menyebut penerima. Kolom Tindakan ditetapkan 128px agar kedua ikon muat tanpa terpotong, tanpa tombol teks panjang atau warna blok besar. Warna WhatsApp menyesuaikan tema gelap.
- Lint PHP dan diff check lulus. Chrome pada fixture tabel dengan CSS asli di lebar 800/1440 pada dua tema memastikan kedua tombol berada dalam batas sel serta ukuran 44px. Pengujian memakai fixture, bukan login/lamaran nyata. Tidak mengubah izin, URL, alur WhatsApp atau database; artefak QA dibersihkan.

## Merapikan form Pesan WhatsApp

- Halaman pemberitahuan memakai kelas applicant-notification-page agar tidak berbagi nama dengan tombol notifikasi navbar. Header dan tautan kembali sejajar, penerima berikon akun dan nomor dalam chip, konteks lowongan dipisah garis tipis, textarea 16px dan footer bantuan/tombol tersusun horizontal desktop serta vertikal pada area kecil. Ikon tombol dibuat inline-flex dan tinggi tetap 44px, tanpa menumpuk di atas teks.
- Handler/POST/CSRF, isi template dan penerima tidak diubah. Lint PHP dan diff check lulus. Render PHP asli dengan data sintetis dan CSS panel diuji pada area komponen 320/720px dalam dua tema: komponen muat, ikon tombol sejajar, tinggi 44px, font textarea 16px, action/token formulir tetap tersedia. Ini uji komponen terisolasi, bukan alur kirim atau perangkat fisik. Tidak mengirim pesan atau mengubah database; artefak QA dibersihkan.

## Membuka langsung chat WhatsApp di laptop

- Tombol Buka WhatsApp kini mengarahkan browser desktop ke https://web.whatsapp.com/send dengan parameter phone dan text yang di-encode RFC3986. Android/iPhone/iPad memakai tautan resmi wa.me yang dapat membuka aplikasi. Nomor tetap dinormalisasi helper yang sama; tujuan URL hanya dua host tetap.
- Handler POST+CSRF, pengecekan penerima/opt-out dan pencatatan dibuka tetap dipakai. Panduan halaman menjelaskan kebutuhan login WhatsApp pada browser yang sama, nomor aktif, serta Kirim manual; tidak mengklaim pesan terkirim hanya karena tautan dibuka. Tidak menambah API/dependency, SQL atau mengirim pesan otomatis.
- Lint tiga PHP, diff check dan CLI URL builder lulus: Windows/Mac/kosong menuju Web, Android/iPhone/iPad menuju app-aware link, nomor nasional menjadi internasional, pesan multibaris dan karakter khusus utuh, nomor berbahaya ditolak. Pembukaan chat dalam sesi WhatsApp pengguna dan penerimaan pesan belum dapat diverifikasi dari lingkungan ini. Tidak membuka URL WhatsApp berisi data pelamar atau mengirim pesan nyata saat pengujian.

## Mengaktifkan kembali pintu lamaran melalui BKK

- Atas persetujuan pengguna, detail lowongan aktif menyediakan tombol Lamar melalui BKK menuju formulir publik lamar.php yang sudah ada. Tombol tidak bergantung pada metadata migrasi 024 sehingga jalur BKK tetap tersedia untuk lowongan lama yang masih dibuka. Tautan Daftar ke perusahaan tetap memerlukan metadata rekrutmen dan URL http/https valid seperti sebelumnya, dibuka di tab baru.
- Kedua jalur diberi keterangan terpisah: formulir mengirim data/berkas ke BKK dan tidak otomatis ke perusahaan. Pengantar formulir diperbarui tanpa menyebut formulir lama. Lowongan tutup tidak menawarkan tombol; pemeriksaan published dan server deadline pada GET/POST serta validasi berkas/CSRF tetap sama. CSS hanya memberi jarak/pemisah antarrute. Tidak mengubah database, tanggal lowongan atau SQL.
- Lint dua PHP, regresi CLI jobs.php dan diff check lulus. Render bagian pendaftaran dengan data sintetis memverifikasi aktif lengkap (dua rute), aktif tanpa metadata (BKK tersedia), URL berbahaya (tanpa rute perusahaan), kedaluwarsa/draft (tanpa tombol). HTTP daftar lowongan serta tautan detail yang tersedia merespons 200. Pengujian ini tidak mengirim formulir atau berkas nyata; alur simpan database dan visual browser belum diuji ulang.

## Perbaikan lamaran berikutnya tidak masuk panel (19 November 2026)

- Bug direproduksi pada salinan aplikasi dan database sementara dengan struktur database lokal tanpa menyalin data asli: lamaran pertama tersimpan dengan dua berkas, tetapi formulir berikutnya menggunakan submission_key yang sama. POST data pelamar berbeda mengembalikan 303/konfirmasi berhasil sementara jumlah lamaran tetap satu, karena cabang idempotensi melewati INSERT.
- GET formulir baru kini memeriksa token yang sudah dipakai dan menggantinya. Konfirmasi berhasil terikat pada token yang baru selesai diproses, bukan flag boolean lama. Halaman berhasil dan pesan penggunaan ulang menyediakan tautan Buka formulir baru. Pembatas satu menit tetap berlaku dan memberi pesan yang jelas.
- POST ulang dengan token sama hanya diterima sebagai pengulangan identik jika lowongan, data pelamar, persetujuan WhatsApp dan isi berkas cocok. Data atau berkas berbeda mendapat 422 dengan penjelasan formulir sudah pernah dikirim; tidak lagi menyatakan berhasil diam-diam. Pencocokan berkas memakai SHA-256 dan nama simpan berformat allowlist, tanpa menerima path dari klien. Formulir baru tetap memungkinkan pengiriman baru.
- Perubahan pada lowongan-kerja/lamar.php dan app/helpers/application-files.php. Tidak mengubah query daftar Admin/Staf, izin, skema atau data nyata; tidak memerlukan SQL tambahan.
- Lint kedua PHP, regresi CLI jobs/access dan git diff --check lulus. Uji HTTP/PDO/MariaDB terisolasi membuktikan token baru, cooldown, dua lamaran/empat berkas terlihat pada inbox Admin dan Staf berizin, pengulangan identik tidak menduplikasi, perubahan data/berkas ditolak, unggahan wajib/invalid ditolak, akses tanpa login/CSRF/konfirmasi palsu ditolak, serta lowongan tutup/draft terlindungi. Kegagalan INSERT metadata berkas disimulasikan dengan trigger hanya di database sementara: transaksi/berkas baru dibersihkan, pesan SQL tidak bocor, dan percobaan berikutnya berhasil (total akhir tiga lamaran/enam berkas). Tidak mengirim pesan WhatsApp. Tampilan perangkat fisik belum diuji ulang. Artefak QA workspace dibersihkan.

## Editor deskripsi BMW Sakti lebih ringkas

- Halaman panel/program/deskripsi.php memakai wrapper bmw-description-editor terisolasi, header dengan ikon program jelas, tautan publik/daftar berikon, serta label Judul/Status/Deskripsi berikon. Judul dan status berdampingan desktop, satu kolom pada area kecil. Textarea tujuh baris sekitar 212px tetap dapat diperbesar manual; form maksimal 980px, jarak/padding diringkas, footer publikasi dan tombol Simpan sejajar dengan ikon. Tema gelap memakai warna ikon tersendiri.
- Backend simpan/validasi/izin program/CSRF/version tetap sama. Perubahan hanya markup dan CSS scoped di bmw.css. Lint PHP, regresi CLI industry-classes.php termasuk validasi deskripsi, dan diff check lulus. Chrome menguji render PHP asli dengan data sintetis pada area 320/960px dan dua tema: tidak overflow, kolom sesuai, textarea ringkas, tombol 44px/ikon sejajar, label, status draft dan escaping isi. Token CSRF/version tetap ada. Belum menyimpan pengaturan nyata atau menguji perangkat fisik; database/SQL tidak diubah. Artefak QA dibersihkan.

## Memperjelas tambah siswa dan penempatan PKL (19 November 2026)

- Halaman pemilihan siswa di panel/pkl/form.php kini memiliki tombol Tambah siswa baru pada header dan empty state. Pesan membedakan data siswa kosong dari pencarian tanpa hasil; pencarian berlabel eksplisit dan menyediakan Tampilkan semua siswa saat difilter. Pemilihan siswa lama tetap tersedia.
- Tombol membuka formulir peserta yang sudah ada melalui panel/peserta/?lanjut=pkl#peserta-form; details dibuka dari server tanpa bergantung JS. Konteks lanjut disimpan pada hidden input, tetap bertahan ketika validasi gagal, dan tombol menjadi Simpan & lanjutkan ke PKL. Setelah INSERT siswa berhasil, lastInsertId mengarah ke formulir penempatan dengan siswa tersebut terpilih. Tidak menggandakan implementasi tambah siswa atau menimpa identitas/riwayat lama.
- Tujuan lanjutan hanya nilai allowlist pkl dan URL internal tetap. Izin peserta tetap diwajibkan oleh middleware; konteks PKL juga memeriksa izin pkl beserta dependensinya pada GET/POST. Alur edit, impor dan simpan peserta biasa tetap memakai perilaku semula. Tidak membuat akun siswa atau mengubah database nyata/SQL.
- Lint dua PHP, regresi CLI pkl/pkl-schedules/access dan git diff --check lulus. Uji HTTP/PDO/MariaDB sementara dengan struktur database lokal tanpa data asli membuktikan alur Admin dan Staf berizin: daftar kosong, tombol tambah, form terbuka, validasi/CSRF, simpan siswa dengan NIS nol awal, redirect ke siswa terpilih, simpan penempatan dan detail. Duplikasi NIS, pencarian, Staf hanya berizin peserta (GET/POST konteks PKL ditolak), tamu, tujuan lanjutan tidak dikenal serta penyimpanan peserta biasa turut diperiksa. Tidak ada warning/fatal/notice PHP pada uji. Belum melakukan verifikasi visual perangkat fisik. Artefak QA workspace dibersihkan.

## Dropdown kelas dan jurusan peserta (19 November 2026)

- Form tambah/edit peserta mengganti input Kelas dan Jurusan dengan select native berlabel. Kelas tetap wajib; Jurusan tetap opsional seperti sebelumnya. Pilihan diambil dari dua pengaturan baru participant_classes/participant_departments dan nilai unik siswa lama. Nilai yang sudah tersimpan tetap dapat dipilih/edit tanpa menimpa data. Form manual memeriksa allowlist dan tipe input di server; input palsu/array ditolak. Alur impor Excel tetap memakai validasi lama dan dapat menjadi sumber pilihan dari data yang diimpor.
- Pengaturan khusus Admin menambahkan bagian Pilihan siswa / peserta (anchor settings-student-options), satu nilai per baris. Helper merapikan spasi/baris kosong, membuang duplikasi, memeriksa UTF-8/panjang/kontrol dan membatasi 300 pilihan per daftar. Simpan settings yang tidak mengirim field baru mempertahankan konfigurasi sebelumnya. Menggunakan tabel settings lama; tidak perlu migrasi/SQL tambahan.
- Saat diperiksa, data siswa belum berisi pilihan kelas/jurusan dan pengguna belum memberikan daftar resmi. Dropdown menampilkan petunjuk untuk mengisi pengaturan sekali; Staf diarahkan menghubungi Admin. Tidak memasukkan nama kelas/jurusan rekaan ke database nyata. Pengguna dimintai daftar melalui pertanyaan singkat; fitur dapat dikonfigurasi dari panel tanpa menunggu perubahan kode.
- Lint lima PHP, regresi CLI pkl/access dan diff check lulus. CLI parser menguji normalisasi/duplikasi, daftar kosong, batas per item/jumlah, karakter kontrol dan encoding tidak valid. HTTP/PDO/MariaDB sementara menguji pengaturan kosong, simpan Admin/CSRF, penolakan daftar salah tanpa menimpa konfigurasi, field yang tidak dikirim tetap dipertahankan, dropdown tambah/edit Admin/Staf, penolakan nilai palsu/array, pilihan bertahan saat validasi gagal, simpan ke PKL, nilai lama ter-escape, dan pembatasan halaman/handler pengaturan bagi Staf. Tidak ada warning/fatal/notice PHP. Tidak mengubah data sekolah asli atau SQL; tampilan perangkat fisik belum diuji. Artefak QA dibersihkan.

## Dropdown angkatan peserta

- Angkatan pada form peserta kini select wajib, memakai pengaturan participant_years (satu tahun per baris) dan tahun unik dari data siswa lama. Bagian Pilihan siswa / peserta di Pengaturan ditambah pilihan angkatan berikon kalender; pengaturan yang tidak mengirim field baru mempertahankan daftar tersimpan. Helper memvalidasi tahun 1900-2199, membuang duplikasi dan menolak format tidak sah. Form manual memeriksa allowlist/tipenya di server seperti kelas/jurusan.
- Memperbaiki participant_input agar tahun numerik dari PDO tetap dikonversi menjadi string saat edit; sebelumnya nilai integer angkatan bisa hilang dari form. Tidak mengubah format penyimpanan, impor, atau skema. Daftar resmi dapat diisi Admin sekali; tidak memasukkan tahun angkatan sekolah rekaan ke database nyata.
- Lint lima PHP dan diff check lulus. Uji CLI batas tahun, duplikasi, format tidak valid dan integer database lulus. HTTP/PDO/MariaDB pada fixture sementara membuktikan simpan pilihan, penolakan tahun di luar rentang/tidak terdaftar, dropdown wajib, simpan peserta Admin/Staf, pilihan tetap terpilih saat edit, redirect PKL, akses handler pengaturan khusus Admin, serta pelestarian konfigurasi saat field tidak dikirim. Tidak mengubah data asli atau SQL; perangkat fisik belum diuji. Artefak QA dibersihkan.

## Pengaturan dipisah per fitur (19 November 2026)

- Pengaturan tidak lagi menampilkan satu formulir panjang. settings_sections() menjadi allowlist bersama untuk delapan halaman melalui parameter bagian: identitas, profil, logo, sosial, privasi, kelas, jurusan, dan angkatan. Navigasi lokal berikon dan penanda aktif; hanya satu bagian/form tampil dan tombol Simpan menyebut nama bagian tersebut. Kelas/Jurusan/Angkatan masing-masing terpisah. Pada HP menu tersusun dua kolom, desktop memakai navigasi samping.
- Handler simpan memvalidasi bagian dan hanya memproses key allowlist bagian itu, termasuk memproses unggahan/pelepasan logo hanya pada bagian Logo. Field bagian lain yang disisipkan pada POST diabaikan. Validasi, CSRF, transaksi dan akses khusus Admin dipertahankan. Draft error disimpan dengan penanda bagian dan redirect kembali ke bagian yang sama. Form lama tanpa parameter bagian tetap dapat menyimpan key yang dikirim tanpa mengosongkan key lain yang tidak dikirim.
- Tautan konfigurasi pilihan dari form peserta diperbarui ke bagian Kelas; navigasi menyediakan Jurusan/Angkatan. CSS scoped settings-separated membuat input 44px/16px, textarea daftar dan profil lebih sesuai jumlah baris, dan save footer ringkas. Tidak membuat tabel, SQL atau README dan tidak mengubah konfigurasi sekolah nyata.
- Lint empat PHP, regresi CLI access.php dan diff check lulus. HTTP/PDO/MariaDB pada fixture sintetis menguji semua delapan GET/form/save, perubahan hanya pada key bagian yang diizinkan, extra field ditolak sebagai perubahan lintas bagian, invalid tahun/URL tanpa simpan, draft kembali ke bagian tepat, rollback logo invalid, pelepasan satu logo tidak mengubah logo/teks lain, bagian GET/POST invalid, CSRF, pembatasan Admin pada halaman/handler, dan kompatibilitas form lama. Struktur field dipastikan tercakup sekali pada peta bagian.
- Chrome CDP pada delapan snapshot render sintetis dengan CSS/JS asli, lebar 320/390/768/1024/1440 dalam tema terang/gelap, lulus: satu form, delapan menu/satu aktif, label, target menu/simpan minimal 44px dan tanpa overflow. Tinggi textarea daftar/profil diuji setelah memperbaiki aturan lama tinggi tetap 60px. Screenshot HP/desktop diperiksa. Browser menggunakan snapshot, bukan pengujian perangkat fisik. Artefak QA workspace dibersihkan.

## Tampilan kertas Excel untuk pilihan siswa

- Sesuai permintaan desain saja, area pilihan Kelas/Jurusan/Angkatan memakai bingkai lembar, header kolom A, garis horizontal setiap baris dan gutter nomor baris. Textarea asli tetap menjadi field formulir dengan id/name/maxlength/nilai yang sama; satu pilihan per baris dan penyimpanan/validasi tidak diubah. Profil dan pengaturan lainnya tidak menggunakan desain ini.
- CSS scoped mendukung tema terang/gelap, tinggi awal 192px, font 16px, garis 30px dan area yang dapat diperbesar vertikal. JS dekoratif hanya menampilkan nomor yang sedang terlihat, mengikuti scroll/resize tanpa menulis nilai textarea atau menambah field. Gutter/header disembunyikan dari pembaca layar; label asli dan fokus pada bingkai tetap tersedia.
- Lint PHP dan diff check lulus. Chrome pada render komponen asli dengan data sintetis untuk tiga jenis pilihan, area 320/960px dan dua tema memverifikasi tidak overflow, garis tampil, nomor mengikuti scroll, label dan FormData tetap utuh, serta jumlah nomor DOM terbatas. Screenshot diperiksa. Tidak menyimpan pengaturan, mengubah database/SQL atau menguji perangkat fisik; artefak QA dibersihkan.
# Rapor per siswa dan penghilangan nomor peserta (19 November 2026)

- Capaian Pembelajaran kini menyediakan rapor per siswa/periode: kompetensi dinamis, nilai opsional 0-100, predikat manual, deskripsi, catatan pembimbing, Draft/Final, detail dan cetak/PDF. Identitas disalin saat dibuat; transaksi, versi perubahan dan keunikan siswa/periode menjaga riwayat. Halaman, handler dan cetak memerlukan login serta izin Capaian dan Data Peserta. Dokumen lama dipindah secara navigasi ke Dokumen pendukung, tanpa menghapus berkas/data.
- Nomor peserta dihapus dari formulir, tabel, pilihan siswa, cetak dan ekspor terkait peserta, absensi, PKL, kelas industri dan perjalanan. Kolom internal lama dipertahankan agar relasi dan riwayat tetap utuh; siswa baru mendapat kode internal acak yang tidak tampil. NISN tracer tetap terpisah. Template Excel baru sembilan kolom; impor lama perlu memakai ulang template baru. Impor menambah siswa dan tidak menyamakan orang berdasarkan nama.
- SQL terpisah `database/updates/025_student_learning_reports.sql` menambah dua tabel; master SQL tidak diubah dan database asli belum dimigrasikan. Panduan aktivasi dan pemeriksaan manual: `docs/RAPOR-PEMBELAJARAN.md`. README tidak dibuat kembali.
- Verifikasi: sintaks seluruh 229 PHP dan sepuluh skrip regresi CLI lulus; diff check lulus. HTTP/PDO/MariaDB terisolasi membuktikan migrasi berulang aman, dokumen lama tetap tersedia, tambah/edit siswa tanpa nomor, template/impor/ekspor baru, Draft/Final, nilai/predikat invalid, CSRF, hak akses halaman/handler/cetak, versi kedaluwarsa, larangan penggantian siswa, periode duplikat, identitas historis, escaping, pencarian/filter, serta hapus dengan cascade hanya pada item rapor. Database asli tidak ditulis.
- Chrome dengan snapshot data sintetis dan CSS/JS asli memverifikasi tambah/hapus kompetensi (termasuk penomoran nama field yang sudah diperbaiki), PDF, serta tidak ada overflow halaman pada viewport tepat 320/390/768/1440. Screenshot HP diperiksa. Bukan pengujian perangkat fisik atau database operasional; pengelola perlu mengimpor pembaruan 025 sebelum memakai rapor.

## Penyempurnaan desain formulir rapor (19 November 2026)

- Formulir Buat/Edit rapor diringkas dengan tiga bagian: identitas, kompetensi, dan catatan. Pencarian siswa, tautan kembali dan area simpan lebih ringkas. Ikon SVG lokal eksplisit pada label/tombol, ukuran dan ketebalan konsisten, aksen hijau lembut, garis pemisah dan latar kompetensi mengikuti tema panel. Label tetap terhubung ke input, fokus keyboard terlihat dan tombol tetap minimal 44px. CSS scoped pada formulir; data, validasi, SQL dan tampilan cetak tidak berubah.
- File utama: panel/capaian-pembelajaran/form.php dan assets/css/learning-reports.css. Lint PHP dan diff check lulus. Render PHP sintetis memakai template/CSS/JS asli diperiksa dengan Chrome pada viewport 504 dan 1182px: tanpa overflow, 11 ikon label tampil, tambah/hapus kompetensi mempertahankan nama field unik. Screenshot desktop diperiksa; tidak menguji perangkat fisik atau penyimpanan database ulang karena perubahan hanya presentasi.

## Alur Absensi kegiatan diperjelas (19 November 2026)

- Halaman utama menampilkan tiga langkah singkat dan satu pintu Buat kegiatan baru yang terbuka saat daftar kosong/validasi gagal. Pemilihan angkatan dan kelas industri yang sebelumnya terpisah diganti satu Kelompok peserta berisi pasangan nyata dari siswa beserta jumlahnya. Server memilih kedua nilai dari kelompok allowlist, mengabaikan field angkatan/kelas sisipan, dan transaksi lama memeriksa ulang peserta saat pembuatan. Kelompok tanpa siswa tidak ditawarkan.
- Jika belum ada kelompok, tampil instruksi melengkapi angkatan/kelas industri dan tautan Data siswa, bukan formulir dropdown kosong. Teks sumber data diperbaiki dari Data PKL menjadi Data siswa. Tombol Buat & lanjut isi kehadiran mengarah ke detail setelah simpan; status kehadiran awal tetap kosong. Halaman detail memperjelas pengisian dan penyimpanan per halaman. Filter tidak mengubah riwayat peserta. Tidak ada perubahan SQL atau data operasional.
- File utama: app/helpers/attendance.php, panel/absensi/index.php, detail.php, assets/css/bmw.css; regresi tambahan app/tests/attendance.php. Lint empat PHP, uji kelompok valid/kosong/palsu/tidak tersedia serta field sisipan, access.php dan diff check lulus. MariaDB sementara berisi data sintetis membuktikan kelompok nyata, pembuatan kegiatan, snapshot peserta berstatus kosong, penyimpanan hadir, penolakan versi lama dan daftar asing. Browser memakai render template/CSS asli pada viewport 504/1182px: kondisi berkelompok/kosong, form terbuka, tanpa overflow; screenshot desktop diperiksa. Bukan pengujian perangkat fisik atau HTTP login ulang; database asli tidak diubah.
- Uji manual: lengkapi Data siswa (angkatan + kelas industri), pilih kelompok saat membuat kegiatan, lanjut isi Hadir/Izin/Sakit/Alpa, simpan setiap halaman, lalu buka Rekap kehadiran/Excel. Bila dropdown belum memiliki kelompok, lengkapi data siswa terlebih dahulu.

## Profil pemateri ringkas (19 November 2026)

- Detail pemateri memakai foto terbatas 88px (64px pada layar kecil), fakta profil dalam grid dan CV terpisah di samping; foto memakai contain agar tidak terpotong. Biodata panjang berada dalam bagian Tentang pemateri yang dapat dibuka, dengan area gulir berfokus keyboard. Nama berkas panjang dibatasi dua baris dengan nama lengkap pada title. Ikon lokal dan tindakan edit/unduh/hapus tetap berlabel.
- Riwayat kegiatan kini memakai prepared LIMIT/OFFSET sepuluh baris per halaman, jumlah dan navigasi yang mempertahankan ID pemateri, serta header tabel tetap saat digulir. Daftar pemateri utama sudah memiliki pencarian/pagination sepuluh baris dan tidak diubah. Tidak ada perubahan SQL, unggahan atau data asli. File: panel/pemateri/detail.php dan assets/css/bmw.css.
- Lint PHP dan diff check lulus. Chrome memakai render PHP/CSS asli dan fixture sintetis (biodata/nama berkas panjang, sepuluh kegiatan dari total 25), memeriksa viewport 504/1182px tanpa overflow, biodata awal tertutup, jumlah baris dan tautan pagination; screenshot desktop diperiksa. Pagination query ditinjau, belum diuji dengan database/browser login aktif pada perubahan ini.

## Tabel pemateri seperti Excel dan hapus dari daftar (19 November 2026)

- Daftar pemateri memakai panel-sheet bersama untuk garis vertikal/horizontal dan header sel. Kolom tindakan disesuaikan untuk Profil, Edit, Hapus sejajar; tombol hapus ikon merah dengan title/aria-label nama pemateri, target 44px di layar kecil. Tabel bergulir di dalam bingkai, pagination tetap sepuluh baris.
- Handler POST pada panel/pemateri/index.php memeriksa login/izin yang sudah ada, CSRF, aksi, ID dan versi sebelum memakai speaker_delete() bersama. Konfirmasi menyebut profil/berkas dihapus sementara kegiatan dan absensi tetap ada. Versi lama ditolak (422); redirect berhasil mempertahankan filter/halaman. CSS scoped di assets/css/bmw.css. Tidak ada SQL baru atau penghapusan pada database asli.
- Lint PHP dan diff check lulus. HTTP pada salinan proyek/database sementara menguji login Admin, guest redirect, CSRF salah 403, versi lama 422, hapus sah 303, relasi pemateri-kegiatan dibersihkan sementara kegiatan/absensi tetap utuh, filter redirect serta pagination 12 pemateri menjadi 10+2. Chrome render sintetis viewport 504/1182px memverifikasi garis solid, seluruh tombol muat dalam sel, sepuluh form hapus dan tanpa overflow halaman; screenshot desktop diperiksa. Perangkat fisik dan role Staf tidak diuji ulang pada perubahan ini. Artefak QA workspace dibersihkan.

## Lima pemateri per halaman dan nama kegiatan (19 November 2026)

- Daftar pemateri kini maksimal lima baris per halaman melalui LIMIT/OFFSET server, selaras dengan slider lima baris panel. Pagination tetap mempertahankan filter; seluruh data tetap dapat diakses. Kolom Kegiatan menampilkan nama dua kegiatan terbaru yang benar-benar dikaitkan, bukan jumlah. Jika lebih banyak, tautan Lihat kegiatan lainnya menuju profil; tanpa relasi tampil Belum ada kegiatan. Judul di-escape dan dibatasi tampilan dua baris agar tidak memperpanjang tabel. Query kegiatan dibatasi tiga per pemateri (maksimal lima pemateri), urutan tanggal/ID terbaru; tanpa GROUP_CONCAT atau pemotongan data tersimpan.
- File utama: panel/pemateri/index.php dan assets/css/bmw.css. Lebar kolom kegiatan diperbesar. Tidak ada perubahan skema/SQL atau data operasional. Lint PHP dan diff check lulus; evaluasi kode query serta markup halaman asli pada database sementara dengan 12 pemateri membuktikan halaman 5+5+2, nama kegiatan, escaping judul berkarakter HTML, empty state, tombol hapus setiap baris dan filter pagination. Uji ini bukan login HTTP/perangkat fisik ulang. Artefak QA dibersihkan.

## Perapihan tombol tindakan pemateri (19 November 2026)

- CSS scoped pada assets/css/bmw.css menyamakan tinggi tombol Profil/Edit/Hapus, sudut 6px, jarak 6px dan ukuran ikon 16px. Desktop 32px, layar kecil 44px; form hapus tidak lagi memengaruhi baseline. Fokus keyboard tetap jelas. Tidak mengubah handler atau data.
- Chrome memakai markup tindakan asli dengan data sintetis dan CSS proyek memverifikasi ketiga kontrol memiliki posisi atas/tinggi/radius yang sama pada ukuran jendela 500/1200; diff check lulus.

## Data siswa lebih mudah dipahami (19 November 2026)

- Header Data siswa kini menonjolkan dua tindakan Tambah siswa dan Ekspor Excel. Layanan PKL/Absensi/Rapor dipindah ke navigasi terpisah berikon dan mengikuti izin modul. Form manual berlabel Tambah siswa satu per satu; impor berlabel Tambah banyak siswa melalui Excel dengan langkah unduh template, unggah, serta peringatan impor menambah baris. Field, endpoint, CSRF dan proses penyimpanan/impor tidak berubah.
- Daftar memakai garis Excel, heading/jumlah halaman, tindakan edit/rapor berikon dan empty state yang membedakan filter kosong dari belum ada siswa. CSS scoped participants-workspace mendukung layar kecil dan warna variabel tema. File utama panel/peserta/index.php dan assets/css/bmw.css; tidak ada SQL baru atau perubahan data operasional.
- Lint PHP dan diff check lulus. Chrome menggunakan template PHP/CSS asli, handler data-open-details asli (snapshot tidak menyertakan sidebar), serta fixture sintetis pada viewport 504/1182px: dua tombol utama, tiga pintasan Admin, satu tautan template di impor, sembilan field data + CSRF utuh, tombol tambah membuka form, dan tanpa overflow halaman. Screenshot desktop diperiksa. Bukan pengujian perangkat fisik atau simpan/import database ulang.

## Pengelolaan siswa dipisah menjadi halaman khusus (19 November 2026)

- Mengikuti koreksi pengguna, daftar tidak lagi mencampur formulir manual, impor, atau pintasan PKL/Absensi/Rapor. Tiga menu lokal menunjuk halaman terpisah: panel/peserta/index.php (filter, tabel identitas, Edit, ekspor hasil filter), form.php (tambah/edit satu siswa), dan impor.php (template serta unggah Excel). PKL/Absensi/Rapor tetap melalui sidebar. Penanda aktif memperjelas lokasi, formulir tampil langsung tanpa accordion.
- Logika simpan dan impor yang sudah ada dipindah tanpa duplikasi ke app/helpers/participant-editor.php; tampilan editor dan navigasi bersama di app/layouts/participant-editor.php serta participant-navigation.php. Halaman/handler memeriksa login dan izin peserta; mode handler dibatasi dan CSRF tetap wajib. ID edit invalid 404, validasi gagal 422, aksi lintas halaman 400. POST dan tautan GET lama tetap kompatibel; tautan tambah dari PKL diperbarui ke form.php dan alur lanjut PKL tetap memeriksa izinnya. Tidak mengubah tabel, data asli, template atau ketentuan impor. Tidak ada SQL baru/README.
- Verifikasi: lint tujuh PHP, regresi access.php/learning-reports.php dan diff check lulus. HTTP/PDO/MariaDB pada salinan proyek/database sementara menguji tiga halaman, login Admin/Staf, guest redirect, staf tanpa izin 403 pada halaman/POST, CSRF salah 403, allowlist dropdown, tambah/edit, filter redirect, ID invalid, aksi lintas halaman, kompatibilitas URL/POST lama, PKL lanjut dan pembatasan izin PKL, impor invalid serta impor XLSX sah oleh Staf memakai template terbaru. Database operasional tidak disentuh.
- Chrome memakai snapshot HTTP sintetis lengkap dengan layout/CSS/JS asli untuk daftar/tambah/edit/impor, viewport 504/1182px: tiga menu/satu aktif, daftar tanpa formulir POST, tambah/edit tanpa upload, impor tanpa input identitas, masing-masing editor tepat satu form dan tanpa overflow. Screenshot daftar/tambah diperiksa. Perangkat fisik belum diuji. Artefak QA workspace dibersihkan.

## Hapus siswa dan batas sepuluh baris (19 November 2026)

- Daftar siswa dibatasi sepuluh baris per halaman server; filter/urutan tetap dipertahankan pada pagination dan redirect hapus. data-list-size=10 mengatur slider generik agar tidak kembali menyembunyikan baris menjadi lima; modul lain tetap default lima. Tombol Edit dan Hapus berlabel/ikon, sejajar dan sama tinggi (36px desktop, 44px layar kecil), dalam tabel Excel.
- POST hapus di index.php memeriksa login/izin peserta, CSRF dan ID, lalu memakai helper participant_delete dengan transaksi dan row lock. Siswa tanpa riwayat dapat dihapus; FK RESTRICT melindungi absensi/PKL/kelas industri/rapor dan diterjemahkan menjadi pesan jelas tanpa SQL. Siswa tidak ditemukan ditolak; tidak menghapus riwayat secara cascade. POST simpan/impor lama tetap diteruskan ke editor bersama. File: panel/peserta/index.php, app/helpers/participants.php, assets/js/panel.js, assets/css/bmw.css. Tidak ada perubahan SQL atau database operasional.
- Lint PHP, access.php, attendance.php dan diff check lulus. HTTP/PDO/MariaDB sementara membuktikan pagination 12 siswa menjadi 10+2, hapus Admin/Staf berizin, CSRF salah 403, guest redirect, staf tanpa izin 403, ID invalid/tidak ada 422, siswa terkait absensi gagal dihapus dan riwayat tetap utuh, redirect mempertahankan filter serta halaman terakhir menyesuaikan setelah penghapusan.
- Chrome pada snapshot HTTP sintetis lengkap/CSS/JS asli, viewport 504/1182px, membuktikan sepuluh baris benar-benar terlihat, sepuluh form konfirmasi, kedua tombol sama tinggi dan muat dalam sel serta tidak ada overflow halaman. Screenshot desktop diperiksa. Perangkat fisik belum diuji; data sekolah asli tidak disentuh. Artefak QA dibersihkan.

## Gulir vertikal dalam kotak siswa (19 November 2026)

- Tabel siswa dibatasi maksimum 420px atau 55vh, mengikuti nilai yang lebih kecil. Overflow vertikal/horizontal berada di dalam kotak, scrollbar ramping mengikuti tema, dan header kolom sticky. Kotak diberi tabindex/region/label untuk navigasi keyboard. Batas sepuluh siswa dan pagination tetap. File: assets/css/bmw.css dan panel/peserta/index.php.
- Lint PHP dan diff check lulus. Chrome dengan sepuluh baris sintetis/CSS asli pada jendela 500/1200 membuktikan tinggi kotak di bawah 420px, scrollTop bergerak 120px, header tetap di tempat, dan tidak ada overflow halaman. Tidak mengubah database/SQL.

## Warna menu pengelolaan siswa (19 November 2026)

- Menu Daftar siswa memakai biru, Tambah/Edit siswa hijau, dan Impor Excel ungu. Menu tidak aktif memakai latar lembut; menu aktif berwarna solid, teks putih, font tebal dan garis bawah inset, tetap memakai aria-current sehingga status tidak hanya dibedakan warna. Ikon mengikuti warna teks; tema gelap memiliki palet tersendiri. Ukuran dan susunan responsif tetap. File: app/layouts/participant-navigation.php dan assets/css/bmw.css.
- Lint PHP dan diff check lulus. Perhitungan kontras sembilan pasangan teks/latar (normal, aktif, gelap) seluruhnya di atas 4.5:1. Tidak mengubah data/handler/SQL; browser tidak diuji ulang untuk perubahan warna ini.

## Desain daftar Kelas Industri (19 November 2026)

- Header dirapikan: judul/penjelasan singkat di kiri, Tambah kelas hijau sebagai aksi utama dan Deskripsi BMW Sakti sekunder di kanan. Filter berlabel eksplisit dan ikon, tabel panel-sheet bergaris Excel dengan header sticky/area gulir, judul kelas tebal, progres pertemuan berwarna disertai angka/label, dan status Aktif/Arsip berbentuk badge berikon. Tindakan Jadwal dan Edit menjadi dua tombol sejajar. Empty state membedakan filter tidak cocok dan data kosong.
- File utama panel/kelas-industri/index.php dan assets/css/bmw.css. CSS scoped industry-list-workspace karena halaman ini bukan bmw-admin; mendukung tema gelap/terang dan layar kecil. Tidak mengubah query, pagination, izin, SQL atau data.
- Lint PHP, app/tests/industry-classes.php dan diff check lulus. Chrome dengan template PHP/CSS asli dan empat kelas sintetis pada viewport 504/1182px, dua tema: tidak ada overflow halaman, tombol muat dan sama tinggi, empat progress terlihat, label terhubung serta garis sel solid. Screenshot desktop diperiksa. Belum menguji perangkat fisik atau CRUD database ulang karena perubahan tampilan. Artefak QA dibersihkan.

## Desain detail Kelas Industri (19 November 2026)

- Detail kelas kini memiliki header tindakan sejajar, kartu ringkasan dua area (progres dan fakta kelas), serta Catatan kelas yang dapat dibuka. Jadwal dan Peserta berada pada kartu terpisah dengan tabel Excel, header sticky dan area gulir terbatas. Pertemuan memiliki badge teks berwarna untuk lima status, jam WIB, keterangan yang dapat digulir, dan tombol Atur jadwal. Empty state peserta menjelaskan pemilihan siswa melalui Edit kelas; pagination tetap mempertahankan ID dan mengarah ke bagian peserta.
- File utama: panel/kelas-industri/detail.php, assets/css/bmw.css. Query, perhitungan progres, izin dan handler tidak diubah; tanpa SQL baru/data asli. Lint PHP, app/tests/industry-classes.php dan diff check lulus.
- Chrome menggunakan template PHP/CSS asli dengan data sintetis (lima status pertemuan, catatan panjang, 15 peserta) pada viewport 504/1182px dan dua tema. Memastikan tiga aksi header, empat fakta, dua bagian data, lima badge, catatan awal tertutup, tombol muat dalam sel, gulir internal bekerja, header tabel tetap, dan tidak ada overflow halaman. Screenshot desktop diperiksa. Tidak menguji perangkat fisik/CRUD database ulang; artefak QA dibersihkan.

## Detail kelas lebih minimalis (19 November 2026)

- Area detail Kelas Industri dibatasi 980px dan tetap berada di tengah. Kolom progres diperkecil dari 230px menjadi 190px; padding, jarak fakta, angka progres, judul dan tombol header dirapatkan. Kartu jadwal/peserta mengikuti lebar area dan tabel tetap bergulir di dalam kotak. Breakpoint satu kolom pada layar kecil tetap dipertahankan. Perubahan hanya assets/css/bmw.css, tanpa data/SQL atau handler.
- Diff check lulus; aturan responsif dan cakupan selector diperiksa. Tidak menjalankan ulang pengujian database untuk perubahan ukuran CSS.

## Garis Excel pada ringkasan kelas (19 November 2026)

- Kartu ringkasan detail kelas kini berupa grid tanpa celah, dengan bingkai dan garis vertikal/horizontal antara progres serta empat fakta kelas. Catatan memakai garis penuh di bawah. Warna garis disesuaikan tema gelap; pada layar kecil progres menjadi baris tersendiri dan fakta satu kolom tidak menyisakan garis vertikal. Lebar maksimal dan fungsi tetap. Hanya assets/css/bmw.css berubah; diff check dan cakupan breakpoint diperiksa. Tidak ada perubahan SQL/data atau pengujian database ulang.

## Formulir kelas industri terstruktur (19 November 2026)

- Tambah/Edit kelas disusun menjadi empat fieldset bernomor: identitas (nama/status), mitra dan pemateri, sasaran dan peserta, rencana pertemuan/catatan. Multi-select yang memerlukan Ctrl/Cmd diganti checkbox berlabel dan area gulir, pencarian pemateri lokal serta penghitung pilihan. Petunjuk membedakan kewajiban minimal satu pemateri, sasaran ATAU siswa, dan jadwal yang diisi sesudah kelas disimpan. Tombol Simpan/Batal berada di akhir.
- Pencarian siswa memakai form GET terpisah melalui atribut form, tanpa nested form. JS mengambil HTML dari halaman GET terlindungi yang sama dan hanya mengganti daftar opsi, mempertahankan pilihan saat respons tiba serta seluruh isian kelas; pilihan lama yang dilepas tidak otomatis dicentang kembali. Respons gagal/login redirect tidak mengganti daftar. Tanpa JS, pencarian GET tetap tersedia dengan petunjuk menyimpan sebelum memuat ulang. Tidak menambah endpoint/dependency. CSRF, version, array speakers[]/participants[], form_complete dan validasi server tetap; error simpan kini 422.
- File: panel/kelas-industri/form.php, assets/js/industry-class-form.js, assets/css/bmw.css. Tidak ada perubahan skema/SQL atau data operasional. Lint PHP, industry-classes.php dan diff check lulus. HTTP/PDO/MariaDB sementara menguji empat bagian, create/edit Admin/Staf, relasi dua pemateri/dua siswa, pembentukan slot, checkbox wajib, CSRF, form_complete hilang, versi lama, akses guest/staf terbatas serta hasil pencarian GET yang menyertakan pilihan tersimpan.
- Chrome memakai snapshot HTTP sintetis/layout/CSS/JS asli dan respons pencarian sintetis dari GET teruji, dua tema pada viewport 504/1182px: empat fieldset, tanpa overflow, filter pemateri tidak melepas centang, pilihan baru/isian belum disimpan dipertahankan, centang lama yang dilepas tidak kembali, hasil baru muncul, kegagalan pencarian aman, penghitung benar dan marker form utuh. Screenshot desktop diperiksa; input dirapatkan menjadi sekitar 46px setelah perbaikan grid stretch dan ikon otomatis checkbox disembunyikan. Perangkat fisik belum diuji. Artefak QA dibersihkan.

## Pemilih siswa untuk ribuan data (19 November 2026)

- Pemilihan siswa pada form kelas industri kini menampilkan hasil pencarian sepuluh siswa per halaman dengan total/rentang dan navigasi Sebelumnya/Berikutnya. Query prepared memakai COUNT dan LIMIT/OFFSET; hanya nama/kelas/ID yang diambil. Hasil tidak lagi digabung dengan seluruh siswa terpilih. Nama dan kelas berjajar dalam daftar satu kolom bergaris, tinggi maksimal 340px, serta susunan bertumpuk pada HP.
- Siswa terpilih berada pada panel terpisah yang dapat dibuka, memiliki penghitung dan gulir maksimal 220px. JS menyimpan pilihan lintas halaman di input kanonis pada panel tersebut, bukan menggandakan input hasil pencarian; respons pencarian hanya mengganti area hasil. Centang yang dilepas tidak kembali dari data tersimpan. Isian kelas, pilihan saat pencarian kosong/gagal, dan checkbox yang dipilih sambil menunggu respons tetap dipertahankan. Tanpa JS, hasil GET dan panel terpilih tetap dapat digunakan dengan petunjuk menyimpan sebelum pindah. Batas referensi per kelas dan form_complete server yang sudah ada tetap berlaku.
- Helper industry_participant_page mengganti pencarian lama yang dibatasi 50; industry_selected_participants mengambil siswa terpilih secara terpisah dan menerima ID integer database maupun string POST melalui normalisasi/validasi. File: app/helpers/industry-classes.php, panel/kelas-industri/form.php, assets/js/industry-class-form.js, assets/css/bmw.css. Tidak ada perubahan skema/SQL atau data operasional.
- Lint dua PHP, industry-classes.php dan diff check lulus. HTTP/PDO/MariaDB terisolasi menguji 3.000 hasil siswa sintetis: halaman pertama/kedua/300, 10 hasil tiap halaman, clamp halaman berlebih, pencarian kelas 1.500 hasil, kosong, query terlalu panjang, pilihan tersimpan di luar halaman, akses guest/staf terbatas, penyimpanan lintas halaman oleh Staf serta pelepasan pilihan dan CSRF.
- Chrome dengan snapshot HTTP/CSS/JS asli dan respons pencarian sintetis teruji pada viewport 504/1182px dalam dua tema membuktikan sepuluh hasil, daftar bergulir terbatas, pilihan lintas halaman pulih, hasil kosong/gagal tidak menghapus pilihan/isian, ID FormData unik dan dua penghitung sinkron. Screenshot komponen diperiksa. Pengujian browser memakai respons yang disimulasikan dari hasil HTTP aktual; perangkat fisik belum diuji. Artefak QA workspace dibersihkan.

## Form kelas bergaris seperti Excel (19 November 2026)

- Judul Tambah/Edit kelas menjadi header berbingkai dengan tindakan Daftar kelas pada sel terpisah. Empat bagian bernomor memakai header latar biru lembut dan garis penuh; label/isian dalam grid memiliki garis antarsel, serta bidang tunggal diberi bingkai. Lebar, checkbox, pencarian berhalaman dan proses simpan tetap. Tema gelap memakai warna garis/header tersendiri; grid satu kolom memindahkan garis vertikal menjadi horizontal pada HP.
- Hanya assets/css/bmw.css berubah; diff check dan aturan responsif diperiksa. Tidak mengubah data/SQL/handler atau menjalankan ulang pengujian database.

## Desain halaman Pengguna (20 November 2026)

- Daftar Pengguna memakai tabel Excel berbingkai/garis vertikal-horizontal, header ikon dan teks sejajar serta area gulir maksimal 420px. Role Admin biru/Staf ungu; status tetap berlabel Aktif/Nonaktif. Tombol Edit akun lebih ringkas (title menjelaskan profil/role/reset password), sejajar/sama tinggi dengan Aktivasi; konfirmasi dan aria-label menyebut akun. Penanda akun sendiri diperbaiki menjadi (Anda).
- File: panel/pengguna/index.php, assets/css/panel-theme.css. Selector users-list-page membatasi perubahan ke daftar; header lebih ringkas dan informasi akses menjadi kotak penjelasan. Handler, CSRF, role Admin dan perlindungan Admin aktif terakhir tidak diubah. Tidak ada SQL atau perubahan akun asli.
- Lint PHP dan diff check lulus. Chrome dengan template PHP/CSS asli dan tiga akun sintetis pada viewport 504/1182px, tema terang/gelap, membuktikan garis solid, tombol sama tinggi/muat dalam sel, tiga form konfirmasi, badge role dan tanpa overflow halaman. Screenshot desktop diperiksa. Tidak menguji perubahan status/database ulang karena hanya presentasi; artefak QA dibersihkan.

## Form Pengguna terstruktur (20 November 2026)

- Form Tambah/Edit Pengguna tidak lagi memakai grid foto 210px yang menyebabkan izin masuk kolom sempit. Form utama menjadi bagian penuh bernomor: identitas/role, foto opsional, hak akses Staf, dan password awal khusus akun baru. Izin disajikan grid bergaris tiga kolom desktop/dua-satu kolom HP dengan tinggi gulir 350px. Penjelasan Admin mengakses semua modul dipisah dari rincian izin yang saling terkait, yang dapat dibuka.
- Reset password akun lama tetap menjadi formulir terpisah setelah Simpan akun, dengan keterangan tidak menyimpan perubahan identitas dan membatalkan sesi. Foto menggunakan helper yang sama; input/fieldname, CSRF, action simpan/reset, daftar permission, serta validasi dan perlindungan admin terakhir tidak berubah. File: panel/pengguna/form.php, assets/css/panel-theme.css. Tanpa SQL/data akun asli.
- Lint PHP, access.php dan diff check lulus. Chrome menggunakan template PHP/CSS asli dan data akun sintetis untuk mode tambah/edit, viewport 504/1182px, tema terang/gelap: area izin melebihi 80% lebar form, 21 pilihan/dua centang tetap, label terhubung, tanpa form bersarang, form utama dan reset terpisah, password awal hanya di tambah, serta tanpa overflow. Screenshot desktop diperiksa. Tidak menjalankan ulang perubahan akun/database karena perubahan presentasi; artefak QA dibersihkan.

## Pengguna minimalis seperti lembar Excel (20 November 2026)

- Form Pengguna dibatasi 960px, header berbingkai, bagian bernomor dengan latar biru lembut, grid identitas bergaris vertikal/horizontal dan izin dalam sel lebih padat. Area izin maksimal 280px, foto dan tombol lebih ringkas, tema gelap memiliki warna garis/header tersendiri. Input desktop 14px/minimal 40px, HP 16px/minimal 44px. Reset password tetap terpisah. Hanya assets/css/panel-theme.css berubah.
- Diff check lulus. Chrome memakai template/CSS asli dengan akun sintetis pada viewport 504/1182px: lebar maksimal 960, empat identitas dan 21 izin tetap utuh, dua formulir tetap terpisah, garis solid dan tanpa overflow. Screenshot desktop diperiksa. Tidak mengubah SQL/data/handler dan tidak menguji database ulang; artefak QA dibersihkan.

## Pengaturan Website seperti lembar Excel (20 November 2026)

- Header pengaturan diberi bingkai ringkas; menu bagian menjadi daftar sel bergaris dengan aktif biru. Panel bagian memakai header biru lembut dan grid input berbingkai/garis antarsel. Area Simpan memakai latar terpisah dan tetap menyebut bagian yang disimpan. Logo mengikuti grid yang sama; textarea kelas/jurusan/angkatan beserta gutter/JS yang sudah ada tidak diubah. CSS scoped settings-separated, dukungan garis/header gelap dan menu dua kolom HP. Hanya assets/css/panel-theme.css berubah; handler dan data/SQL tetap.
- Diff check lulus. Chrome memakai template PHP/CSS asli dan data sintetis pada tujuh bagian non-logo (identitas, profil, sosial, privasi, kelas, jurusan, angkatan), viewport 504/1182px: satu form, delapan menu/satu aktif, label terhubung, garis solid dan tanpa overflow. Screenshot identitas desktop diperiksa. Logo dan tema gelap ditinjau CSS, tidak dirender ulang; tidak menguji simpan/database ulang. Artefak QA dibersihkan.

## Akun Pribadi bergaris Excel (20 November 2026)

- Halaman profil pribadi diberi header berbingkai dengan role Admin/Staf yang mudah dibaca. Foto opsional dan identitas dipisah menjadi area berheader biru lembut; nama/username/email memakai sel bergaris. Simpan profil dan Ubah password sejajar pada footer terpisah. Lebar maksimal 1000px, satu kolom pada HP, input 16px/44px di layar kecil, tema gelap sesuai variabel panel. File: panel/akun/profil.php dan assets/css/panel-theme.css.
- Handler, CSRF, helper foto, field identitas dan tautan password tidak berubah; role tetap tampilan saja, tanpa field yang dapat diedit. Lint PHP dan diff check lulus. Chrome dengan template/CSS asli, data Admin/Staf sintetis, viewport 504/1182px, tema terang/gelap: satu form, tiga identitas, label terhubung, garis solid, tombol sama tinggi, tanpa input role dan tanpa overflow. Screenshot desktop diperiksa. Tidak menguji simpan/foto/database ulang karena perubahan presentasi; artefak QA dibersihkan.

## Ringkasan Tracer dashboard bergaris Excel (20 November 2026)

- Kartu Ringkasan Tracer Study di dashboard Admin mengganti batang vertikal kosong/terlalu lega menjadi tabel Kegiatan alumni/Jumlah respons, bergaris penuh. Header biru lembut memuat total, kategori memakai penanda warna dan teks, angka rata kanan, serta footer Lihat rekap dan filter berikon. Catatan jumlah respons bukan alumni unik tetap, kondisi kosong dan kategori Lainnya data lama dipertahankan. Grafik komposisi/PKL lain tidak diubah.
- File panel/index.php dan assets/css/panel-theme.css; CSS khusus tracer-sheet-summary, mendukung tema gelap dan tidak memaksa tinggi kartu mengikuti kartu lain. Query, akses, perhitungan dan data pribadi tidak diubah; tanpa SQL baru.
- Lint PHP dan diff check lulus. Chrome dengan template PHP/CSS asli dan data sintetis memverifikasi empat kategori/total 4, kosong/total 0 dan kategori lama/total 6, garis solid, tautan rekap tetap, tanpa overflow pada viewport 504/1182px dalam dua tema. Screenshot desktop diperiksa. Tidak menguji database atau perangkat fisik ulang; artefak QA dibersihkan.

## Grafik kipas komposisi alumni sesuai referensi (20 November 2026)

- Grafik donat Komposisi kegiatan alumni pada dashboard Admin diganti SVG kipas setengah lingkaran: bidang gradasi oranye/hijau/biru/merah muda (sian jika kategori lama tersedia), lingkaran total di kiri, nomor bidang dan kartu kategori bernomor di kanan dengan jumlah/persentase. Sudut bidang dihitung dari proporsi respons nyata; kategori nol tetap muncul pada legenda tanpa bidang palsu, kosong memakai bidang netral, satu kategori memenuhi setengah lingkaran, dan Lainnya data lama tetap didukung. Bukan gambar statis/dependency baru.
- Interaksi legenda yang sudah ada diperluas untuk kipas: hover/fokus/klik memperjelas bidang dan jumlah tengah, blur mengembalikan total. Ikon otomatis dikecualikan dari legenda bernomor. Grafik ringkasan Excel, PKL, query/akses dan data tidak diubah. File: panel/index.php, assets/js/panel.js, assets/css/panel-theme.css. Tanpa SQL atau perubahan database asli.
- Lint PHP dan diff check lulus. Render SVG/XML dan jumlah kategori diperiksa pada data sintetis normal, kosong, satu kategori, proporsi 98/1/1, dan lima kategori lama; tidak ada koordinat NaN/INF dan total benar. Chrome pada viewport 504/1182px, terang/gelap, membuktikan angka, bidang nonnol, legenda minimal 44px/muat, interaksi fokus/reset dan tanpa overflow. CDP memverifikasi layout HP tepat 320/390px tanpa overflow; fokus otomatis emulator HP tidak dijadikan syarat (interaksi keyboard teruji pada desktop). Screenshot desktop diperiksa. Pengujian memakai template/CSS dan handler grafik asli, bukan perangkat fisik/database operasional. Artefak QA dibersihkan.

## Grafik kelengkapan PKL yang terbaca (20 November 2026)

- Grafik kolom Kelengkapan Data PKL diganti batang horizontal bertumpuk: hijau untuk riwayat terisi, emas untuk belum terisi, panjang bidang sesuai persentase nyata. Angka persentase utama, jumlah dari total siswa, skala 0-100%, dan legenda dua sel bergaris membuat kasus 31/0 tetap jelas. Data kosong memakai batang netral tanpa persentase fiktif; label di dalam bidang sempit tidak dipaksakan. Definisi terisi tetap tempat ATAU tahun PKL dicatat, sesuai query lama.
- Header biru lembut/tautan Kelola data siswa mengikuti desain dashboard dan mendukung tema gelap serta satu kolom HP. File panel/index.php dan assets/css/panel-theme.css; query, akses, grafik Tracer dan data tidak diubah. Tidak ada SQL/dependency baru.
- Lint PHP dan diff check lulus. Render template asli memverifikasi persentase/total pada penuh 31/31, sebagian 23/31, belum terisi 0/31, kosong 0/0, dan proporsi kecil 1/1000. Chrome dengan fixture sintetis/CSS asli pada viewport 504/1182px dan dua tema membuktikan jumlah benar, batang 36px, garis legenda, tautan tujuan dan tanpa overflow. Screenshot sebagian desktop diperiksa. Tidak menguji perangkat fisik/database ulang; artefak QA dibersihkan.

## Kartu ringkasan dashboard lebih ramping (20 November 2026)

- Mengikuti permintaan, lebar/kolom/jarak horizontal kartu dashboard tetap. Padding vertikal, jarak judul dan angka dirapatkan. Judul Berita/Lowongan/dll memakai bidang warna transparan yang menyatu sepanjang bagian atas kartu, dengan garis batas halus; pada HP judul memakai chip menyatu di dalam kartu. Angka, unit, deskripsi, tautan seluruh kartu dan badge lamaran tetap. CSS scoped dashboard-view/summary-grid di assets/css/panel-theme.css.
- Diff check lulus. Chrome membandingkan sebelum/sesudah memakai markup PHP asli dan angka sintetis pada viewport 504/1182px: lebar semua kartu, nilai dan URL identik, tidak overflow; tinggi maksimum turun 68 ke 60px (layar kecil), 121,1 ke 97,9px (desktop). Ikon judul disimulasikan dari ikon kartu asli agar mengikuti penambahan ikon panel. Screenshot desktop diperiksa. Tidak ada PHP/SQL/query/data yang diubah; artefak QA dibersihkan.

## Bingkai dashboard dan penghapusan tombol animasi (20 November 2026)

- Tombol Putar ulang animasi dihapus dari dashboard, beserta CSS tombol dan blok JS animasi lama yang menargetkan grafik kolom/donat sebelumnya. Interaksi legenda grafik kipas tetap tersedia. Dashboard Admin dan Staf diberi wrapper dashboard-sheet: header berbingkai, tepi kartu ringkasan lebih tegas dan bingkai kelompok tanpa mengubah lebar grid, serta tepi/header biru pada seluruh panel grafik. Tinggi kartu ramping, warna kategori dan data tetap.
- File: panel/index.php, panel/layouts/staff-dashboard.php, assets/js/panel.js, assets/css/panel-theme.css. Tidak mengubah query, izin staf, angka, URL, SQL atau database. Lint dua PHP, access.php dan diff check lulus; pencarian memastikan tidak ada referensi tombol animasi tersisa.
- Chrome memakai render PHP/CSS asli dengan data sintetis untuk dashboard Admin/Staf pada viewport 504/1182px serta tema terang/gelap: semua kartu/header memiliki border solid, tombol animasi tidak ada, tiga grafik Admin tetap dan tidak ada overflow. Sintaks seluruh panel.js diverifikasi melalui parser JavaScript Chrome. Render Staf menggunakan semua izin untuk pemeriksaan visual saja, bukan klaim pengujian login/izin ulang. Screenshot Admin desktop diperiksa; perangkat fisik/database operasional tidak diuji ulang. Artefak QA dibersihkan.

## Batik pada header dashboard (20 November 2026)

- Header judul/sapaan dashboard Admin/Staf memakai batik sekolah lokal batik-sekolah.webp yang sudah ada, dengan lapisan gradasi lembut agar teks tetap jelas. Tema gelap memiliki lapisan gelap tersendiri. Ukuran header, border, kartu dan fungsi tidak berubah; URL relatif CSS tetap mendukung subfolder. Hanya assets/css/panel-theme.css berubah.
- Diff check dan keberadaan aset lulus. Tidak mengubah SQL/data atau menjalankan ulang browser/database untuk perubahan latar ini.

## Batik pada judul kartu dan grafik dashboard (20 November 2026)

- Semua bidang judul kartu ringkasan dashboard Admin memakai batik sekolah lokal yang dibaurkan dengan warna kartu dan lapisan gelap agar teks putih tetap jelas. Header grafik Tracer, komposisi alumni dan PKL memakai batik dengan lapisan terang lembut; tema gelap memiliki lapisan gelap. Judul kartu dashboard Staf mengikuti pola batik lembut. Hover judul Admin tidak lagi menghilangkan gambar latar. Ukuran kartu, angka dan fungsi tidak berubah.
- Hanya assets/css/panel-theme.css berubah; aset batik-sekolah.webp yang sama digunakan melalui URL relatif subfolder. Diff check lulus. Chrome pada fixture sembilan judul kartu/panel dengan CSS/aset asli, viewport 504/1182px, memastikan seluruhnya memakai latar batik dan tidak overflow; screenshot desktop diperiksa. Tidak mengubah database/SQL; artefak QA dibersihkan.

## Warna kartu menyatu dengan batik (20 November 2026)

- Lapisan judul batik kartu Admin kini memakai tint dari --stat-bg kartu masing-masing (92-88%) sehingga tidak lagi berupa pita hitam kontras dengan badan kartu. Garis pemisah dibuat lebih lembut; hover mempertahankan pola dan palet yang sama. Deklarasi lama tetap sebagai fallback untuk browser tanpa color-mix. Ukuran/kolom/angka/tautan dan data tidak berubah. Hanya assets/css/panel-theme.css berubah.
- Diff check lulus; perhitungan kontras teks putih terhadap skenario pola putih paling terang pada 12 palet seluruhnya minimal 4.5:1. Chrome menggunakan CSS/aset asli dan fixture 12 kartu pada viewport 504/1182px membuktikan tint terpakai, lapisan normal, semua kategori tampil dan tanpa overflow. Screenshot desktop diperiksa. Tidak ada perubahan database/SQL; artefak QA dibersihkan.

## Batik kartu ringkasan dibatalkan (20 November 2026)

- Mengikuti koreksi pengguna, batik di judul kartu ringkasan Admin/Staf dihapus, termasuk aturan hover dan tema gelap. Judul kembali memakai bidang polos lembut yang menyatu dengan warna kartu; dimensi, garis Excel, angka dan tautan tetap. Batik pada header utama/grafik tidak termasuk kartu pada gambar dan tetap mengikuti keputusan sebelumnya. Hanya assets/css/panel-theme.css berubah.
- Diff check dan pemeriksaan selector memastikan latar judul summary-grid tidak lagi merujuk gambar batik. Tidak mengubah SQL/data atau menguji ulang database/browser untuk penghapusan latar ini.

## Daftar Berita seperti Excel (20 November 2026)

- Berita & Kegiatan memiliki header ringkas berbingkai, ikon eksplisit pada judul/filter/tombol dan tabel panel-sheet bergaris vertikal/horizontal dengan header sticky/area gulir maksimal 430px. Label published ditampilkan sebagai Dipublikasikan tanpa mengubah nilai database/filter. Tindakan Edit (biru lembut), Lihat publik, dan Hapus (merah lembut) sejajar, sama tinggi dan tetap memakai handler/CSRF/konfirmasi lama. CSS scoped news-list-page mendukung tema gelap serta target 44px HP.
- File: panel/berita/index.php, assets/css/panel-theme.css. Query, pagination sepuluh baris, aturan is_public, popup/unggulan dan akses tidak berubah. Lint PHP dan diff check lulus. Chrome dengan template PHP/CSS asli serta fixture terbit/draft/terbit belum publik, viewport 504/1182px, dua tema: tiga baris/form konfirmasi, tautan publik hanya satu sesuai kondisi, garis solid, tombol sama tinggi/muat dan tanpa overflow. Screenshot desktop diperiksa. Tidak menguji CRUD/database ulang untuk perubahan presentasi; artefak QA dibersihkan.

## Jarak vertikal daftar Berita dirapatkan (20 November 2026)

- Padding atas/bawah header menjadi 8px, filter 10px, dan sel tabel 7px. Margin antarblok, nama/slug dan badge dirapatkan. Padding kiri/kanan, lebar kolom, isi, URL dan tinggi target tindakan HP tetap. Hanya assets/css/panel-theme.css berubah.
- Diff check dan spesifisitas aturan scoped news-list-page diperiksa; tidak mengubah SQL/handler/data atau menjalankan ulang browser/database untuk perubahan spasi ini.

## Navigasi dan gulir daftar Berita (20 November 2026)

- Daftar Berita tetap memakai sepuluh data per halaman server; data-list-size=10 menyelaraskan slider generik sehingga tidak menyembunyikan separuh data. Pagination kini menampilkan maksimal sepuluh nomor halaman sekaligus dan Sebelumnya/Berikutnya; rentang bergeser mengikuti halaman aktif sehingga halaman di atas sepuluh tetap dapat diakses. Query/status dipertahankan dan aria-current menandai halaman aktif.
- Kotak tabel dibatasi 400px atau 55vh, scrollbar ramping, header sticky tetap, sehingga bergulir ke bawah tanpa memperpanjang halaman. File panel/berita/index.php dan assets/css/panel-theme.css. Tidak mengubah query/handler/SQL/data.
- Lint PHP dan diff check lulus. Render PHP asli menguji satu/dua/25 halaman, awal/tengah/akhir, jumlah nomor maksimal sepuluh, satu aktif dan parameter filter/URL. Chrome dengan sepuluh berita sintetis, CSS dan kode slider asli pada viewport 504/1182px membuktikan sepuluh baris tersedia, kotak di bawah 400px, scroll 90px bekerja, header tetap, sepuluh nomor/satu aktif dan tanpa overflow. Screenshot desktop diperiksa. Tidak menguji database/perangkat fisik ulang; artefak QA dibersihkan.

## Form Berita terstruktur seperti Excel (20 November 2026)

- Tambah/Edit Berita kini memiliki header berbingkai dan tiga bagian bernomor: Isi berita, Publikasi, Gambar utama opsional. Judul/slug/ringkasan/isi menjadi sel bergaris; panel samping dipisahkan garis vertikal dan header biru lembut, menjadi satu kolom pada layar kecil. Petunjuk popup panjang berada dalam disclosure, dengan keterangan singkat tetap terlihat. Preview gambar dibatasi 130px dan footer Simpan/Batal terpisah. CSS scoped news-form-page tidak memengaruhi editor modul lain.
- File panel/berita/form.php dan assets/css/panel-theme.css. Fieldname, IDs, required/maxlength, CSRF, unggulan/popup, status value draft/published, upload/katalog, hapus gambar, draft error dan handler simpan tidak berubah. Status published ditulis Dipublikasikan pada label. Tanpa SQL atau data asli.
- Lint PHP dan diff check lulus. Chrome memakai template PHP/CSS asli dan fixture tambah/edit (gambar placeholder lokal) pada viewport 504/1182px, tema terang/gelap: satu formulir/tiga judul bagian, semua 11/12 field tetap, centang popup/unggulan tetap, petunjuk awal tertutup, label terhubung, garis solid, tujuan simpan sama dan tanpa overflow. Escape teks HTML di textarea dipastikan. Screenshot desktop diperiksa. Tidak menguji upload/CRUD database ulang karena perubahan presentasi; artefak QA dibersihkan.

## Form Berita diperkecil dan header batik (20 November 2026)

- Lebar form Tambah/Edit Berita diperkecil dari 1150 ke 960px, panel samping desktop 300px. Padding sel/header dan ukuran judul dirapatkan, textarea ringkasan 60px/isi 180px tetap bisa diperbesar, serta preview gambar maksimal 100px. Breakpoint satu kolom dan input 16px/44px HP tetap. Header halaman memakai batik sekolah lokal dengan lapisan terang/gelap; isi form tetap bergaris Excel. Hanya assets/css/panel-theme.css berubah.
- Diff check, keberadaan aset dan aturan breakpoint diperiksa. Tidak ada perubahan field, handler, SQL/data atau pengujian database ulang.

## Katalog Media ringkas bergaris (20 November 2026)

- Kartu berkas diganti tabel seperti Excel dengan kolom Berkas, Jenis/ukuran, dan Tindakan. Thumbnail 44px, nama berkas dapat membungkus, garis sel jelas, tombol Unduh/Hapus sejajar dengan warna berbeda. Lebar halaman maksimal 1060px; daftar memiliki scroll internal maksimal 430px dan header tetap. Pada HP tabel dapat digeser dalam kotaknya dan tombol tindakan minimal 44px.
- Bagian unggah dan pencarian tetap terpisah dan dirapatkan. Query/filter/pagination 12 berkas, handler upload/unduh/hapus, CSRF, konfirmasi penghapusan, dan pemeriksaan berkas tetap dipakai. Tanpa perubahan SQL atau data.
- File: panel/media/index.php, assets/css/panel-theme.css. Lint PHP dan git diff --check lulus. Belum diuji ulang melalui browser/database; pemeriksaan ini mencakup sintaks dan kode tampilan.

## Media: header batik, edit nama, pagination 10 (20 November 2026)

- Header katalog/edit media memakai batik lokal berlapis terang/gelap. Tindakan Edit, Unduh, Hapus sejajar; tabel tetap bergaris dan scroll internal. Daftar maksimal 10 berkas per halaman; slider menampilkan maksimal 10 nomor bergeser dengan Sebelumnya/Berikutnya dan mempertahankan pencarian, tanpa membatasi total halaman.
- Halaman baru panel/media/edit.php untuk mengubah nama tampilan berkas. Ekstensi dan path acak tetap, tidak mengganti file atau referensi konten. Login/role dan izin media diperiksa server pada GET/POST, simpan POST+CSRF, prepared UPDATE, validasi panjang/nama tanpa traversal atau karakter kontrol, output di-escape. Tidak ada SQL baru atau perubahan data operasional saat pengembangan.
- File utama panel/media/index.php, panel/media/edit.php, assets/css/panel-theme.css. Lint kedua PHP dan diff check lulus. Pengujian CLI sintetis: 9 kasus validasi nama, render pagination awal/tengah/akhir (10 nomor, satu aktif, pencarian tetap); limit query 10 diperiksa. Browser dan simpan database langsung belum diuji.

## Form lowongan ringkas seperti Excel (20 November 2026)

- Tambah/Edit Lowongan dibatasi 960px, header batik lokal lembut, ikon eksplisit pada judul/tindakan dan tiga bagian: Informasi pekerjaan, Publikasi & pendaftaran, Poster opsional. Sel isian bergaris dengan header biru lembut; padding ringkas, textarea 150px dapat diperbesar dan preview maksimal 100px. Desktop dua kolom, layar kecil satu kolom, input HP 16px/minimal 44px. Tema gelap memakai warna sel/header sesuai.
- File panel/lowongan/form.php dan assets/css/panel-theme.css. Semua field/required, CSRF, handler, unggahan, pilihan katalog, konfirmasi tautan resmi, tanggal WIB, status dan pesan error tetap. Label published menjadi Dipublikasikan. Tidak ada SQL atau perubahan data operasional.
- Lint PHP dan diff check lulus. Render sintetis template lama/baru memastikan nama seluruh field sama, tiga judul bagian, pembungkus section berpasangan, textarea di-escape, pilihan status/konfirmasi resmi dan tujuan simpan tetap. Browser/perangkat fisik dan CRUD database belum diuji ulang.

## Form lowongan lebih minimalis (20 November 2026)

- Lebar maksimal turun dari 960px ke 800px. Informasi pekerjaan, publikasi/pendaftaran, dan poster disusun berurutan agar panel samping yang panjang tidak menyisakan ruang kosong di bawah informasi pekerjaan. Jenis hubungan kerja memakai satu baris penuh; tanggal/status berdampingan desktop dan satu kolom HP. Padding dirapatkan, textarea awal 110px tetap bisa diperbesar. Batik header, garis Excel, fokus dan ukuran sentuh HP dipertahankan.
- Hanya assets/css/panel-theme.css serta catatan progres berubah; field, handler dan data tetap. Diff check lulus dan cakupan selector/breakpoint diperiksa. Belum diuji ulang melalui browser.

## Lebar form lowongan mengikuti panel (20 November 2026)

- Batas 800px dan margin tengah pada vacancy-form-page dilepas. Form/header kini memakai 100% lebar area konten panel sehingga tidak ada lagi ruang samping tambahan akibat wrapper. Padding panel, susunan bagian, tinggi isian ringkas, batik dan breakpoint HP tetap.
- File assets/css/panel-theme.css dan progres. Diff check lulus; selector dan perubahan satu aturan diperiksa. Tidak mengubah PHP/data; browser belum diuji ulang.

## Jenis hubungan kerja: desain ringkas bergaris (20 November 2026)

- Halaman mengikuti lebar panel tanpa batas tengah tambahan. Header batik dengan ikon dan tombol Tambah jenis; form Tambah/Edit memiliki header bagian, Nama/Status berdampingan, Penjelasan dan Rujukan berupa sel penuh dengan garis Excel. Textarea awal 80px dapat diperbesar, footer simpan terpisah, label terhubung ke ID. HP satu kolom dengan input 16px/44px.
- Daftar diberi judul/jumlah, garis vertikal/horizontal, status berwarna dan tombol Edit; kotak scroll maksimal 380px dengan header tetap dan empty state. Tidak menambah fitur backend, SQL atau mengubah field, CSRF, version, validasi maupun proses simpan.
- File panel/hubungan-kerja/index.php dan assets/css/panel-theme.css. Lint PHP dan diff check lulus. Struktur form, label dan scope CSS diperiksa; browser serta simpan database belum diuji ulang.

## Jenis hubungan kerja: tindakan Lihat dan Edit (20 November 2026)

- Tindakan daftar berisi tombol Lihat hijau dan Edit biru dengan ikon/ukuran konsisten. Lihat menuju panel/hubungan-kerja/detail.php: nama, status, penjelasan, rujukan dalam sel bergaris, header batik, kembali daftar dan edit. Detail membaca data saja, login/role serta izin modul tetap diperiksa, ID valid/404, SELECT prepared, output escape dan URL rujukan divalidasi http/https.
- File panel/hubungan-kerja/index.php, detail.php dan assets/css/panel-theme.css. Tanpa SQL/perubahan data. Lint kedua PHP dan diff check lulus; tautan/ID dan struktur diperiksa. Browser serta database langsung belum diuji.

## Hapus jenis hubungan kerja (20 November 2026)

- Tombol Hapus merah dengan ikon/konfirmasi disandingkan Lihat/Edit. Handler hapus.php memeriksa login, role/izin modul, POST+CSRF, ID dan version. Helper employment_type_delete memakai transaksi, lock baris, pengecekan version dan referensi vacancy_details/alumni_work_profiles. Jenis terpakai ditolak dengan petunjuk Nonaktif; foreign key RESTRICT tetap menjadi proteksi database. Error teknis tidak ditampilkan.
- File panel/hubungan-kerja/index.php, hapus.php, app/helpers/employment.php, assets/css/panel-theme.css. Tidak ada SQL atau penghapusan data operasional saat pengembangan.
- Lint tiga PHP dan diff check lulus. Uji helper dengan adapter database sintetis: jenis bebas commit/delete; referensi lowongan, referensi alumni, version berubah, dan data hilang rollback tanpa delete. Browser dan database nyata belum diuji.

## Hapus pada Lamaran Masuk (20 November 2026)

- Kolom tindakan memiliki tombol ikon Hapus merah 44px sejajar Lihat/WhatsApp, label aksesibel dan konfirmasi penghapusan permanen. Handler panel/lamaran/hapus.php memeriksa role/izin modul, POST+CSRF dan ID; transaksi serta lock baris melindungi penghapusan. Lamaran terkait alumni_application_results ditolak untuk mempertahankan riwayat. Berkas metadata mengikuti FK CASCADE; lampiran fisik privat hanya dibersihkan setelah commit dengan allowlist nama/path dan pengecekan pemakaian bersama. Kesalahan teknis dicatat tanpa SQL ditampilkan.
- File panel/lamaran/index.php, hapus.php dan assets/css/panel-theme.css. Tidak ada SQL baru atau penghapusan data operasional saat pengembangan.
- Lint kedua PHP dan diff check lulus. Adapter sintetis menguji hapus mandiri commit, riwayat alumni/missing rollback tanpa delete, dan pemanggilan penjagaan CSRF. Browser/database nyata serta penghapusan lampiran fisik belum diuji.

## Ikon tindakan daftar lowongan (20 November 2026)

- Tombol tindakan dibuat ikon saja agar ringkas: Edit biru, Lihat publik hijau, Hapus merah. SVG lokal 18px, tombol desktop 38px/HP 44px, bentuk/spacing konsisten, hover dan fokus keyboard, warna tema gelap. Setiap tombol memiliki title dan aria-label termasuk posisi lowongan.
- File panel/lowongan/index.php dan assets/css/panel-theme.css; selector scoped vacancy-list-page. Tujuan link, kondisi published, POST hapus, CSRF dan konfirmasi tetap. Tanpa SQL/data.
- Lint PHP, diff check serta pengecekan tiga ikon dan penjagaan hapus lulus. Browser belum diuji ulang.

## Rekap hasil alumni lebih ringkas (20 November 2026)

- Header memakai batik lembut mengikuti lebar area panel. Filter dipadatkan; tiga statistik menjadi satu lembar bergaris dengan ikon, judul dan angka sejajar serta keterangan di baris berikutnya. Padding/border radius diperkecil agar kartu tidak tinggi. Warna kategori dan penjelasan tentang alumni unik/lolos seleksi tetap.
- Rincian alumni memiliki batas sel, judul tabel ringkas, header sticky dan scroll internal maksimal 450px. Susunan kartu data HP sebelumnya tetap dengan garis pemisah; statistik satu kolom dan input/tombol HP minimal 44px. Tema gelap disesuaikan.
- Hanya assets/css/panel-theme.css dan progres berubah; perhitungan/filter/akses/data tidak diubah. Diff check lulus dan selector, breakpoint serta struktur template terkait diperiksa. Browser belum diuji ulang.

## Rincian alumni: 10 baris dan kotak scroll (20 November 2026)

- Maksimal 10 alumni per halaman (sebelumnya 20), nomor halaman bergeser maksimal 10 dengan Sebelumnya/Berikutnya, status aktif dan parameter tahun tetap; total halaman tidak dibatasi. Kotak rincian bisa difokuskan keyboard, scroll internal maksimal min(340px,55vh), header desktop sticky. Padding/baris dipadatkan; tombol HP tetap minimal 44px.
- File app/layouts/career-dashboard.php dan assets/css/panel-theme.css. Lint/diff check lulus; render dengan 1005 alumni sintetis menguji awal/tengah/akhir/nomor melampaui batas, maksimal 10 baris (akhir 5), 10 nomor/satu aktif, filter tahun dan escape. Tidak ada SQL/perubahan data. Browser belum diuji ulang.
- Batas saat ini: helper career_dashboard masih membaca/mengelompokkan seluruh data sebelum array_slice agar aturan alumni unik dan ringkasan tetap sama. Perubahan ini membatasi tampilan/DOM; bukan klaim optimasi SQL atau uji beban database untuk ribuan data.

## Daftar pemilihan alumni untuk lamaran (20 November 2026)

- Daftar tautan bertitik diganti tabel Excel: Nama, Jurusan, Tahun lulus dan tombol Kelola lamaran berikon. Header batik mengikuti lebar panel, pencarian dengan label/placeholder/reset, informasi jumlah/halaman, empty state. Kotak scroll internal maksimal min(340px,55vh), header sticky, fokus keyboard, tema gelap dan tombol sentuh HP.
- Query daftar kini memakai COUNT serta LIMIT/OFFSET prepared untuk 10 alumni per halaman (bukan berhenti 50 hasil). Navigasi maksimal 10 nomor bergeser mempertahankan pencarian, total halaman tidak dibatasi. Data NISN sama tetap catatan terpisah; jalur formulir pencatatan/hasil alumni tidak diubah.
- File panel/hasil-alumni/lamaran.php dan assets/css/panel-theme.css. Lint PHP dan diff check lulus; struktur tabel, parameter prepared dan tautan tindakan diperiksa. Tanpa SQL/data operasional. Browser dan query database langsung belum diuji.

## Data Alumni bergaris seperti Excel (20 November 2026)

- Header Data Alumni memakai batik lembut mengikuti lebar panel. Tombol toolbar/filter dipadatkan, tab angkatan menjadi satu strip sel berbatas (pilihan aktif tetap jelas). Tabel memakai garis vertikal/horizontal, header biru lembut sticky, padding ringkas, tombol Lihat biru dan kotak scroll maksimal min(360px,55vh). Tema gelap/fokus keyboard serta ukuran input/tombol HP dipertahankan.
- Hanya assets/css/panel-theme.css dan progres; query, filter, pagination 25, ekspor dan izin akses tidak berubah. Diff check lulus; template/helper filter dan selector terkait diperiksa. Browser belum diuji ulang.

## Pesan WhatsApp bergaris dan ringkas (20 November 2026)

- Halaman penyusunan pesan memiliki header batik, tiga langkah dalam strip sel bergaris, filter flex yang tidak membesarkan tombol pencarian. Panel penerima/pesan memakai header biru lembut dan jarak ringkas; tabel penerima/riwayat bergaris, scroll internal maksimal min(340px,55vh), header sticky. Textarea 120px dapat diperbesar dan preview pesan dibatasi tinggi. Tema gelap dan susunan HP/ukuran sentuh tetap.
- File panel/whatsapp/index.php hanya menambah class wa-sheet-page; CSS scoped di assets/css/panel-theme.css. Field, seleksi keranjang, JS, persetujuan, tautan, CSRF dan alur kirim manual tidak berubah. Tidak ada SQL/data atau pesan WhatsApp dikirim saat pengerjaan.
- Lint PHP dan diff check lulus; template/selector dan breakpoint diperiksa. Browser belum diuji ulang.

## Daftar BMW SAKTI bergaris dan ikon ringkas (20 November 2026)

- Judul kini di kiri header batik; Deskripsi/Minat/Tambah dikelompokkan setelah judul. Filter dirapatkan, tabel bergaris Excel dengan header sticky dan kotak scroll maksimal min(360px,55vh). Ikon tindakan seragam: Edit biru, Lihat publik hijau (published), Hapus merah, dengan title/aria-label. Tema gelap dan ukuran sentuh HP minimal 44px dipertahankan.
- File panel/program/index.php dan assets/css/bmw.css. Pagination backend 10, filter/query, tujuan link, POST+CSRF/konfirmasi hapus tidak berubah. Tanpa SQL/data.
- Lint PHP dan diff check lulus; selector dan markup tindakan diperiksa. Browser belum diuji ulang.

## Deskripsi BMW Sakti bergaris dan ringkas (20 November 2026)

- Header batik dengan satu ikon judul, tombol Halaman publik/Daftar program ringkas. Wrapper mengikuti lebar panel tanpa batas 980px/margin tengah. Judul/Status menjadi sel berdampingan dengan garis pemisah; Deskripsi berheader biru lembut, textarea awal 130px bisa diperbesar, petunjuk/footer simpan dipadatkan. HP satu kolom dengan input 16px/tombol minimal 44px; warna gelap disesuaikan.
- File panel/program/deskripsi.php (ikon judul saja) dan assets/css/bmw.css. Field, version, CSRF, status, escape dan proses penyimpanan tidak berubah. Tanpa SQL/data.
- Lint PHP dan diff check lulus, aturan desktop/HP serta selector diperiksa. Browser belum diuji ulang.

## Minat dan pertanyaan program bergaris (20 November 2026)

- Header batik mengikuti lebar panel, filter ringkas, tabel bergaris Excel dengan header biru lembut dan label Tindakan yang jelas. Tombol Buka/balas berikon hijau; kotak daftar scroll maksimal min(360px,55vh) dengan header tetap dan fokus keyboard. Warna gelap/ukuran sentuh HP dipertahankan.
- File panel/program-minat/index.php dan assets/css/bmw.css. Filter/query, pagination 25, hak akses dan alur balas tidak diubah. Lint PHP dan diff check lulus; browser belum diuji ulang. Tanpa SQL/data.

## Alur perjalanan dinas dan biaya diperjelas (21 November 2026)

- Alur dibuat eksplisit: Catat perjalanan membuka pemilihan penempatan siswa, kemudian rincian perjalanan/biaya, lalu kembali ke rekap. Header batik dan tabel Excel ringkas mengikuti lebar panel; tombol Catat/Jadwal/Ekspor dikelompokkan. Rekap sumber sekolah dan pribadi dipisah, cakupan total/ekspor seluruh hasil filter dijelaskan. Daftar 10 per halaman dengan maksimal 10 nomor navigasi dan kotak scroll internal.
- Filter biaya memakai form khusus: Periode penempatan PKL dan Minggu perjalanan opsional, dengan rentang Senin-Minggu yang sedang dipakai terlihat; reset melepas semua filter termasuk penempatan. Filter shared jadwal PKL tidak diubah. Empty state membedakan data kosong/filter tanpa hasil.
- Form menampilkan identitas/tempat/periode penempatan, opsi ganti penempatan sebelum tambah, bagian Rincian perjalanan dan Biaya. Tanggal perjalanan baru tidak lagi otomatis mengambil tanggal awal PKL; harus diisi, kecuali masuk dari monitoring yang sudah memiliki tanggal. Edit mempertahankan tanggal lama. Konfirmasi monitoring tetap opsional, tidak otomatis mengisi rincian saat mengganti pilihan atau mengubah status monitoring. Nominal/sumber dan petunjuk format jelas.
- Batas model dijelaskan: satu catatan terkait satu penempatan dan satu sumber biaya; bila satu perjalanan mencakup beberapa siswa, nominal harus dialokasikan agar tidak ganda. Tidak menambah model perjalanan rombongan/pembagian biaya otomatis atau mengubah SQL/data.
- File panel/biaya-perjalanan/index.php, form.php, assets/css/panel-theme.css. Lint kedua PHP dan diff check lulus. Pengujian sintetis memastikan filter 21 November 2026 menjadi 16-22 November, parameter penempatan tetap, field tambah/edit sama dengan versi sebelumnya, escape/version/CSRF tetap. Browser, ekspor Excel dan simpan database langsung belum diuji ulang.

## Pemilihan penempatan perjalanan tidak lagi buntu (21 November 2026)

- Picker perjalanan memakai komponen khusus app/layouts/trip-placement-picker.php; picker jadwal bersama tidak diubah. Menjelaskan perbedaan data siswa dan penempatan, menyediakan Buat penempatan PKL, empty state dengan langkah/tombol mulai, serta membedakan belum ada penempatan dan pencarian tanpa hasil. Data yang tersedia memiliki tombol Gunakan penempatan ini, COUNT/LIMIT 10 prepared, pagination maksimal 10 nomor, pencarian nama/tempat, escape dan scroll tabel.
- Konteks lanjut=perjalanan dibawa lewat pencarian/pemilihan siswa, formulir penempatan, serta hidden POST. Simpan penempatan langsung menuju formulir perjalanan dengan ID hasil simpan. Bila siswa baru diperlukan, flag kembali=perjalanan dibawa lewat formulir peserta menuju penempatan. Flag berupa allowlist tetap (bukan URL input), tambahan izin biaya-perjalanan diperiksa server. Alur PKL/peserta normal tetap memakai tujuan lama.
- File panel/biaya-perjalanan/form.php, app/layouts/trip-placement-picker.php, panel/pkl/form.php, app/helpers/participant-editor.php, app/layouts/participant-editor.php dan assets/css/panel-theme.css. Tanpa SQL/data operasional.
- Lint lima PHP dan diff check lulus. Uji sintetis: picker kosong/tanpa hasil/berisi, link buat/pilih, escaping, pagination; route simpan penempatan ke perjalanan vs alur normal, CSRF dan izin tambahan. Browser/database nyata serta alur tambah siswa lengkap belum diuji langsung.

## Perjalanan: tampilan inti, hapus, dan rekap Excel (21 November 2026)

- Daftar disederhanakan ke Catat perjalanan/Rekap Excel, filter, ringkasan sumber biaya dan tabel. Panel Cara menggunakan dan tautan Jadwal di header daftar dilepas; petunjuk penting tetap pada formulir pencatatan. Tombol Terapkan/Reset dan ikon dibuat satu baris, tindakan Edit/Hapus sejajar dengan warna berbeda, tetap bergaris/scroll dan dukungan HP/gelap.
- Handler baru panel/biaya-perjalanan/hapus.php memakai login/izin modul, POST+CSRF, ID/version, helper pkl_trip_delete dengan transaksi/lock/version. Hanya pkl_business_trips yang dihapus; penempatan, siswa, dan monitoring tidak disentuh. Penghapusan harus dikonfirmasi dan total berikutnya dihitung ulang dari database. Tidak menghapus data operasional selama pengembangan.
- Ekspor XLSX yang ada diperluas menjadi dua lembar: Rekap biaya (sekolah, pribadi, total, jumlah catatan dan cakupan filter) serta Rincian perjalanan. Seluruh hasil filter diekspor, bukan hanya halaman aktif; agregat DECIMAL tetap string tanpa pembulatan float. Nama siswa/nomor peserta tidak ditambah selain nama yang memang diperlukan pada rincian.
- File panel/biaya-perjalanan/index.php, hapus.php, unduh.php, app/helpers/pkl-schedules.php, assets/css/panel-theme.css. Tanpa SQL baru.
- Lint empat PHP dan diff check lulus. Adapter sintetis menguji delete commit/version berubah/data hilang rollback. XLSX sungguhan dibuat dari query sintetis lalu ZIP/XML diperiksa: dua sheet, jumlah desimal 150000.00+25000.50=175000.50 benar, nama berawalan formula tetap inline text tanpa elemen formula. Browser, Excel desktop dan database operasional belum diuji langsung.

## Desain pemilihan siswa penempatan PKL (21 November 2026)

- Header batik mengikuti lebar panel, tombol Tambah siswa baru/Daftar PKL ringkas. Petunjuk alur perjalanan dipersingkat. Daftar diberi judul/jumlah hasil, tabel bergaris vertikal/horizontal dengan header biru lembut sticky, kotak scroll maksimal min(340px,55vh), tombol Pilih siswa berikon biru.
- Selector scoped pkl-placement-workspace/pkl-student-sheet; slider daftar yang sudah ada memakai data-list-size=10 (sebelumnya default 5). Batas pencarian backend 50 tetap, pencarian diperlukan untuk siswa lain. Fokus/tema gelap/ukuran sentuh HP dipertahankan.
- File panel/pkl/form.php dan assets/css/panel-theme.css. Form dan handler simpan, pilihan siswa/flag lanjut perjalanan, CSRF dan version tidak berubah. Tanpa SQL/data. Lint PHP dan diff check lulus; browser belum diuji ulang.

## Penyelarasan tabel pilih siswa (21 November 2026)

- Proporsi kolom menjadi 42% nama, 38% kelas/jurusan, 20% tindakan; tombol Pilih siswa berukuran konsisten di tengah kolom. Padding/baris dirapatkan dan nama lebih tegas, kelas/jurusan warna sekunder. Header dan footer navigasi menyatu dalam lembar bergaris; status rentang di kiri, tombol navigasi di kanan. Scroll/header sticky/10 siswa per slide tetap.
- Label rentang khusus tabel ini diperbaiki dari pemisah tanda tanya menjadi 1�10 dari 31 siswa. Tabel lain tidak diubah. Mobile tetap tombol 44px, pager membungkus secara teratur.
- File assets/css/panel-theme.css dan assets/js/panel.js, progres diperbarui. node --check, diff check, dan evaluasi label rentang awal/tengah/akhir dari kode asli lulus. Tidak mengubah query/data/tautan pilih. Browser belum diuji ulang.

## Form penempatan PKL ringkas dan berikon (21 November 2026)

- Identitas/riwayat siswa dan Ganti siswa disatukan dalam satu strip ringkas. Form tiga bagian bergaris Excel: Kelas & periode, Tempat pelaksanaan, Pembimbing & status. Field desktop berdampingan dua kolom; tempat/alamat/status penuh, HP satu kolom. Ikon SVG eksplisit tiap label/judul/footer mencegah ikon otomatis ganda. Textarea alamat awal 65px bisa diperbesar, padding dipadatkan, petunjuk dan tombol simpan menjadi satu footer.
- File panel/pkl/form.php dan assets/css/panel-theme.css. Field/required/maxlength, status, hidden siswa/version/CSRF/lanjut, validasi dan handler tidak diubah. Tanpa SQL/data.
- Lint PHP dan diff check lulus; render sintetis tambah/edit memeriksa semua sembilan field dan hidden field, tiga bagian, escape nama siswa serta kelanjutan perjalanan. Browser belum diuji ulang.

## Pilih beberapa siswa untuk penempatan PKL (21 November 2026)

- Daftar siswa memiliki checkbox, Centang semua hasil, Kosongkan, penghitung terpilih, dan Lanjutkan pilihan. Pilihan tetap saat berganti slide 10 baris dalam hasil pencarian yang sama; pencarian baru memulai pilihan baru. Tombol Pilih siswa untuk alur satu siswa tetap tersedia. Maksimal 50 siswa per pengisian.
- Setelah lanjut, pratinjau nama/kelas/jurusan terpilih ditampilkan dalam kotak scroll. Admin mengisi tempat/alamat, periode, pembimbing dan status bersama; kelas/jurusan dibaca ulang dari data masing-masing siswa di server dan tidak ditimpa isian bersama. Setiap siswa mendapat penempatan terpisah.
- Helper pkl_selected_students memvalidasi bentuk/jumlah/ID/keberadaan dan deduplikasi; pkl_save_batch memakai satu transaksi, lock siswa urut ID, validasi seluruh entri/duplikasi sebelum insert, rollback jika gagal. Semua POST melewati login/izin/CSRF yang sama; edit tetap satu siswa. Alur perjalanan setelah batch kembali ke pemilihan penempatan (tidak memilih satu siswa sewenang-wenang atau membuat biaya otomatis).
- File panel/pkl/form.php, app/helpers/pkl.php, assets/js/pkl-selection.js (baru), assets/js/panel.js (lewati auto-icon pada checkbox nama), assets/css/panel-theme.css. Tanpa SQL atau perubahan data operasional.
- Lint kedua PHP, node --check kedua JS dan diff check lulus. Adapter sintetis: dua kelas berbeda, deduplikasi, pilihan kosong/lebih 50/ID hilang/ID tidak valid/tanggal salah/penempatan duplikat/gagal insert kedua; semua kegagalan rollback. Render picker/batch/single memastikan form tidak bersarang, CSRF/ID pilihan, kelas/jurusan readonly pada batch dan escape. JS penghitung/pilih semua/kosongkan diuji dari kode asli. Browser/database nyata belum diuji langsung.

## Lokasi tempat PKL tersimpan dan peta (21 November 2026)

- Form penempatan tunggal/banyak memiliki Cari tempat tersimpan (nama/alamat dari penempatan terdahulu), Cari di peta, pilih hasil untuk mengisi nama/alamat, pin yang bisa diklik/digeser, koordinat lintang/bujur opsional, Ambil alamat titik, Hapus titik, dan Buka Google Maps. Reverse lookup mempertahankan nama perusahaan yang sudah diisi. Peta dimuat saat panel peta dibuka/dipilih; pengetikan tidak memanggil layanan. Alamat manual tetap bisa digunakan bila peta gagal. Tidak mengambil GPS pengguna atau mengirim identitas siswa.
- Leaflet 1.9.4 disimpan lokal di assets/vendor/leaflet dengan lisensi BSD-2-Clause, gambar marker, tanpa build Node atau kunci Google. Tile OpenStreetMap dengan attribution terlihat, browser caching biasa, tanpa prefetch/offline-download. Pencarian Nominatim lewat endpoint privat panel/pkl/lokasi.php: login/role/izin PKL, POST+CSRF, URL upstream dari konfigurasi server saja, timeout/ukuran respons terbatas, tanpa redirect, identitas User-Agent aplikasi. Hanya pencarian bisnis dan reverse lookup eksplisit; hasil di-cache 7 hari dan global file lock menjaga interval minimal 1,1 detik seluruh aplikasi. HTML hasil dipasang melalui textContent, bukan innerHTML.
- Ketentuan layanan yang diterapkan: https://operations.osmfoundation.org/policies/nominatim/ (maksimal 1 request/detik seluruh aplikasi, tidak autocomplete/bulk, cache, attribution dan tanpa data pribadi) serta https://operations.osmfoundation.org/policies/tiles/ (attribution, caching normal, tanpa prefetch). Cocok untuk penggunaan admin terbatas; kapasitas/ketersediaan layanan publik tidak dijamin. Jika penggunaan meningkat, operator mengganti endpoint dengan layanan Nominatim kompatibel/self-hosted melalui konfigurasi.
- SQL terpisah database/updates/026_pkl_location_points.sql membuat tabel opsional pkl_location_points; SQL awal tidak digabung dan database operasional tidak diimpor/diubah. Koordinat DECIMAL 6 digit disimpan per placement dalam transaksi yang sama dengan data penempatan, termasuk batch. Null/kosong melepas koordinat; validasi pasangan, format, rentang -90..90/-180..180. Tanpa migrasi, nama/alamat manual/tersimpan tetap berjalan; UI memberi petunjuk impor dan penyimpanan koordinat ditolak aman.
- Aktivasi: backup database, pilih database aplikasi di phpMyAdmin lalu impor hanya 026_pkl_location_points.sql satu kali. Muat ulang form. PHP cURL dengan sertifikat HTTPS yang valid serta akses keluar HTTPS diperlukan untuk pencarian; direktori storage harus bisa ditulis untuk cache privat storage/map-cache (diabaikan Git). Leaflet lokal tidak memerlukan CDN runtime. Provider dapat diganti lewat maps.geocoder_url/maps.tile_url pada app/config/app.local.php, mencontoh app.example.php. Jangan memasukkan data siswa ke pencarian peta. README tidak dibuat kembali sesuai permintaan sebelumnya.
- File utama app/helpers/pkl-locations.php (baru), app/helpers/pkl.php, panel/pkl/lokasi.php (baru), panel/pkl/form.php, app/layouts/pkl-location-picker.php (baru), assets/js/pkl-location.js, CSS panel, app/config/app.example.php, .gitignore, vendor Leaflet dan SQL 026.
- Pemeriksaan: lint 6 PHP, node --check JS aplikasi/Leaflet, diff check lulus. Adapter sintetis menguji format/batas/nol/pembulatan koordinat, penyimpanan dua siswa dengan lokasi sama/kelas berbeda, pelepasan titik, dan rollback jika koordinat gagal. Render template asli memeriksa field koordinat satu kali, alamat/CSRF/URL subfolder, serta mode sebelum/sesudah migrasi. Cache/throttle diuji tanpa koneksi keluar. Chrome headless memakai DOM sintetis, kode JS asli dan layanan/Leaflet simulasi: tidak ada request otomatis, hasil aman dari HTML injection, pemilihan lokasi, klik pin tanpa request, reverse tidak mengganti nama perusahaan, clear dan reuse lokasi tersimpan. Tidak menguji migrasi/database operasional atau layanan/tile peta langsung. Artefak QA dibersihkan setelah pengujian.

## Form PKL disederhanakan ke bagian penting (21 November 2026)

- Rincian siswa batch tertutup awal; penghitung dan Ganti siswa tetap terlihat. Judul periode pada batch tidak lagi menyebut kelas yang memang mengikuti masing-masing siswa. Pencarian lokasi menjadi satu baris input dengan tombol Tersimpan/Cari peta berikon; petunjuk dipersingkat tanpa menghilangkan larangan memasukkan data pribadi/attribution OSM.
- Ringkasan Peta & koordinat dirapikan ke satu baris dengan ikon dan indikator buka/tutup, bukan marker/ikon/teks bertumpuk. Status pencarian kosong tidak mengambil ruang. HP input pencarian di atas dua tombol, target sentuh 44px. Seluruh field, data-place-action, JS, endpoint, fungsi peta, dan penyimpanan tetap.
- File panel/pkl/form.php, app/layouts/pkl-location-picker.php, assets/css/panel-theme.css. Lint dua PHP dan diff check lulus; markup field/aksi serta breakpoint diperiksa. Tidak ada SQL/data; browser belum diuji ulang.

## Daftar utama PKL dirapikan (21 November 2026)

- Header batik mengikuti lebar panel; judul/keterangan singkat dan tombol Tambah penempatan/Jadwal PKL/Data siswa dikelompokkan. Filter ringkas dengan label terhubung dan ikon. Tabel bergaris Excel, header biru lembut sticky, kotak scroll maksimal min(360px,55vh), proporsi kolom dan tanggal dua baris lebih mudah dibaca.
- Status menjadi badge berwarna; tindakan Jurnal/laporan hijau dan Edit biru memakai ikon seragam, title/aria-label per siswa, fokus keyboard serta ukuran sentuh HP 44px. Tidak menambah fitur hapus. Query/pagination backend 20, filter, izin dan tujuan tindakan tidak berubah.
- File panel/pkl/index.php dan assets/css/panel-theme.css. Lint PHP/diff check lulus; struktur markup/tautan diperiksa. Browser belum diuji ulang. Tanpa SQL/data.

## Desain Jadwal PKL terstruktur (21 November 2026)

- Header batik dengan judul/keterangan singkat dan navigasi Penempatan/Rekap Excel/Biaya perjalanan. Tombol Tambah jadwal dikelompokkan satu toolbar: Monitoring hijau, Pertemuan orang tua biru, Sidang ungu dengan ikon. Filter dipadatkan, label minggu diperjelas dan catatan Senin-Minggu/pertemuan di luar periode tetap tersedia.
- Tabel bergaris Excel dengan header kolom sticky, pemisah minggu tidak sticky agar tidak bertumpuk, scroll internal maksimal min(380px,55vh). Tindakan berupa ikon Edit biru/Detail hijau/Catat perjalanan emas dengan title dan aria-label. Izin biaya serta syarat monitoring tetap; tema gelap dan target sentuh HP 44px.
- File panel/pkl/jadwal.php, app/layouts/pkl-schedule-filter.php, assets/css/panel-theme.css. Query/filter/pagination 25, parameter pembuatan jadwal, ekspor dan aturan data tidak berubah. Lint kedua PHP dan diff check lulus; tautan/kondisi izin diperiksa. Browser belum diuji ulang; tanpa SQL/data.

## Alur tambah jadwal/pertemuan orang tua diperjelas (21 November 2026)

- Halaman Tambah Monitoring/Pertemuan/Sidang memiliki header batik dan dua langkah eksplisit: pilih siswa/penempatan, isi jadwal lalu simpan. Picker menampilkan nama, tempat PKL, periode dan tombol Pilih siswa ini; pencarian COUNT/LIMIT 10 prepared dengan navigasi maksimal 10 nomor, query/jenis dipertahankan. Empty state memiliki jalan buat penempatan; satu jadwal tetap terkait satu penempatan, bukan acara massal baru.
- Setelah memilih, konteks siswa/tempat/periode dan Ganti siswa jelas. Form bergaris tiga bagian: Waktu & peserta, Lokasi & agenda, Status & hasil. Field kondisional orang tua/monitoring tetap sesuai jenis. Nama undangan orang tua tetap opsional; tanggal/jam/petugas/lokasi/alamat/agenda wajib. Status Dibatalkan mempertahankan riwayat. Layout dua kolom tanpa sel tambahan kosong dan satu kolom HP, ikon/fokus, ukuran sentuh 44px.
- Default tanggal awal PKL dan jam 08:00 dilepas pada jadwal baru agar admin memilih waktu sebenarnya. Pertemuan orang tua/sidang tidak lagi otomatis memakai perusahaan/alamat PKL sebagai lokasi acara; monitoring tetap dibantu tempat/alamat PKL. Edit mempertahankan data lama dan validasi periode monitoring tetap server-side; orang tua/sidang boleh di luar periode.
- File panel/pkl/jadwal-form.php, app/layouts/pkl-placement-picker.php, assets/css/panel-theme.css. Field/CSRF/version/jenis, handler/izin/validasi penyimpanan yang ada tetap; tanpa SQL/data.
- Lint dua PHP dan diff check lulus. Uji sintetis menguji default kosong, bantuan lokasi hanya monitoring, tanggal di luar periode diterima orang tua/sidang dan ditolak monitoring, render tiga bagian/field sesuai jenis, CSRF dan escape. Browser serta simpan database langsung belum diuji.

## Pilih beberapa siswa pada jadwal PKL (21 November 2026)

- Picker jadwal memiliki checkbox, Pilih halaman ini, Kosongkan semua, jumlah terpilih dan Lanjutkan pilihan. Maksimal 50 penempatan, tetap 10 hasil per halaman; ID pilihan disimpan dalam sessionStorage per aplikasi/user/jenis di tab yang sama agar tetap saat pindah halaman/pencarian. Tidak menyimpan nama siswa di browser storage. Tanpa JS, pilihan pada halaman saat ini tetap dapat dikirim; fitur mempertahankan antarhalaman memerlukan storage browser.
- Pratinjau siswa/penempatan sebelum simpan. Waktu, lokasi, agenda, status dan bila diisi hasil monitoring berlaku bersama; satu record terpisah dibuat untuk tiap penempatan. Bantuan petugas/lokasi hanya diisi jika sama pada semua penempatan (lokasi hanya monitoring). Hasil berbeda dapat diedit per jadwal. Edit existing tetap satu record.
- pkl_schedule_save_batch memakai data allowlist, transaksi, lock penempatan urut ID, validasi seluruh periode/field/hasil serta duplikasi jenis-tanggal-jam per penempatan sebelum insert. Semua gagal rollback termasuk gagal insert tengah. Monitoring harus masuk periode setiap siswa; periode tanpa irisan mendapat pesan dan tombol simpan nonaktif (server tetap validasi). Orang tua/sidang tetap boleh di luar periode PKL. Session flag membersihkan keranjang setelah berhasil pada kunjungan picker berikutnya.
- File panel/pkl/jadwal-form.php, app/layouts/pkl-placement-picker.php, app/helpers/pkl-schedules.php, assets/js/schedule-selection.js (baru), assets/js/panel.js (skip auto-icon checkbox), assets/css/panel-theme.css. Login/izin, CSRF, jenis allowlist tetap. Tidak ada SQL/data operasional.
- Lint tiga PHP, node --check kedua JS dan diff check lulus. Adapter sintetis menguji 3 jenis jadwal, insert per penempatan/deduplikasi, serta 9 kasus kegagalan/rollback (kosong, >50, hilang, ID invalid, tanggal salah, duplikat, gagal insert kedua, monitoring Selesai tanpa hasil, tanggal di luar periode). JS asli diuji select/restore antarpages, hidden pilihan saat submit, clear/guard kosong/reset; template picker/single/batch diuji tanpa form bersarang, ID tersembunyi, CSRF/jenis dan escape. Browser/database nyata belum diuji langsung.

## Pertemuan orang tua difokuskan ke rapat di sekolah (21 November 2026)

- Jenis orang_tua ditampilkan sebagai Rapat orang tua / wali (key database tetap). Form memakai Waktu & undangan, Lokasi rapat & agenda, Status rapat; label Penanggung jawab rapat, Nama/kelompok undangan, Ruang/lokasi rapat di sekolah, Alamat sekolah dan Agenda rapat. Tidak menampilkan istilah pembimbing/penguji atau hasil monitoring untuk rapat. Tombol simpan batch menyebut undangan, pilihan beberapa siswa tetap.
- Jadwal rapat baru mengambil nama sekolah/alamat dari Pengaturan yang sudah ada; alamat placeholder seperti Belum diisi tidak dipakai sebagai alamat resmi. Alamat kosong mendapat pemberitahuan untuk mengisi alamat sekolah yang benar. Nama penanggung jawab tidak otomatis disamakan dengan guru pembimbing PKL. Tanggal/jam tetap ditentukan admin dan tidak dibatasi periode praktik. Lokasi perusahaan tidak dijadikan tujuan rapat; data rapat lama tidak ditimpa otomatis.
- Picker rapat mencari nama/kelas dan menampilkan kelas/jurusan, bukan perusahaan/periode sebagai lokasi acara. Rincian siswa rapat menggunakan kelas/jurusan. Model backend masih mengaitkan undangan/jadwal ke penempatan yang tersedia; bukan modul rapat sekolah umum tanpa penempatan. Identitas sekolah tidak diganti menjadi nama baru atau alamat Tangerang rekaan; mengikuti Pengaturan aplikasi. Ringkasan rincian siswa dibuat satu baris agar ikon/marker tidak bertumpuk.
- File app/helpers/pkl-schedules.php, panel/pkl/jadwal-form.php, panel/pkl/jadwal.php, app/layouts/pkl-placement-picker.php, assets/css/panel-theme.css. Tanpa SQL/perubahan data operasional.
- Lint empat PHP dan diff check lulus. Uji helper/render sintetis memastikan default sekolah/penolakan placeholder alamat, penanggung jawab kosong, istilah rapat/kelas/simpan undangan, seleksi batch/CSRF dan escape tetap, tanpa field hasil monitoring. Browser/database nyata belum diuji langsung.

## Form penempatan PKL dua kolom (21 November 2026)

- Form desktop dibagi dua: kiri kelas/periode serta pembimbing/status, kanan pencarian tempat, peta/koordinat, nama tempat dan alamat. Dua kolom memakai lebar panel, header kelompok bergaris dan satu footer simpan bersama. Rincian siswa tetap disclosure ringkas. Pencarian lokasi mempunyai input penuh dan dua tombol sejajar agar tidak sesak pada kolom kanan. Di bawah 900px menjadi satu kolom; field HP tetap 16px/44px.
- File panel/pkl/form.php dan assets/css/panel-theme.css; hanya pengelompokan markup/CSS. Nama/ID/required field, hidden CSRF/version/lanjut/ID batch, handler, peta, data dan SQL tidak berubah.
- Lint PHP/diff check lulus. Render PHP asli untuk single/batch memastikan semua field/form/ID pilihan tetap dan tidak bersarang. Chrome headless dengan CSS asli serta data sintetis offline pada viewport 1262px (single/batch) dan 504px (batch): dua kolom sejajar desktop, satu kolom HP, satu form/footer dan tidak ada horizontal overflow. Screenshot desktop diperiksa. Tidak memanggil tile/geocoder atau database; tidak menguji simpan ulang. Artefak QA dibersihkan.

## Tindakan siswa terpilih pada form penempatan (21 November 2026)

- Rincian siswa batch memiliki kolom Tindakan: ikon Edit biru membuka panel/peserta/form.php?id=... di tab baru, ikon Hapus merah mengeluarkan siswa dari pilihan penempatan saja. Data induk siswa tidak dihapus. Tombol Perbarui rincian mengambil nama/kelas/jurusan terbaru setelah edit tanpa menyimpan penempatan; petunjuk tab baru ringkas tersedia.
- Hapus/refresh mengirim POST ke form penempatan yang sama dengan CSRF, ID pilihan allowlist serta formnovalidate supaya isian belum lengkap tidak menghalangi pengubahan pilihan. Handler mengenali submitter dan tidak memanggil save; data tempat/tanggal/pembimbing/koordinat/lanjut dipertahankan. Menghapus ID di luar pilihan ditolak; minimal satu siswa harus tersisa (Ganti siswa untuk memilih ulang). Setelah hapus/refresh rincian tetap terbuka, penghitung dan ID tersembunyi diperbarui sehingga siswa yang dikeluarkan tidak ikut disimpan nanti.
- Konfirmasi menggunakan event.submitter pada helper JS yang sudah ada; konfirmasi form lain tetap. Tampilan ikon konsisten 34px desktop/44px HP, warna gelap dan title/aria-label per siswa. Edit tetap melewati izin server modul peserta; tidak mengubah akses.
- File panel/pkl/form.php, assets/js/panel.js, assets/css/panel-theme.css. Tanpa SQL atau perubahan data operasional saat pengerjaan.
- Lint PHP, node --check JS dan diff check lulus. Uji controller sintetis: hapus hanya mengubah pilihan, refresh metadata terbaru, input/draft/koordinat/konteks lanjut tetap, penolakan siswa terakhir/ID asing dan pemanggilan CSRF tanpa save. JS asli diuji konfirmasi batal/setuju serta penyimpanan biasa tanpa prompt tambahan. Browser/database nyata belum diuji langsung.

## Koreksi Edit siswa terpilih menjadi Ganti siswa (21 November 2026)

- Makna Edit dikoreksi sesuai pengguna: ikon biru membuka pilihan siswa pengganti pada baris tersebut, bukan editor nama/data siswa. Link edit data induk dan tombol Perbarui rincian dihapus dari UI. Pencarian pengganti berdasarkan nama/kelas, maksimal 10 hasil, mengecualikan semua siswa yang sudah dipilih. Gunakan siswa ini mengganti satu ID tepat pada posisinya; jumlah/siswa lain tetap. Batal ganti tidak mengubah pilihan.
- Seluruh langkah POST+CSRF dengan formnovalidate memakai form penempatan yang sama, mempertahankan tanggal/tempat/alamat/pembimbing/koordinat/konteks lanjut. Validasi server menolak target di luar daftar, ID pengganti invalid/tidak tersedia, dan siswa yang sudah dipilih. Tidak ada perubahan data induk atau penyimpanan penempatan sampai tombol Simpan akhir. Save diblokir selama penggantian belum selesai (UI dan server), agar tidak menyimpan siswa lama tanpa sengaja. Hapus dari pilihan tetap ada.
- File panel/pkl/form.php dan assets/css/panel-theme.css. Tidak ada SQL/data operasional. Handler perbarui lama tetap diterima untuk kompatibilitas form yang sudah terbuka, tetapi tidak lagi ditampilkan.
- Lint PHP/diff check lulus. Controller sintetis menguji mulai/cari/ganti satu baris, duplikat/ID asing ditolak, batal/pending-save guard, draft/koordinat/konteks tetap dan tidak ada pemanggilan save/data induk. Render aktual memeriksa tombol eksternal terkait owning form, tanpa form bersarang, tidak ada link editor data siswa, CSRF dan escape. Browser/database nyata belum diuji langsung.

## Alur lokasi PKL dirapikan kembali (21 November 2026)

- Urutan kolom lokasi menjadi Cari tempat -> nama tempat -> alamat lengkap -> atur titik opsional. Tombol Tersimpan diperjelas menjadi Cari tersimpan dan Cari di peta, sehingga tidak menyerupai tombol simpan penempatan. Pemilih peta/koordinat dipindah ke bawah alamat melalui partial baru app/layouts/pkl-location-map.php; field dan endpoint tetap.
- Memilih hasil mengisi nama/alamat/koordinat lalu menutup daftar hasil, tanpa membuka peta otomatis. Peta tetap dibuka hanya saat diperlukan. Ambil alamat dari pin langsung mengisi alamat (tidak perlu memilih hasil lagi), tidak mengganti nama perusahaan atau menggeser pin ke koordinat hasil reverse geocoding. Nama perusahaan yang kosong tetap harus diisi sendiri, bukan diganti nama jalan. Attribution OSM tetap terlihat, isian manual tetap tersedia.
- JS memakai satu konteks kolom kanan untuk pencarian/field/peta agar elemen terpisah tetap terhubung. Peta diselaraskan ke titik aktif saat dibuka. Batas request/cache/CSRF/validasi koordinat/server tidak berubah. File panel/pkl/form.php, app/layouts/pkl-location-picker.php, pkl-location-map.php (baru), assets/js/pkl-location.js, CSS panel. Tanpa SQL/perubahan data.
- Lint tiga PHP, node --check dan diff check lulus. Render PHP asli memeriksa urutan komponen; Chrome headless dengan DOM asli dan layanan/Leaflet simulasi menguji isi otomatis, peta tidak auto-open, pin tanpa request, reverse langsung mengisi alamat sambil mempertahankan nama/titik, hasil tertutup, clear/reuse dan escape HTML. Tidak memanggil layanan peta/database nyata. Artefak QA dibersihkan.

## Rekap Excel daftar penempatan PKL (21 November 2026)

- Tombol Rekap Excel berikon ditambahkan di toolbar daftar PKL, membawa pencarian/status aktif. Endpoint baru panel/pkl/rekap-excel.php memerlukan login/role/izin PKL, modul siap, dan GET saja. Mengambil seluruh penempatan sesuai filter tanpa LIMIT halaman; tidak mengekspor NIS/nomor peserta.
- XLSX menggunakan helper Excel lokal yang ada: Ringkasan (jumlah penempatan/aktif/selesai/arsip dan cakupan filter) serta Daftar penempatan (nama, kelas/jurusan, periode, tempat/alamat, kedua pembimbing, status). Sel teks eksplisit menjaga nama/keterangan dari formula injection. Jumlah adalah penempatan, bukan siswa unik. Ekspor per siswa/jurnal/laporan yang sudah ada tidak diubah.
- pkl_list_filters pada app/helpers/pkl.php digunakan bersama oleh daftar dan ekspor agar validasi/escape LIKE/prepared params sama. File panel/pkl/index.php, panel/pkl/rekap-excel.php, app/helpers/pkl.php, CSS panel. Tanpa SQL/data operasional.
- Lint tiga PHP/diff check lulus. XLSX sungguhan dari query sintetis diperiksa sebagai ZIP/XML: dua sheet, 25 penempatan melampaui satu halaman, total 25, q/status tetap, wildcard di-escape, input filter invalid ditolak, nama formula tetap teks. Browser/Excel desktop dan database nyata belum diuji langsung.


## Detail PKL: jurnal dan rekap lebih jelas (21 November 2026)

- Detail PKL memakai ringkasan dua kolom bergaris Excel (satu kolom pada HP), header batik, tindakan berikon, status laporan, tautan Maps dari titik tersimpan/alamat. Jurnal bukan rekap kehadiran; laporan tidak otomatis mengubah status penempatan.
- Jurnal terbaru dahulu, 10 per halaman, maksimal 10 nomor navigasi, tabel scroll dengan header melekat. Rekap mingguan memakai seluruh jurnal; rincian panjang dapat dibuka sesuai kebutuhan. Edit/hapus berikon dan berlabel, hapus tetap POST + CSRF + pemeriksaan versi.
- Tanggal jurnal tidak dipaksa ke batas periode ketika hari ini di luar PKL. Laporan baru sebelum periode mulai juga tidak diisi tanggal masa depan otomatis. Gagal hapus tidak mengosongkan form edit. Duplikat tanggal diarahkan ke Edit. Payload edit hanya tiga field jurnal.
- File: panel/pkl/detail.php, panel/pkl/laporan.php, assets/css/panel-theme.css. Tanpa SQL/perubahan data operasional.
- Uji MariaDB 10.4 terisolasi dengan migrasi 019 dan data sintetis lulus: tambah/edit/hapus, tanggal di luar periode, duplikat, versi lama, jurnal milik penempatan lain, rollback, rekap ISO lintas tahun, simpan/edit/konflik laporan dan status tetap aktif. Render template asli memverifikasi 10 jurnal + halaman berikut dan form setelah gagal hapus. XLSX asli diperiksa ZIP/XML: tiga sheet, seluruh 12 jurnal. Lint PHP/diff check lulus. Sesi operasional dan tampilan browser desktop/HP belum diuji langsung. Database uji dihentikan dan artefak dibersihkan.


## Batas jurnal PKL (21 November 2026)

- Jurnal harian di detail PKL menjadi 5 data per halaman, navigasi maksimal 5 nomor dengan Sebelumnya/Berikutnya. Scroll vertikal/horizontal tabel dan header melekat tetap tersedia; rekap mingguan tetap mencakup semua jurnal. File panel/pkl/detail.php. Tanpa SQL. Lint PHP, pemeriksaan batas pagination sintetis dan diff check lulus.


## Garis ringkasan detail PKL (21 November 2026)

- Dua sisi ringkasan memakai baris CSS subgrid bersama agar garis horizontal sejajar meski alamat panjang. Garis label/nilai tetap utuh sampai akhir baris. Pada layar kecil bagian ditumpuk kembali dengan garis pemisah masing-masing. Hanya assets/css/panel-theme.css; tanpa SQL/logika data. Pemeriksaan diff dan struktur CSS lulus; tampilan browser belum diverifikasi langsung.


## Form laporan akhir dua kolom (21 November 2026)

- Laporan akhir PKL disusun kiri Pelaksanaan & hasil (tanggal, ringkasan, kompetensi) dan kanan Evaluasi & kesimpulan (kendala, saran, kesimpulan). Header batik ringkas, identitas terpisah, garis sel Excel, ikon, tombol simpan di bawah. HP satu kolom, input 16px dan tindakan minimal 44px.
- Backend, validasi, CSRF, versi data dan unduhan tetap. File panel/pkl/laporan.php, assets/css/panel-theme.css. Tanpa SQL. Lint PHP/diff check dan render sintetis dua kolom/lima textarea/escaping/CSRF/version/tautan unduh lulus. Tampilan browser belum diuji langsung.


## Garis form laporan sejajar (21 November 2026)

- Kedua kolom laporan berbagi empat baris CSS subgrid (judul + tiga baris isian). Garis horizontal mengikuti tinggi terbesar pasangan isian, termasuk saat textarea diperbesar; kontrol tetap rata atas. Pada HP kembali satu kolom. File assets/css/panel-theme.css; tanpa perubahan PHP/SQL. Struktur CSS dan diff check lulus; browser belum diuji langsung.


## Navigasi jurnal berwarna (21 November 2026)

- Pagination jurnal memakai nomor biru muda, halaman aktif biru navy, tombol arah hijau lembut dengan ikon panah lokal. Ikon dokumen otomatis pada nomor dihilangkan khusus navigasi ini. Batas 5 data/5 nomor dan parameter tautan tetap. Target HP minimal 44px, dukungan dark mode. File detail.php, panel-theme.css, panel.js. Lint PHP/JS dan diff check lulus; tampilan browser belum diuji langsung.


## Rekap mingguan jelas dan bernavigasi (21 November 2026)

- Label Minggu/tahun dan rentang tanggal diperjelas; jumlah hari berwarna dengan penjelasan bukan kehadiran. Rincian kegiatan/catatan memakai tombol disclosure berikon, jumlah entri dan daftar bergaris; catatan kosong eksplisit.
- Rekap terbaru dahulu, maksimal 5 minggu per halaman dan 5 nomor navigasi berwarna. Parameter week_page terpisah dari page jurnal; navigasi keduanya mempertahankan parameter pasangan. Tabel tetap scroll dengan header melekat; total/Excel tetap seluruh data.
- File panel/pkl/detail.php, assets/css/panel-theme.css. Tanpa SQL. Lint PHP/diff check dan render sintetis 7 minggu (5 tampil, halaman berikut, terbaru dahulu, escape HTML, catatan kosong) lulus. Tampilan browser belum diuji langsung.


## Empat status siswa PKL dan riwayat (21 November 2026)

- Status yang tersedia: Sedang PKL (nilai internal aktif untuk kompatibilitas), Belum PKL (belum), Gagal PKL (gagal), PKL selesai (selesai). Arsip lama tetap dipertahankan dan diberi label perlu ditinjau, tidak diubah menjadi gagal. Status bersifat pencatatan pengelola; tanggal atau simpan laporan tidak otomatis mengubahnya.
- Halaman baru panel/pkl/status-siswa.php ditautkan dari daftar dan detail PKL. Satu baris per siswa, termasuk siswa tanpa penempatan (Belum PKL). Status keseluruhan mengikuti penempatan terakhir yang dicatat (ID terbesar), bukan menimpa penempatan sebelumnya. Riwayat tempat/tahun lama pada siswa tidak dianggap bukti selesai. Ringkasan seluruh siswa, pencarian nama/kelas, filter status, 10 data per halaman, maksimal 5 nomor navigasi berwarna, tabel scroll/header melekat.
- Detail siswa menyediakan perubahan status penempatan terakhir dengan alasan wajib, versi data, serta riwayat seluruh penempatan (waktu WIB, sebelum/sesudah, alasan, pengelola, tautan penempatan). Status sama tidak membuat riwayat palsu. Gagal mencakup batal berangkat/berhenti pelaksanaan sesuai alasan. Penempatan ulang dibuat baru agar kegagalan sebelumnya tetap terlihat. Tanpa penempatan, buat penempatan dahulu sebelum mencatat pelaksanaan/gagal/selesai.
- Integrasi pkl_save dan pkl_save_batch mencatat status awal/perubahan dalam transaksi yang sama dengan penempatan dan koordinat. Perubahan status pada form lama wajib alasan; edit metadata tanpa perubahan status tidak menambah event. Lock siswa/penempatan dan version check menolak benturan; handler mengikat penempatan terakhir ke siswa yang dipilih. Riwayat tidak menyediakan edit/hapus. Form penempatan baru memilih Belum PKL secara default setelah migrasi; batch tetap atomik. Label/filter/rekap Excel/laporan mengikuti empat status.
- SQL TERPISAH: database/updates/027_pkl_status_history.sql. Prasyarat tabel siswa/users dan migrasi 019. Memperluas enum tanpa mengganti nilai lama, membuat pkl_status_history dengan FK RESTRICT dan indeks versi unik, serta snapshot awal. Snapshot jelas diberi waktu saat impor, tidak mengarang tanggal/pengelola masa lalu. Pengujian impor ulang tidak menggandakan baseline/riwayat. Tidak mengubah bkk_jaya_buana.sql atau migrasi lama.
- Cara mengaktifkan: backup database, pilih database aplikasi pada phpMyAdmin, impor hanya 027_pkl_status_history.sql, lalu Panel > PKL > Status siswa & riwayat. JANGAN mengimpor ulang SQL instalasi awal. SQL ini belum dijalankan pada database operasional. Sebelum impor, penyimpanan penempatan lama tetap bekerja dan halaman status memberi petunjuk migrasi. Jangan memproses perubahan data saat impor.
- File utama: app/helpers/pkl-status.php (baru), app/helpers/pkl.php, panel/pkl/status-siswa.php (baru), form.php, index.php, detail.php, rekap-excel.php, unduh.php, app/layouts/pkl-report.php, assets/css/panel-theme.css. Tidak menambah dependency atau mengubah modul lain.
- Validasi: MariaDB 10.4 terisolasi, migrasi 019 + 027, 26 assertion untuk empat status, snapshot/idempotensi, alasan, versi lama, siswa salah, status invalid/sama, rollback, penempatan ulang, batch dan status siswa terakhir. Middleware autentikasi/role/izin asli pada database sintetis memverifikasi tamu ditolak, admin boleh, staf perlu izin PKL+peserta, akun nonaktif ditolak; CSRF valid/invalid diuji. Handler POST asli, redirect, escaping alasan HTML dan query/render daftar/filter/riwayat/tanpa penempatan lulus. XLSX asli diperiksa ZIP/XML: seluruh 5 penempatan dan label empat status. Kompatibilitas sebelum migrasi lulus. Lint 16 PHP terkait, node --check dan diff check lulus. Chrome headless fixture sintetis desktop dan lebar 390px diperiksa; tabel scroll tanpa memanjangkan daftar. Login/browser end-to-end di situs operasional belum dilakukan. Server MariaDB uji dihentikan dan artefak QA dibersihkan.
- Uji manual setelah impor: buka siswa tanpa penempatan; buat penempatan Belum PKL; ubah Sedang dengan alasan; ubah Gagal; buat penempatan baru untuk siswa yang sama, lalu selesaikan. Pastikan riwayat penempatan lama tetap ada. Coba tanpa alasan, dua tab dengan versi lama, filter empat status, rekap Excel, serta akses staf tanpa izin.


## Tata letak detail status PKL (21 November 2026)

- Ringkasan penempatan kiri dalam sel label/nilai bergaris; formulir perubahan status kanan dengan judul, status, alasan dan footer simpan terpisah. Satu bingkai, jarak ringkas, ikon eksplisit, catatan bersama di bawah. HP satu kolom; siswa tanpa penempatan tidak menampilkan form perubahan. Riwayat dan backend tetap.
- File panel/pkl/status-siswa.php dan assets/css/panel-theme.css. Tanpa SQL. Lint PHP/diff check, struktur CSS, render sintetis dengan/tanpa penempatan, escaping serta CSRF/placement/version field lulus. Browser belum diuji langsung untuk revisi ini.


## Tabel riwayat status dirapikan (21 November 2026)

- Lebar kolom proporsional 16/27/27/30; tanggal/jam dipisah, jenis event kecil, nama tempat tebal, badge sebelum/sesudah sejajar dengan panah. Alasan dan pengelola dibedakan secara visual. Penjelasan snapshot dipindah ke disclosure Tentang riwayat. Garis/scroll/header tabel dan pagination tetap, backend tidak diubah.
- File status-siswa.php dan panel-theme.css. Tanpa SQL. Lint PHP/diff check, struktur CSS dan render sintetis perubahan/snapshot (format WIB, escaping, badge sebelum hanya bila ada) lulus. Browser belum diuji langsung pada revisi ini.


## Tombol riwayat di daftar PKL (21 November 2026)

- Kolom Tindakan memiliki ikon Riwayat PKL berwarna ungu, title/aria-label nama siswa, menuju status-siswa.php?siswa_id=ID#pkl-status-history. Menggunakan siswa_id, bukan ID penempatan. Jurnal dan Edit tetap tersedia; tombol dapat wrap agar tidak saling menimpa. File panel/pkl/index.php dan panel-theme.css. Tanpa SQL. Lint PHP/diff check dan pemeriksaan tujuan link lulus.


## Ikon riwayat PKL (21 November 2026)

- Tombol Riwayat PKL menggunakan ikon history lokal (jam dengan panah melingkar), menggantikan reset. Warna dan tautan tetap. File panel/layouts/icons.php dan panel/pkl/index.php. Lint PHP/diff check lulus.


## Warna tombol penempatan (21 November 2026)

- Jurnal & laporan hijau, Penempatan baru biru dengan teks/ikon putih, hover dan dark mode. Khusus detail status siswa. File status-siswa.php, panel-theme.css. Tanpa SQL/logika berubah. Lint PHP, struktur CSS dan diff check lulus.


## Nama kelas industri siswa (21 November 2026)

- Kolom daftar siswa menampilkan nama kelas industri yang tersimpan. Nilai lama Ya/Tidak/boolean atau kosong menggunakan nama kelas dari industry_class_participants + industry_classes bila tersedia (satu query untuk maksimal 10 siswa terlihat). Jika nama tidak diketahui, tampil Nama kelas belum diisi atau Belum tercatat; tidak menebak nama perusahaan/kelas dan tidak mengubah data lama. Label filter boolean diperjelas sebagai data lama.
- Form dan petunjuk impor Excel meminta nama kelas, bukan Ya/Tidak; validasi server bersama menolak boolean pada tambah/edit/impor dan tetap menerima kosong. Untuk data Ya lama tanpa hubungan kelas, pengelola perlu Edit nama yang benar. File app/helpers/participants.php, panel/peserta/index.php, app/layouts/participant-editor.php. Tanpa SQL.
- Lint tiga PHP/diff check dan uji helper dengan query simulasi lulus: relasi tunggal/jamak, nama tersimpan tetap, nama belum diketahui, migrasi kelas industri belum ada, kueri terikat ID, penolakan boolean, nama sah/kosong diterima. Database dan browser operasional belum diuji langsung untuk perubahan ini.


## Tampilan daftar siswa diringkas (21 November 2026)

- Khusus halaman daftar: header batik ringkas, tab lebih pendek, filter grid sejajar dengan ikon, ekspor berupa tombol hijau, judul Nama siswa, tabel proporsional 25/15/12/28/20 dengan garis Excel dan padding lebih kecil. Edit biru/hapus merah, navigasi panah berwarna. HP filter bertumpuk dan target 44px, dark mode didukung. Batas 10 siswa, filter, nama kelas industri, scroll dan logika hapus tetap.
- File panel/peserta/index.php dan assets/css/bmw.css. Tanpa SQL. Lint PHP/diff check, struktur CSS dan render sintetis (escaping, filter pada tautan halaman, tombol hapus/CSRF, nama kelas) lulus. Browser belum diuji langsung pada revisi ini.


## Dropdown kelas industri dari Pengaturan (21 November 2026)

- Pengaturan admin memiliki bagian Pilihan Kelas Industri (participant_industries), satu nama per baris memakai sheet bergaris yang ada. Simpan terpisah melalui upsert settings; duplikat dinormalisasi, maksimal 150 karakter/nama, 300 pilihan dan 10.000 karakter; Ya/Tidak ditolak. Tidak mengisi nama rekaan.
- Form siswa memakai dropdown opsional dari pengaturan. Admin mendapat tautan pengaturan, staf mendapat petunjuk hubungi Admin. Nama lama di luar master tersedia khusus edit siswa itu, tidak menjadi pilihan siswa baru. Impor Excel memvalidasi nama terhadap master yang sama; kosong diterima.
- File management.php, participants.php, participant-editor.php (helper/layout), panel/pengaturan/index.php dan simpan.php. Tidak perlu SQL baru. Cara pakai: Pengaturan > Pilihan Kelas Industri > isi nama sebenarnya satu per baris > Simpan > Tambah/Edit siswa.
- Lint enam PHP/diff check lulus. Handler pengaturan asli dengan query simulasi memeriksa admin, CSRF, allowlist field, normalisasi duplikat dan penolakan boolean. Parser dan render formulir asli memverifikasi dropdown/selected/tautan tanpa input manual. Controller siswa asli menolak nama POST di luar master dan mempertahankan nama lama pada edit. Database/browser operasional belum diuji langsung pada perubahan ini.


## Dropdown tahun PKL dari Pengaturan (21 November 2026)

- Tahun PKL pada form siswa menjadi dropdown opsional dengan pilihan Belum ditentukan. Admin mengelola satu tahun/tahun ajaran per baris melalui Pengaturan > Pilihan Tahun PKL (participant_pkl_years), memakai sheet dan simpan bagian terpisah. Format 1900-2199 atau dua tahun berurutan seperti 2026/2027; duplikat dinormalisasi.
- Form dan impor memvalidasi pilihan di server; tahun lama yang sah di luar master tetap tersedia khusus edit siswa itu. Tidak mengubah tanggal atau status penempatan PKL. File participants.php, management.php, participant-editor.php (helper/layout), panel/pengaturan/index.php dan simpan.php. Memakai tabel settings; tidak perlu SQL baru.
- Lint enam PHP/diff check lulus. Parser, batas tahun/rentang invalid, render dropdown/selected/link pengaturan, dan handler simpan pengaturan asli dengan kueri simulasi (hanya key sasaran, normalisasi, rentang salah ditolak) lulus. Database/browser operasional belum diuji langsung pada perubahan ini.


## Impor dan template mengikuti dropdown Pengaturan (21 November 2026)

- Bagian impor menjadi dua langkah ringkas: siapkan template dan unggah, petunjuk bullet, tombol template hijau, tautan Pengaturan Kelas Industri/Tahun PKL khusus admin. Batas unggahan dan peringatan impor menambah data tetap jelas.
- Template XLSX kini tiga sheet: Peserta (pertama, 9 header asli tetap), Pilihan dan Panduan. Dropdown native Excel untuk kelas/jurusan/angkatan/kelas industri/tahun PKL menggunakan named ranges dari pilihan server, berlaku baris 2-1001. Kolom data berformat teks untuk telepon/tahun; header berwarna dan dibekukan. Tidak berisi siswa contoh. Jika master kosong, tidak membuat rentang/dropdown kosong yang rusak. Pilihan adalah snapshot saat unduh; setelah Pengaturan berubah, unduh ulang. Validasi server tetap berlaku meski dropdown Excel dilewati.
- File panel/peserta/template.php, app/layouts/participant-editor.php, assets/css/bmw.css. Tanpa SQL. Lint dua PHP/diff check dan render impor/CSRF/upload/pengaturan lulus. XLSX asli diperiksa ZIP/XML: tiga sheet, lima dropdown dan named ranges, batas 1.000 baris, header 9 kolom, format teks, escape karakter khusus, master kosong. Template diisi sintetis lalu dibaca parser impor asli: data dan nol awal telepon tetap; rumus ditolak. Excel desktop/browser operasional belum diuji langsung. Artefak QA dibersihkan.


## Tampilan kesalahan impor ringkas (21 November 2026)

- Khusus halaman impor, pesan kesalahan ditampilkan sebagai panel ringkas: judul/jumlah masalah, tabel bernomor bergaris dengan header melekat, area scroll maksimal 230px/35vh, dan petunjuk unggah ulang. Batas lama 30 pesan tetap, jumlah total dan sisa masalah jelas. Halaman tambah/edit serta validasi/pemrosesan impor tidak berubah.
- File app/layouts/participant-editor.php dan assets/css/bmw.css. Tanpa SQL. Lint PHP/diff check, struktur CSS dan render sintetis 100 kesalahan (30 tampil, total benar, escaping, region scroll keyboard, upload/CSRF tetap) lulus. Browser belum diuji langsung pada revisi ini.


## Audit dan perapian formulir rapor siswa (21 November 2026)

- Form rapor diringkas dengan header batik, identitas dua kolom bergaris Excel, label wajib, kompetensi dalam area scroll, hitungan 1-50, tombol tambah hijau/hapus merah, dan footer simpan. HP satu kolom/target 44px. CSS hidden diperbaiki agar kontrol yang belum diaktifkan JS tidak tampak; dukungan dark mode dan versi aset CSS/JS khusus rapor ditambahkan agar pembaruan tidak tertahan cache.
- Pemilih siswa mencari nama atau kelas, maksimal 50 hasil ditambah siswa terpilih (menggantikan 500). Peringatan sebelum pencarian ulang/navigasi membuang isian yang berubah; penghapusan kompetensi terisi meminta konfirmasi. Tombol tambah dibatasi 50, penomoran/nama field diperbarui dan predikat required saat Final. Tidak menghitung predikat otomatis dari nilai.
- Periode dinormalisasi untuk spasi, slash dan tanda hubung, sehingga satu siswa tidak mendapat rapor ganda hanya karena perbedaan penulisan periode. Pemeriksaan duplikat juga menormalkan periode lama tanpa mengubah data lama; konflik unique key menjadi pesan terarah. Transaksi, kunci siswa, versi, snapshot identitas dan satu siswa/periode tetap. Rapor lama tanpa detail diberi satu baris kosong saat edit. Isian nilai invalid tidak dipotong menjadi nilai valid secara diam-diam saat validasi gagal.
- File app/helpers/learning-reports.php, panel/capaian-pembelajaran/form.php, assets/css/learning-reports.css, assets/js/learning-reports.js, panel/layouts/header.php (versi CSS rapor saja). Tanpa SQL atau perubahan database operasional.
- MariaDB 10.4 terisolasi dengan migrasi 025: 22 assertion lulus untuk draft/final, desimal/batas angka, periode duplikat termasuk tanda hubung lama, tanggal, identitas/versi, snapshot, rollback, dan hapus anak. Query/render formulir asli memverifikasi 50 hasil + siswa terpilih dan rapor kosong. POST invalid asli mengembalikan 422 serta mempertahankan 100.005 dan escape HTML.
- Chrome headless fixture lokal sintetis: tampilan desktop diperiksa; uji DOM lebar 390px untuk tambah/hapus/konfirmasi batal, batas 50, predikat Final/Draft, pembatalan pencarian ketika isian belum tersimpan dan tidak ada overflow halaman semuanya lulus. Lint PHP terkait, node --check, struktur CSS dan diff check lulus. Login dan alur browser end-to-end operasional belum diuji; tidak mengubah middleware yang ada. MariaDB uji dihentikan dan artefak QA dibersihkan.
- Uji manual: cari siswa lalu isi periode/pembimbing; simpan Draft tanpa predikat; isi semua predikat lalu Final; coba nilai di luar 0-100; coba membuat periode sama dengan variasi spasi; coba dua tab edit; coba pencarian ulang setelah mengisi kompetensi.


## Lebar formulir rapor (21 November 2026)

- Batas lebar 1.100px pada editor rapor dihapus; formulir dan area konten khusus halaman ini menggunakan lebar tersedia penuh dengan gutter 14px. Halaman lain dan logika rapor tetap. File assets/css/learning-reports.css. Struktur CSS/diff check lulus; browser belum diuji langsung pada revisi ini.


## Desain dokumen rapor sekolah dan kop (21 November 2026)

- Pratinjau/detail dan cetak memakai partial dokumen yang sama: kop, judul rapor, identitas dua kolom, bagian A kompetensi dengan kolom proporsional/garis hitam, bagian B catatan, serta tanggal/nama pembimbing dan ruang tanda tangan. Tipografi dokumen serif, skor dua desimal, nilai kosong ditandai garis, draft ditandai jelas. Tidak menambah NIS/nomor peserta atau pejabat penandatangan yang tidak tersedia.
- Cetak A4 portrait margin 12mm, header tabel berulang saat lanjut halaman, baris/catatan/tanda tangan dijaga dari pemotongan bila memungkinkan. Pratinjau HP tabel scroll di dalam kertas. Ikon otomatis panel tidak disisipkan ke dokumen rapor. Toolbar dan cache versi CSS/JS halaman cetak disesuaikan. Tidak mengubah penyimpanan/penilaian.
- Kop referensi pengguna belum tersedia sebagai file di proyek. Dukungan kop gambar utuh siap: assets/images/kop-rapor.png (juga mendukung .webp/.jpg/.jpeg). Jika file ada dan valid, langsung dipakai pada detail serta cetak, tanpa crop. Sementara memakai logo sekolah dan identitas dari Pengaturan serta pita batik lokal; alamat placeholder tidak dicetak. Logo mitra/teks kop referensi tidak direka ulang. Pemasangan kop persis referensi masih memerlukan berkas gambar tersebut.
- File app/layouts/learning-report.php, panel/capaian-pembelajaran/detail.php, cetak.php, assets/css/learning-reports.css, assets/js/panel.js (pengecualian ikon dokumen). Tanpa SQL.
- Uji render sintetis partial asli lulus: escaping identitas/deskripsi, Final/Draft, nilai opsional, alamat placeholder tidak muncul, tanda tangan, kop fallback dan kop gambar valid (fixture), penilaian kosong. Chrome headless memeriksa tampilan dokumen dan menghasilkan PDF uji dengan 45 kompetensi menjadi 4 halaman A4 (media box sekitar 595x842pt). Lint tiga PHP, node --check, struktur CSS/diff check lulus. File kop asli dan browser operasional belum diverifikasi; artefak QA dibersihkan.


## Kop rapor asli terpasang (21 November 2026)

- File pengguna assets/images/kop-rapor.png sudah tersedia dan diperiksa visual: sesuai referensi, 892x165. Partial rapor otomatis menggunakan gambar utuh pada detail dan cetak. Dimensi natural ditambahkan, width 100%/height auto mempertahankan proporsi; garis tambahan di luar gambar dihilangkan. URL berversi filemtime agar penggantian kop tidak tertahan cache. Berkas gambar asli tidak diedit.
- File app/layouts/learning-report.php, assets/css/learning-reports.css. Tanpa SQL. Lint/diff check lulus. Chrome fixture lokal dengan data sintetis memverifikasi gambar termuat, proporsi benar, tanpa fallback/garis tambahan. PDF A4 dihasilkan dengan gambar tertanam. Artefak QA dibersihkan.


## Garis rapor dan ketajaman kop (21 November 2026)

- Identitas rapor memiliki bingkai dan garis label/nilai; baris kiri/kanan sejajar memakai subgrid, HP satu kolom dan cetak kembali dua kolom. Border tabel hitam utuh termasuk baris terakhir; override khusus rapor mengatasi CSS panel yang menghapus border bawah. Catatan memakai warna garis yang sama.
- Kop asli 892x165 dipertahankan tanpa edit/upscale. Maksimal lebar tampil mengikuti lebar piksel sumber (dari getimagesize), responsif height auto dan rata tengah. Ini mencegah blur karena pembesaran layar; detail cetak lebih tinggi tetap memerlukan berkas sumber beresolusi lebih tinggi. Tidak mengklaim menambah detail yang tidak ada pada gambar asli.
- File app/layouts/learning-report.php dan assets/css/learning-reports.css. Tanpa SQL. Lint/diff check serta struktur CSS lulus. Chrome fixture dengan CSS panel lengkap, layar 1700px dan nama siswa panjang memverifikasi kop termuat/tidak dibesarkan/proporsional, border terakhir tampil, baris identitas sejajar dan tidak ada overflow halaman. Artefak QA dibersihkan.


## Kop dan lembar rapor seimbang (21 November 2026)

- Lebar pratinjau kertas kini mengikuti kop asli ditambah gutter/border (892+58px untuk gambar saat ini), sehingga sisi kop, identitas dan tabel sejajar tanpa ruang kosong lebar di dalam kertas. Lembar rata tengah, jarak atas dan judul diringkas. Pengaturan lebar ini hanya layar, cetak A4 tetap. Variabel ukuran gambar dipindah ke artikel. Berkas kop asli tidak diubah/diperbesar.
- File app/layouts/learning-report.php dan assets/css/learning-reports.css. Tanpa SQL/logika data. Lint PHP, struktur CSS dan diff check lulus.


## Desain rekap absensi peserta (21 November 2026)

- Header batik ringkas, toolbar Daftar kegiatan/Rekap Excel, filter grid sejajar, penjelasan cara membaca dalam disclosure. Tabel bergaris Excel, nama tegas, angka rata tengah dengan warna Hadir hijau/Izin biru/Sakit emas/Alpa merah/Belum diisi ungu; nol lebih redup.
- Area tabel scroll dengan header melekat; slider client 5 baris dihapus khusus tabel ini agar tidak bertumpuk dengan pagination server. Query/perhitungan/25 peserta per halaman tetap, navigasi server maksimal 5 nomor berwarna dan mempertahankan filter. HP target 44px, dukungan dark mode.
- File panel/absensi/rekap.php, assets/css/bmw.css, assets/js/panel.js (nomor halaman tanpa ikon otomatis). Tanpa SQL. Lint PHP/node --check/diff check dan struktur CSS lulus. Render sintetis memverifikasi escape nama, warna status, area scroll, filter/tautan ekspor dan halaman berikut tetap. Browser operasional belum diuji langsung pada revisi ini.


## Perapian dan audit alur mitra (21 November 2026)

- Daftar mitra/direktori kampus memakai header batik, toolbar ringkas, filter sejajar, tabel bergaris/header melekat/scroll internal dan tindakan ikon berlabel: Detail/PIC hijau, Edit biru, MoU ungu sesuai izin, Hapus merah. Logo hilang memakai ikon lokal. Status aktif/nonaktif jelas. Pagination server tetap 20 mitra dengan maksimal 5 nomor; slider client tidak menggandakan navigasi. HP target 44px dan dark mode.
- Filter kampus mengabaikan klaster perusahaan yang tidak relevan; UI menonaktifkan filter/field klaster saat Kampus. Form perusahaan mewajibkan klaster/bidang usaha di browser sesuai validasi server. Parameter array/filter invalid ditolak; LIKE menggunakan prepared params dan escape wildcard. Izin MoU dibaca sekali untuk seluruh daftar, bukan setiap baris.
- partner_save menolak permintaan tanpa profil ketika migrasi 022 sudah aktif, menutup jalur melewati pemeriksaan versi. Penulisan base/profile tetap satu transaksi. Hapus memakai helper baru partner_delete: POST+CSRF pada handler, lock mitra/profil, versi dari daftar harus sama, ID sudah hilang tidak dianggap berhasil. FK tetap melindungi MoU/Kelas Industri/riwayat; pesan menyarankan Nonaktif. Logo/media tidak dihapus.
- Query publik tetap hanya nama/jenis/logo/website/deskripsi untuk mitra aktif; PIC dan MoU tidak ditambahkan ke publik. Download MoU tetap lewat middleware login/izin MoU+mitra dan penyimpanan privat. Tidak mengganti klasifikasi kampus dalam negeri yang sudah ada.
- File app/helpers/partners.php, panel/mitra/index.php, hapus.php, assets/css/bmw.css, assets/js/panel.js. Tanpa SQL/perubahan database operasional. Sebelum migrasi 022, daftar dan penyimpanan base lama tetap didukung.
- MariaDB 10.4 terisolasi dengan schema mitra + migrasi 022: 22 assertion lulus untuk tambah/edit, canonical negara, data PIC, konflik versi, profil hilang, URL unsafe, aturan klaster, perubahan jenis berelasi, hapus versi lama/null, FK MoU/Kelas Industri, cascade profil untuk mitra tanpa relasi, filter kampus dan LIKE. Middleware asli memeriksa tamu/staf tanpa izin ditolak, admin/staf berizin boleh, unduh MoU ditolak tanpa izin tersendiri, GET dan CSRF invalid ditolak.
- Query/render asli dengan data sintetis memverifikasi escaping, field versi+CSRF, tautan MoU sesuai izin, filter kampus/direktori dan query publik tidak mengandung PIC/nonaktif. Chrome fixture desktop diperiksa; probe ponsel memastikan klaster dinonaktifkan/diaktifkan dengan tepat, tabel scroll dan tidak ada overflow halaman. Lint 9 PHP/node --check/struktur CSS/diff check lulus. Unggah logo, berkas MoU asli dan login browser operasional belum diuji end-to-end; helper unggah tidak diubah. Database uji dihentikan dan artefak QA dibersihkan.


### Pratinjau bingkai HP lokal ? 21 November 2026
- Menambahkan preview-hp.php serta assets/css/preview-hp.css dan assets/js/preview-hp.js: bingkai iPhone/Android, ukuran otomatis/100%, navigasi halaman relatif, beranda dan muat ulang. Website utama tidak diubah; URL aset mengikuti base_url subfolder.
- Hanya localhost, GET, no-store/noindex dan CSP terbatas. Ini pratinjau bingkai/viewport, bukan emulator mesin Safari/Android.
- PHP lint, node --check, HTTP 200, penolakan akses nonlokal/POST dan screenshot Chrome iPhone/Android lulus. Tidak menguji perangkat fisik. Tanpa SQL/dependency baru.


### Pemberitahuan koneksi ? 21 November 2026
- Komponen bersama connection-assets.php memuat CSS/JS lokal pada layout publik, panel dan login. Banner ringkas responsif dengan ikon, status aksesibel, offline sejak awal dan event perubahan koneksi browser.
- Setelah jaringan kembali, tampil tombol Muat ulang dan pengingat menyalin isian belum disimpan. Tidak reload otomatis; navigasi GET menghindari pengiriman ulang POST. Tidak menambah penyimpanan offline, service worker atau SQL. Status jaringan browser tidak menjamin server/internet dapat dijangkau; halaman yang belum pernah dimuat tetap mengikuti halaman error browser saat offline.
- Lint 4 PHP, node --check, uji state JS online/offline/reconnect/tombol dan HTTP publik/login lulus. Putus-sambung jaringan perangkat fisik belum diuji.


### Detail mitra ringkas ? 21 November 2026
- Merapikan panel/mitra/detail.php dan CSS terisolasi di bmw.css: header batik lembut, tombol berikon/warna, identitas-logo kecil, profil dan PIC dua kolom, tabel definisi bergaris seperti Excel, deskripsi terpisah. Mobile menjadi satu kolom; alamat/URL panjang membungkus. Mendukung dark mode.
- PIC tetap internal dan akses/izin MoU serta Kotak Ide tetap diperiksa. Tidak mengubah data, handler, atau SQL. Tampilan kampus tidak menampilkan klaster perusahaan.
- PHP lint, struktur kurung CSS, pemeriksaan escaping/izin dan git diff --check lulus. Tampilan browser dengan akun pengelola belum diverifikasi pada perubahan ini.


### Formulir mitra terstruktur ? 21 November 2026
- Form bersama tambah/edit mitra kini berupa lembar bergaris: identitas dan profil di kiri, PIC serta status/logo di kanan; satu kolom di HP. Header batik lembut, tombol berikon, label jelas dan logo ringkas. CSS khusus partner-form-sheet mendukung dark mode.
- Mempertahankan nama field, nilai lama, CSRF, version/profile_complete, helper unggah dan selector JS jenis/klaster/bidang usaha. Tidak mengubah penyimpanan, database atau izin.
- Lint PHP, pemeriksaan kontrak field/CSRF/helper gambar, struktur CSS dan git diff --check lulus. Browser login pengelola dan unggah aktual belum diuji pada perubahan desain ini.


### Desain riwayat MoU ? 21 November 2026
- panel/mou/index.php memakai header batik ringkas, tombol berikon/warna, petunjuk status yang dapat dibuka, filter sejajar, tabel bergaris dengan header melekat dan scroll internal. Label status aktif/belum berlaku/kedaluwarsa berbeda warna, tindakan perpanjang/unduh jelas, empty state memberi petunjuk. CSS terisolasi mou-list-page mendukung HP dan dark mode.
- Query, filter mitra, pagination 20 dokumen, perhitungan tanggal WIB, izin dan endpoint dokumen privat tetap dipertahankan. Tanpa perubahan SQL atau data.
- PHP lint, struktur CSS dan git diff --check lulus; tampilan browser dengan akun pengelola belum diuji pada perubahan ini.

- Revisi tampilan: menghapus bagian Cara membaca status MoU dari halaman riwayat sesuai permintaan. Perhitungan status dan fitur dokumen tetap tersedia. PHP lint lulus.


### Alur tambah MoU jelas ? 21 November 2026
- Form MoU menjadi dua tahap: pilih mitra melalui tabel bergaris dengan tombol eksplisit, lalu isi nomor/periode/PDF. Pencarian server 10 mitra per halaman, scroll internal, status/jenis mitra, empty state dan tautan Data mitra. Validasi pencarian kini sebelum output layout.
- Tahap dokumen memakai dua kolom ringkas, mitra terpilih dan Ganti mitra (dokumen baru), label perpanjangan serta referensi dokumen lama. Tidak menyediakan ganti mitra pada perpanjangan. Header batik, ikon, tombol warna dan responsif mengikuti desain panel.
- Handler POST dalam form tetap menggunakan CSRF, validasi PDF, transaksi, relasi perpanjangan mitra yang sama dan penyimpanan riwayat baru. Tidak mengubah SQL/database/izin.
- Lint PHP, pemeriksaan kontrak CSRF/pagination dan struktur CSS/diff lulus. Alur unggah dengan browser/database operasional belum diuji pada revisi ini.

- Tabel daftar dokumen MoU dirampingkan: ikon/judul sejajar, proporsi kolom tetap, riwayat dan tombol dalam satu grup, nama PDF dipotong visual dengan title lengkap, ukuran berkas kecil. Garis tabel tetap sejajar; target sentuh HP 44px. Tanpa perubahan query/handler. PHP lint, struktur CSS dan diff check lulus; browser belum diverifikasi.

- Tampilan tambah MoU disederhanakan sesuai permintaan: hapus penanda tahap, subjudul tutorial, petunjuk Data mitra dan penjelasan penyimpanan. Pertahankan konteks perpanjangan, validasi, batas PDF dan tindakan utama. Tabel pemilihan dirampingkan dengan ikon/judul sejajar, proporsi kolom tetap dan tombol kecil. PHP lint, kontrak CSRF, struktur CSS dan diff check lulus; browser belum diverifikasi.

- Form mitra dipadatkan lagi: kolom kiri/kanan seimbang, nama/jabatan PIC berdampingan, ikon-teks sejajar, header tanpa subjudul, hilangkan nomor bagian dan keterangan berulang. Field/textarea dan jarak vertikal lebih ringkas; HP tetap satu kolom dengan input 44px. Kontrak CSRF/profil/unggah tetap. PHP lint/struktur CSS/diff lulus; browser belum diuji.


### Jadwal Sarasehan Industri
- Daftar memakai header batik ringkas, filter sejajar, tabel bergaris dengan header melekat/scroll internal, 10 kegiatan per halaman, ikon sejajar dan tombol Detail/Edit berwarna. Filter tetap dipertahankan di pagination. Empty state membedakan belum ada data dan hasil filter kosong. Tambah jadwal tidak ditampilkan sebelum migrasi modul tersedia.
- Memeriksa alur form/detail/helper: validasi tanggal, jam WIB, URL http/https, lokasi atau tautan wajib, minimal satu mitra perusahaan dan PIC, CSRF serta version lock tetap tersedia. Tidak menambah fitur hapus atau mengubah SQL.
- Lint 4 PHP dan uji validasi jadwal/tanggal/jam/URL/lokasi/undangan lulus. Struktur CSS dan diff check lulus. Simpan/edit database, ekspor dan browser operasional belum diuji pada perubahan ini.

- Form tambah/edit sarasehan dirapikan menjadi kegiatan/waktu di kiri dan lokasi/tautan di kanan; tabel undangan bergaris dengan scroll internal dan ikon sejajar. Header batik, tombol simpan hijau, label ringkas. Keterangan panjang diganti petunjuk singkat yang diperlukan untuk lokasi/PIC dan risiko pencarian memuat ulang. Kontrak field, CSRF, version dan penyimpanan tetap. Lint PHP, struktur CSS dan kontrak field lulus; browser belum diuji.

- Detail sarasehan: header batik dengan tindakan Daftar/Edit/Rekap Excel dikelompokkan, ringkasan tanggal/jam/lokasi/PIC bergaris dua kolom, tautan membungkus, tabel undangan bergaris/header melekat/scroll internal, jumlah mitra dan empty state. HP satu kolom, dark mode tersedia. Izin, escaping dan ekspor tetap. Lint PHP, struktur CSS/diff lulus; browser belum diuji.


### Kotak Ide Industri: daftar dan alur
- Daftar usulan memakai header batik ringkas, tombol Tautan pengiriman mitra, keterangan privasi singkat, filter status+Reset, status berwarna, tombol Baca usulan, tabel Excel/header melekat/scroll internal dan 10 usulan per halaman. CSS khusus idea-list-page mendukung mobile/dark mode. Query daftar tetap tidak mengambil isi/kontak.
- Alur ditinjau: tautan satu kali terikat mitra aktif, token hash/kedaluwarsa/pencabutan, validasi persetujuan/kontak, detail berizin dan perubahan status POST+CSRF/version. Halaman tautan diberi no-store; tombol Cabut memakai formnovalidate agar tidak terhalang field jam yang hanya relevan untuk pembuatan. Server tetap memvalidasi aksi/izin.
- Lint dua PHP, seluruh app/tests/industry-forums-ideas.php, struktur CSS/diff lulus. Transaksi database, pengiriman tautan riil dan browser operasional belum diuji pada revisi ini. Tanpa SQL/perubahan database.

- Pilihan perusahaan pada Tautan khusus mitra kini berupa tabel bergaris dengan status aktif/nonaktif dan tombol Kelola tautan, bukan daftar bullet. Header batik, filter cari/reset, 10 mitra per halaman, scroll internal dan pagination mempertahankan pencarian. Validasi pencarian sebelum output. Alur pembuatan/pencabutan dan izin tetap. Lint PHP, struktur CSS/diff dan pemeriksaan CSRF/no-store lulus; browser/database belum diuji.

- Detail tautan mitra diringkas: identitas perusahaan dan Ganti perusahaan, masa berlaku kecil, tombol Buat hijau/Cabut merah sejajar, URL readonly dalam bagian terpisah dan riwayat bergaris dengan header melekat/scroll. Penjelasan panjang diganti satu catatan sekali kirim/pencabutan, peringatan salin URL tetap. Mobile/dark mode dan empty state tersedia. CSRF/no-store/alur token tetap. Lint PHP/struktur CSS/diff lulus; browser belum diuji.

- Halaman Pengguna memakai seluruh lebar konten panel, menghapus max-width 1150px dan margin tengah. Header, catatan dan sel tabel dipadatkan; tombol HP tetap 44px. Data/izin/aktivasi akun tidak diubah. PHP lint, struktur CSS dan diff check lulus; tampilan browser belum diuji.

- Tambah/edit Pengguna memakai seluruh lebar konten panel. Identitas di kiri dan foto opsional di kanan, izin/password/tindakan tetap selebar lembar, garis pemisah rapi, jarak lebih padat dan nomor bagian/subjudul dibuang. Mobile satu kolom dan input 44px tetap. Tidak mengubah field/CSRF/izin/handler akun. Lint PHP, struktur CSS dan diff lulus; browser belum diuji.

- Form pengguna direvisi menjadi dua kolom seimbang: identitas/role/password awal di kiri, foto dan izin Staf BKK di kanan. Tombol Batal/Simpan satu baris bawah selebar lembar. Reset password akun lama tetap formulir terpisah untuk menjaga tindakan tidak tercampur. Mobile satu kolom, izin memakai scroll internal. Field/CSRF/handler tetap. Lint PHP, pemeriksaan urutan kolom dan struktur CSS/diff lulus; browser belum diuji.

- Pemadatan lanjutan form pengguna: padding/jarak/tinggi input desktop diperkecil, keterangan berulang diringkas, area izin dibatasi 190px dengan scroll internal agar tinggi kolom lebih seimbang. Tombol tetap di bawah, target HP 44px dipertahankan. Tanpa perubahan izin/handler/data. PHP lint, struktur CSS dan diff check lulus; browser belum diuji.

- Footer publik dan panel diberi latar batik sekolah lokal melalui bmw.css bersama. Overlay navy pada publik, terang pada panel dan gelap mengikuti tema; tinggi/padding footer tidak ditambah. Background cetak dimatikan. Struktur CSS, HTTP aset batik/CSS dan diff check lulus; tampilan browser belum diuji.

- Copyright footer publik dan panel diseragamkan menjadi &copy; BKK SMKS JAYA BUANA v.1.2.2 sesuai permintaan. Latar batik tetap. Lint kedua layout PHP lulus.

- Grafik kelengkapan PKL diberi animasi reveal kiri-ke-kanan saat pertama terlihat dan kilau halus 2 putaran (4 detik). Persentase/lebar dari server tetap, tidak mengulang tanpa henti, menghormati reduced-motion dan cetak. JS tanpa IntersectionObserver tetap menampilkan grafik. node --check/struktur CSS/diff lulus; animasi browser belum diuji.

- Angka persentase kelengkapan PKL memakai badge hijau menyatu, angka tebal tabular, simbol persen proporsional dan count-up satu kali 1,1 detik saat terlihat. Nilai server dan label aksesibel tetap, tanpa data menampilkan strip; reduced-motion/print menampilkan nilai akhir. Lint PHP/node/struktur CSS/diff lulus; browser belum diuji.

- Akun Pribadi memakai seluruh lebar konten, panel foto lebih lapang agar file input tidak terpotong berlebihan, identitas ringkas bergaris, padding/header/tombol dipadatkan. Mobile satu kolom dan tombol/input 44px. Hanya CSS bmw.css, proses simpan/CSRF/unggah tidak diubah. Lint PHP/struktur CSS/diff lulus; browser belum diuji.

- Footer publik ditata ulang dengan kelas footer-neat: desktop brand/menu sejajar, HP menu 3 kolom (2 untuk layar sangat sempit), sosial wrap terpusat, copyright rapi; batik dan tinggi minimalis tetap. Ikon sosial tanpa URL valid tidak lagi ditampilkan sebagai link palsu. Footer panel teks terpusat dan membungkus.
- Enam halaman tujuan footer HTTP 200; hasil render tidak mengandung href kosong/#. Lint PHP, struktur CSS dan diff lulus. URL sosial resmi yang belum diisi harus dilengkapi melalui Pengaturan; tujuan eksternal dan tata letak browser/perangkat fisik belum diuji.

- Pengaturan Website memakai seluruh lebar panel; menu samping/header/isian/tombol diringkas. Hak cipta dibuat selebar baris agar tidak menyisakan sel kosong, textarea alamat/retensi dipendekkan, profil tetap lebih tinggi. HP menu dua kolom dan tombol 44px. Hapus petunjuk umum berulang, simpan per bagian/CSRF tetap. Lint PHP/struktur CSS/diff lulus; browser belum diuji.

- Daftar Pengguna diberi pencarian GET nama/username/email dengan tombol Cari/Reset, validasi 100 karakter, prepared params terpisah dan escape wildcard LIKE. Nilai pencarian di-escape saat ditampilkan; empty state khusus hasil kosong. Hak akses admin dan tindakan akun tetap. Lint PHP/struktur CSS/diff lulus; query database/browser belum diuji langsung.

- Ringkasan Tracer Study pada dashboard diganti infografik bintang geometris SVG lokal dengan bidang gradasi/ikon, total di tengah dan legenda angka/persentase berwarna. Empat bidang mengikuti empat status; bila data lama lainnya tersedia, lima bidang. Bentuk bersifat ringkasan kategori, bukan luas proporsional (grafik komposisi yang terpisah tetap). Tidak menambah kategori rekaan.
- Animasi masuk bergiliran sekali saat terlihat, reduced-motion/cetak statis, legenda aksesibel dan tautan filter status_kegiatan valid (data lama lainnya ke daftar umum). Nilai tetap dari database. Layout responsif tanpa library baru. Lint PHP/node, struktur CSS/diff lulus; tampilan browser belum diuji.


### Dashboard ringkas untuk laptop
- Pada viewport >=1100px dan tinggi >=600px: kartu statistik 6 kolom/2 baris dipadatkan, tiga grafik sejajar satu baris, ilustrasi/legenda ringkas, padding konten/header/link dikurangi. Untuk tinggi <=800px grafik lebih kecil. Tidak memotong konten atau menyembunyikan scrollbar; HP dan layar lebih kecil tetap scroll normal.
- Data/query/tautan/animasi tetap. CSS terisolasi dashboard admin. Chrome fixture data sintetis 1366x768 diperiksa: seluruh kartu dan footer terlihat setelah pemadatan; fixture memakai navbar/sidebar sederhana, belum akun operasional. Lint PHP/struktur CSS/diff lulus. Ukuran browser, zoom dan sidebar aktual dapat memengaruhi tinggi.

- Dashboard: tiga kartu grafik dibuat stretch dengan header/note/area ilustrasi/legenda/footer seragam. Bagian bawah kartu kini sejajar, kartu komposisi mendapat tautan rekap nyata, jarak antarkartu 8px dan antarbagian 10px. Konten tetap terbuka tanpa overflow hidden tambahan; mobile tetap alami. PHP lint/struktur CSS/diff lulus; browser belum diuji pada penyejajaran ini.

- Grafik kipas komposisi alumni diberi animasi masuk tiap segmen bergiliran saat terlihat, otomatis aktif lagi setiap refresh (tanpa penyimpanan status animasi). Proporsi dan interaksi legenda tetap; reduced-motion/cetak statis. node --check/struktur CSS/diff lulus; browser belum diuji.

- Ruang kosong bawah dashboard laptop dikurangi: khusus dashboard admin >=1100x700, halaman mengikuti tinggi viewport dengan navbar/konten/footer, sidebar dibatasi tinggi tersedia dan grafik mengisi sisa ruang. Konten tetap bisa digulir bila diperlukan, tanpa dipotong. HP/modul lain tetap. Struktur CSS dan diff check lulus; browser belum diuji.

- Daftar Berita & Kegiatan memakai seluruh lebar panel tanpa batas tengah. Header/filter/tabel/tindakan dipadatkan, ikon sejajar dan proporsi kolom disesuaikan. Pagination/scroll/10 berita per halaman serta CSRF/handler tidak diubah. Target HP 44px dipertahankan. PHP lint/struktur CSS/diff lulus; browser belum diuji.

- Tambah/edit Berita memakai seluruh lebar panel, kolom isi/pengaturan lebih seimbang, header batik ringkas tanpa subjudul/nomor bagian. Field, textarea, petunjuk dan tombol dipadatkan; HP satu kolom dan target 44px. Tidak mengubah data/CSRF/unggah/popup/handler. PHP lint, struktur CSS/kontrak field/diff lulus; browser belum diuji.

- Daftar berita tetap LIMIT 10 per halaman; navigasi kini selalu terlihat dengan rentang data, panah hijau, nomor biru dan halaman aktif tegas. Maksimal 10 nomor, nomor bisa digeser horizontal pada HP; Prev/Next dinonaktifkan di batas. Data-vertical-list mencegah pagination client ganda; filter tetap di URL. Lint PHP, simulasi batas pagination 0-1000 data, struktur CSS/diff lulus; browser/database belum diuji.

- Katalog Media diperlebar ke seluruh konten panel. Header batik dipadatkan, unggah/pencarian dua kolom seimbang, judul tabel berikon sejajar, thumbnail/baris/tombol diperkecil dan petunjuk unggah diringkas. HP satu kolom/target 44px tetap. Query 10 berkas/pagination, unggah/unduh/edit/hapus dan CSRF tidak berubah. Lint PHP/struktur CSS/kontrak field/diff lulus; browser belum diuji.
