# Pemateri kelas — tahap 5

Menu **Pemateri Kelas** tersedia untuk admin dan staf BKK. Profil mencakup nama (wajib), instansi/perusahaan, bidang keahlian, kelas industri utama, dan profil singkat. CV dan foto opsional agar profil dapat dibuat sebelum berkas tersedia.

CV menggunakan PDF maksimal 5 MB. Foto menggunakan JPG, PNG, atau WebP maksimal 3 MB dengan dimensi maksimal 6.000 piksel per sisi dan total maksimal 16 juta piksel. Foto dibaca dan disimpan ulang sebagai gambar. CV/foto berada pada penyimpanan internal `storage/speakers`; akses berkas memerlukan login admin/staf.

Satu pemateri dapat mengampu beberapa kegiatan (maksimal 100 pilihan per profil), dan satu kegiatan dapat memiliki beberapa pemateri. Pilihan kegiatan berasal dari menu Absensi Kegiatan. Pada halaman kegiatan, nama pemateri mengarah ke profilnya. Profil menampilkan semua kegiatan yang diampu dan tautan download CV.

Kelas industri utama dapat diisi tanpa mengaitkan kegiatan. Filter kelas industri pada daftar mencakup kelas utama dan kelas dari kegiatan terkait. Pencarian mencakup nama, instansi, dan keahlian.

Edit tanpa unggahan mempertahankan berkas. Centang Hapus CV/Foto untuk menghapus berkas tertentu. Unggahan baru menggantikan berkas lama, termasuk jika Hapus dicentang. Penghapusan profil membersihkan berkas dan relasi pemateri; kegiatan, peserta, dan absensi tetap tersedia. Versi profil diperiksa untuk mencegah perubahan lama menimpa perubahan pengelola lain.

Instalasi lama: jalankan `database/updates/012_speakers.sql` setelah migrasi absensi 011. SQL utama sudah diperbarui untuk instalasi baru. Tidak ada akun peserta; profil dikelola admin/pengguna.

Uji integrasi lokal: `C:\xampp\php\php.exe tests/pemateri.php` dengan Apache/MySQL aktif. Pengujian mencakup upload/download, foto, penggantian dan penghapusan berkas, relasi kegiatan, filter kelas, versi profil, validasi berkas, CSRF, dan akses admin/staf/anonim. Akun, sesi, kegiatan, dan berkas sementara dibersihkan setelah pengujian. Jalankan hanya pada lingkungan lokal.
