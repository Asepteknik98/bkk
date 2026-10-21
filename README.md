# BKK SMK JAYA BUANA

Portal berbasis PHP Native 8.2 dan MySQL/MariaDB. Tahap 1–8 menyediakan fondasi, database, autentikasi, portal publik, CRUD berita/lowongan/program, katalog media, serta tracer study publik dengan panel respons dan ekspor.

## Instalasi XAMPP

1. Gunakan Apache 2.4, PHP 8.2 dengan ekstensi `pdo_mysql` dan `fileinfo`, serta MySQL/MariaDB.
2. Letakkan proyek di `C:/xampp/htdocs/bkk`.
3. Salin `app/config/app.example.php` menjadi `app/config/app.local.php`. Atur `base_url` menjadi `/bkk`, lalu isi konfigurasi database lokal sesuai lingkungan. Jangan commit file lokal ini.
4. Jalankan Apache dan MySQL, lalu impor database sesuai petunjuk di bawah. Buka `http://localhost/bkk/`. Jika database belum tersedia, kerangka portal tetap tampil dengan pemberitahuan bahwa informasi belum dapat dimuat.
5. Pastikan stylesheet dimuat melalui `http://localhost/bkk/assets/css/style.css`. Bootstrap 5.3.3 disertakan lokal di `assets/vendor/bootstrap` beserta lisensi MIT; tidak ada proses build atau kebutuhan CDN saat website digunakan.

Untuk hosting shared, unggah isi proyek ke direktori tujuan, lalu atur `base_url` menjadi `/nama-subfolder`, string kosong untuk root domain, atau URL lengkap seperti `https://contoh.sch.id/bkk`. Gunakan HTTPS pada hosting. Zona waktu default adalah `Asia/Jakarta` dan dapat diubah di konfigurasi lokal.

## Konfigurasi server

Apache harus mengizinkan `.htaccess` (`AllowOverride All`) dan mengaktifkan `mod_rewrite` serta `mod_authz_core`. Direktori `app`, `database`, `storage`, dan `docs` menolak akses HTTP. Root memblokir berkas tersembunyi dan listing direktori; `uploads` menolak eksekusi skrip. Jangan gunakan server bawaan PHP untuk menilai proteksi Apache karena server tersebut tidak membaca `.htaccess`.

Pastikan proses PHP dapat menulis ke `storage` untuk membuat `sessions` dan `login-attempts`, serta ke `storage/logs` dan, saat fitur unggahan tersedia, `uploads`. Session disimpan di direktori aplikasi yang terlindungi, bukan direktori session bersama hosting. Jangan gunakan izin `777`. Kesalahan dicatat ke `storage/logs/php-error.log`; detail kesalahan dan argumen fungsi tidak ditampilkan kepada pengunjung atau dicatat dalam trace.

## Database dan helper

### Impor untuk instalasi baru

1. Jalankan MySQL/MariaDB. Gunakan MySQL 8.0.16+ atau MariaDB 10.4+ agar constraint `CHECK` ditegakkan.
2. Melalui phpMyAdmin atau panel hosting, buat database **baru dan kosong**, misalnya `bkk_jaya_buana`, dengan collation `utf8mb4_unicode_ci`. Pada hosting, nama database dapat memiliki prefiks akun.
3. Pilih database tersebut, buka tab **Import**, lalu pilih `database/bkk_jaya_buana.sql` dengan encoding UTF-8. SQL tidak berisi `CREATE DATABASE`, `USE`, `DROP`, atau kredensial. Jangan impor ke database yang sudah berisi tabel aplikasi; ini bukan skrip migrasi atau impor berulang. DDL MySQL tidak dapat diandalkan untuk rollback seluruh impor, sehingga jika gagal, periksa penyebabnya sebelum mencoba ulang pada database kosong yang lain.
4. Sesuaikan nama database dan kredensial pada `app/config/app.local.php`. Berikan hak akses hanya untuk database aplikasi.
5. Verifikasi delapan tabel: `users`, `berita`, `lowongan`, `program`, `mitra`, `tracer_responses`, `settings`, dan `media`. Tabel `users` dan `tracer_responses` harus kosong setelah impor. Buat admin pertama dengan skrip CLI di bawah.

Data awal berisi satu berita, satu lowongan, dan lima program berlabel **DATA DEMO**, seluruhnya berstatus `draft`, tanpa penulis, tautan pendaftaran, atau batas lamaran rekaan. Hapus atau ganti contoh sebelum publikasi. Kontak yang belum tersedia berupa NULL atau teks placeholder, bukan informasi resmi. Tidak ada data pribadi alumni contoh.

### Pembaruan database yang sudah terpasang

`database/bkk_jaya_buana.sql` khusus instalasi awal dan tidak boleh diimpor ulang untuk memperbarui aplikasi. Setiap perubahan database berikutnya harus dibuat sebagai file SQL terpisah, berurutan, di `database/updates/`, misalnya `001_nama_perubahan.sql`. File pembaruan hanya memuat perubahan yang diperlukan, tanpa membuat ulang seluruh tabel atau menimpa data yang sudah ada. Jangan menggabungkan pembaruan kembali ke SQL instalasi awal.

Untuk database yang sudah terpasang, cadangkan terlebih dahulu, pilih database yang benar di phpMyAdmin, lalu impor hanya file pembaruan yang belum diterapkan sesuai urutan nomor. Petunjuk setiap pembaruan harus menyebutkan prasyarat, cara verifikasi, dan apakah aman diimpor ulang. Catat file yang sudah diterapkan; jangan mengimpor ulang tanpa petunjuk eksplisit. Instalasi baru menggunakan SQL awal, dilanjutkan file pembaruan yang tersedia secara berurutan.

Tahap 3–7 tidak mengubah skema/seed. Mulai tahap 8 tersedia `database/updates/001_tracer_submission_key.sql`: impor **satu kali** pada database BKK yang sudah memiliki tabel `tracer_responses`, sebelum menerima pengiriman tracer. File menambah kolom `submission_key` dan indeks unik tanpa menghapus respons lama. Jangan mengimpor ulang SQL awal pada database yang sudah terpasang. Untuk instalasi baru, impor SQL awal kemudian pembaruan 001. Cadangkan database sebelum pembaruan. Jika 001 sudah diterapkan, jangan jalankan lagi; file ini tidak dirancang untuk impor berulang. Verifikasi dengan `SHOW COLUMNS FROM tracer_responses LIKE 'submission_key';` dan `SHOW INDEX FROM tracer_responses WHERE Key_name='uq_tracer_submission_key';`. Pesan `#1050 Table 'users' already exists` dari SQL awal berarti tabel tersebut sudah ada, bukan alasan menghapusnya.

Slug unik berlaku per tabel konten. Referensi penulis/pengunggah dapat NULL dan memakai `ON DELETE SET NULL`; menonaktifkan pengguna pada tahap berikutnya tetap menjadi pilihan utama. Gambar/logo menyimpan path relatif; tabel `media` menjadi katalog metadata unggahan yang dapat digunakan kembali. Tracer menyimpan persetujuan, waktu, dan versi teks persetujuan; akses data pribadi harus dibatasi pengelola pada tahap fitur tracer. Constraint database tidak menggantikan validasi server.

Koneksi PDO baru dibuat saat `db()` dipanggil. PDO memakai `utf8mb4`, exception, dan prepared statements native; setiap query berparameter wajib memakai `prepare()` dan `execute()`. Portal publik sekarang membaca database melalui `app/helpers/public.php`; helper `setting()` memuat pengaturan sekali per permintaan dengan nilai fallback bila belum diisi. Kegagalan pembacaan dicatat dengan kode SQLSTATE tanpa detail kredensial, dan ditampilkan sebagai pemberitahuan layanan.

Muat `app/bootstrap.php` sekali dari setiap entry point PHP. Helper tersedia: `app_config()`, `base_url()`, `asset_url()`, `redirect()` untuk path internal, dan `e()` untuk escape teks/atribut HTML. Gunakan helper URL untuk mendukung instalasi di subfolder.

Folder fitur yang belum berisi implementasi dipertahankan dengan `.gitkeep`. Tidak ada akun pengelola atau kredensial bawaan.

## Admin pertama dan login

Setelah impor dan konfigurasi lokal, jalankan dari terminal interaktif pada direktori proyek:

```powershell
C:\xampp\php\php.exe app/create-admin.php
```

Pada Linux/hosting dengan akses SSH, jalankan `php app/create-admin.php`. Jika hosting tidak menyediakan CLI, gunakan fasilitas terminal dari penyedia hosting; skrip tidak menyediakan jalur pembuatan admin melalui browser. Isi nama, email, password sepanjang 12–72 byte, dan konfirmasi. Password dibaca tanpa echo melalui PowerShell `Read-Host -AsSecureString` pada Windows atau `stty` pada terminal Unix. Windows membutuhkan PowerShell dan `proc_open`; Unix membutuhkan `exec` dan `stty`. Jangan memasukkan password sebagai argumen perintah, dalam source code, atau berkas konfigurasi. Skrip menyimpan hasil `password_hash()` dan hanya bekerja jika belum ada admin; database lock mencegah dua proses membuat admin pertama bersamaan.

Buka `http://localhost/bkk/auth/login.php`. Admin dan `staf_bkk` memakai formulir yang sama. Pembuatan akun staf melalui panel menunggu tahap pengelolaan pengguna. Dashboard ada di `panel/index.php`; menu hanya memuat halaman yang tersedia. Tidak ada modul pengguna/pengaturan pada tahap ini.

Login dibatasi maksimal 5 percobaan per kombinasi email/IP dan 30 percobaan per IP dalam jendela 15 menit, termasuk percobaan berhasil. Batas disimpan di `storage/login-attempts` dan tidak hilang ketika cookie dihapus. Respons batas adalah HTTP 429 dengan `Retry-After`. Aplikasi memakai `REMOTE_ADDR`, bukan header proxy dari pengunjung; di balik reverse proxy, konfigurasikan alamat klien dan HTTPS pada server tepercaya agar batas tidak mengelompokkan seluruh pengunjung. Cookie session memakai HttpOnly, SameSite=Lax, path sesuai base URL, serta Secure ketika Apache/PHP mendeteksi HTTPS. Jangan menghapus state pembatas untuk melewati batas. Berkas pembatas yang sudah kedaluwarsa dapat dibersihkan melalui pemeliharaan server berkala.

Logout hanya melalui tombol **Keluar** (POST dengan CSRF). ID session dan token CSRF diganti setelah login; session serta cookie dihapus saat logout. Gunakan `require_auth()` untuk endpoint yang membutuhkan login atau `require_role('admin')` untuk **setiap halaman dan handler** pengguna/pengaturan pada tahap berikutnya. Keduanya membaca ulang status aktif dan role dari database; menu bukan mekanisme otorisasi. Untuk perubahan data, panggil juga `require_post_csrf()` sebelum melakukan perubahan. `require_role('admin', 'staf_bkk')` mengizinkan kedua role.

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
