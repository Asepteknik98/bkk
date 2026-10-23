# Pretest dan poster — tahap 7

Pada **Absensi Kegiatan → Isi/edit absensi → Kelola pretest dan poster**, admin/staf dapat menyimpan URL pretest dan poster per kegiatan. Keduanya memiliki centang Aktifkan yang terpisah dan harus disimpan untuk berlaku. Default keduanya nonaktif.

URL pretest harus http/https valid maksimal 2.048 karakter dan wajib diisi jika diaktifkan. Menonaktifkan pretest mempertahankan URL, menyembunyikan tombol pengguna, dan menghentikan pengalihan dari endpoint pretest aplikasi. Sistem tidak menyimpan jawaban atau nilai dari layanan pretest eksternal.

Poster berupa JPG, PNG, atau WebP maksimal 3 MB, dimensi maksimal 6.000 piksel per sisi dan 16 juta piksel total. Poster disimpan ulang sebagai gambar di `storage/event-posters`. Mengganti poster membersihkan berkas lama setelah penyimpanan berhasil. Menghapus poster juga menonaktifkannya. Saat nonaktif, pengelola tetap dapat melihat preview, tetapi endpoint poster publik menolak akses.

Menu publik **Kegiatan** (`kegiatan.php`) menampilkan kegiatan yang memiliki minimal satu item aktif, dengan filter angkatan, kelas industri, dan nama kegiatan. Halaman `kegiatan.php?id=ID` dapat dibagikan; jika kedua item nonaktif, halaman tersebut mengembalikan 404. Yang tampil hanya nama/tanggal kegiatan, angkatan, kelas industri, poster aktif, dan tombol pretest aktif. Data siswa, absensi, dan kontak tidak dipublikasikan.

Pengaturan menggunakan versi tersendiri agar perubahan lama tidak menimpa pengelola lain dan tidak mengganggu versi absensi. Berkas disimpan di penyimpanan internal; halaman dan endpoint publik menggunakan no-store agar perubahan on/off diperiksa kembali pada permintaan berikutnya.

Instalasi lama: jalankan `database/updates/013_event_resources.sql` setelah migrasi absensi 011. SQL utama memuat tabel untuk instalasi baru. Akses publik tidak memerlukan akun; pembatasan berdasarkan akun siswa merupakan tahap berikutnya.

Uji integrasi lokal: `C:\xampp\php\php.exe tests/event-resources.php` dengan Apache/MySQL aktif. Pengujian mencakup kombinasi on/off, validasi URL/poster, preview pengelola, akses publik langsung, filter, ganti/hapus berkas, konflik versi, CSRF, dan akses admin/staf/anonim. Akun, sesi, siswa, kegiatan, serta poster sementara dibersihkan setelah pengujian. Jalankan hanya pada lingkungan lokal.
