# Tahap 2 — Praktik Kerja Lapangan

## Penerapan database

1. Cadangkan database dan berkas portal.
2. Pada phpMyAdmin, pilih database portal yang sudah digunakan.
3. Pastikan tabel `siswa` dan `users` tersedia. Jika Tahap 1 belum diterapkan, impor `database/updates/018_permissions_alumni_links.sql` terlebih dahulu.
4. Impor **hanya** `database/updates/019_pkl.sql` untuk Tahap 2. Jangan impor ulang SQL instalasi atau seluruh pembaruan lama.
5. Verifikasi tabel `pkl_placements`, `pkl_journals`, dan `pkl_final_reports` tersedia. Skrip 019 dapat diimpor ulang tanpa menghapus/mengisi ulang data yang sudah ada.
6. Admin dapat langsung mengakses menu **Master Data → Praktik Kerja Lapangan**. Untuk staf, pilih izin **Praktik Kerja Lapangan (PKL)** dan **Data siswa / PKL** pada Pengguna → Edit. Izin data siswa diperlukan karena modul membaca identitas peserta. Izin tidak ditambahkan otomatis ke staf lama.

SQL disiapkan dan diuji pada database sementara, belum diterapkan otomatis ke database asli. Sebelum impor 019, halaman daftar PKL menampilkan petunjuk; halaman operasional baru memberikan 503. Data peserta dan fitur lama tetap dapat digunakan.

## Data lama dan penempatan

Tabel `siswa` tetap menjadi sumber identitas siswa/peserta. Satu siswa dapat mempunyai beberapa penempatan/periode PKL. Tidak ada akun siswa atau guru baru. Guru pembimbing disimpan sebagai nama pada penugasan penempatan, terpisah dari `users`.

Kolom lama `siswa.tempat_pkl` dan `siswa.tahun_pkl`, impor/ekspor peserta, dan relasi absensi tidak diubah. Form penempatan menampilkan riwayat lama serta mengisi awal tempat PKL jika tersedia. Penempatan baru tidak otomatis menimpa kolom lama. Riwayat lama tidak otomatis dikonversi karena tanggal, alamat, dan pembimbingnya belum lengkap.

Penempatan menyimpan siswa, kelas atau jurusan pada periode tersebut, tanggal mulai/selesai, tempat PKL, alamat lengkap, guru pembimbing, pembimbing industri, serta status aktif/selesai/arsip. Minimal kelas atau jurusan wajib diisi. Nama tempat, alamat lengkap, dan kedua pembimbing wajib diisi. Sekolah mengisi alamat jalan/nomor, desa/kelurahan, kecamatan, kabupaten/kota, provinsi, serta kode pos sesuai lokasi.

Siswa pemilik penempatan tidak dapat diganti saat edit. Untuk siswa lain, buat penempatan baru. Kombinasi siswa/periode/tempat yang identik ditolak agar tidak membuat penempatan ganda. Gunakan status **Arsip** untuk menyimpan riwayat; tidak disediakan penghapusan penempatan beserta jurnalnya. Status adalah penanda administratif dan tetap mengizinkan koreksi oleh pengelola berizin.

## Penggunaan

1. Tambah siswa melalui **Data siswa / peserta** atau pilih siswa lama saat membuat penempatan. Pencarian nama/NIS tersedia; hasil pilihan dibatasi 50 untuk setiap pencarian.
2. Lengkapi penempatan dan penugasan pembimbing, lalu simpan.
3. Buka **Jurnal & laporan**. Admin/Staf mencatat tanggal, kegiatan, dan catatan harian atas nama siswa. Catatan opsional. Satu tanggal hanya mempunyai satu jurnal; beberapa kegiatan dapat ditulis pada kolom yang sama.
4. Tanggal jurnal wajib berada di dalam periode PKL. Jurnal dapat diedit atau dihapus melalui POST dengan CSRF. Server memastikan jurnal merupakan milik penempatan yang sedang dibuka. Penyempitan periode yang mengeluarkan jurnal lama ditolak.
5. Rekap mingguan dihitung dari jurnal dalam minggu kalender Senin–Minggu menggunakan tahun/minggu ISO, termasuk pergantian tahun. Hanya minggu yang mempunyai jurnal ditampilkan. Jumlah hari tercatat bukan jumlah kehadiran atau jumlah hari kerja yang seharusnya.
6. **Unduh rekap Excel** menyediakan tiga lembar: penempatan, rekap mingguan, jurnal harian. NIS dan teks disimpan sebagai teks agar nol awal dan perlindungan terhadap formula Excel tetap terjaga.
7. Buka **Laporan akhir** dan isi tanggal laporan, ringkasan pelaksanaan, hasil/kompetensi, kendala, saran, dan kesimpulan. Kendala dan saran opsional. Tanggal laporan tidak boleh sebelum mulai PKL. Laporan disimpan per penempatan.
8. **Unduh laporan HTML / cetak** menghasilkan dokumen mandiri yang memuat identitas penempatan, pembimbing, narasi, rekap, dan jurnal terbaru. Buka berkas di browser untuk mencetak atau memilih Simpan sebagai PDF. Portal tidak menambah pustaka PDF atau fitur unggah laporan pada tahap ini.

Jika penempatan/jurnal/laporan sudah diubah pengelola lain, penyimpanan dari formulir versi lama ditolak. Muat ulang formulir dan periksa perubahan sebelum menyimpan kembali. Unduhan memakai satu snapshot database; narasi laporan tetap tersimpan, sedangkan lampiran jurnal dan rekap mengikuti data saat unduhan dibuat.

## Hak akses dan privasi

Seluruh endpoint `panel/pkl/`, termasuk POST dan unduhan, memanggil pemeriksaan role/izin server Tahap 1. Pengunjung diarahkan ke login; staf tanpa kedua izin ditolak dengan 403. Pencabutan izin berlaku pada permintaan berikutnya walaupun sesi tetap aktif. Tidak ada URL PKL publik, berkas pribadi statis, atau akun khusus guru/siswa. Dokumen dibangkitkan ketika diunduh oleh pengelola berizin; respons tidak disimpan di cache. Teks pengguna di-escape pada panel dan laporan, dan ekspor memakai helper Excel yang sudah ada.

## File utama

- `database/updates/019_pkl.sql`: tiga tabel tambahan.
- `app/helpers/pkl.php`: validasi, transaksi, pemeriksaan versi, jurnal, dan perhitungan minggu.
- `panel/pkl/index.php`, `form.php`, `detail.php`, `laporan.php`, `unduh.php`: daftar, penempatan, jurnal/rekap, narasi akhir, dan unduhan.
- `app/layouts/pkl-report.php`: dokumen HTML untuk cetak.
- `app/helpers/permissions.php`, `panel/pengguna/form.php`: izin modul dan dependensi data siswa.
- `panel/layouts/sidebar.php`, `staff-dashboard.php`, `panel/peserta/index.php`: navigasi dan tautan modul.
- `app/tests/pkl.php`: regresi validasi tanggal/periode, jurnal, laporan, minggu ISO, dan pemetaan unduhan.

## Hasil pengujian

- 63 pemeriksaan HTTP pada salinan kode, skema database lokal, dan data sintetis lulus. Cakupan: migrasi dua kali, kondisi migrasi belum tersedia, data lama tetap ada, login kedua role, izin ganda, URL langsung/POST/unduhan, CSRF, simpan/edit penempatan, duplikasi, guru tanpa akun baru, jurnal dalam/luar periode, minggu lintas tahun, konflik versi, ID jurnal lintas penempatan, periode yang melindungi jurnal, laporan akhir, escaping HTML, isi Excel, pencabutan izin pada sesi aktif, serta regresi ekspor peserta dan beranda publik. Tidak ada warning/error PHP selama pengujian.
- Regresi CLI: `C:\xampp\php\php.exe app/tests/pkl.php`, `app/tests/access.php`, `app/tests/jobs.php`, `app/tests/tracer.php`, dan `tests/participants.php`.
- Pemeriksaan sintaks seluruh PHP dan `git diff --check`.
- Tidak mengisi data pengujian atau menerapkan migrasi pada database asli. Server, database, dan salinan aplikasi sementara dibersihkan setelah pengujian. Pengujian HTTP menggunakan server PHP lokal; tampilan browser/perangkat fisik dan HTTPS belum diuji ulang pada tahap ini.
