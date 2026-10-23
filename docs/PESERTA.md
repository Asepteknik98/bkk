# Data Peserta

Menu **Data Peserta** digunakan admin dan pengguna untuk tambah/edit manual, impor Excel, pencarian, dan pengelompokan kegiatan. Peserta adalah catatan kegiatan dan tidak mempunyai akun login.

Unduh template melalui menu Data Peserta lalu unggah kembali sebagai `.xlsx`, maksimal 5 MB dan 1.000 peserta. Kolom template: `nis` (nomor peserta), `nama`, `kelas`, `jurusan`, `angkatan`, `kelas_industri`, `telepon`, `email`, `tempat_pkl`, `tahun_pkl`. Nomor peserta, nama, kelas, dan angkatan wajib diisi. Nomor peserta harus unik. Format nomor peserta dan telepon sebagai teks agar nol awal tersimpan.

Impor menambahkan data baru. Kesalahan baris atau nomor peserta duplikat membatalkan seluruh impor. Gunakan Edit untuk perubahan data lama. Filter angkatan/kelas industri dapat digabung dengan pencarian; urutan tersedia menurut nama, angkatan, atau kelas industri. Ekspor memuat kolom lengkap.

Skema data lama dipertahankan untuk kompatibilitas dan perlindungan data kegiatan. Tabel `siswa` serta kolom `nis`/`siswa_id` merupakan nama internal historis untuk data peserta dan relasi absensi; tidak memberikan akun atau akses login. Kolom historis lain dipertahankan tanpa ditampilkan sebagai fitur akun.

Instalasi baru menggunakan SQL utama atau `database/updates/009_participants.sql`. Penghapusan fitur login lama pada instalasi terpasang menggunakan migrasi 015 setelah pemeriksaan prasyarat. Lihat `PENGHAPUSAN-AKUN.md`.

Pemeriksaan: `C:\xampp\php\php.exe tests/participants.php`.
