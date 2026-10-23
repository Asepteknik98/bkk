# Akses admin, pengguna, dan publik

Tidak ada akun peserta/siswa, pendaftaran akun peserta, atau ruang siswa. Admin dan pengguna memakai Login Pengelola. Role internal pengguna tetap `staf_bkk`; label antarmuka menjadi Pengguna. Publik mengakses informasi dan pretest/poster yang diaktifkan tanpa akun.

Penghapusan mencakup tombol Login Siswa, direktori ruang siswa, pengelolaan akun siswa, helper/middleware khusus, pengalihan login siswa, role enum siswa, serta tabel `student_accounts` beserta semua kolom dan relasinya. Migrasi 014 yang menambahkan fitur tersebut tidak lagi disertakan. Login, reset password, dan pengaturan pengguna pengelola tetap tersedia.

Data kegiatan dikelola melalui **Data Peserta** (`panel/peserta/`), dengan impor, edit, filter, absensi, dan ekspor. Data peserta tidak membuat akun login. Nama skema historis untuk data kegiatan dipertahankan; lihat `PESERTA.md`.

## Migrasi database

`database/updates/015_remove_student_accounts.sql` hanya boleh dijalankan setelah cadangan dibuat dan dipastikan tabel penghubung kosong serta tidak ada akun dengan role siswa. Pada database lokal pemeriksaan menunjukkan kedua jumlah tersebut nol. Migrasi menghapus tabel penghubung yang kosong dan membatasi enum role ke admin/staf_bkk, tanpa menghapus akun pengelola atau kolom data kegiatan.

Migrasi awal yang juga menghapus/mengubah kolom data kegiatan ditolak oleh tinjauan otomatis karena risiko kehilangan data dan cakupan di luar penghapusan login. Implementasi menggunakan alternatif aman di atas. Cadangan lengkap database dan sumber sebelum perubahan tersimpan dalam `storage/backups`, yang ditutup dari akses publik.

## Validasi

`C:\xampp\php\php.exe tests/no-student-accounts.php` memeriksa skema login, login admin/pengguna, URL lama yang dihapus, header publik, impor peserta tanpa pembuatan akun, kegiatan/absensi, dan semua jenis ekspor Excel. Akun, sesi, peserta, dan kegiatan uji dibersihkan. Jalankan hanya pada server lokal dengan Apache/MySQL aktif.
