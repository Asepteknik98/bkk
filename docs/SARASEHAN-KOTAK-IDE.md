# Tahap 6 — Sarasehan Industri dan Kotak Ide Industri

## Penerapan database dan izin

1. Cadangkan database dan berkas portal.
2. Pada phpMyAdmin, pilih database portal yang sudah digunakan. Pastikan `users`, `mitra`, dan tabel izin Tahap 1 (018) tersedia. Profil mitra Tahap 5 (022) dipakai untuk mengisi awal PIC jika sudah tersedia.
3. Impor **hanya** `database/updates/023_industry_forums_ideas.sql`. Skrip menambah `industry_forums`, `industry_forum_invitees`, `industry_idea_links`, dan `industry_ideas`, tanpa menghapus/mempublikasikan data lama. Dapat diimpor ulang pada skema hasil migrasi ini.
4. Admin mengakses seluruh modul. Untuk staf, pilih izin sesuai tugas pada Pengguna → Edit:
   - **Sarasehan Industri** + **Mitra** untuk jadwal, undangan, PIC, dan unduhan jadwal.
   - **Kotak Ide Industri (privat)** + **Mitra** untuk membaca usulan/kontak, mengubah status penanganan, dan mengelola tautan.
5. Menu Sarasehan Industri dan Kotak Ide Industri tersedia di sidebar. Detail perusahaan juga menyediakan tautan Kelola tautan Kotak Ide jika pengguna berizin.

Migrasi disiapkan dan diuji pada database sementara, belum diterapkan otomatis ke database asli. Daftar modul baru menampilkan petunjuk sebelum migrasi; endpoint operasional memberikan 503. Formulir mitra memberikan pemberitahuan umum bila layanan belum tersedia.

## Jadwal Sarasehan Industri

Catat nama kegiatan, tanggal, jam WIB, lokasi atau URL pertemuan, dan PIC sekolah (nama/kontak sebagai teks). Lokasi dan tautan boleh diisi bersama; minimal salah satu diperlukan. Tautan hanya menerima URL http/https valid.

Pilih satu atau beberapa mitra perusahaan, maksimal 200 per kegiatan. Kampus tidak muncul sebagai pilihan industri. Setiap mitra terpilih mempunyai nama PIC industri wajib dan kontak opsional. Nama/kontak terisi awal dari profil mitra jika tersedia. Setelah disimpan, PIC merupakan catatan undangan kegiatan; mengubahnya tidak menimpa profil PIC mitra. Tidak membuat akun guru/PIC atau mengirim undangan otomatis.

Pencarian mitra menampilkan maksimal 50 hasil ditambah seluruh undangan yang sudah tersimpan. Cari sebelum mengisi formulir dan simpan pilihan sebelum pencarian berikutnya. Undangan tersimpan di luar hasil pencarian tetap dipertahankan pada pilihan. Formulir memakai penanda kelengkapan untuk menolak data terpotong batas input server.

Daftar jadwal mendukung pencarian nama dan filter rentang tanggal. Detail menampilkan seluruh perusahaan undangan beserta PIC. Unduhan Excel memuat dua lembar: jadwal dan undangan/PIC, dengan satu snapshot database. Unduhan memerlukan izin Sarasehan+Mitra dan diberi no-store. Jadwal/PIC kegiatan berada di panel internal; tidak dipublikasikan pada portal umum.

Simpan memakai POST+CSRF, transaksi, dan versi edit untuk menolak formulir yang sudah tertinggal perubahan pengelola lain. Mitra harus tetap perusahaan terdaftar. Mitra yang direferensikan kegiatan atau tautan ide dilindungi dari penghapusan dan perubahan jenis menjadi kampus agar riwayat tetap utuh.

## Tautan khusus Kotak Ide

Pengelola dengan izin Kotak Ide+Mitra membuka **Kotak Ide Industri → Kelola tautan mitra**, lalu memilih perusahaan. Tautan baru hanya dibuat untuk mitra perusahaan aktif, bukan kampus. Masa berlaku 1–720 jam (default 48 jam), satu pengiriman per tautan.

Tautan ditampilkan hanya ketika dibuat. Browser mengubah alamat relatif menjadi alamat lengkap untuk disalin; konfigurasi `base_url` lengkap juga didukung. Bagikan secara pribadi kepada perwakilan perusahaan yang tepat. Tidak ada pengiriman email/WhatsApp otomatis.

Tindakan Buat tautan baru mencabut semua tautan perusahaan itu yang belum digunakan. Cabut dapat dilakukan tanpa menghapus usulan lama. Sepuluh metadata tautan terakhir ditampilkan dengan waktu dibuat, kedaluwarsa, digunakan, dan dicabut; token asli/hash tidak ditampilkan sebagai riwayat. Semua waktu tautan/ide disimpan sebagai UTC dan ditampilkan sebagai WIB.

Token acak 256 bit disimpan sebagai hash SHA-256. Saat penerima membuka `kotak-ide/?token=...`, server memeriksa perusahaan dan batas waktu, menyimpan kapabilitas formulir dalam sesi, lalu mengarahkan ke URL tanpa token. Kapabilitas terpisah dari login pengelola; tidak membuat akun atau memberikan `user_id` pengelola.

Tautan adalah rahasia pembawa: orang yang memilikinya dapat mengisi atas nama perusahaan yang dituju selama masih berlaku. Tautan bukan verifikasi identitas pribadi pengirim. Nama perusahaan ditampilkan sebagai konteks formulir; PIC/kontak yang tersimpan pada profil perusahaan tidak diisikan atau dibuka kepada penerima.

Tautan perusahaan nonaktif tidak dapat dipakai saat perusahaan nonaktif. Cabut tautan secara eksplisit jika tidak boleh dipakai lagi setelah perusahaan diaktifkan kembali. Tautan awal dapat tercatat di log akses server; batasi akses/masa simpan log dan gunakan HTTPS pada hosting.

## Formulir dan penyimpanan usulan

Formulir memuat perusahaan yang ditentukan server, nama pengirim, jabatan opsional, email/telepon, judul, isi usulan, dan persetujuan pemrosesan untuk evaluasi/tindak lanjut BKK. Minimal email atau telepon wajib diisi. Judul maksimal 190 karakter; isi maksimal 10.000 karakter. Tidak ada unggahan berkas.

Perusahaan dan ID mitra selalu berasal dari token dan data perusahaan di database, bukan nilai yang dikirim pengguna. Nama perusahaan saat pengiriman disimpan sebagai snapshot. Pengubahan kolom perusahaan atau ID pada POST tidak dapat mengalihkan usulan ke perusahaan lain.

Setiap formulir mempunyai kunci sesi yang terikat ke token saat dirender, selain CSRF. Membuka tautan perusahaan lain pada browser yang sama membatalkan kecocokan formulir lama; formulir lama ditolak, bukan disimpan sebagai perusahaan baru. Kunci formulir berlaku dua jam dan dapat diperbarui dengan memuat ulang formulir selama tautan masih berlaku.

Pengiriman memeriksa kembali validitas tautan, perusahaan aktif, kedaluwarsa/pencabutan, CSRF, kunci formulir, data, persetujuan, serta kolom anti-spam. Pembatas yang sudah ada pada proyek dipakai: 30 percobaan valid per IP dan 5 per tautan dalam 15 menit. Penolakan 429 tidak menyimpan usulan atau menghabiskan tautan. Identitas/IP mentah tidak dimasukkan ke nama berkas pembatas.

Transaksi dan row lock menyimpan usulan serta menandai tautan digunakan dalam satu operasi. Unique link_id mencegah duplikasi dari pengiriman bersamaan. Urutan lock sama dengan penerbitan/pencabutan agar perubahan tautan terserialisasi. Validasi gagal tidak menghabiskan tautan. Tautan digunakan/kedaluwarsa/dicabut memberikan 410 dan tidak membuka usulan sebelumnya.

Setelah sukses, kapabilitas dan draft formulir dihapus. Halaman konfirmasi hanya menyatakan usulan diterima dan tidak menampilkan judul, isi, identitas/kontak tersimpan, atau akses dashboard. Tidak ada daftar/detail publik atau riwayat yang dapat dibaca melalui tautan mitra.

## Kotak masuk dan privasi

Hanya Admin atau staf dengan izin Kotak Ide+Mitra dapat membuka daftar/detail. Daftar memuat judul, perusahaan, nama pengirim, waktu, serta status; isi lengkap dan email/telepon hanya diambil oleh endpoint detail yang juga berizin. Teks selalu di-escape, sehingga HTML kiriman tidak dijalankan.

Status penanganan tersedia: Baru, Ditinjau, Ditindaklanjuti, Arsip. Perubahan status menggunakan POST+CSRF dan versi; tidak mengubah identitas, isi usulan, atau kontak asli dan tidak mengirim balasan otomatis. Tidak ada fitur publikasi, hapus, atau ekspor usulan pada tahap ini.

Izin Mitra atau Sarasehan saja tidak membuka Kotak Ide, detail, kontak, atau penerbitan tautan. Status/isi tidak ditambahkan ke beranda, dashboard tanpa izin, profil mitra, maupun ekspor jadwal. Pencabutan izin berlaku pada permintaan berikutnya dalam sesi aktif. URL langsung dan handler POST mempunyai pemeriksaan server yang sama.

Halaman formulir, konfirmasi, dan penerbitan tautan menggunakan no-store/no-referrer; formulir dan konfirmasi juga noindex/nofollow. Pengguna yang sudah login sebagai pengelola tetap memiliki hak login lamanya jika membuka formulir di browser yang sama, tetapi tautan sendiri tidak pernah menambah hak pengelola. Formulir yang gagal validasi hanya menampilkan kembali input draft milik sesi tersebut, bukan membaca isi usulan yang sudah tersimpan.

## File utama

- `database/updates/023_industry_forums_ideas.sql`: empat tabel tambahan dan relasi.
- `app/helpers/industry-forums.php`, `panel/sarasehan/index.php`, `form.php`, `detail.php`, `unduh.php`: jadwal, mitra/PIC, validasi, dan Excel.
- `app/helpers/industry-ideas.php`: token, batas waktu, pengikatan formulir/perusahaan, penyimpanan usulan, dan status.
- `panel/ide-industri/index.php`, `detail.php`, `tautan.php`: kotak masuk privat dan pengelolaan tautan.
- `kotak-ide/index.php`, `simpan.php`, `berhasil.php`: formulir mitra dan konfirmasi umum.
- `app/helpers/permissions.php`, `panel/pengguna/form.php`, `panel/layouts/sidebar.php`, `staff-dashboard.php`: izin dan navigasi terpisah.
- `app/helpers/partners.php`, `panel/mitra/detail.php`, `hapus.php`: tautan dari mitra dan perlindungan referensi.
- `app/tests/industry-forums-ideas.php`: regresi validasi jadwal/undangan, tautan, formulir, kontak, identitas, dan pemetaan izin.

## Pengujian

- 86 pemeriksaan HTTP Tahap 6 pada salinan kode/skema dan data sintetis lulus: impor dua kali, kondisi migrasi belum tersedia, jadwal luring/daring, beberapa mitra/PIC, filter, referensi, konflik versi, CSRF, unduhan, izin terpisah, penerbitan/hash token, perusahaan aktif, kedaluwarsa/pencabutan/rotasi, token sekali pakai, binding perusahaan/formulir lintas tab, pengiriman, validasi/persetujuan, anti-spam, konfirmasi tanpa data privat, penanganan status, larangan mengubah isi lewat handler status, dan tidak adanya usulan/kontak pada halaman/ekspor lain. Tidak ada warning/error PHP.
- Regresi 73 pemeriksaan HTTP Tahap 5 dan 77 HTTP Tahap 4 lulus. Regresi CLI Sarasehan/ide, mitra/MoU, Kelas Industri, jadwal PKL, PKL, akses, lowongan, tracer, dan peserta lulus.
- Seluruh 212 file PHP lolos sintaks; `git diff --check` lulus. Database asli hanya dibaca untuk definisi skema, tidak dimigrasikan atau diisi data uji. Server/database/salinan aplikasi sementara dibersihkan.
- Uji HTTP memakai server PHP lokal. Batas 429 lintas proses, perlombaan simultan proses, HTTPS, dan tampilan perangkat fisik belum diuji ulang langsung pada tahap ini; pengiriman ulang, perubahan tautan setelah formulir dibuka, dan pencabutan izin pada sesi aktif diuji melalui HTTP.
