# Tahap 1 — Hak akses, privasi, dan tautan alumni

## Penerapan pada database terpasang

1. Cadangkan database dan berkas terlebih dahulu.
2. Pilih database portal yang sudah digunakan pada phpMyAdmin.
3. Impor **hanya** `database/updates/018_permissions_alumni_links.sql`. Jangan impor ulang SQL awal atau seluruh pembaruan lama.
4. Pastikan tabel `user_permissions` dan `alumni_form_links` tersedia. Skrip 018 dapat diimpor ulang; tidak menghapus atau mengisi ulang data lama. Prasyarat: tabel `users` dan `tracer_responses` dengan primary key BIGINT UNSIGNED seperti skema proyek saat ini.
5. Login sebagai Admin → Pengguna → Edit akun Staf BKK → pilih izin → Simpan. Staf yang sudah ada mulai tanpa izin operasional sampai Admin menentukannya. Data dan akun mereka tetap ada. Akses profil pribadi dan dashboard tetap tersedia.
6. Isi Pengaturan → Hak cipta & privasi. Semua identitas awal kosong; sekolah menentukan nama/unit, alamat, kontak layanan privasi, pemegang hak cipta, dan keterangan masa simpan. Halaman publik `privasi.php` dan `hak-cipta.php` menampilkan nilai tersebut dengan escaping HTML. Nilai memakai tabel `settings` lama, tanpa migrasi tambahan.

Migrasi disiapkan dan diuji pada database sementara; tidak diterapkan otomatis ke database asli. Jika belum diimpor, izin staf ditolak secara aman; pengelolaan izin dan tautan belum bisa digunakan. Admin tetap dapat membuka modul lama.

## Model akses

- Hanya dua role pengelola: `admin` dan `staf_bkk`.
- Admin mengelola seluruh modul, akun, dan izin. Tidak menambah role siswa/alumni.
- Izin staf diberikan per modul/per akun dan mencakup baca, kelola, ekspor, serta unduhan modul tersebut. Tahap ini tidak memisahkan izin baca/simpan/hapus.
- Server mengecek role, status aktif, versi sesi, dan izin setiap permintaan, termasuk URL langsung dan handler. Perubahan izin berlaku pada permintaan berikutnya tanpa login ulang.
- Absensi juga membutuhkan izin peserta karena menampilkan identitas peserta. WhatsApp juga membutuhkan izin lamaran dan tracer karena membaca kontak kedua sumber tersebut.
- Dashboard staf menampilkan pintasan modul yang diizinkan tanpa statistik pribadi global. Notifikasi lamaran hanya muncul dengan izin lamaran.
- Modul baru di dalam `panel/` yang belum dipetakan ditolak untuk staf. Daftarkan modul melalui `permission_modules()` sebelum digunakan dan tetap panggil `require_role()` pada setiap endpoint.
- Pengunjung umum tetap dapat membuka halaman publik dan formulir tracer umum lama. Formulir umum hanya menambah respons; tidak membaca/mengubah respons pribadi yang ada. Formulir pribadi merupakan jalur terpisah untuk memperbarui respons yang terikat.

## Data sensitif dan berkas

Data siswa/PKL, alumni, lamaran, kontak, absensi, serta unduhan dokumen internal mengikuti izin server. Berkas lamaran, capaian, dan pemateri tetap di `storage/` yang ditolak oleh Apache untuk akses langsung. Jangan memindahkannya ke katalog media publik.

Belum ada tabel, berkas, atau modul MoU dan biaya perjalanan pada proyek yang diperiksa. Tahap ini mencadangkan izin `mou` dan `biaya-perjalanan` tanpa membuat modul bisnis baru. Lokasi URL `uploads/mou/`, `uploads/biaya-perjalanan/`, dan `uploads/private/` ditolak oleh Apache. Untuk implementasi modul berikutnya, simpan dokumen di `storage/`, gunakan handler unduhan dengan `require_role()` dan izin terkait; jangan gunakan `uploads/` atau tautan statis publik. Mencentang izin yang dicadangkan belum menyediakan modul.

Proteksi direktori mengikuti konfigurasi Apache proyek (AllowOverride dan mod_rewrite aktif). Uji hosting tujuan sebelum mengunggah dokumen pribadi. Konfigurasi server lain harus menyediakan larangan akses direktori yang setara.

## Tautan alumni

Pada detail respons alumni, pilih **Kelola tautan formulir alumni**. Admin atau staf dengan izin tracer dapat membuat tautan dengan masa berlaku 1–720 jam, atau mencabut tautan yang belum digunakan. Pembuatan tautan baru mencabut tautan lama untuk respons yang sama. Tautan hanya ditampilkan saat dibuat, tidak dikirim otomatis.

Token acak 256 bit disimpan sebagai SHA-256. Waktu berlaku diperiksa menggunakan UTC database. Setelah dibuka, token ditukar dengan kapabilitas sesi formulir dan dibuang dari URL melalui redirect. Tidak ada `user_id` pengelola yang dibuat. Respons menggunakan `no-store`, `no-referrer`, dan `noindex`. Tautan awal tetap dapat tercatat di log akses server; batasi akses dan masa simpan log serta gunakan HTTPS pada hosting.

Identitas (nama, NISN, jurusan, tahun lulus) dan ID respons berasal dari database. Alumni dapat memperbarui kegiatan/kontak, bukan menentukan ID pemilik. Formulir meminta persetujuan dan CSRF. Transaksi dengan row lock memastikan satu penggunaan; setelah pengiriman berhasil token tidak berlaku lagi. Kegagalan validasi tidak menghabiskan token. Batas waktu dan pencabutan diperiksa kembali saat simpan. Permintaan berhenti WhatsApp yang sudah tercatat tidak dihapus oleh formulir.

Tautan adalah rahasia pembawa: siapa pun yang memilikinya dapat membuka formulir terikat selama masih berlaku. Verifikasi penerima sebelum membagikan. Gunakan base_url lengkap pada konfigurasi hosting, atau tambahkan domain portal jika tautan yang ditampilkan relatif.

## Verifikasi

Pengujian HTTP pada salinan kode dan skema database sementara, dengan data sintetis, mencakup login kedua role, akses publik, izin staf, penolakan URL/handler/unduhan/ekspor, CSRF, perubahan dan pencabutan izin pada sesi aktif, dependensi izin, pembuatan/hash token, pengikatan pemilik meskipun ID dipalsukan, pengiriman sekali, token salah/kedaluwarsa/dicabut, serta penolakan dashboard bagi pemilik tautan alumni. Migrasi diuji dua kali. Database asli tidak berisi data pengujian.

Pemeriksaan sintaks seluruh PHP dan regresi helper lowongan/tracer/peserta juga dijalankan. Uji tambahan meliputi simpan/hapus berita oleh staf yang diberi izin, ekspor peserta, penyimpanan identitas pengelola dan escaping pada halaman publik, tautan setelah dibuka lalu kedaluwarsa/dicabut/diganti, validasi gagal yang tidak menghabiskan tautan, serta pengiriman formulir tracer umum lama. Uji HTTP memakai server PHP lokal; aturan Apache diuji terpisah pada Apache lokal (halaman publik 200; storage/app dan lokasi unggahan privat 403). HTTPS dan tampilan perangkat fisik belum diuji.

Regresi keamanan tanpa perubahan database: `C:\xampp\php\php.exe app/tests/access.php`. Menguji pengikatan identitas alumni, token tidak valid, pemetaan ekspor, modul tak dikenal, serta alias path yang tidak boleh melewati izin.
