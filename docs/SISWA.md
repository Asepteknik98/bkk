# Data siswa — tahap 1

Menu **Data Siswa** dapat digunakan admin dan staf BKK untuk menambah, mengedit, mencari, dan mengimpor siswa. Data pribadi siswa hanya ditampilkan di panel yang memerlukan login.

Untuk instalasi lama, jalankan `database/updates/009_siswa.sql` sekali pada database aplikasi. Migrasi hanya menambahkan tabel baru, tanpa mengubah data lama.

Download template Excel dari menu Data Siswa, isi lembar pertama, lalu unggah kembali dalam format `.xlsx`. Format kolom NIS, NISN, dan telepon sebagai **Text** di Excel sebelum mengetik agar angka nol awal tetap utuh. Jangan gunakan rumus. Batas file 5 MB, maksimal 1.000 siswa per impor.

Kolom berurutan: `nis`, `nisn`, `nama`, `kelas`, `jurusan`, `angkatan`, `kelas_industri`, `telepon`, `email`, `tempat_pkl`, `tahun_pkl`.

Wajib: NIS, nama, kelas, angkatan. Angkatan sementara diartikan sebagai tahun masuk (4 digit). NIS unik; NISN opsional, jika diisi harus 10 digit. Tahun PKL opsional, misalnya `2025` atau `2025/2026`.

Impor menambahkan siswa baru. Seluruh impor dibatalkan jika ada baris tidak valid atau NIS sudah terdaftar. Untuk mengubah siswa yang sudah ada, gunakan Edit. Tidak ada penimpaan otomatis.

Belum ditemukan sumber data PKL atau akun siswa pada sistem awal. Data PKL lama dapat dipindahkan ke template untuk diimpor; penarikan langsung membutuhkan sumber data dan pemetaan kolomnya. Akses akun siswa, absensi, capaian pembelajaran, pemateri, ekspor lengkap, dan pretest/poster dikerjakan pada tahap berikutnya.

Pemeriksaan otomatis: `C:\xampp\php\php.exe tests/students.php`.
