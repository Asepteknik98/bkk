# Rapor capaian pembelajaran

## Mengaktifkan

1. Cadangkan database dan unggahan.
2. Pilih database proyek yang sudah digunakan di phpMyAdmin, lalu impor **hanya** `database/updates/025_student_learning_reports.sql`. Jangan impor ulang SQL instalasi awal. Pembaruan ini menambah dua tabel rapor tanpa menghapus siswa atau dokumen lama.
3. Masuk panel sebagai Admin. Staf memerlukan izin Capaian Pembelajaran dan Data Peserta.

## Alur penggunaan

1. Tambahkan siswa melalui Data Peserta. Kelas, jurusan, dan angkatan menggunakan pilihan yang sudah dikonfigurasi.
2. Buka Rapor Pembelajaran, pilih Buat rapor, lalu pilih siswa dan isi periode/semester, tanggal, serta pembimbing.
3. Tambahkan kompetensi dan deskripsi. Nilai angka opsional, antara 0 dan 100 dengan maksimal dua desimal. Predikat dipilih pembimbing; tidak dihitung otomatis dari angka.
4. Simpan sebagai Draft selama belum lengkap. Status Final memerlukan predikat pada setiap kompetensi. Satu siswa hanya memiliki satu rapor untuk satu periode; gunakan Edit untuk memperbaikinya.
5. Buka detail, pilih Cetak / Simpan PDF. Pilih printer atau tujuan Simpan sebagai PDF pada browser. Rapor hanya dapat diakses pengelola yang memiliki izin.

Nama, kelas, jurusan, dan angkatan disalin saat rapor dibuat untuk menjaga riwayat. Perubahan identitas siswa kemudian tidak mengganti identitas pada rapor lama. Saat pengelola lain sudah mengubah rapor, simpan/hapus dari halaman lama ditolak dengan penjelasan agar pengguna memuat ulang.

Dokumen pembelajaran lama tetap tersedia melalui **Dokumen pendukung**. Dokumen tidak otomatis diubah menjadi nilai siswa.

## Formulir dan Excel tanpa nomor peserta

Nomor peserta tidak lagi diminta atau ditampilkan pada daftar, pilihan siswa, cetak, dan ekspor modul terkait. Identitas internal lama tetap dipertahankan untuk menjaga hubungan data; siswa baru mendapat kode internal acak yang tidak ditampilkan. NISN alumni tracer study merupakan data berbeda dan tidak diubah.

Unduh ulang template Excel Data Peserta sebelum impor. Template baru berisi sembilan kolom tanpa nomor peserta; template lama harus disesuaikan dengan template baru. Impor menambah baris siswa, sehingga jangan mengimpor berkas yang sama berulang kali. Siswa bernama sama tidak otomatis dianggap orang yang sama.

## Pengujian manual

- Buat siswa tanpa nomor peserta, kemudian buat rapor dengan dua kompetensi.
- Simpan Draft, periksa detail, lalu lengkapi predikat dan simpan Final.
- Coba nilai 101 dan periode yang sudah digunakan oleh siswa yang sama: harus ditolak.
- Buka rapor yang sama di dua tab; perubahan dari tab yang sudah kedaluwarsa harus ditolak.
- Periksa cetak/PDF, Dokumen pendukung, serta impor dan ekspor menggunakan template baru.
- Pastikan akun tanpa izin dan pengguna yang belum login tidak dapat membuka rapor/cetak.

Pengujian otomatis memakai data sintetis dan database sementara. SQL pembaruan belum diimpor ke database operasional oleh pengembang.
