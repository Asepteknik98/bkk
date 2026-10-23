# Akses pengguna/siswa — tahap 8

## Membuat akun

1. Admin atau staf membuka **Data Siswa → Akun siswa** pada siswa yang sesuai.
2. Isi username, email login unik, password 12–72 byte, konfirmasi, dan status akun.
3. Sampaikan kredensial kepada siswa. Password tidak ditampilkan setelah disimpan dan tidak disimpan sebagai teks biasa.
4. Siswa membuka **Login Siswa** pada portal atau `/siswa/`, lalu masuk memakai username/email dan password.

Satu siswa memiliki satu akun, dan satu akun terhubung ke satu siswa. Role siswa dipasang otomatis; form ini tidak dapat membuat akun admin/staf atau memindahkan akun ke siswa lain. Impor Excel siswa tidak otomatis membuat akun atau password bawaan. Email login dapat berbeda dari email dalam Data Siswa; perubahan identitas siswa tetap dikelola pengelola.

## Ruang siswa

- **Beranda:** kelompok siswa, jumlah kegiatan/materi, dan ringkasan absensi sendiri.
- **Kegiatan:** sesuai angkatan dan kelas industri siswa terbaru, termasuk kegiatan yang pretest/posternya belum dibuka. Pretest/poster hanya tersedia jika pengelola mengaktifkannya.
- **Capaian pembelajaran:** lihat/download PDF sesuai angkatan dan kelas industri terbaru.
- **Pemateri:** profil, foto, dan CV pemateri dengan kelas industri utama yang sesuai atau terkait kegiatan pada kelompok siswa. Profil tidak membuka daftar peserta atau data siswa lain.
- **Absensi saya:** hanya riwayat siswa yang login, termasuk riwayat kelompok sebelumnya. Absensi dicatat admin/staf dan tidak dapat diedit siswa.
- **Akun saya:** identitas pribadi dan perubahan password dengan verifikasi password saat ini. Perubahan password mempertahankan sesi saat ini dan membatalkan sesi lain.

Kelompok kosong tidak membuka data kelas yang belum ditentukan. Perubahan angkatan/kelas industri oleh pengelola langsung berlaku pada permintaan berikutnya; materi kelompok lama tidak lagi dapat diunduh dari ruang siswa, tetapi riwayat absensi sendiri dipertahankan.

## Pengelolaan dan pembatasan

Pengelola dapat mengubah identitas login, reset password, dan mengaktifkan/nonaktifkan akun melalui menu Akun siswa. Setiap perubahan akun dari pengelola membatalkan sesi lama. Halaman Pengguna admin menampilkan role Siswa dan mengarahkan Edit ke akun yang terhubung; role akun siswa tidak dapat diubah menggunakan form akun pengelola.

Login mengarahkan admin/staf ke panel dan siswa ke ruang siswa. Siswa tidak memiliki akses panel, ekspor pengelola, pengelolaan akun, atau pengubahan absensi. Unduhan siswa memeriksa kelompok di server; mengganti ID kegiatan, materi, pemateri, atau parameter kelompok tidak memperluas akses.

Halaman publik **Kegiatan**, poster, dan pretest yang diaktifkan pada tahap 7 tetap bersifat publik. Pembatasan kelompok pada tahap 8 berlaku untuk ruang siswa dan dokumen internal; data pribadi/absensi siswa tidak dipublikasikan.

## Instalasi dan validasi

Instalasi lama menjalankan `database/updates/014_student_accounts.sql` setelah migrasi siswa 009. SQL utama telah diperbarui untuk instalasi baru. Migrasi memperluas role tanpa mengubah password/akun pengelola yang sudah ada dan menambahkan tabel pemetaan siswa.

Uji integrasi lokal: `C:\xampp\php\php.exe tests/student-portal.php` dengan Apache/MySQL aktif. Pengujian menggunakan akun sementara, login HTTP asli, pemetaan akun, cakupan antarkelas/angkatan, unduhan, absensi pribadi, on/off pretest/poster, CSRF, perubahan password, reset, pencabutan sesi, dan akses panel. Data/berkas/sesi uji dibersihkan setelah selesai. Jalankan hanya pada lingkungan lokal; pengujian login menggunakan pembatas percobaan login yang sama dengan aplikasi.
