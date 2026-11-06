# Tahap 4 — Deskripsi BMW Sakti dan Kelas Industri

## Penerapan

1. Cadangkan database dan berkas portal, lalu pilih database portal pada phpMyAdmin.
2. Pastikan tabel `users`, `settings`, `mitra`, `siswa` (009), `speakers` (012), dan izin staf (018) sudah tersedia sesuai instalasi proyek. Jangan impor ulang SQL instalasi atau seluruh pembaruan lama.
3. Impor **hanya** `database/updates/021_industry_classes.sql` untuk Tahap 4. Skrip menambah empat tabel: `industry_classes`, `industry_class_speakers`, `industry_class_participants`, dan `industry_class_meetings`. Tidak mengubah isi tabel lama dan aman diimpor ulang pada skema hasil migrasi ini.
4. Admin mempunyai akses penuh. Untuk staf, gunakan Pengguna → Edit dan pilih izin sesuai tugas:
   - **Program BMW SAKTI**: deskripsi dan konten program.
   - **Kelas Industri (operasional)** + **Data siswa / PKL**: kelas, pemateri, sasaran/peserta, pertemuan, dan unduhan.
5. Buka **BMW SAKTI → Deskripsi BMW Sakti** untuk mengisi deskripsi dan **Master Data → Kelas Industri / Pertemuan** untuk mengelola kelas operasional.

Migrasi telah diuji pada database sementara; belum diterapkan otomatis ke database asli. Daftar industri menampilkan petunjuk jika migrasi belum tersedia; formulir/detail/unduhan memberi 503. Deskripsi BMW memakai tabel `settings` yang sudah ada dan dapat dikelola tanpa migrasi 021.

## Deskripsi Program BMW Sakti

Pengelola berizin mengisi judul (190 karakter), deskripsi (15.000 karakter), dan status Draft/Dipublikasikan. Isi berupa teks biasa, dengan pemisah paragraf melalui baris kosong; HTML ditampilkan sebagai teks. Batas isi disesuaikan dengan kolom TEXT lama, termasuk teks UTF-8.

Nilai disimpan dalam `settings` dengan kunci `bmw_title`, `bmw_description`, `bmw_status`, dan `bmw_version`. Tidak membuat tabel konten baru atau mengubah kategori program lama. Simpan memakai POST+CSRF, transaksi, lock konten, dan pemeriksaan versi agar perubahan pengelola lain tidak tertimpa.

Halaman publik **`bmw-sakti.php`** ditautkan dari dropdown BMW SAKTI dan bagian program di beranda. Pengunjung tidak perlu login. Hanya judul/isi berstatus Dipublikasikan yang ditampilkan. Draft disimpan tanpa dipublikasikan; mengubah kembali ke Draft menyembunyikan deskripsi tanpa menghapusnya. Saat belum ada publikasi, halaman menampilkan pemberitahuan dan tautan kategori program lama. Tidak mengarang penjelasan resmi sekolah.

## Data kelas industri

- Nama kelas dan mitra industri wajib diisi. Mitra dipilih dari tabel `mitra` jenis perusahaan; nama/ID lama dipakai kembali. Mitra nonaktif tetap tersedia dengan penanda untuk pencatatan historis.
- Pilih minimal satu pemateri dari tabel `speakers`. Beberapa pemateri diperbolehkan. Modul ini hanya membaca nama/instansi pemateri, tidak membuka CV/foto privat tanpa izin modul Pemateri.
- Isi kelas/kelompok sasaran atau pilih peserta satu per satu dari tabel `siswa`; keduanya juga boleh diisi. Pencarian peserta mendukung nama, NIS, dan kelas. Sasaran teks tidak otomatis memasukkan semua anggota suatu kelas.
- Pencarian menampilkan maksimal 50 hasil serta semua peserta yang sudah terpilih pada kelas tersimpan. Cari sebelum mengisi formulir; simpan pilihan sebelum melakukan pencarian berikutnya. Peserta terpilih di luar hasil pencarian tetap tampil dan dipertahankan. Pilihan yang dilepas hanya menghapus relasi ke kelas, bukan identitas siswa.
- Referensi peserta/pemateri dibatasi masing-masing 1.000 pilihan. Formulir mempunyai penanda kelengkapan di akhir untuk menolak penyimpanan jika input terpotong batas server. Untuk penggunaan dengan pilihan sangat banyak, sesuaikan `max_input_vars` PHP; jangan meneruskan penyimpanan parsial.
- Status kelas Aktif/Arsip dan catatan tersedia. Arsip mempertahankan jadwal serta peserta dan tetap dapat dikoreksi oleh pengelola berizin. Tidak disediakan penghapusan kelas beserta riwayat pertemuannya.

Mitra, pemateri, dan siswa yang dipakai kelas/pertemuan dilindungi foreign key agar tidak terhapus meninggalkan referensi kosong. Data kontak, berkas pemateri, peserta lama, atribut kelas_industri historis, kegiatan/absensi lama, PKL, dan konten program publik tidak ditimpa atau dikonversi otomatis. Data kelas operasional tidak otomatis membuat kegiatan absensi atau akun baru.

## Rencana dan jadwal pertemuan

Rencana menerima 1–500 pertemuan. Ketika kelas dibuat, slot bernomor 1 sampai rencana dibuat dengan status **Belum dijadwalkan**. Buka Jadwal & pertemuan → Edit jadwal/status pada setiap slot untuk mencatat tanggal, jam WIB, lokasi, pemateri, materi, status, dan catatan. Pemateri per pertemuan dapat memakai daftar pemateri kelas atau dipilih khusus dari pemateri lama.

Status pertemuan:

| Status | Pengaruh rekap |
|---|---|
| Belum dijadwalkan | Tidak dihitung terlaksana; slot dapat belum memiliki tanggal |
| Dijadwalkan | Tanggal, jam, lokasi wajib; belum dihitung terlaksana |
| Terlaksana | Tanggal, jam, lokasi wajib; dihitung terlaksana |
| Ditunda | Jadwal sebelumnya dan catatan tetap tersimpan; tidak dihitung terlaksana |
| Dibatalkan | Riwayat tetap tersimpan; tidak dihitung terlaksana |

Tanggal/jam wajib valid. Pertemuan dengan tanggal mendatang tidak dapat ditandai Terlaksana. Rekap pada daftar/detail/Excel menghitung status saat ini, misalnya **3 / 8 pertemuan terlaksana**. Mengoreksi status dari Terlaksana menjadi Ditunda mengurangi angka, dan mengembalikannya menambah tepat satu kali. Tidak ada penghitung manual yang bisa menjadi tidak sinkron.

Rencana yang ditambah menghasilkan slot baru tanpa mengubah pertemuan lama. Pengurangan hanya boleh menghapus slot terakhir yang belum pernah diisi/disimpan (status awal, tanpa tanggal/jam/lokasi/materi/pemateri/catatan, versi awal). Jika slot di luar rencana baru sudah pernah diisi, pengurangan ditolak dan transaksi tidak mengubah data. Jadwal yang ditunda/dibatalkan tetap memakan nomor slot; ubah jadwal pada slot yang sama jika perlu menjadwalkan ulang.

Semua simpan memeriksa izin server dan CSRF. Versi kelas/pertemuan mencegah formulir lama menimpa perubahan pengelola lain. Server mengikat ID pertemuan ke kelas yang sedang dibuka. Rencana dan pertemuan memakai lock kelas yang sama untuk menjaga perubahan bersamaan.

## Rekap dan privasi

Unduh rekap Excel memuat tiga lembar: ringkasan kelas (termasuk rencana/terlaksana), pertemuan, dan peserta. Semua lembar memakai snapshot database yang sama. Helper Excel lama mempertahankan NIS bernol awal dan menulis teks tanpa menjalankan formula.

Seluruh data operasional kelas, jadwal, dan peserta berada di `panel/kelas-industri/` dengan izin server, termasuk URL langsung, POST dan unduhan. Izin operasional tidak otomatis memberikan izin mengedit deskripsi/konten program atau membuka berkas pemateri. Pencabutan izin berlaku pada permintaan berikutnya dalam sesi aktif. Unduhan diberi `no-store`.

Halaman publik `kelas-industri/index.php` dan detailnya tetap menjadi katalog konten program yang diterbitkan melalui modul Program. Daftar peserta/jadwal operasional tidak ditambahkan ke publik. Publik hanya mendapat deskripsi BMW dan konten program yang memang dipublikasikan.

## File utama

- `database/updates/021_industry_classes.sql`: tabel tambahan dan relasi.
- `app/helpers/bmw.php`, `panel/program/deskripsi.php`, `bmw-sakti.php`: editor dan deskripsi publik memakai settings lama.
- `app/helpers/industry-classes.php`: validasi, referensi, slot pertemuan, transaksi, dan konflik versi.
- `panel/kelas-industri/index.php`, `form.php`, `detail.php`, `pertemuan.php`, `unduh.php`: operasional kelas dan Excel.
- `app/helpers/permissions.php`, `panel/pengguna/form.php`, `panel/layouts/sidebar.php`, `staff-dashboard.php`: izin dan navigasi panel.
- `app/layouts/navbar.php`, `index.php`, `panel/program/index.php`: tautan deskripsi.
- `app/tests/industry-classes.php`: 26 pemeriksaan validasi kelas/pertemuan, referensi, deskripsi, serta pemetaan izin.

## Hasil pengujian

- 77 pemeriksaan HTTP Tahap 4 lulus pada salinan aplikasi/skema dengan data sintetis: migrasi dua kali, kondisi migrasi belum tersedia, draft/publikasi/sembunyikan deskripsi, escaping, izin terpisah, CSRF, pemilihan referensi, sasaran kelas, peserta, slot sesuai rencana, penjadwalan, status/capaian, koreksi capaian, konflik versi, penambahan/pengurangan rencana, perlindungan riwayat, ID lintas kelas, kelengkapan formulir, peserta di luar pencarian, unduhan, pencabutan izin pada sesi aktif, data lama, dan akses modul lama. Tidak ada warning/error PHP.
- Regresi 70 pemeriksaan HTTP Tahap 3 dan 63 HTTP Tahap 2 lulus.
- Regresi CLI `app/tests/industry-classes.php`, `app/tests/pkl-schedules.php`, `app/tests/pkl.php`, `app/tests/access.php`, `app/tests/jobs.php`, `app/tests/tracer.php`, serta `tests/participants.php` lulus. Pemeriksaan sintaks seluruh PHP dan `git diff --check` lulus.
- Database asli hanya dibaca untuk definisi skema; tidak dimigrasikan atau diisi data uji. Database, server, dan salinan aplikasi sementara dibersihkan. HTTP memakai server PHP lokal. Tampilan browser/perangkat fisik dan HTTPS belum diuji ulang pada tahap ini.
