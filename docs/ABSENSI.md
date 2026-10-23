# Absensi kegiatan — tahap 4

Menu **Absensi Kegiatan** tersedia bagi admin dan staf BKK. Buat kegiatan dengan nama, tanggal, angkatan (tahun masuk), kelas industri, dan keterangan opsional. Peserta diambil dari Data Peserta sesuai angkatan dan kelas industri; kegiatan tanpa peserta ditolak.

Identitas dan daftar peserta disalin saat kegiatan dibuat. Perubahan Data Peserta berikutnya tidak mengubah riwayat peserta kegiatan. Peserta baru setelah kegiatan dibuat tidak otomatis ditambahkan. Angkatan dan kelas industri kegiatan tidak dapat diganti; nama, tanggal, dan keterangannya dapat diedit.

Isi Hadir, Izin, Sakit, Alpa, atau biarkan Belum diisi. Setiap peserta memiliki keterangan maksimal 500 karakter. Simpan setiap halaman (25 peserta) sebelum pindah. Belum diisi tidak dihitung sebagai Alpa. Versi data diperiksa agar penyimpanan lama tidak menimpa perubahan pengelola lain; jika ada konflik, muat ulang halaman.

Daftar kegiatan dan rekap dapat difilter berdasarkan angkatan, kelas industri, dan nama kegiatan. Rekap per peserta mencakup jumlah kegiatan yang diikuti, tiap status, dan jumlah belum diisi. Identitas pada rekap mengikuti data peserta terbaru, sedangkan identitas pada kegiatan mengikuti riwayat saat pembuatan.

Instalasi lama: jalankan `database/updates/011_attendance.sql` setelah migrasi peserta 009. SQL utama sudah memuat tabel untuk instalasi baru. Absensi dicatat admin/pengguna. Peserta tidak memiliki akun login.

Validasi integrasi melalui Apache/MySQL lokal mencakup pembuatan kegiatan, kelompok tanpa peserta, 27 peserta dalam dua halaman, semua status, keterangan, edit kegiatan, riwayat peserta, rekap, filter, CSRF, akses anonim, dan penolakan versi lama. Akun, sesi, peserta, kegiatan, dan absensi sementara dibersihkan setelah pengujian.
