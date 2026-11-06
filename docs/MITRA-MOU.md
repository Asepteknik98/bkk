# Tahap 5 — Mitra industri, Kampus Dalam Negeri, dan riwayat MoU

## Penerapan database

1. Cadangkan database dan berkas portal, termasuk direktori penyimpanan privat.
2. Pilih database portal yang digunakan pada phpMyAdmin. Pastikan tabel `mitra`, `users`, dan izin pengelola Tahap 1 (018) sudah tersedia.
3. Impor **hanya** `database/updates/022_partner_profiles_mou.sql` untuk Tahap 5. Skrip menambah `mitra_profiles` dan `mitra_mous`; tidak mengubah atau menghapus data lama pada `mitra`, media, Kelas Industri, atau modul lain. Aman diimpor ulang pada skema hasil migrasi ini.
4. Login Admin → Pengguna → Edit akun Staf BKK. Izin **Mitra** membuka perusahaan/kampus, profil, dan PIC. Izin **Riwayat & dokumen MoU** juga memerlukan izin Mitra; keduanya diperlukan untuk membaca riwayat, mengunggah, memperpanjang, dan mengunduh MoU.
5. Gunakan menu **Mitra**, **Kampus Dalam Negeri**, dan **Riwayat MoU** pada panel.

Migrasi disiapkan dan diuji pada database sementara, belum diterapkan otomatis ke database asli. Sebelum impor 022, daftar/formulir/simpan mitra lama tetap berjalan dan menampilkan petunjuk pembaruan. Fitur MoU memberi 503 sampai tabelnya tersedia.

## Data mitra dan kompatibilitas

Tabel `mitra` tetap menyimpan ID, nama, jenis perusahaan/kampus, logo, website, deskripsi, dan status aktif. Relasi Kelas Industri tetap memakai ID mitra yang sama. Logo memakai unggahan/katalog media lama: JPG/PNG/WebP maksimal 5 MB, dapat digunakan kembali dan tetap dilindungi pemeriksaan referensi media.

Tabel tambahan `mitra_profiles` menyimpan klaster, bidang usaha, alamat lengkap, negara, nama PIC, jabatan PIC, kontak PIC, serta versi edit. Data lama tidak otomatis diberi klaster atau negara. Perusahaan lama ditampilkan sebagai **Belum diklasifikasikan**, dengan penanda alamat/negara belum diisi. Lengkapi melalui Edit profil.

Formulir profil lengkap memakai aturan:

- Perusahaan memilih **Industri Dalam Negeri** atau **Industri Luar Negeri** dan mengisi bidang usaha, alamat lengkap, serta negara.
- Dalam Negeri memakai negara Indonesia; Luar Negeri menggunakan negara selain Indonesia.
- Alamat memuat jalan/nomor, desa/kelurahan, kecamatan, kabupaten/kota, provinsi, dan kode pos sesuai informasi mitra. Disimpan sebagai teks alamat lengkap.
- Website resmi opsional jika informasinya belum tersedia, dan harus URL http/https valid ketika diisi.
- Nama, jabatan, dan kontak PIC tersedia. Informasi yang belum diketahui dapat dikosongkan, tanpa mengarang kontak. Kontak dapat berupa telepon/email dan ditampilkan sebagai teks aman di panel.
- Kampus pada direktori Dalam Negeri wajib memakai negara Indonesia dan alamat lengkap. Bidang usaha/klaster industri tidak diwajibkan untuk kampus.

Penyimpanan profil lengkap memakai transaksi, POST+CSRF, dan versi edit. Formulir yang tertinggal perubahan pengelola lain ditolak. Penanda kelengkapan formulir membantu menolak data yang terpotong batas input server. Integrasi lama yang hanya mengirim kolom dasar mitra tetap dapat dipakai: kolom profil/PIC yang sudah ada dipertahankan, dan versi profil dinaikkan agar editor lama tidak menimpa perubahan dasar terbaru.

Perusahaan yang masih dipakai Kelas Industri tidak dapat diubah jenisnya menjadi kampus. Mitra yang mempunyai riwayat MoU atau relasi Kelas Industri tidak dapat dihapus; gunakan Nonaktif untuk mempertahankan riwayat. Logo lama tetap di katalog sebagaimana alur proyek sebelumnya.

## Direktori Kampus Dalam Negeri

`panel/mitra/kampus.php` menyediakan direktori internal kampus dengan negara Indonesia yang sudah diisi, dengan nama, alamat, website resmi, nama/kontak PIC, status, pencarian, dan pagination. Kampus lama dengan negara belum diketahui tetap tersedia pada **Semua mitra → jenis Kampus**, tetapi tidak otomatis dimasukkan sebagai kampus dalam negeri. Tambah kampus dari direktori akan membuka formulir jenis kampus dengan negara awal Indonesia.

Daftar Mitra menyediakan filter jenis dan dua klaster industri, serta pilihan Belum diklasifikasikan untuk perusahaan lama. Detail menampilkan jabatan PIC dan seluruh profil. Semua akses ini memerlukan izin Mitra.

## Riwayat MoU dan perpanjangan

MoU tersedia untuk setiap mitra perusahaan/kampus. Dari detail mitra, buka Riwayat MoU → Tambah MoU. Isi nomor dokumen, tanggal mulai, tanggal berakhir, dan PDF wajib maksimal 5 MB. Tanggal berakhir tidak boleh sebelum mulai; periode satu hari diperbolehkan. Nomor/periode yang identik untuk mitra yang sama ditolak agar pengiriman berulang tidak menggandakan dokumen.

Status tidak disimpan sebagai pilihan manual dan tidak memerlukan cron:

| Kondisi tanggal WIB | Status |
|---|---|
| Hari ini sebelum tanggal mulai | Belum Berlaku |
| Hari ini dari mulai sampai berakhir, inklusif | Aktif |
| Hari ini setelah tanggal berakhir | Kedaluwarsa |

Dokumen aktif sampai akhir hari tanggal berakhir di Asia/Jakarta, tanpa bergantung pada zona waktu database. Daftar menyediakan pencarian nomor/nama mitra, filter status otomatis, filter mitra, dan pagination. Status MoU terpisah dari status aktif/nonaktif mitra.

Klik **Perpanjang** pada dokumen sebelumnya. Form menampilkan nomor/periode lama dan mengisi awal tanggal mulai setelah tanggal berakhir lama. Masukkan nomor/tanggal/berkas baru. Tanggal berakhir perpanjangan harus lebih akhir daripada dokumen sebelumnya. Periode baru yang bertumpang tindih tidak otomatis menghapus atau mengganti periode lama.

Perpanjangan menghasilkan baris baru dan nama berkas baru, dengan `previous_id` yang mengarah ke dokumen lama pada mitra yang sama. Server dan foreign key gabungan menolak kaitan ke mitra lain. PDF dan tanggal lama tidak ditimpa. Tautan MoU sebelumnya pada daftar membuka PDF lama, termasuk jika barisnya berada di halaman riwayat lain.

Tahap ini tidak menyediakan edit/penghapusan dokumen lama. Periksa nomor/tanggal/berkas sebelum simpan; catatan baru mempertahankan jejak dokumen lama. Data historis dengan tanggal kedaluwarsa tetap dapat dicatat dan diunduh.

## Penyimpanan dan privasi

- PDF divalidasi melalui ukuran nyata, ekstensi, MIME fileinfo, signature PDF, dan pemeriksaan uploaded-file/path. SVG, executable yang menyamar, path traversal, file kosong, dan file terlalu besar ditolak.
- Berkas memakai nama acak 64 karakter pada `storage/mou/`, di luar katalog media publik. Direktori diabaikan Git dan dilindungi aturan Apache untuk `storage/`. Jangan menaruh MoU pada katalog media atau uploads publik.
- Unduhan hanya melalui `panel/mou/unduh.php?id=...` dengan pemeriksaan login, role, dan izin MoU+Mitra. Respons berupa attachment PDF, no-store dan nosniff; path berkas tidak diterima dari pengguna.
- PDF baru dibersihkan jika penyimpanan database gagal; berkas yang sudah tercatat tidak dibuang. Jika status database tidak dapat dipastikan, pembersihan ditunda agar dokumen tersimpan tidak terhapus.
- PIC, riwayat, nomor dokumen, dan path PDF tidak ditambahkan ke halaman publik. Beranda tetap membaca kolom publik lama yang eksplisit: nama/jenis/logo/website/deskripsi mitra aktif. Jangan menulis kontak internal pada kolom Deskripsi publik.
- Data PIC tidak membuat akun login. Pencabutan izin berlaku pada permintaan berikutnya dalam sesi aktif, termasuk URL langsung, simpan/upload, dan unduhan. Izin Mitra saja tidak membuka metadata atau berkas MoU.
- Cadangkan database, `uploads/`, dan `storage/mou/` bersama, lalu uji pemulihan. Lindungi cadangan dan pastikan konfigurasi hosting menyediakan larangan akses storage yang setara.

## File utama

- `database/updates/022_partner_profiles_mou.sql`: tabel tambahan, relasi, dan pembatas riwayat.
- `app/helpers/partners.php`: profil mitra, klaster, validasi, kompatibilitas simpan, dan versi edit.
- `panel/mitra/index.php`, `tambah.php`, `edit.php`, `form.php`, `simpan.php`, `hapus.php`: perluasan modul lama.
- `panel/mitra/detail.php`, `kampus.php`: profil/PIC dan direktori kampus.
- `app/helpers/mou.php`, `panel/mou/index.php`, `form.php`, `unduh.php`: status tanggal, riwayat, unggahan privat, dan unduhan.
- `app/helpers/permissions.php`, `panel/pengguna/form.php`, `panel/layouts/sidebar.php`, `staff-dashboard.php`: izin dan navigasi MoU/kampus.
- `.gitignore`: penyimpanan MoU privat tidak dilacak Git.
- `app/tests/partners-mou.php`: 22 pemeriksaan profil, negara/klaster, periode MoU, batas tengah malam WIB, dan path/izin.

## Pengujian

- 73 pemeriksaan HTTP Tahap 5 pada salinan aplikasi/skema dan data sintetis lulus: kompatibilitas sebelum migrasi, impor dua kali, klasifikasi lama tetap kosong, profil dalam/luar negeri, logo, PIC, direktori kampus, simpan lama yang mempertahankan profil baru, konflik versi, akses pengunjung/staf, CSRF, PDF valid/tidak valid, duplikasi dan pembersihan berkas gagal, perpanjangan dan berkas lama, tanggal/status/filter, kaitan lintas mitra, unduhan, privasi publik, larangan menghapus mitra dengan riwayat, relasi Kelas Industri, dan pencabutan izin pada sesi aktif. Tidak ada warning/error PHP.
- Regresi 77 pemeriksaan HTTP Tahap 4 dan 70 HTTP Tahap 3 lulus. Regresi CLI profil/MoU, Kelas Industri, jadwal PKL, PKL, akses, lowongan, tracer, dan peserta juga lulus.
- Seluruh 199 file PHP lolos sintaks; `git diff --check` lulus. Apache lokal mengembalikan 403 untuk `storage/mou/test.pdf`, `uploads/mou/test.pdf`, dan akses langsung helper internal.
- Database asli hanya dibaca untuk definisi skema, tidak dimigrasikan atau diisi data uji. Database, server, dan salinan aplikasi sementara dibersihkan. Uji HTTP memakai server PHP lokal; tampilan browser/perangkat fisik dan HTTPS belum diuji ulang pada tahap ini.
