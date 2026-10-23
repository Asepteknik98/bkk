# Download Excel — tahap 6

Admin dan staf BKK dapat mengunduh `.xlsx` dari tombol Download Excel pada masing-masing menu. Ekspor mengikuti filter yang telah diterapkan dan mencakup seluruh hasil, bukan hanya halaman yang sedang dibuka. Untuk mengubah hasil unduhan, ubah filter lalu klik Terapkan terlebih dahulu.

- **Data Peserta:** ID, nomor peserta, nama, kelas, jurusan, angkatan, kelas industri, telepon, email, tempat/tahun PKL, waktu pembuatan dan pembaruan. Pengurutan mengikuti pilihan daftar.
- **Absensi Kegiatan:** lembar Kegiatan memuat identitas kegiatan, tanggal, angkatan, kelas industri, deskripsi, jumlah peserta, dan jumlah tiap status. Lembar Absensi Peserta memuat setiap peserta, status, keterangan, pencatat, serta waktu pencatatan. Tombol di halaman detail hanya mengekspor kegiatan tersebut, tetap mencakup semua halaman peserta.
- **Rekap Peserta:** identitas lengkap peserta terbaru, jumlah kegiatan yang diikuti, Hadir, Izin, Sakit, Alpa, dan Belum diisi sesuai filter kegiatan. Belum diisi dihitung terpisah dari Alpa.
- **Pemateri:** lembar profil berisi identitas, instansi, keahlian, kelas industri utama, bio, metadata CV/foto, tautan berkas, dan jumlah kegiatan. Lembar kegiatan memuat seluruh relasi kegiatan dari pemateri yang lolos filter. Filter kelas industri mencakup kelas utama dan kelas dari kegiatan terkait.
- **Capaian Pembelajaran:** judul, deskripsi, angkatan, kelas industri, nama/ukuran file, pengunggah, waktu pembaruan, dan tautan download PDF.

CV, foto, dan PDF tidak disisipkan sebagai berkas dalam Excel; kolom tautan mengarah ke endpoint internal aplikasi dan tetap memerlukan login. Identitas NIS/nama/kelas pada rincian absensi mengikuti riwayat kegiatan, sedangkan kontak dan jurusan mengikuti data peserta terbaru.

Nomor peserta, telepon, dan nilai teks disimpan sebagai sel teks agar nol awal utuh. Teks yang menyerupai rumus juga disimpan sebagai teks. Rekap jumlah menggunakan sel angka. Baris judul ditebalkan, dibekukan, dan dilengkapi filter kolom. Hasil kosong tetap menghasilkan Excel dengan judul kolom.

Tidak diperlukan migrasi database baru. Uji lokal: `C:\xampp\php\php.exe tests/exports.php` dengan Apache/MySQL aktif. Pengujian memeriksa kelima jenis ekspor, isi XLSX, filter, pagination, leading zeros, teks rumus, hasil kosong, dan akses admin/staf/anonim. Data dan sesi sementara dibersihkan setelah pengujian; jalankan hanya pada lingkungan lokal.
