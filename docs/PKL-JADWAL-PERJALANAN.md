# Tahap 3 — Jadwal PKL dan perjalanan dinas

## Penerapan

1. Cadangkan database dan berkas portal.
2. Pilih database portal yang sedang digunakan pada phpMyAdmin.
3. Pastikan migrasi 018 (izin) dan 019 (penempatan PKL) sudah diterapkan. Jangan impor ulang SQL instalasi atau seluruh pembaruan lama.
4. Impor `database/updates/020_pkl_schedules_travel.sql`. Skrip membuat tabel tambahan `pkl_schedules` dan `pkl_business_trips`; tidak mengubah data siswa, penempatan, jurnal, laporan akhir, atau data lama lainnya. Aman diimpor ulang pada skema hasil migrasi ini.
5. Login Admin dan buka **Praktik Kerja Lapangan → Jadwal monitoring, pertemuan & sidang**. Detail PKL siswa juga mempunyai tautan jadwal untuk penempatan itu.
6. Untuk staf: beri izin **PKL** dan **Data siswa / PKL** agar dapat mengelola jadwal. Tambahkan izin **Perjalanan dinas & biaya** jika staf perlu melihat atau mengubah perjalanan/nominal. Admin mengakses semuanya.

Migrasi disiapkan dan diuji pada database sementara; tidak diterapkan otomatis ke database asli. Halaman baru memberikan 503 dengan petunjuk SQL jika migrasi belum tersedia. Penempatan, jurnal, dan laporan Tahap 2 tetap dapat digunakan.

## Jadwal dan periode

Semua jadwal dikaitkan ke satu penempatan siswa, sehingga siswa dan periode PKL bersumber dari data Tahap 2. Gunakan pencarian nama/NIS/tempat ketika memilih penempatan. Guru bertugas, pembimbing, dan penguji disimpan sebagai data penugasan; tidak dibuatkan akun.

Daftar menyediakan filter **periode PKL**, **jenis jadwal**, dan **tanggal pada minggu yang ditampilkan**. Periode adalah tanggal mulai/selesai penempatan, bukan tanggal jadwal. Karena itu sidang setelah praktik selesai maupun pertemuan sebelum praktik tetap muncul pada periode PKL terkait. Daftar dikelompokkan berdasarkan minggu kalender Senin–Minggu menggunakan minggu ISO, termasuk pergantian tahun. Jam menggunakan WIB (UTC+7).

- **Monitoring mingguan:** buat jadwal untuk setiap minggu kunjungan yang direncanakan, dengan guru, tujuan/tempat, alamat lengkap, tanggal/jam, agenda, dan status. Tidak ada pembentukan jadwal berulang otomatis. Tanggal monitoring harus berada dalam periode praktik. Isi hasil dan tindak lanjut melalui Edit jadwal/hasil. Hasil wajib diisi ketika status Selesai; tindak lanjut opsional.
- **Pertemuan orang tua/wali:** catat tanggal/jam, lokasi dan alamat lengkap, guru/petugas, agenda, nama wali atau kelompok undangan bila tersedia, dan status. Siswa terkait berasal dari penempatannya. Pertemuan dapat dijadwalkan sebelum/sesudah praktik.
- **Sidang PKL:** catat tanggal/jam, lokasi dan alamat, guru pembimbing/penguji, agenda, dan status. Nama siswa dan periode tampil dari penempatan. Beberapa guru dapat ditulis dalam kolom petugas.

Status tersedia: Dijadwalkan, Selesai, Ditunda, Dibatalkan. Gunakan Dibatalkan untuk mempertahankan riwayat; tidak ada penghapusan jadwal yang dapat memutus kaitan perjalanan. Jenis dan penempatan pemilik tidak dapat diganti saat edit. Buat jadwal baru jika salah memilih jenis atau siswa. Perubahan periode penempatan ditolak jika membuat jadwal monitoring lama berada di luar periode, termasuk jadwal yang dibatalkan.

Unduhan **jadwal Excel** mengikuti filter dan memuat seluruh hasil, bukan hanya halaman aktif. Unduhan jadwal hanya memuat data jadwal dan identitas penempatan, tanpa biaya perjalanan.

## Perjalanan dinas

Buka **Master Data → Perjalanan dinas & biaya**, atau **Catat perjalanan** dari baris monitoring. Setiap perjalanan dikaitkan ke penempatan/periode PKL; kaitan ke jadwal monitoring bersifat opsional. Pilihan monitoring hanya berasal dari penempatan yang sama. Server menolak ID monitoring dari penempatan lain atau ID pertemuan/sidang yang dikirim lewat formulir. Relasi penempatan juga dilindungi foreign key gabungan.

Catat tujuan, petugas, tanggal, nominal rupiah, dan sumber biaya **sekolah** atau **pribadi**. Bila dibuat dari monitoring, tujuan/petugas/tanggal terisi awal. Data perjalanan kemudian berdiri sebagai catatan tersendiri: perubahan jadwal tidak otomatis menimpa tujuan, tanggal, atau biaya perjalanan. Tanggal perjalanan tidak dibatasi tanggal kunjungan atau praktik agar perjalanan keberangkatan/kepulangan dan keperluan terkait dapat dicatat.

Nominal menerima angka tanpa pemisah ribuan, misalnya `150000`, `150000,50`, atau `150000.50`; maksimal `9999999999999.99`, dua desimal. Nilai negatif, notasi ilmiah, dan desimal berlebih ditolak. Nol diperbolehkan. Nominal memakai DECIMAL(15,2) dan string desimal pada PHP/Excel, sehingga tidak mengalami pembulatan float. Total sekolah/pribadi dihitung terpisah untuk seluruh hasil filter, bukan halaman aktif. Nominal di Excel disimpan sebagai teks desimal untuk menjaga ketepatannya.

Daftar perjalanan dan unduhan Excel dapat difilter menurut periode/penempatan dan minggu tanggal perjalanan. Perjalanan dapat diedit oleh pengelola berizin. Tidak ada fitur pembayaran, persetujuan biaya, unggah bukti, atau penghapusan catatan pada tahap ini.

## Hak akses dan perlindungan data

- Halaman, URL langsung, POST, dan unduhan menjalankan pemeriksaan izin server pada setiap permintaan. Staf membutuhkan izin pkl+peserta untuk jadwal, serta biaya-perjalanan+pkl+peserta untuk perjalanan.
- Staf dengan izin PKL saja tidak mendapat akses halaman, simpan, nominal, total, atau unduhan biaya. Dashboard/sidebar mengikuti izin. Kueri dan ekspor jadwal tidak membaca tabel biaya.
- Pencabutan izin berlaku pada permintaan berikutnya dalam sesi aktif. Semua simpan memakai POST+CSRF, transaksi, dan pemeriksaan versi untuk menolak formulir yang sudah tertinggal perubahan pengelola lain.
- Semua jadwal, siswa, wali, dan biaya adalah data internal; tidak ditambahkan halaman publik. Teks di-escape dan ekspor memakai helper Excel lama. Unduhan diberi no-store. Data penugasan tidak memberikan hak login kepada guru/wali/siswa.

## File

- `database/updates/020_pkl_schedules_travel.sql`: tabel tambahan dan relasi.
- `app/helpers/pkl-schedules.php`: validasi, jadwal, filter periode/minggu, perjalanan, dan rupiah.
- `panel/pkl/jadwal.php`, `jadwal-form.php`, `jadwal-unduh.php`: tiga jenis jadwal dan ekspor.
- `panel/biaya-perjalanan/index.php`, `form.php`, `unduh.php`: data perjalanan dan ekspor biaya.
- `app/layouts/pkl-placement-picker.php`, `pkl-schedule-filter.php`: pemilih penempatan dan filter bersama.
- `app/helpers/pkl.php`: perlindungan jadwal ketika periode penempatan diedit.
- `app/helpers/permissions.php`, `panel/pengguna/form.php`, `panel/layouts/sidebar.php`, `staff-dashboard.php`: izin biaya dan navigasi.
- `panel/pkl/index.php`, `detail.php`: tautan jadwal global/per siswa.
- `app/tests/pkl-schedules.php`: regresi tanggal/jam/status, periode/minggu ISO, nominal, sumber biaya, dan pemetaan izin unduhan.

## Pengujian

- 70 pemeriksaan HTTP Tahap 3 lulus pada salinan aplikasi/skema dan data sintetis: migrasi dua kali, login, kondisi migrasi belum tersedia, tiga jenis jadwal, filter periode/minggu lintas tahun, hasil monitoring, konflik versi, edit periode, CSRF, kaitan monitoring, biaya sekolah/pribadi, ketepatan nominal/total, ekspor jadwal/biaya, penolakan biaya bagi staf PKL, pencabutan izin pada sesi aktif, data lama, dan beranda publik. Tidak ada warning/error PHP pada alur uji.
- 63 pemeriksaan HTTP regresi Tahap 2 lulus: penempatan, jurnal, laporan akhir, izin, ekspor, dan data lama. Lingkungan uji mensimulasikan migrasi belum diterapkan dengan menghapus tabel kosong **di database uji saja**, kemudian menerapkan kembali migrasi.
- `C:\xampp\php\php.exe app/tests/pkl-schedules.php` (29 pemeriksaan), `app/tests/pkl.php`, `app/tests/access.php`, `app/tests/jobs.php`, `app/tests/tracer.php`, dan `tests/participants.php` lulus. Seluruh 181 file PHP lolos sintaks; `git diff --check` lulus.
- Database asli hanya dibaca untuk menyalin definisi skema, tidak dimigrasikan atau diisi data uji. Server, database, dan salinan aplikasi sementara dibersihkan. Pengujian HTTP memakai server PHP lokal; tampilan browser/perangkat fisik serta HTTPS belum diuji ulang.
