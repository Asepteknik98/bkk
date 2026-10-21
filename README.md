# BKK SMK JAYA BUANA

Portal berbasis PHP Native 8.2 dan MySQL/MariaDB. Tahap 1–4 menyediakan fondasi, database, autentikasi, dashboard awal, serta portal publik responsif. Modul CRUD, formulir tracer, dan detail lowongan/program belum diimplementasikan.

## Instalasi XAMPP

1. Gunakan Apache 2.4, PHP 8.2 dengan ekstensi `pdo_mysql`, dan MySQL/MariaDB.
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

Saat ini belum ada file pembaruan: tahap 3 dan 4 tidak mengubah skema atau seed SQL. Jika impor SQL awal menghasilkan `#1050 Table 'users' already exists`, berarti tabel tersebut sudah ada pada database yang dipilih. Hentikan impor ulang dan periksa kelengkapan delapan tabel sebelum menentukan perbaikan; jangan menghapus tabel yang sudah berisi data untuk mengatasi pesan tersebut.

### Pembaruan database yang sudah terpasang

`database/bkk_jaya_buana.sql` khusus instalasi awal dan tidak boleh diimpor ulang untuk memperbarui aplikasi. Setiap perubahan database berikutnya harus dibuat sebagai file SQL terpisah, berurutan, di `database/updates/`, misalnya `001_nama_perubahan.sql`. File pembaruan hanya memuat perubahan yang diperlukan, tanpa membuat ulang seluruh tabel atau menimpa data yang sudah ada. Jangan menggabungkan pembaruan kembali ke SQL instalasi awal.

Untuk database yang sudah terpasang, cadangkan terlebih dahulu, pilih database yang benar di phpMyAdmin, lalu impor hanya file pembaruan yang belum diterapkan sesuai urutan nomor. Petunjuk setiap pembaruan harus menyebutkan prasyarat, cara verifikasi, dan apakah aman diimpor ulang. Catat file yang sudah diterapkan; jangan mengimpor ulang tanpa petunjuk eksplisit. Instalasi baru menggunakan SQL awal, dilanjutkan file pembaruan yang tersedia secara berurutan.

Saat ini belum ada file pembaruan: tahap 3 dan 4 tidak mengubah skema atau seed SQL. Jika impor SQL awal menghasilkan `#1050 Table 'users' already exists`, berarti tabel tersebut sudah ada pada database yang dipilih. Hentikan impor ulang dan periksa kelengkapan delapan tabel sebelum menentukan perbaikan; jangan menghapus tabel yang sudah berisi data untuk mengatasi pesan tersebut.

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

Halaman tersedia: beranda, profil/kontak, daftar dan detail berita (`detail-berita.php?slug=...`), pencarian judul/ringkasan berita, ringkasan lowongan, informasi tracer study, dan lima kategori program. Semua menu memiliki tujuan yang tersedia. Daftar berita memakai pagination 9 item; daftar awal lowongan/program menampilkan maksimal 30 item. Rincian dan pendaftaran lowongan/program serta formulir tracer menunggu tahap berikutnya.

Berita hanya tampil jika `published` dan waktu publikasinya sudah tiba (NULL memakai waktu pembuatan). Draft, berita terjadwal, dan slug tidak dikenal menghasilkan 404 pada detail; gangguan database menghasilkan 503 pada detail. Lowongan/program hanya memakai status `published`; mitra hanya `aktif = 1`. Batas lamaran kosong diberi keterangan belum ditentukan, sedangkan yang lewat diberi label sudah berakhir. Data pribadi tracer tidak pernah dibaca oleh halaman publik.

Seed SQL tetap draft sehingga instalasi baru menampilkan empty state. Jangan menerbitkan contoh sebagai informasi resmi. Konten yang mengandung penanda DATA DEMO diberi badge yang terlihat. Semua isi berita ditampilkan sebagai teks dengan baris baru dan escape HTML; HTML dari database tidak dieksekusi. Tidak ada editor HTML pada tahap ini.

Identitas/kontak dibaca dari tabel `settings`. `bkk_logo` dapat diisi path gambar lokal; profil resmi dan pengumuman opsional memakai key `profile_description` dan `public_announcement`, dengan empty state bila belum ada. Jangan mengisi kontak atau pengumuman rekaan. Pengelolaan settings melalui panel belum dibuat.

Gambar yang belum tersedia memakai `assets/images/placeholder.svg`, ilustrasi geometris lokal yang jelas berlabel placeholder. Monogram teks BKK adalah identitas sementara, bukan logo resmi sekolah. Gambar konten/logo hanya diambil dari path relatif raster dalam `uploads/` atau `assets/images/`; URL gambar eksternal, path tidak aman, dan berkas yang hilang memakai placeholder. Tidak ada foto pihak ketiga. Semua URL halaman/aset menggunakan base URL agar bekerja di subfolder.

Untuk pemeriksaan manual, buka semua menu, detail berita published, slug draft/tidak dikenal, pencarian, dan halaman kosong; pada lebar 320/390/768/1440 piksel periksa menu, dropdown program, pemenggalan judul panjang, batas lamaran, dan gambar. Salinan proyek/database sementara dipakai untuk pengujian konten tanpa mengubah database pengguna.
