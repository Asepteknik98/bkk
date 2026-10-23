# Capaian pembelajaran — tahap 3

Menu **Capaian Pembelajaran** mengelola dokumen materi/target pembelajaran per kelas industri dan angkatan (tahun masuk). Ini belum berupa nilai per siswa.

Admin dan staf BKK dapat menambah judul, deskripsi opsional, angkatan, kelas industri, serta PDF maksimal 5 MB. Pilihan angkatan dan kelas industri disarankan dari data siswa/dokumen yang sudah ada; nilai baru juga dapat diketik. Beberapa dokumen dapat menggunakan angkatan dan kelas industri yang sama.

Edit dapat mengubah metadata dan mengganti PDF. Tanpa unggahan baru, PDF lama dipertahankan. Setelah penggantian berhasil, berkas lama dibersihkan. Hapus menghapus dokumen dan berkasnya. Daftar dapat dicari berdasarkan judul/deskripsi dan difilter berdasarkan angkatan serta kelas industri.

Berkas berada di `storage/learning-documents`, yang ditutup dari akses HTTP langsung oleh konfigurasi Apache aplikasi. Unduh menggunakan endpoint dengan pemeriksaan login dan role. Belum ada akun siswa, sehingga akses siswa menunggu tahap penyediaan akun dan pemetaan ke data siswa.

Instalasi lama: jalankan `database/updates/010_learning_documents.sql` sekali. Instalasi baru: skema sudah disertakan pada SQL utama.

Validasi integrasi dilakukan melalui HTTP pada Apache/MySQL lokal: render halaman, unggah PDF, unduh, edit metadata, ganti PDF, filter gabungan, dan hapus untuk akses admin/staf. Akses anonim, akses langsung berkas, CSRF tidak valid, dan PDF palsu juga diperiksa. Akun, sesi, dan dokumen uji dibersihkan setelah pengujian.
