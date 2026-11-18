# Tahap 7 — Lowongan, jenis hubungan kerja, dan hasil alumni

## Penerapan database dan izin

1. Cadangkan database dan berkas portal.
2. Pilih database portal pada phpMyAdmin. Pastikan tabel lama `users`, `lowongan`, `lamaran`, `tracer_responses`, dan `alumni_form_links` Tahap 1 (018) sudah tersedia, beserta pembaruan profil pengguna/lamaran yang sebelumnya dipakai proyek.
3. Impor **hanya** `database/updates/024_jobs_alumni_outcomes.sql`. Skrip menambah `employment_types`, `vacancy_details`, `alumni_application_results`, dan `alumni_work_profiles`; tidak menghapus atau menebak isi data lama.
4. Tiga nama awal jenis hubungan kerja disediakan: Harian Lepas, PKWT, PKWTT. Penjelasan dan tautan rujukan sengaja kosong untuk diisi sekolah dari aturan resmi yang telah diperiksa. Impor ulang tidak menimpa perubahan jenis bawaan karena kode jenisnya tetap.
5. Admin mengakses semuanya. Izin staf diatur pada Pengguna → Edit:
   - **Lowongan Kerja** untuk konten lowongan.
   - **Jenis hubungan kerja** untuk mengelola data induk/penjelasan/rujukan. Izin ini tidak otomatis memberikan akses data pribadi alumni.
   - **Hasil seleksi & kerja alumni (privat)** + **Tracer Study** untuk daftar hasil, rekap dashboard, pencatatan awal lamaran, dan penandaan seleksi selesai.

Migrasi sudah diuji pada database sementara, belum diterapkan otomatis ke database asli. Sebelum migrasi, halaman lowongan lama dan dashboard tetap dapat dibuka; fitur baru memberikan petunjuk/503 sesuai endpoint. Penyimpanan lowongan yang memenuhi persyaratan baru memerlukan migrasi 024.

## Lowongan dan sumber rekrutmen

Modul/tabel `lowongan` lama dipakai kembali untuk perusahaan, posisi, lokasi, kualifikasi/persyaratan, pendidikan, deskripsi, batas pendaftaran, poster, dan publikasi. Perbaikan utama: `vacancy_input()` tidak lagi mengosongkan `tautan_pendaftaran`.

Lowongan baru atau yang diedit wajib mempunyai:

- URL http/https valid menuju halaman rekrutmen resmi perusahaan atau halaman resmi posisi yang ditawarkan, termasuk penyedia rekrutmen yang ditunjuk perusahaan.
- Konfirmasi pengelola bahwa sumber tersebut sudah diperiksa.
- Jenis hubungan kerja dari data induk yang tersedia.

Validasi sintaks URL tidak membuktikan kepemilikan situs. Pengelola memeriksa hubungan perusahaan dan halaman rekrutmen sebelum menyimpan/menerbitkan. Aplikasi tidak mengambil situs eksternal atau menganggap sebuah URL resmi hanya berdasarkan nama domain. Identitas pengelola dan waktu konfirmasi disimpan pada `vacancy_details`.

Daftar/detail published tetap dapat dibuka pengunjung tanpa login. Lowongan draft tetap privat. Tombol pendaftaran pada detail menuju rekrutmen perusahaan, dengan noopener/noreferrer; batas tanggal tetap sampai akhir hari WIB. Penjelasan dan tautan rujukan jenis hubungan kerja ditampilkan sebagai teks/tautan aman pada detail lowongan.

Lowongan lama tidak dipetakan ke jenis kontrak tertentu atau diberi URL rekaan. Kontennya tetap dapat dibaca, tetapi tombol rekrutmen resmi belum disediakan sampai tautan dan jenisnya dilengkapi serta diperiksa pengelola. Nama jenis disalin juga ke `jenis_pekerjaan` lama sebagai snapshot kompatibilitas filter/kartu lama; nama/penjelasan data induk saat ini tersedia pada detail.

Formulir berkas BKK lama tetap tersedia pada URL lamaran lama, dengan penjelasan bahwa pengiriman ke BKK tidak otomatis mengirim lamaran ke perusahaan. Pendaftaran perusahaan mengikuti URL rekrutmen resmi. Data/berkas lamaran lama tidak dihapus dan tidak otomatis dihubungkan ke alumni berdasarkan kecocokan nama/email.

Lowongan yang sudah dipakai pencatatan hasil alumni dilindungi dari hapus. Ubah ke Draft jika perlu menghentikan tampilannya tanpa memutus riwayat.

## Jenis hubungan kerja

Menu **Jenis hubungan kerja** menyediakan tambah/edit nama, penjelasan, URL rujukan aturan resmi, serta status aktif/nonaktif. Penjelasan maksimal 10.000 karakter, URL harus http/https valid. Tidak mengisi uraian hukum atau nomor aturan yang belum diberikan sekolah.

Nama tidak boleh duplikat. Jenis baru mendapat kode stabil; kode bawaan tetap sama meskipun nama diperbarui. Gunakan Nonaktif untuk menghentikan pemilihan baru, bukan menghapus jenis yang dipakai riwayat. Jenis nonaktif tetap dapat dipertahankan pada lowongan/profil kerja yang sudah memakai jenis itu, dan riwayat tetap dapat dibaca. Perubahan memakai POST+CSRF, transaksi, dan pemeriksaan versi.

## Tautan alumni Tahap 1

Tidak membuat akun alumni atau sistem token baru. Pengelola memakai penerbitan/pencabutan tautan yang sudah tersedia di `panel/tracer-study/tautan.php`. URL tetap `tracer-study/alumni.php?token=...`, terikat ke satu catatan alumni, memiliki kedaluwarsa/pencabutan, dan sekali pakai seperti Tahap 1.

Setelah tautan dibuka, formulir tracer menyediakan tautan **Catat lamaran, hasil seleksi, dan status kerja** menuju `tracer-study/karier.php`. Alumni dapat memilih formulir tracer untuk pembaruan kegiatan/pekerjaan saja, atau formulir karier untuk satu lamaran dan status kerjanya. Satu penyimpanan berhasil menggunakan tautan; pengelola membuat tautan baru untuk pembaruan/lamaran berikutnya. Membuka formulir lain memperbarui kunci formulir; muat ulang formulir yang akan dikirim jika tab lama ditolak.

ID/nama/NISN pemilik berasal dari database, bukan POST. Daftar lamaran dan profil kerja yang dapat dibuka oleh tautan hanya milik `tracer_id` yang terikat. Rekaman dengan NISN sama tidak otomatis diberikan hak saling membaca/mengubah melalui tautan. Kunci formulir diikat ke token agar tab lama tidak berpindah ke alumni lain setelah browser membuka tautan berbeda.

## Pencatatan lamaran dan hasil seleksi

Pengelola membuka **Hasil alumni → Catat/kelola lamaran**, memilih alumni dan lowongan published, lalu menyimpan pencatatan awal. Atau alumni memilih lowongan pada formulir karier pribadinya. Ini merupakan pencatatan BKK; tidak otomatis mendaftarkan alumni ke perusahaan. Lowongan published yang sudah melewati batas pendaftaran tetap dapat dipilih untuk mencatat lamaran historis.

Tabel `lamaran` lama dipakai untuk data lamaran dan snapshot perusahaan/posisi. Tabel tambahan mengikatnya ke satu alumni dan lowongan. Satu pasangan alumni/lowongan tidak membuat lamaran ganda ketika dicatat ulang. Tidak menyalin berkas lama atau mengarang email/alamat yang tidak diketahui. Tidak mengaktifkan langganan WhatsApp otomatis.

| Status | Makna |
|---|---|
| Belum Mengisi | Belum ada laporan hasil; atau seleksi ditandai selesai tetapi hasil akhir belum dilaporkan |
| Dalam Proses | Alumni menyatakan proses belum selesai |
| Lolos | Alumni melaporkan hasil lolos seleksi |
| Tidak Lolos | Alumni melaporkan hasil tidak lolos seleksi |

Pengelola dapat menandai proses seleksi selesai tanpa mengarang hasil alumni. Batas pendaftaran lowongan tidak dianggap tanggal selesai seleksi. Jika pengelola sudah menandai selesai, alumni tidak dapat menghindari pengisian hasil akhir dengan melepas checkbox atau mengirim Dalam Proses. Jika alumni sendiri menyatakan selesai, Lolos/Tidak Lolos juga wajib. Memilih hasil akhir otomatis menandai seleksi selesai.

Belum Mengisi merupakan status awal/menunggu; bukan pilihan hasil akhir alumni. Validasi gagal tidak menghabiskan token. Hasil hanya diperbarui setelah seluruh validasi lamaran dan status kerja lolos dalam transaksi yang sama. Hasil akhir dipetakan ke status lamaran lama untuk kompatibilitas, tetapi handler status lamaran umum tidak boleh menimpanya; hasil diperbarui melalui tautan alumni.

## Status kerja dan mulai bekerja

Alumni memilih **Belum mulai bekerja** atau **Sudah mulai bekerja**. Jika sudah mulai, wajib mengisi perusahaan tempat bekerja, posisi, tanggal mulai yang valid dan tidak mendatang, serta jenis hubungan kerja. Perusahaan/posisi kerja dapat berbeda dari lowongan yang hasilnya sedang dilaporkan, karena alumni mungkin bekerja melalui jalur lain.

Formulir tracer pribadi Tahap 1 juga menyediakan rincian ini ketika migrasi 024 sudah ada. Untuk status kegiatan Bekerja, perusahaan/posisi memakai bagian data kerja yang sama dan tidak diminta dua kali. Data tersebut disimpan ke tracer dan profil kerja dalam satu transaksi. Formulir tracer umum lama tetap tersedia; pernyataan Bekerja tanpa rincian awal bekerja tidak otomatis meningkatkan keterserapan.

Profil kerja adalah status terakhir yang dilaporkan, bukan riwayat kontrak lengkap. Mengubah ke Belum mulai bekerja menghentikan penghitungan kerja saat ini, sambil mempertahankan kolom perusahaan/posisi/tanggal/jenis terakhir pada penyimpanan. Memasukkan pekerjaan berikutnya memperbarui profil kerja tersebut. Tidak ada penghapusan data tracer atau hasil seleksi lama oleh migrasi.

Tautan karier memerlukan persetujuan pemrosesan, CSRF, kecocokan formulir/token, serta pemeriksaan ulang pemilik dan kedaluwarsa di server. Transaksi menyimpan hasil/profil kerja dan menandai token digunakan. Tautan tidak memberi akses dashboard, dan ID lamaran dari alumni lain ditolak.

## Rekap otomatis pada dashboard internal

Admin dan staf berizin Hasil alumni+Tracer Study mendapat tabel nama, jurusan/tahun, hasil per lamaran, dan status kerja yang dilaporkan. Bagian yang sama tersedia di menu Hasil alumni, dengan filter tahun lulus dan pagination. Penghitungan membaca database setiap kali dashboard dibuka/dimuat ulang, tanpa pengubahan angka rekap manual.

**Keterserapan = jumlah alumni tercatat yang sudah mulai bekerja / jumlah alumni tercatat pada filter × 100%.** Angka pembilang hanya memasukkan status sudah bekerja dengan perusahaan, posisi, jenis, serta tanggal mulai terisi valid dan tidak mendatang. Lolos seleksi saja tidak masuk pembilang. Pernyataan tracer lama Bekerja tanpa tanggal mulai/jenis lengkap juga belum masuk.

Jumlah **alumni Lolos seleksi** dihitung terpisah: satu alumni yang mempunyai minimal satu hasil Lolos, bukan jumlah lamaran Lolos. Alumni dengan beberapa lamaran Lolos tetap dihitung satu kali. Alumni yang bekerja melalui jalur lain dapat masuk keterserapan meskipun tidak memiliki hasil Lolos dalam lowongan portal.

Deduplicasi rekap memakai NISN terisi, mempertahankan nol awal; tanpa NISN setiap catatan tracer dihitung terpisah. Pastikan NISN benar sebelum menggunakan rekap. Ini bukan pencocokan identitas otomatis dan tidak menggabungkan/menghapus catatan lama. Nama/jurusan/tahun ditampilkan dari catatan terbaru dalam kelompok NISN; seluruh hasil lamaran kelompok tetap ditelusuri melalui pemilik catatannya. Profil kerja paling baru dilaporkan pada kelompok tersebut menentukan status kerja rekap.

Penyebut adalah alumni yang **tercatat pada tracer**, bukan klaim total seluruh lulusan sekolah yang belum terdata. Batas penghitungan ini ditampilkan pada dashboard. Filter tahun memakai tahun pada catatan alumni terbaru. Tabel siswa/PKL yang masih aktif bersekolah tidak dimasukkan sebagai populasi alumni.

## Hak akses dan kompatibilitas

- Nama/hasil/status kerja hanya tampil pada dashboard dan modul internal yang berizin. Tidak ditambahkan ke lowongan publik, pencarian publik, atau beranda.
- Izin Lowongan, Jenis hubungan kerja, atau Lamaran saja tidak membuka rekap hasil alumni.
- Lamaran yang sudah terikat hasil alumni juga disaring/diperiksa pada daftar, notifikasi, detail, dan unduhan berkas lamaran lama. Izin hasil-alumni+tracer-study tetap diwajibkan agar URL lama tidak menjadi jalan pintas. Penjagaan hubungan lamaran tetap bekerja walaupun tabel profil kerja baru tidak tersedia.
- Lamaran umum yang tidak dihubungkan eksplisit ke alumni tetap memakai alur lama. Tidak menebak relasi berdasarkan nama/email.
- Teks di-escape, URL divalidasi, status/formulir memakai POST+CSRF, dan data pribadi tidak disimpan pada URL. Pencabutan izin berlaku pada permintaan berikutnya dalam sesi aktif.

## File utama

- `database/updates/024_jobs_alumni_outcomes.sql`: tabel tambahan dan nama jenis awal.
- `app/helpers/employment.php`, `panel/hubungan-kerja/index.php`: data induk jenis, penjelasan, rujukan, dan status.
- `app/helpers/vacancies.php`, `panel/lowongan/form.php`, `hapus.php`, `lowongan-kerja/detail.php`, `lamar.php`: sumber rekrutmen wajib, jenis terkelola, publikasi, dan kompatibilitas riwayat.
- `app/helpers/careers.php`: lamaran terikat alumni, validasi hasil/kerja, transaksi token, rekap, dan penjagaan akses lama.
- `tracer-study/karier.php`, perubahan `alumni.php`, `alumni-simpan.php`, `formulir.php`, dan `app/helpers/alumni-links.php`: penggunaan tautan Tahap 1 dan rincian pekerjaan.
- `panel/hasil-alumni/index.php`, `lamaran.php`, `app/layouts/career-dashboard.php`, `career-work-fields.php`: pengelolaan awal, daftar nama/hasil, dan rekap internal.
- `panel/index.php`, `panel/layouts/navbar.php`, `sidebar.php`, `staff-dashboard.php`, `panel/lamaran/index.php`, `detail.php`, `berkas.php`, `app/helpers/permissions.php`, `panel/pengguna/form.php`: navigasi/izin dan perlindungan jalur lama.
- `app/tests/careers.php`: regresi URL, sumber resmi, jenis, hasil akhir wajib, rincian kerja, dan pemetaan izin.

## Pengujian

- 86 pemeriksaan HTTP Tahap 7 pada salinan aplikasi/skema dan data sintetis lulus: kondisi sebelum migrasi, tautan Tahap 1 lama sebelum migrasi, impor ulang/seed, master jenis/rujukan, konfirmasi sumber lowongan, publik tanpa login, penggunaan tabel lamaran lama, status awal/final, keharusan hasil saat selesai, rincian mulai kerja, Lolos terpisah, deduplikasi aplikasi/NISN, tahun/populasi, perubahan laporan kerja terakhir, penolakan tanggal mendatang, token sekali pakai/expiry/pemilik/formulir lintas tab, akses dashboard, pencabutan izin, penolakan lewat URL/handler lama, perlindungan kondisi migrasi parsial, serta data publik tanpa hasil/kontak alumni. Tidak ada warning/error PHP.
- Regresi 86 pemeriksaan HTTP Tahap 6 dan 73 HTTP Tahap 5 lulus. Regresi CLI karier, Sarasehan/ide, mitra/MoU, Kelas Industri, jadwal PKL, PKL, akses, lowongan, tracer, dan peserta lulus.
- Seluruh 221 file PHP lolos sintaks; `git diff --check` lulus. Database asli hanya dibaca untuk definisi skema, tidak dimigrasikan atau diisi data uji. Server/database/salinan aplikasi sementara dibersihkan.
- Pengujian HTTP memakai server PHP lokal. Kepemilikan situs rekrutmen/isi aturan harus diperiksa pengelola; tidak diuji dengan menghubungi situs perusahaan. Browser/perangkat fisik, HTTPS, dan perlombaan proses simultan belum diuji ulang langsung.
