# BKK SMK JAYA BUANA

Portal berbasis PHP Native 8.2 dan MySQL/MariaDB. Tahap 1 sampai 11 menyediakan fondasi, database, autentikasi, portal publik, CRUD berita/lowongan/program, katalog media, tracer study publik dengan panel respons dan ekspor, serta pengelolaan mitra, pengguna, pengaturan, dan akun pribadi.

## Instalasi XAMPP

Kebutuhan: Apache 2.4 dengan `mod_rewrite`, `mod_authz_core`, `mod_mime`, dan `.htaccess` aktif; PHP 8.2 dengan `pdo_mysql`, `fileinfo`, dan session; MySQL 8.0.16+ atau MariaDB 10.4+. Tidak membutuhkan Node.js, Composer, framework PHP, maupun CDN. GD dipakai untuk membuat gambar fixture pengujian pengembang, bukan syarat runtime website. CLI PHP diperlukan untuk admin pertama. Pastikan `file_uploads=On`, `upload_max_filesize` minimal `10M`, `post_max_size` misalnya `16M`, dan direktori temporary PHP dapat ditulis.

1. Gunakan Apache 2.4, PHP 8.2 dengan ekstensi `pdo_mysql` dan `fileinfo`, serta MySQL/MariaDB.
2. Letakkan proyek di `C:/xampp/htdocs/bkk`.
3. Salin `app/config/app.example.php` menjadi `app/config/app.local.php`. Atur `base_url` menjadi `/bkk`, lalu isi konfigurasi database lokal sesuai lingkungan. Jangan commit file lokal ini.
4. Jalankan Apache dan MySQL, lalu impor database sesuai petunjuk di bawah. Buka `http://localhost/bkk/`. Jika database belum tersedia, kerangka portal tetap tampil dengan pemberitahuan layanan dan HTTP 503; setelah impor/config benar, halaman normal memberi HTTP 200.
5. Pastikan stylesheet dimuat melalui `http://localhost/bkk/assets/css/style.css`. Bootstrap 5.3.3 disertakan lokal di `assets/vendor/bootstrap` beserta lisensi MIT; tidak ada proses build atau kebutuhan CDN saat website digunakan.

Untuk hosting shared, unggah isi proyek ke direktori tujuan, lalu atur `base_url` menjadi `/nama-subfolder`, string kosong untuk root domain, atau URL lengkap seperti `https://contoh.sch.id/bkk`. Gunakan HTTPS pada hosting. Zona waktu default adalah `Asia/Jakarta` dan dapat diubah di konfigurasi lokal.

## Konfigurasi server

Apache harus mengizinkan `.htaccess` (`AllowOverride All`) dan mengaktifkan `mod_rewrite` serta `mod_authz_core`. Direktori `app`, `database`, `storage`, dan `docs` menolak akses HTTP. Root memblokir berkas tersembunyi dan listing direktori; `uploads` menolak eksekusi skrip. Jangan gunakan server bawaan PHP untuk menilai proteksi Apache karena server tersebut tidak membaca `.htaccess`.

Pastikan proses PHP dapat menulis ke `storage` untuk membuat `sessions` dan `login-attempts`, serta ke `storage/logs` dan `uploads`. Session disimpan di direktori aplikasi yang terlindungi, bukan direktori session bersama hosting. Jangan gunakan izin `777`. Kesalahan dicatat ke `storage/logs/php-error.log`; detail kesalahan dan argumen fungsi tidak ditampilkan kepada pengunjung atau dicatat dalam trace.

## Database dan helper

### Impor untuk instalasi baru

1. Jalankan MySQL/MariaDB. Gunakan MySQL 8.0.16+ atau MariaDB 10.4+ agar constraint `CHECK` ditegakkan.
2. Melalui phpMyAdmin atau panel hosting, buat database **baru dan kosong**, misalnya `bkk_jaya_buana`, dengan collation `utf8mb4_unicode_ci`. Pada hosting, nama database dapat memiliki prefiks akun.
3. Pilih database tersebut, buka tab **Import**, lalu pilih `database/bkk_jaya_buana.sql` dengan encoding UTF-8. SQL tidak berisi `CREATE DATABASE`, `USE`, `DROP`, atau kredensial. Jangan impor ke database yang sudah berisi tabel aplikasi; ini bukan skrip migrasi atau impor berulang. DDL MySQL tidak dapat diandalkan untuk rollback seluruh impor, sehingga jika gagal, periksa penyebabnya sebelum mencoba ulang pada database kosong yang lain.
4. Sesuaikan nama database dan kredensial pada `app/config/app.local.php`. Berikan hak akses hanya untuk database aplikasi.
5. Verifikasi delapan tabel: `users`, `berita`, `lowongan`, `program`, `mitra`, `tracer_responses`, `settings`, dan `media`. Tabel `users` dan `tracer_responses` harus kosong setelah impor. Impor pembaruan 001 dan 002 secara berurutan, lalu buat admin pertama dengan skrip CLI di bawah.

Data awal berisi satu berita, satu lowongan, dan lima program berlabel **DATA DEMO**, seluruhnya berstatus `draft`, tanpa penulis, tautan pendaftaran, atau batas lamaran rekaan. Hapus atau ganti contoh sebelum publikasi. Kontak yang belum tersedia berupa NULL atau teks placeholder, bukan informasi resmi. Tidak ada data pribadi alumni contoh.

### Pembaruan database yang sudah terpasang

`database/bkk_jaya_buana.sql` khusus instalasi awal dan tidak boleh diimpor ulang untuk memperbarui aplikasi. Setiap perubahan database berikutnya harus dibuat sebagai file SQL terpisah, berurutan, di `database/updates/`, misalnya `001_nama_perubahan.sql`. File pembaruan hanya memuat perubahan yang diperlukan, tanpa membuat ulang seluruh tabel atau menimpa data yang sudah ada. Jangan menggabungkan pembaruan kembali ke SQL instalasi awal.

Untuk database yang sudah terpasang, cadangkan terlebih dahulu, pilih database yang benar di phpMyAdmin, lalu impor hanya file pembaruan yang belum diterapkan sesuai urutan nomor. Petunjuk setiap pembaruan harus menyebutkan prasyarat, cara verifikasi, dan apakah aman diimpor ulang. Catat file yang sudah diterapkan; jangan mengimpor ulang tanpa petunjuk eksplisit. Instalasi baru menggunakan SQL awal, dilanjutkan file pembaruan yang tersedia secara berurutan.

Tahap 3–7 tidak mengubah skema/seed. Mulai tahap 8 tersedia `database/updates/001_tracer_submission_key.sql`: impor **satu kali** pada database BKK yang sudah memiliki tabel `tracer_responses`, sebelum menerima pengiriman tracer. File menambah kolom `submission_key` dan indeks unik tanpa menghapus respons lama. Jangan mengimpor ulang SQL awal pada database yang sudah terpasang. Untuk instalasi baru, impor SQL awal kemudian pembaruan 001 dan 002. Cadangkan database sebelum pembaruan. Jika 001 sudah diterapkan, jangan jalankan lagi; file ini tidak dirancang untuk impor berulang. Verifikasi dengan `SHOW COLUMNS FROM tracer_responses LIKE 'submission_key';` dan `SHOW INDEX FROM tracer_responses WHERE Key_name='uq_tracer_submission_key';`. Pesan `#1050 Table 'users' already exists` dari SQL awal berarti tabel tersebut sudah ada, bukan alasan menghapusnya.

Slug unik berlaku per tabel konten. Referensi penulis/pengunggah dapat NULL dan memakai `ON DELETE SET NULL`; menonaktifkan pengguna menjadi pilihan utama. Gambar/logo menyimpan path relatif; tabel `media` menjadi katalog metadata unggahan yang dapat digunakan kembali. Tracer menyimpan persetujuan, waktu, dan versi teks persetujuan; akses data pribadi harus dibatasi pengelola pada tahap fitur tracer. Constraint database tidak menggantikan validasi server.

Koneksi PDO baru dibuat saat `db()` dipanggil. PDO memakai `utf8mb4`, exception, dan prepared statements native; setiap query berparameter wajib memakai `prepare()` dan `execute()`. Portal publik sekarang membaca database melalui `app/helpers/public.php`; helper `setting()` memuat pengaturan sekali per permintaan dengan nilai fallback bila belum diisi. Kegagalan pembacaan dicatat dengan kode SQLSTATE tanpa detail kredensial, dan ditampilkan sebagai pemberitahuan layanan.

Muat `app/bootstrap.php` sekali dari setiap entry point PHP. Helper tersedia: `app_config()`, `base_url()`, `asset_url()`, `redirect()` untuk path internal, dan `e()` untuk escape teks/atribut HTML. Gunakan helper URL untuk mendukung instalasi di subfolder.

Folder fitur yang belum berisi implementasi dipertahankan dengan `.gitkeep`. Tidak ada akun pengelola atau kredensial bawaan.

## Admin pertama dan login

Setelah impor dan konfigurasi lokal, jalankan dari terminal interaktif pada direktori proyek:

```powershell
C:\xampp\php\php.exe app/create-admin.php
```

Pada Linux/hosting dengan akses SSH, jalankan `php app/create-admin.php`. Jika hosting tidak menyediakan CLI, gunakan fasilitas terminal dari penyedia hosting; skrip tidak menyediakan jalur pembuatan admin melalui browser. Isi nama, username unik, email, password sepanjang 12–72 byte, dan konfirmasi. Password dibaca tanpa echo melalui PowerShell `Read-Host -AsSecureString` pada Windows atau `stty` pada terminal Unix. Windows membutuhkan PowerShell dan `proc_open`; Unix membutuhkan `exec` dan `stty`. Jangan memasukkan password sebagai argumen perintah, dalam source code, atau berkas konfigurasi. Skrip menyimpan hasil `password_hash()` dan hanya bekerja jika belum ada admin; database lock mencegah dua proses membuat admin pertama bersamaan.

Buka `http://localhost/bkk/auth/login.php`. Admin dan `staf_bkk` memakai formulir yang sama. Admin dapat membuat akun melalui menu Pengguna. Dashboard ada di `panel/index.php`; menu hanya memuat halaman yang tersedia. Menu Pengguna dan Pengaturan hanya tersedia untuk Admin.

Login dibatasi maksimal 5 percobaan per kombinasi username atau email/IP dan 30 percobaan per IP dalam jendela 15 menit, termasuk percobaan berhasil. Batas disimpan di `storage/login-attempts` dan tidak hilang ketika cookie dihapus. Respons batas adalah HTTP 429 dengan `Retry-After`. Aplikasi memakai `REMOTE_ADDR`, bukan header proxy dari pengunjung; di balik reverse proxy, konfigurasikan alamat klien dan HTTPS pada server tepercaya agar batas tidak mengelompokkan seluruh pengunjung. Cookie session memakai HttpOnly, SameSite=Lax, path sesuai base URL, serta Secure ketika Apache/PHP mendeteksi HTTPS. Jangan menghapus state pembatas untuk melewati batas. Berkas pembatas yang sudah kedaluwarsa dapat dibersihkan melalui pemeliharaan server berkala.

Logout hanya melalui tombol **Keluar** (POST dengan CSRF). ID session dan token CSRF diganti setelah login; session serta cookie dihapus saat logout. Gunakan `require_auth()` untuk endpoint yang membutuhkan login atau `require_role('admin')` untuk **setiap halaman dan handler** pengguna/pengaturan. Keduanya membaca ulang status aktif dan role dari database; menu bukan mekanisme otorisasi. Untuk perubahan data, panggil juga `require_post_csrf()` sebelum melakukan perubahan. `require_role('admin', 'staf_bkk')` mengizinkan kedua role.

Validasi tahap 3 memakai salinan aplikasi dan MariaDB sementara: login kedua role, pesan gagal generik, penonaktifan akun saat session aktif, regenerasi ID, cookie, CSRF, logout POST, redirect tanpa login, penolakan Staf pada halaman/handler Admin, batas lintas cookie, serta penolakan skrip CLI dari browser dan ketika admin sudah tersedia. Pembacaan password melalui terminal nyata dan HTTPS langsung tetap perlu diperiksa pada lingkungan pemasangan.

## Portal publik (Tahap 4)

Halaman tersedia: beranda, profil/kontak, daftar dan detail berita (`detail-berita.php?slug=...`), pencarian judul/ringkasan berita, daftar/detail lowongan, informasi/formulir/konfirmasi tracer study, serta daftar/detail lima kategori program. Semua menu memiliki tujuan yang tersedia. Daftar berita, lowongan, dan program memakai pagination 9 item.

Berita hanya tampil jika `published` dan waktu publikasinya sudah tiba (NULL memakai waktu pembuatan). Draft, berita terjadwal, dan slug tidak dikenal menghasilkan 404 pada detail; gangguan database menghasilkan 503 pada detail. Lowongan/program hanya memakai status `published`; mitra hanya `aktif = 1`. Batas lamaran kosong diberi keterangan belum ditentukan, sedangkan yang lewat diberi label sudah berakhir. Data pribadi tracer tidak pernah dibaca oleh halaman publik.

Seed SQL tetap draft sehingga instalasi baru menampilkan empty state. Jangan menerbitkan contoh sebagai informasi resmi. Konten yang mengandung penanda DATA DEMO diberi badge yang terlihat. Semua isi berita ditampilkan sebagai teks dengan baris baru dan escape HTML; HTML dari database tidak dieksekusi. Tidak ada editor HTML pada tahap ini.

Identitas/kontak dibaca dari tabel `settings`. `bkk_logo` dapat diisi path gambar lokal; profil resmi dan pengumuman opsional memakai key `profile_description` dan `public_announcement`, dengan empty state bila belum ada. Jangan mengisi kontak atau pengumuman rekaan. Pengelolaan settings melalui panel belum dibuat.

Gambar yang belum tersedia memakai `assets/images/placeholder.svg`, ilustrasi geometris lokal yang jelas berlabel placeholder. Monogram teks BKK adalah identitas sementara, bukan logo resmi sekolah. Gambar konten/logo hanya diambil dari path relatif raster dalam `uploads/` atau `assets/images/`; URL gambar eksternal, path tidak aman, dan berkas yang hilang memakai placeholder. Tidak ada foto pihak ketiga. Semua URL halaman/aset menggunakan base URL agar bekerja di subfolder.

Untuk pemeriksaan manual, buka semua menu, detail berita published, slug draft/tidak dikenal, pencarian, dan halaman kosong; pada lebar 320/390/768/1440 piksel periksa menu, dropdown program, pemenggalan judul panjang, batas lamaran, dan gambar. Salinan proyek/database sementara dipakai untuk pengujian konten tanpa mengubah database pengguna.

## Berita dan media (Tahap 5)

Admin dan Staf BKK dapat membuka **Berita & Kegiatan** serta **Katalog Media** melalui sidebar panel. Daftar berita menyediakan pencarian judul/slug, filter draft/published, dan pagination 10 item. Tambah/edit menyediakan judul, slug opsional, ringkasan (maksimal 1.000 karakter), isi (maksimal 100.000 karakter), status, unggulan, serta gambar utama. Slug dibuat dari judul bila kosong; slug dinormalisasi menjadi ASCII dan diberi akhiran angka jika sudah dipakai. Penulis adalah pembuat awal berita dan dipertahankan saat diedit pengelola lain.

Berita baru default draft. Saat pertama dipublikasikan atau dipublikasikan kembali dari draft, waktu publikasi memakai jam database agar sesuai filter publik `NOW()`. Mengedit berita yang sudah published mempertahankan waktu publikasinya. Tombol **Lihat publik** hanya tersedia untuk berita yang sudah dapat dibuka pengunjung. Mengubah slug juga mengubah URL publik. Isi disimpan sebagai teks biasa; paragraf dipisahkan baris kosong dan di-escape ketika ditampilkan. Tag HTML ditampilkan sebagai teks, tidak dieksekusi.

Gambar dapat diunggah langsung dari formulir berita atau dipilih dari katalog (200 gambar terbaru). Unggahan baru mengalahkan pilihan katalog. **Lepaskan gambar** mengosongkan gambar berita jika tidak ada pilihan/unggahan pengganti. Mengganti gambar atau menghapus berita tidak menghapus berkas dari katalog. Hapus berita dan hapus media hanya menerima POST dengan CSRF; konfirmasi JavaScript membantu mencegah salah klik, sedangkan validasi server tetap wajib.

Katalog media menyediakan pencarian nama, pagination 12 item, thumbnail gambar/penanda PDF, ukuran, dimensi, unduhan sebagai attachment, unggah, dan hapus. Gambar JPG/JPEG, PNG, WebP dibatasi 5 MB; PDF 10 MB. Validasi memakai ukuran berkas aktual, ekstensi, `fileinfo`, dan pemeriksaan dimensi gambar (maksimal 10.000 piksel per sisi / 40 juta piksel). SVG, executable, ekstensi ganda berbahaya, MIME palsu, gambar rusak, dan nama/path tidak aman ditolak. Nama simpan acak 32 karakter heksadesimal; metadata nama asli tetap ditampilkan dengan escape. Tidak memerlukan GD pada aplikasi; GD hanya digunakan untuk membuat fixture pengujian.

Pastikan PHP mengaktifkan `file_uploads`, ekstensi `fileinfo`, dan temporary directory PHP dapat ditulis. Agar batas aplikasi dapat digunakan penuh, atur `upload_max_filesize` minimal `10M` dan `post_max_size` lebih besar, misalnya `16M`, di php.ini/panel hosting lalu restart Apache jika diperlukan. Batas server yang lebih kecil tetap berlaku. Jika POST melebihi `post_max_size`, PHP dapat mengosongkan input dan aplikasi menolak pemeriksaan CSRF; unggah ulang berkas lebih kecil atau sesuaikan batas server. Proses PHP perlu hak tulis pada `uploads` untuk subfolder gambar/dokumen dan pada `storage` untuk direktori sementara penghapusan. Jangan memakai izin 777. Tidak ada konfigurasi server global yang diubah otomatis oleh aplikasi.

Helper bersama ada di `app/helpers/upload.php`, `validation.php`, `panel.php`, dan `news.php`. Penulisan konten/media saat ini memakai named lock database dan transaksi, sehingga pemilihan gambar serta pemeriksaan penghapusan tidak saling mendahului. Modul berikutnya yang mengubah referensi media harus memakai `with_content_lock()` juga. Penghapusan memeriksa pemakaian oleh berita/lowongan/program (termasuk draft), mitra, isi teks yang memuat path, dan settings. Jika masih dipakai, metadata dan berkas dipertahankan. Berkas tanpa referensi dipindah dahulu ke `storage/media-trash` yang terlindungi; jika transaksi gagal, berkas dipulihkan, sedangkan setelah commit berkas dihapus permanen. Bila pembersihan gagal, periksa log dan folder tersebut. Jangan membersihkan folder sementara secara sembarangan saat ada operasi berjalan.

Pengujian integrasi dilakukan pada salinan aplikasi dan MariaDB sementara dengan akun uji yang password-nya dibuat acak saat pengujian. Cakupan: kedua role, tambah/edit/draft/publikasi/hapus, slug bertabrakan, unggulan, paragraf/escape XSS, pencarian/pagination, POST/CSRF dan akses tanpa login, seluruh format unggahan yang diizinkan, penolakan unggahan tidak valid, unduhan, file bersama lintas konten/settings, penghapusan fisik, dan rollback berkas/metadata saat simpan berita gagal. Publikasi juga diuji dengan zona waktu PHP berbeda dari database. Database pengguna dan SQL instalasi awal tidak diubah. Tidak ada file SQL pembaruan yang perlu diimpor untuk tahap ini.

## Lowongan kerja (Tahap 6)

Admin dan Staf BKK mengakses **Lowongan Kerja** di sidebar panel. Modul menyediakan daftar dengan pencarian posisi/perusahaan, filter status, pagination 10 item, tambah/edit, serta hapus melalui POST + CSRF. Field perusahaan, posisi, lokasi, jenis pekerjaan, pendidikan minimal, persyaratan, deskripsi, batas lamaran, dan URL pendaftaran wajib diisi. Status yang dapat disimpan adalah draft/published. Poster opsional memakai layanan unggahan gambar 5 MB dan katalog media yang sama; penggantian/penghapusan lowongan tetap mempertahankan berkas di katalog. Penulis awal dan slug dipertahankan saat edit; slug otomatis dibuat unik ketika menambah lowongan.

Pengunjung dapat membuka `lowongan-kerja/` tanpa login, mencari posisi/perusahaan, memfilter lokasi/jenis pekerjaan, dan berpindah halaman (9 item per halaman). Pilihan filter hanya berasal dari lowongan published. Detail memakai `lowongan-kerja/detail.php?slug=...`, menampilkan teks/paragraf ter-escape, pendidikan, poster, batas lamaran, dan maksimal tiga lowongan terkait berdasarkan lokasi/jenis pekerjaan/perusahaan. Draft, status closed lama, dan slug tidak dikenal tidak tampil di publik dan detailnya 404. Gangguan database pada detail menghasilkan 503.

Tanggal lamaran dihitung server dengan **Asia/Jakarta**, terlepas dari zona waktu konfigurasi PHP/database. Lowongan published bertanggal valid masih dibuka sampai 23:59:59 WIB tanggal tersebut dan ditutup tepat pukul 00:00 WIB hari berikutnya. Tidak perlu cron atau perubahan status database saat kedaluwarsa: lowongan published yang sudah lewat tetap dapat dibaca dengan label **Sudah ditutup** dan tanpa tombol daftar. Data lama tanpa tanggal diberi label **Batas belum ditentukan** dan tidak menyediakan tombol daftar.

URL pendaftaran hanya menerima http/https yang valid tanpa username/password atau karakter kontrol. Pemeriksaan dilakukan saat menyimpan dan saat merender tombol, sehingga URL lama yang tidak valid juga tidak ditampilkan. Tombol daftar hanya tersedia saat lowongan masih dibuka dan URL valid; tautan langsung menuju situs perusahaan di tab baru dengan `noopener noreferrer`, tanpa formulir pendaftaran internal. Pengelola tetap perlu memverifikasi bahwa tautan tersebut merupakan tujuan resmi perusahaan. HTML dalam persyaratan/deskripsi ditampilkan sebagai teks.

Helper `jobs.php` memuat aturan batas waktu/URL yang dipakai bersama panel, daftar publik, detail, dan kartu beranda; `vacancies.php` mengelola validasi/transaksi. Lock referensi media bersama kini berada di `panel.php` dan digunakan berita, lowongan, dan media. Tidak ada SQL pembaruan yang perlu diimpor untuk tahap 6; SQL awal tidak diubah.

Uji batas tanggal dan URL dapat dijalankan tanpa database:

```powershell
C:\xampp\php\php.exe app/tests/jobs.php
```

Pengujian integrasi pada salinan aplikasi/MariaDB sementara mencakup kedua role, CRUD, draft/aktif/kedaluwarsa/batas hari ini, terkait, pencarian/filter/pagination, tanggal dan URL tidak valid, escape HTML, unggahan poster/reuse/perlindungan media, POST/CSRF/akses tanpa login. Pengujian dijalankan saat zona waktu PHP sengaja berbeda dari Asia/Jakarta; regresi berita dan media juga lulus. Database pengguna tidak diubah.

## Program BKK (Tahap 7)

Menu **Program BKK** pada panel tersedia untuk Admin dan Staf BKK. Satu CRUD, satu formulir, dan tabel `program` menangani lima kategori: `kelas_industri`, `ppks`, `kelas_melanjutkan`, `entrepreneur`, dan `kelas_internasional`. Kategori wajib berasal dari allowlist yang sama dengan navigasi publik. Daftar panel menyediakan pencarian judul/slug, filter kategori/status, serta pagination 10 item. Tambah/edit memuat kategori, judul, slug, ringkasan, isi teks biasa, gambar, tautan pendaftaran opsional, dan draft/published. Ringkasan maksimal 1.000 karakter dan isi maksimal 100.000 karakter. Teks dalam negeri/luar negeri, tujuan kuliah, dan beasiswa dapat ditulis langsung pada isi Kelas Melanjutkan; tidak ada data sekolah atau beasiswa rekaan yang ditambahkan.

Slug dinormalisasi dan unik pada seluruh tabel program, dengan akhiran angka bila diperlukan. Slug kosong dibuat dari judul. Mengubah slug atau kategori mengubah URL publik. Penulis awal dipertahankan ketika pengelola lain mengedit. Gambar baru memakai layanan unggahan JPG/PNG/WebP 5 MB atau gambar katalog bersama; melepas/mengganti gambar dan menghapus program tetap mempertahankan berkas di media. Penghapusan program hanya menerima POST+CSRF dan semua penulisan referensi gambar memakai lock/transaksi bersama.

Kelima direktori publik memakai `app/layouts/program-list.php` dan `program-detail.php`, dengan entry point yang menetapkan kategori di server. Daftar hanya memuat published dari kategori tersebut (9 item per halaman). Detail diakses lewat `detail.php?slug=...` dan memeriksa **slug + kategori + status published** sekaligus. Mengganti query `kategori` tidak dapat mengubah kategori halaman. Slug milik kategori lain, draft, dan program yang dihapus menghasilkan 404; gangguan database pada detail menghasilkan 503. Judul, ringkasan, isi, serta nama penulis di-escape; paragraf teks biasa dirender dengan helper yang sama seperti berita.

Tautan pendaftaran boleh kosong (disimpan sebagai NULL). Jika diisi, hanya URL http/https valid tanpa username/password atau karakter kontrol yang diterima. URL diperiksa kembali saat ditampilkan, termasuk untuk data lama. Tombol hanya muncul bila URL valid, menuju situs luar di tab baru dengan `noopener noreferrer`. Pengelola perlu memastikan tujuan pendaftaran dan isi program telah diverifikasi.

Pengujian pada salinan aplikasi/MariaDB sementara mencakup CRUD kedua role, lima kategori, seluruh 20 kombinasi akses detail lintas kategori (404), draft/unpublish, pindah kategori, slug unik, tautan opsional/tidak valid, paragraf/XSS, gambar bersama, rollback upload saat INSERT gagal, POST/CSRF/akses tanpa login, serta pencarian/filter/pagination. Komponen daftar/detail publik dan panel diperiksa di Chrome pada lebar 320/390/768/1440 piksel tanpa overflow halaman. Tidak ada perubahan SQL atau impor pembaruan untuk tahap ini; database pengguna tidak diubah.

## Tracer study (Tahap 8)

**Impor pembaruan 001 terlebih dahulu**, sesuai petunjuk di atas. SQL instalasi awal tetap utuh; tidak ada data pribadi alumni contoh dalam berkas SQL. Migrasi mengizinkan nilai NULL untuk respons lama, dan tidak melakukan deduplikasi atau perubahan data lama. Aplikasi tidak menjalankan ALTER TABLE secara otomatis.

Alumni membuka `tracer-study/`, kemudian `formulir.php` tanpa login. Identitas meliputi nama lengkap, NISN opsional (angka hingga 20 digit, nol di depan dipertahankan), jurusan yang diisi sesuai keadaan saat sekolah, tahun lulus antara 1900 dan tahun berjalan Asia/Jakarta, WhatsApp wajib (8–15 digit; boleh diawali +), serta email opsional. Status yang diizinkan adalah bekerja, kuliah, wirausaha, atau mencari kerja. Jurusan tidak memakai daftar rekaan karena daftar resmi belum tersedia.

JavaScript menampilkan field sesuai status: perusahaan/posisi wajib dan lokasi opsional untuk bekerja, kampus/program studi wajib untuk kuliah, nama/bidang usaha wajib untuk wirausaha. Mencari kerja dapat menggunakan keterangan opsional. Tanpa JavaScript semua kelompok terlihat dengan petunjuk mengisi yang relevan; validasi server tetap memeriksa detail yang diwajibkan dan membuang nilai dari status lain. Keterangan maksimal 5.000 karakter. Seluruh nilai ditampilkan dengan escape HTML.

Formulir menjelaskan tujuan pendataan, pemakaian kontak untuk tindak lanjut, kerahasiaan respons, dan cara meminta koreksi melalui kontak BKK pada profil. Persetujuan wajib diberikan secara eksplisit, tidak dicentang pada formulir baru. Server menyimpan waktu persetujuan serta versi `tracer-v1`; teks versinya ada di `app/helpers/tracer.php`. Jika tujuan/teks persetujuan diubah pada tahap berikutnya, buat versi baru dan pertahankan riwayat teks lama agar persetujuan terdahulu tetap dapat ditelusuri. Kontak yang belum diisi tetap berupa placeholder; lengkapi `contact_email`/`contact_phone` di settings dengan data terverifikasi.

Pengiriman memakai POST+CSRF, honeypot `company_website`, dan batas 5 pengiriman valid per session serta 30 per IP dalam 15 menit. State pembatas memakai helper berkas yang sama dengan login, dengan key `tracer-*` terpisah sehingga tidak menghabiskan jatah login. Tidak menyimpan IP/alamat kontak mentah dalam berkas pembatas. Batas menghasilkan 429 dan `Retry-After`; biarkan formulir tetap terbuka lalu kirim ulang setelah batas berakhir. Pengiriman yang tidak lolos validasi tidak disimpan dan dapat diperbaiki. Pembatas memakai REMOTE_ADDR; konfigurasi proxy tepercaya mengikuti petunjuk login.

Setiap formulir memperoleh token acak yang disimpan di session (maksimal 5 formulir aktif selama 2 jam). Hash token disimpan pada `submission_key` unik. Mengirim ulang token dan isi yang sama mengarah ke konfirmasi bahwa respons sudah tersimpan tanpa menambah baris. Token yang sudah digunakan dengan isi berbeda menghasilkan penjelasan untuk membuka formulir baru atau meminta koreksi. Formulir baru dengan data serupa tetap disimpan; bila sama dengan pengiriman terakhir pada session itu, halaman berhasil menjelaskan bahwa respons tambahan telah diterima. Tidak ada penolakan berdasarkan nama/WhatsApp/NISN yang mirip. Rekap menghitung **respons**, bukan alumni unik. Jika penyimpanan gagal, kirim ulang formulir yang sama; unique key menghindari duplikasi akibat percobaan ulang setelah hasil koneksi tidak pasti.

Halaman `berhasil.php` memerlukan penanda sukses pada session yang berlaku 30 menit, tanpa ID atau data respons dalam URL/isi halaman. Respons tidak dapat dibaca lewat halaman publik, termasuk dengan menebak ID. Input yang gagal validasi hanya dikembalikan ke session/formulir pengirim itu sendiri dan dibersihkan setelah sukses. Tidak ada email/pesan otomatis yang dikirim oleh aplikasi.

Admin dan Staf mengakses **Tracer Study** di sidebar. Tersedia daftar berpagination 15 item, filter jurusan/tahun/status, detail respons, rekap jumlah per status, dan ekspor CSV seluruh hasil filter. Daftar, rekap, dan ekspor memakai satu pembangun filter; parameter tidak valid menghasilkan 400. Seluruh endpoint, termasuk CSV, memeriksa login, status aktif, dan role di server. Status `lainnya` yang mungkin ada pada data lama tetap dapat dilihat dan dihitung pada rekap, tetapi tidak ditawarkan pada pengiriman baru.

Ekspor memakai UTF-8 BOM dan `fputcsv`, diunduh sebagai attachment dengan no-store. Kolom token internal tidak diekspor. Sel yang mengandung awalan formula (`=`, `+`, `-`, `@`, termasuk setelah karakter spasi/kontrol/format Unicode) atau diawali tab/CR/LF diberi apostrof untuk mengurangi CSV formula injection. Karena itu nomor WhatsApp berawalan + dapat memiliki apostrof pada berkas CSV. Ekspor dialirkan tanpa memuat semua baris ke array. Gunakan data alumni hanya untuk tujuan yang sudah disetujui.

Uji validasi/status/CSV tanpa database: `C:\xampp\php\php.exe app/tests/tracer.php`. Pengujian HTTP/MariaDB terpisah memverifikasi migrasi mempertahankan baris lama, empat status dan pengosongan detail lain, validasi tahun/kontak/persetujuan, CSRF/honeypot/limit, kirim ulang identik/berbeda/formulir baru, tidak ada PII pada halaman publik, akses panel kedua role, filter/rekap/pagination, CSV sesuai filter, serta formula injection. Pengujian memakai respons sintetis pada database sementara; database pengguna tidak diubah.

## Mitra, pengguna, pengaturan, dan akun (Tahap 9)

**Impor satu kali `database/updates/002_users_username_session.sql` sebelum memakai versi ini**, pada database yang sudah memiliki tabel `users`. Urutan instalasi baru: SQL awal ? 001 ? 002 ? skrip admin pertama. Untuk instalasi lama, impor hanya pembaruan yang belum diterapkan; jangan impor ulang SQL awal atau menghapus tabel. Cadangkan database dan lakukan pembaruan saat panel tidak digunakan. File 002 menambah `username` unik dan `session_version`, tanpa mengubah password atau role. Akun lama memperoleh username `akun-<id>` dan tetap bisa login menggunakan email; Admin atau pemilik akun dapat menggantinya. Semua sesi sebelum pembaruan perlu login ulang. Verifikasi dengan `SHOW COLUMNS FROM users LIKE 'username';`, `SHOW COLUMNS FROM users LIKE 'session_version';`, dan `SHOW INDEX FROM users WHERE Key_name='uq_users_username';`. Pembaruan ini tidak aman diimpor ulang dan tidak dijalankan otomatis oleh aplikasi.

Admin dan Staf dapat mengelola perusahaan/perguruan tinggi lewat **Mitra**. Logo boleh diunggah (JPG/PNG/WebP maksimal 5 MB) atau dipilih dari katalog. Website opsional hanya menerima http/https. Beranda menampilkan hingga 12 mitra aktif, dengan logo, nama, jenis, deskripsi, dan tautan website yang valid; nonaktif tidak tampil. Hapus memakai POST+CSRF. Penggantian/penghapusan mitra tidak menghapus berkas logo bersama.

**Pengguna** hanya untuk Admin, pada halaman maupun handler. Tambah akun memerlukan nama, username, email unik, role, password dan konfirmasi. Username 3?50 huruf kecil/angka/titik/garis, diawali huruf atau angka; karakter @ dicadangkan untuk login email. Password 12?72 byte dan disimpan dengan `password_hash()`. Edit identitas/role, aktivasi/nonaktivasi, dan reset password tersedia. Tidak ada penghapusan akun. Perubahan akun diserialisasi dengan database lock dan transaksi; kewenangan pelaku diperiksa ulang di dalam transaksi. Admin aktif terakhir tidak dapat dinonaktifkan atau diturunkan role-nya, termasuk pada perubahan bersamaan. Jangan mengubah role/status langsung di database karena akan melewati aturan aplikasi.

Status aktif, role, dan versi sesi dibaca ulang pada setiap akses panel. Penonaktifan dan reset password membatalkan seluruh sesi akun; aktivasi ulang tidak memulihkan sesi lama. Penurunan role langsung membatasi halaman/handler admin pada permintaan berikutnya. Reset password sendiri lewat menu Pengguna mengharuskan login kembali. Password tidak disimpan dalam flash/session atau dicetak ke log.

**Pengaturan** khusus Admin menyimpan nama BKK/sekolah, logo BKK/sekolah, alamat, nomor kontak, email, URL Instagram/Facebook/YouTube, dan profil teks biasa. Hanya key yang diizinkan server yang ditulis. Kolom kontak/media sosial boleh kosong dan tampil sebagai placeholder; jangan memasukkan kontak resmi yang belum diverifikasi. Identitas/logo diterapkan pada header, kontak dan media sosial pada footer, profil pada halaman Profil BKK. Logo memakai layanan media bersama; berkas yang masih dirujuk pengaturan atau mitra tidak dapat dihapus melalui katalog.

**Akun Pribadi** tersedia untuk kedua role: ubah nama, username, email, serta password dengan verifikasi password lama. Endpoint hanya mengubah akun dari sesi, tidak menerima perubahan role/status atau target pengguna lain. Perubahan password pribadi mempertahankan sesi perangkat saat ini dengan regenerasi ID dan membatalkan sesi perangkat lain. Percobaan ubah password dibatasi 10 per akun per 15 menit.

Pengujian memakai salinan aplikasi dan MariaDB sementara, bukan database pengguna: migrasi/akun lama, login username/email, CRUD mitra/pengguna, username/email unik, perlindungan admin terakhir, penonaktifan dan pencabutan sesi, perubahan password pribadi/reset, validasi unggahan/URL, penggunaan ulang logo dan perlindungan media, escape konten publik, POST/CSRF/logout, serta akses langsung Staf ke seluruh halaman dan handler khusus Admin.

## Integrasi dan pemeriksaan (Tahap 10)

Pencarian pada header dan `pencarian.php` mencakup judul/ringkasan/isi berita dan program, serta posisi/perusahaan/lokasi/deskripsi lowongan. Filter jenis informasi dan kategori program tersedia; memilih kategori membatasi hasil ke program. Semua sumber hanya mengambil `published`; berita bertanggal publikasi di masa depan tetap tersembunyi. Hasil gabungan diurutkan tanggal, jenis, dan ID, sembilan per halaman. Semua nilai pencarian/filter dibind melalui prepared statements, termasuk limit/offset; karakter `%` dan `_` dicari secara literal. Parameter q/type/kategori dipertahankan pada pagination. Input pencarian tidak valid menghasilkan 400 dengan penjelasan; nomor halaman tidak valid kembali ke halaman pertama atau dibatasi ke halaman terakhir.

Dashboard Admin/Staf menampilkan jumlah konten per status, lowongan yang masih dibuka menurut tanggal Asia/Jakarta, mitra aktif, katalog media, dan respons tracer per status. Admin juga melihat jumlah pengguna aktif. Hitungan published menyatakan status tersimpan, termasuk berita yang dijadwalkan; berita terjadwal tetap tidak muncul publik sebelum waktunya. Jumlah tracer adalah respons, bukan alumni unik. Tidak ada query ringkasan tracer pada halaman publik.

Metadata deskripsi dipendekkan secara aman hingga 160 karakter UTF-8 dan di-escape. Judul/deskripsi menggunakan konten detail, kategori, atau konteks pencarian/filter. Gambar lokal hilang memakai placeholder lokal; gambar dekoratif pada kartu memiliki alt kosong, gambar detail/logo memiliki alt. Fokus keyboard tersedia pada tautan, tombol, input, select, dan textarea. Navigasi aktif memakai aria-current.

`404.php` dipakai untuk detail yang tidak tersedia dan path yang hilang melalui aturan Apache `.htaccess` (mod_rewrite, AllowOverride All). Aturan relatif mendukung subfolder dan root; proteksi direktori internal tetap didahulukan. Server bawaan PHP tidak memproses aturan ini. Kesalahan aplikasi menghasilkan pesan generik 500, kegagalan pembacaan database publik menghasilkan pemberitahuan 503. Log `storage/logs/php-error.log` hanya mencatat jenis/kode/lokasi exception atau kode SQLSTATE, tanpa pesan exception driver, SQL, atau kredensial. Periksa log dan konfigurasi lokal bila layanan gagal; jangan membagikan berkas konfigurasi atau membuka akses HTTP ke logs.

Pemeriksaan pemasangan: terbitkan konten uji berlabel DATA DEMO pada tiga modul dan simpan satu draft, lalu cari kata uniknya dan pastikan draft tidak terlihat. Uji filter/pagination, detail draft (404), URL yang tidak ada (404), serta login kedua role untuk membandingkan dashboard dengan daftar data. Buka halaman utama pada ponsel dan gunakan Tab untuk menavigasi menu/formulir. Jangan memakai data pribadi alumni untuk pengujian publik.

Validasi pengembangan dilakukan pada salinan aplikasi dan database sementara: hasil gabungan/filter/pagination, wildcard/injeksi SQL, input array/panjang, draft/berita terjadwal, ringkasan nyata kedua role, privasi tracer, metadata, gambar hilang, tautan utama dan alt, label formulir, serta error 500/503 tanpa kebocoran respons/log. Apache terpisah memverifikasi 404 root/subfolder dan proteksi app/database/storage/docs/berkas tersembunyi. Chrome memeriksa 27 halaman utama pada lebar 320/390/768/1440 tanpa horizontal overflow. Tidak ada perubahan database pengguna atau pemasangan library baru.

## Menjalankan instalasi lokal

Setelah proyek berada di `C:/xampp/htdocs/bkk`, impor SQL awal, `database/updates/001_tracer_submission_key.sql`, lalu `database/updates/002_users_username_session.sql` satu kali pada database baru. Salin konfigurasi contoh ke `app/config/app.local.php`, isi host/port/nama database/username/password lokal dan `base_url => '/bkk'`. File `.env` tidak dibaca oleh aplikasi; konfigurasi lokal PHP adalah sumber yang digunakan. Jangan menaruh kredensial di file contoh atau Git.

Dari terminal pada folder proyek, jalankan `C:\xampp\php\php.exe app/create-admin.php` dan isi nama, username, email, password serta konfirmasi secara interaktif. Jalankan Apache/MySQL melalui XAMPP, buka `http://localhost/bkk/`, kemudian `http://localhost/bkk/auth/login.php`. Jika memakai nama subfolder atau port Apache berbeda, sesuaikan URL dan base URL. Pastikan aset mengarah ke `/bkk/assets/`, bukan `/assets/`. SQL awal tidak boleh diimpor ulang untuk memperbarui database yang sudah berisi data.

## Persiapan shared hosting

1. Cocokkan versi/ekstensi dan dukungan Apache `.htaccess` dengan kebutuhan di atas. Pastikan penyedia menyediakan terminal/SSH untuk menjalankan CLI admin pertama. Aplikasi belum menyediakan pembuatan admin melalui browser.
2. Siapkan salinan proyek untuk document root/subfolder tujuan, termasuk `.htaccess` root, `app`, `database`, `docs`, `storage`, dan `uploads`. Jangan sertakan `.git`, log/session lokal, konfigurasi lokal XAMPP, atau artefak QA. Jika memindahkan data nyata, unggahan dipindahkan bersama backup database yang sesuai.
3. Buat database dan akun database khusus aplikasi melalui panel hosting. Untuk instalasi kosong, impor SQL awal → 001 → 002. Untuk pemulihan backup yang sudah mencakup migrasi, impor backup saja dan periksa migrasi yang sudah diterapkan; jangan menumpuk impor SQL awal atau pembaruan yang sama.
4. Buat `app/config/app.local.php` khusus hosting. Isi database hosting, `base_url` berupa `''` untuk root, `/bkk` untuk subfolder, atau URL HTTPS lengkap. Tetapkan zona waktu `Asia/Jakarta` untuk lingkungan sekolah. Pastikan hanya proses aplikasi yang dapat membaca konfigurasi.
5. Beri izin tulis yang diperlukan pada `storage`, `storage/logs`, dan `uploads`; contoh umum direktori 0755/berkas 0644 jika sesuai kepemilikan hosting. Session dibuat 0700. Jangan menggunakan 0777; sesuaikan kepemilikan dengan penyedia hosting.
6. Aktifkan HTTPS dan pastikan server meneruskan status HTTPS yang benar ke PHP. Cookie harus beratribut Secure, HttpOnly, dan SameSite=Lax. Jika memakai proxy, konfigurasi alamat/HTTPS dilakukan pada server tepercaya, bukan dengan mempercayai header pengunjung.
7. Jalankan CLI admin pertama untuk instalasi baru, lengkapi informasi resmi melalui Pengaturan, bersihkan demo, lalu lakukan pengujian manual di bawah. Periksa bahwa akses ke `app/config/app.local.php`, `database/`, `storage/`, `docs/`, berkas tersembunyi, dan skrip di `uploads` ditolak. Jika proteksi tidak aktif, perbaiki konfigurasi hosting sebelum website digunakan.

Panduan ini hanya persiapan; tidak ada deploy atau unggahan ke hosting yang dijalankan otomatis.

## Menghapus data demo

Setelah backup, login pengelola dan cari **DATA DEMO** pada Berita, Lowongan, dan Program. SQL awal menyediakan satu berita, satu lowongan, dan lima program draft. Periksa judul/slug/isi tiap entri, lalu hapus melalui tombol panel (POST+CSRF) atau ganti seluruh materi dengan informasi yang sudah diverifikasi sebelum menerbitkan. Jangan menjalankan penghapusan massal atau `TRUNCATE` pada database nyata. Periksa juga mitra/profil/pengaturan bila pernah diisi untuk pengujian. Tidak ada respons alumni contoh dalam SQL.

Setelah menghapus konten, logo/gambar bersama tetap berada di katalog. Hapus hanya media yang tidak dipakai; aplikasi menolak penghapusan referensi aktif. Buka beranda/pencarian tanpa login untuk memastikan demo tidak muncul dan empty state tetap rapi. Kontak resmi yang belum diberikan tetap dikosongkan.

## Backup dan pemulihan

1. Sebelum pembaruan dan secara berkala, batasi sementara perubahan konten/pengiriman tracer menggunakan fasilitas pemeliharaan server agar database dan unggahan konsisten.
2. Ekspor seluruh database beserta struktur dan data melalui phpMyAdmin: pilih database yang benar → Export → SQL. Alternatif CLI `mysqldump --single-transaction -u NAMA_AKUN -p NAMA_DATABASE > backup.sql`; masukkan password pada prompt, bukan argumen atau source. Nama di perintah ini adalah placeholder.
3. Salin seluruh `uploads/`, termasuk `.htaccess`, pada waktu backup yang sama. Simpan juga versi source aplikasi dan daftar migrasi yang diterapkan. Simpan konfigurasi lokal secara terpisah dan aman; jangan masukkan ke repository.
4. Simpan backup SQL, uploads, dan konfigurasi di luar document root dengan akses terbatas; database/CSV tracer berisi data pribadi. Gunakan jadwal dan masa simpan sesuai kebijakan sekolah, serta salinan terpisah dari server utama. CSV tracer bukan backup seluruh database.
5. Uji pemulihan pada database/folder terpisah: impor backup ke database kosong, pulihkan unggahan, sesuaikan konfigurasi/base URL, lalu periksa login, konten, gambar dan tracer. Jangan impor SQL awal atau migrasi yang sudah ada dalam backup. Direktori session/login-attempts/log lama tidak perlu dipulihkan sebagai sesi pengguna; gunakan sesi baru.
6. Verifikasi pemulihan sebelum membuka kembali akses/perubahan. Jangan menyimpan `backup.sql`, ZIP backup, atau dump kredensial pada folder publik.

## Pengujian manual akhir

1. Buka beranda dan semua menu tanpa login pada desktop dan ponsel. Uji pencarian/filter/pagination, gambar, tautan dan navigasi Tab. Pastikan URL aset tetap di subfolder dan path acak menghasilkan 404.
2. Login Admin dan Staf pada profil browser terpisah; logout lewat tombol Keluar. Coba membuka panel tanpa login, serta URL halaman/handler Pengguna dan Pengaturan sebagai Staf: harus ditolak. Nonaktifkan akun uji dan pastikan sesi lamanya kehilangan akses. Pertahankan setidaknya satu Admin aktif.
3. Pada setiap modul konten, tambah materi berlabel DATA DEMO, edit, simpan draft, terbitkan, dan hapus. Draft harus 404 di publik dan tidak muncul pencarian. Uji URL pendaftaran salah, gambar valid, file SVG/PHP/ekstensi palsu/terlalu besar, dan logo yang dipakai bersama. Teks seperti `<script>alert(1)</script>` harus tampil sebagai teks, bukan dijalankan. POST tanpa token CSRF harus ditolak.
4. Isi tracer dengan identitas sintetis untuk setiap status, lalu uji validasi/persetujuan/kirim ulang. Periksa detail/filter/rekap dan unduh CSV di panel kedua role; data dan rekap tidak boleh tampil publik. Uji sel berawalan `=`, `+`, `-`, `@` pada CSV memakai data sintetis dan pastikan dinetralisir. Hapus fixture pada database uji setelah selesai, bukan data alumni nyata.
5. Periksa folder internal dan unggahan melalui Apache; status ditolak berarti berkas tidak dikirim/dieksekusi. Periksa log setelah pengujian. Untuk hosting, verifikasi HTTPS/cookie Secure dan input password CLI langsung pada terminal pemasangan.

Uji CLI yang disertakan dapat diulang tanpa database: `C:\xampp\php\php.exe app/tests/jobs.php` dan `C:\xampp\php\php.exe app/tests/tracer.php`. Pemeriksaan sintaks seluruh PHP di PowerShell: `Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & C:\xampp\php\php.exe -l $_.FullName }`. Pengujian integrasi pengembangan memakai salinan dan layanan sementara; tidak dijalankan pada database pengguna.

## Status audit akhir — 21 Oktober 2026

| Pemeriksaan | Hasil |
| --- | --- |
| Sintaks 107 file PHP dan dua uji CLI dalam proyek | Lulus |
| CRUD berita, lowongan, lima kategori program, mitra, dan media | Lulus melalui HTTP/PDO pada MariaDB sementara |
| Login/logout kedua role, regenerasi sesi, cookie, throttle, CSRF, hak akses langsung | Lulus; cookie HttpOnly/SameSite/path diuji melalui HTTP lokal |
| Pengguna/pengaturan khusus Admin, admin aktif terakhir, reset/password pribadi, pencabutan sesi | Lulus, termasuk dua demosi admin bersamaan |
| Tracer empat status, validasi/anti-spam/kirim ulang, filter/rekap/ekspor dan CSV formula injection | Lulus dengan respons sintetis; tidak mengakses data alumni nyata |
| Draft/berita terjadwal, pencarian/filter/pagination, metadata, escaping, gambar/tautan | Lulus |
| Instalasi `/bkk`, 404 dan folder internal/unggahan melalui Apache terpisah | Lulus; PHP dan ekstensi ganda berbahaya pada uploads ditolak, JPG biasa dapat dibuka |
| Chrome 36 halaman pada 320/390/768/1440 piksel | Lulus tanpa overflow; menu mobile, fokus Tab, label/alt, dan field tracer dinamis diuji pada HTML hasil render data uji |
| Kredensial repository | Tidak ditemukan literal kredensial nonkosong pada file yang dipindai; konfigurasi lokal/.env diabaikan Git dan tidak ditemukan dalam riwayat path tersebut |

Perbaikan audit: handler exception dipasang sebelum membaca konfigurasi agar exception/syntax error di konfigurasi lokal juga menghasilkan pesan dan log yang aman. Kasus tersebut diuji dengan token rahasia sintetis; token tidak bocor ke halaman maupun log. README yang berulang sudah dirapikan. Tidak ada SQL baru, deploy, perubahan layanan XAMPP, atau perubahan database pengguna.

Batas verifikasi: pemeriksaan browser memakai salinan HTML hasil render dan JavaScript/aset aplikasi, sedangkan penyimpanan/otorisasi diuji melalui HTTP ke aplikasi hidup. HTTPS dengan sertifikat nyata, cookie Secure melalui TLS, dan pembacaan password CLI tanpa echo pada terminal interaktif pemasangan belum diuji langsung; periksa ketiganya saat menyiapkan lingkungan tujuan. Tidak ada pengujian pada shared hosting karena deploy tidak dilakukan.

## Status audit akhir — 21 Oktober 2026

| Pemeriksaan | Hasil |
| --- | --- |
| Sintaks 107 file PHP dan dua uji CLI dalam proyek | Lulus |
| CRUD berita, lowongan, lima kategori program, mitra, dan media | Lulus melalui HTTP/PDO pada MariaDB sementara |
| Login/logout kedua role, regenerasi sesi, cookie, throttle, CSRF, hak akses langsung | Lulus; cookie HttpOnly/SameSite/path diuji melalui HTTP lokal |
| Pengguna/pengaturan khusus Admin, admin aktif terakhir, reset/password pribadi, pencabutan sesi | Lulus, termasuk dua demosi admin bersamaan |
| Tracer empat status, validasi/anti-spam/kirim ulang, filter/rekap/ekspor dan CSV formula injection | Lulus dengan respons sintetis; tidak mengakses data alumni nyata |
| Draft/berita terjadwal, pencarian/filter/pagination, metadata, escaping, gambar/tautan | Lulus |
| Instalasi `/bkk`, 404 dan folder internal/unggahan melalui Apache terpisah | Lulus; PHP dan ekstensi ganda berbahaya pada uploads ditolak, JPG biasa dapat dibuka |
| Chrome 36 halaman pada 320/390/768/1440 piksel | Lulus tanpa overflow; menu mobile, fokus Tab, label/alt, dan field tracer dinamis diuji pada HTML hasil render data uji |
| Kredensial repository | Tidak ditemukan literal kredensial nonkosong pada file yang dipindai; konfigurasi lokal/.env diabaikan Git dan tidak ditemukan dalam riwayat path tersebut |

Perbaikan audit: handler exception dipasang sebelum membaca konfigurasi agar exception/syntax error di konfigurasi lokal juga menghasilkan pesan dan log yang aman. Kasus tersebut diuji dengan token rahasia sintetis; token tidak bocor ke halaman maupun log. README yang berulang sudah dirapikan. Tidak ada SQL baru, deploy, perubahan layanan XAMPP, atau perubahan database pengguna.

Batas verifikasi: pemeriksaan browser memakai salinan HTML hasil render dan JavaScript/aset aplikasi, sedangkan penyimpanan/otorisasi diuji melalui HTTP ke aplikasi hidup. HTTPS dengan sertifikat nyata, cookie Secure melalui TLS, dan pembacaan password CLI tanpa echo pada terminal interaktif pemasangan belum diuji langsung; periksa ketiganya saat menyiapkan lingkungan tujuan. Tidak ada pengujian pada shared hosting karena deploy tidak dilakukan.
