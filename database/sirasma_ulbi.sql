-- ========================================================
-- DATABASE SCHEMA & SEED DATA: SIRASMA ULBI
-- Sistem Reservasi Asrama Mahasiswa Berbasis Web
-- Universitas Logistik dan Bisnis Internasional (ULBI)
-- Mata Kuliah: Proyek 3 & Keamanan Sistem Informasi
-- Role: Admin & Mahasiswa (Access Control System)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `sirasma_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sirasma_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `pengumuman`;
DROP TABLE IF EXISTS `reservasi`;
DROP TABLE IF EXISTS `kamar`;
DROP TABLE IF EXISTS `periode`;
DROP TABLE IF EXISTS `asrama`;
DROP TABLE IF EXISTS `mahasiswa_profiles`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 1. Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa',
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(25) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Table structure for table `mahasiswa_profiles`
-- --------------------------------------------------------
CREATE TABLE `mahasiswa_profiles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL UNIQUE,
  `npm` VARCHAR(20) NOT NULL UNIQUE,
  `prodi` VARCHAR(100) NOT NULL,
  `jurusan` VARCHAR(100) DEFAULT 'Sekolah Teknologi Informasi',
  `angkatan` VARCHAR(10) DEFAULT '2024',
  `gender` ENUM('L', 'P') NOT NULL DEFAULT 'L',
  `tempat_lahir` VARCHAR(100) DEFAULT NULL,
  `tanggal_lahir` DATE DEFAULT NULL,
  `alamat_asal` TEXT DEFAULT NULL,
  `kota_asal` VARCHAR(100) DEFAULT NULL,
  `provinsi_asal` VARCHAR(100) DEFAULT NULL,
  `nama_ortu_wali` VARCHAR(100) DEFAULT NULL,
  `hp_ortu_wali` VARCHAR(30) DEFAULT NULL,
  `kontak_darurat` VARCHAR(50) DEFAULT NULL,
  `ktm_file` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Table structure for table `asrama`
-- --------------------------------------------------------
CREATE TABLE `asrama` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_asrama` VARCHAR(100) NOT NULL,
  `kategori_gender` ENUM('L', 'P', 'Campur') NOT NULL DEFAULT 'L',
  `lokasi` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `jumlah_lantai` INT(11) DEFAULT 4,
  `kapasitas_total` INT(11) DEFAULT 120,
  `fasilitas_gedung` TEXT DEFAULT NULL,
  `gambar` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Table structure for table `periode`
-- --------------------------------------------------------
CREATE TABLE `periode` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_periode` VARCHAR(100) NOT NULL,
  `tahun_akademik` VARCHAR(20) NOT NULL DEFAULT '2026/2027',
  `semester` VARCHAR(20) NOT NULL DEFAULT 'Ganjil',
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `status` ENUM('aktif', 'tutup') NOT NULL DEFAULT 'aktif',
  `keterangan` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Table structure for table `kamar`
-- --------------------------------------------------------
CREATE TABLE `kamar` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `asrama_id` INT(11) NOT NULL,
  `nomor_kamar` VARCHAR(50) NOT NULL,
  `lantai` INT(11) NOT NULL DEFAULT 1,
  `tipe_kamar` VARCHAR(100) NOT NULL DEFAULT 'Standar (4 Orang)',
  `kapasitas` INT(11) NOT NULL DEFAULT 4,
  `terisi` INT(11) NOT NULL DEFAULT 0,
  `tarif_per_semester` DECIMAL(12,2) NOT NULL DEFAULT 2250000.00,
  `tarif_per_tahun` DECIMAL(12,2) NOT NULL DEFAULT 4200000.00,
  `fasilitas_kamar` TEXT DEFAULT NULL,
  `status` ENUM('tersedia', 'penuh', 'pemeliharaan', 'nonaktif') NOT NULL DEFAULT 'tersedia',
  `gambar` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_kamar_asrama` FOREIGN KEY (`asrama_id`) REFERENCES `asrama` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Table structure for table `reservasi`
-- --------------------------------------------------------
CREATE TABLE `reservasi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_reservasi` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT(11) NOT NULL,
  `kamar_id` INT(11) NOT NULL,
  `periode_id` INT(11) NOT NULL,
  `tanggal_pengajuan` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rencana_checkin` DATE NOT NULL,
  `durasi_sewa` VARCHAR(50) NOT NULL DEFAULT '1 Semester',
  `total_biaya` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `dokumen_ktm` VARCHAR(255) DEFAULT NULL,
  `dokumen_pernyataan` VARCHAR(255) DEFAULT NULL,
  `bukti_pembayaran` VARCHAR(255) DEFAULT NULL,
  `status_pembayaran` ENUM('belum_bayar', 'menunggu_konfirmasi', 'lunas') NOT NULL DEFAULT 'belum_bayar',
  `status` ENUM('menunggu_verifikasi', 'disetujui', 'ditolak', 'dibatalkan', 'selesai') NOT NULL DEFAULT 'menunggu_verifikasi',
  `catatan_admin` TEXT DEFAULT NULL,
  `catatan_mahasiswa` TEXT DEFAULT NULL,
  `diverifikasi_oleh` INT(11) DEFAULT NULL,
  `tanggal_verifikasi` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_rsv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rsv_kamar` FOREIGN KEY (`kamar_id`) REFERENCES `kamar` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_rsv_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Table structure for table `audit_logs`
-- --------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `user_role` VARCHAR(50) DEFAULT NULL,
  `username` VARCHAR(100) DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Table structure for table `pengumuman`
-- --------------------------------------------------------
CREATE TABLE `pengumuman` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `judul` VARCHAR(255) NOT NULL,
  `isi` TEXT NOT NULL,
  `kategori` VARCHAR(50) NOT NULL DEFAULT 'Informasi',
  `tanggal` DATE NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DATA SEEDING (Pengguna & Data Awal)
-- Password Default:
-- Admin: admin123
-- Mahasiswa: mahasiswa123
-- ========================================================

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `full_name`, `phone`, `status`) VALUES
(1, 'admin', 'admin@ulbi.ac.id', '$2y$10$wE9q.F7tB1U6Y/xLdC0PFeZ6Lq9wKjJ3k4mQ0fK1t0uB9R9xYm6W2', 'admin', 'Pengelola Asrama ULBI', '081234567890', 'active'),
(2, 'hasan', 'hasan.zubeir@ulbi.ac.id', '$2y$10$3p4B5z5P6eL/3nF.p5I4k.f2iO1nQ6tH5rD0sE.YwY5xK6Z9P5m5q', 'mahasiswa', 'Hasan Zubeir Pohan', '082198765432', 'active'),
(3, 'zidan', 'zidan.ramadhan@ulbi.ac.id', '$2y$10$3p4B5z5P6eL/3nF.p5I4k.f2iO1nQ6tH5rD0sE.YwY5xK6Z9P5m5q', 'mahasiswa', 'Zidan Hairra Ramadhan', '081377889900', 'active'),
(4, 'sarah', 'sarah.putri@ulbi.ac.id', '$2y$10$3p4B5z5P6eL/3nF.p5I4k.f2iO1nQ6tH5rD0sE.YwY5xK6Z9P5m5q', 'mahasiswa', 'Sarah Amalia Putri', '085712348877', 'active');

INSERT INTO `mahasiswa_profiles` (`id`, `user_id`, `npm`, `prodi`, `jurusan`, `angkatan`, `gender`, `tempat_lahir`, `tanggal_lahir`, `alamat_asal`, `kota_asal`, `provinsi_asal`, `nama_ortu_wali`, `hp_ortu_wali`, `kontak_darurat`) VALUES
(1, 2, '714240045', 'D4 Teknik Informatika', 'Sekolah Teknologi Informasi', '2024', 'L', 'Medan', '2004-05-14', 'Jl. Dr. Mansyur No. 12', 'Medan', 'Sumatera Utara', 'Bapak Pohan', '081299990001', '081299990001 (Ayah)'),
(2, 3, '714240061', 'D4 Teknik Informatika', 'Sekolah Teknologi Informasi', '2024', 'L', 'Bandung', '2004-09-20', 'Jl. Gegerkalong Hilir No. 45', 'Bandung', 'Jawa Barat', 'Bapak Ramadhan', '081388880002', '081388880002 (Ayah)'),
(3, 4, '714240012', 'D4 Logistik Niaga Internasional', 'Sekolah Bisnis dan Manajemen', '2024', 'P', 'Jakarta', '2004-11-08', 'Jl. Tebet Barat Raya No. 19', 'Jakarta Selatan', 'DKI Jakarta', 'Ibu Lestari', '085677770003', '085677770003 (Ibu)');

INSERT INTO `asrama` (`id`, `nama_asrama`, `kategori_gender`, `lokasi`, `deskripsi`, `jumlah_lantai`, `kapasitas_total`, `fasilitas_gedung`) VALUES
(1, 'Gedung Asrama Putra (Tower Al-Fatih)', 'L', 'Kompleks Kampus ULBI, Jl. Sariasih No. 54, Sarijadi, Bandung (Zona Barat)', 'Gedung asrama khusus mahasiswa putra dengan lingkungan asri, aman, dan kondusif untuk mendukung kegiatan akademik serta pembentukan karakter.', 4, 120, 'WiFi Kampus High Speed, Musholla Putra, Dapur Bersama di Setiap Lantai, Ruang Belajar Mandiri & Diskusi, Lapangan Olahraga, Area Parkir Motor, Keamanan & CCTV 24 Jam, Layanan Kebersihan Koridor, Dispenser Air Minum'),
(2, 'Gedung Asrama Putri (Tower An-Nisa)', 'P', 'Kompleks Kampus ULBI, Jl. Sariasih No. 54, Sarijadi, Bandung (Zona Timur)', 'Gedung asrama khusus mahasiswi putri dengan akses gerbang khusus berpintu magnetik, ruang bersama yang nyaman, dan petugas pengawas ramah.', 4, 120, 'WiFi Kampus High Speed, Musholla Putri, Dapur Bersama Modern, Ruang Komunal Santai, Ruang Belajar Khusus, Akses Kartu RFID, Keamanan & Petugas Jaga Putri 24 Jam, CCTV, Mesin Cuci Koin, Kantin Asrama');

INSERT INTO `periode` (`id`, `nama_periode`, `tahun_akademik`, `semester`, `tanggal_mulai`, `tanggal_selesai`, `status`, `keterangan`) VALUES
(1, 'Semester Ganjil 2026/2027', '2026/2027', 'Ganjil', '2026-09-01', '2027-01-31', 'aktif', 'Periode reguler perkuliahan semester ganjil tahun akademik 2026/2027'),
(2, 'Tahun Akademik Penuh 2026/2027', '2026/2027', 'Penuh (1 Tahun)', '2026-09-01', '2027-08-31', 'aktif', 'Periode sewa 1 tahun penuh dengan potongan biaya khusus'),
(3, 'Semester Genap 2026/2027', '2026/2027', 'Genap', '2027-02-01', '2027-06-30', 'aktif', 'Periode reservasi semester genap 2026/2027');

INSERT INTO `kamar` (`id`, `asrama_id`, `nomor_kamar`, `lantai`, `tipe_kamar`, `kapasitas`, `terisi`, `tarif_per_semester`, `tarif_per_tahun`, `fasilitas_kamar`, `status`) VALUES
(1, 1, 'A-101', 1, 'Deluxe (2 Orang)', 2, 1, 3500000.00, 6500000.00, '2 Ranjang Single Springbed, 2 Meja Belajar + Kursi Ergonomis, 2 Lemari Pakaian 2 Pintu, AC Split, Kamar Mandi Dalam (Shower & Kloset Duduk), Jendela Menghadap Taman', 'tersedia'),
(2, 1, 'A-102', 1, 'Standar (4 Orang)', 4, 2, 2250000.00, 4200000.00, '2 Ranjang Tingkat Berkualitas, 4 Meja Belajar, 4 Lemari Pribadi Bersekat, Kipas Angin Dinding, Rak Sepatu, Kamar Mandi Luar Khusus Koridor Lantai 1', 'tersedia'),
(3, 1, 'A-201', 2, 'Standar (4 Orang)', 4, 4, 2250000.00, 4200000.00, '2 Ranjang Tingkat, 4 Meja Belajar, 4 Lemari, Kipas Angin Dinding, Kamar Mandi Luar', 'penuh'),
(4, 1, 'A-202', 2, 'Deluxe (2 Orang)', 2, 0, 3500000.00, 6500000.00, '2 Ranjang Single, 2 Meja Belajar, 2 Lemari Pakaian, AC Split, Kamar Mandi Dalam', 'tersedia'),
(5, 1, 'A-301', 3, 'Single Room (1 Orang)', 1, 0, 5000000.00, 9500000.00, '1 Ranjang Queen Springbed, 1 Meja Belajar Eksekutif, 1 Lemari Besar, AC Split, Kamar Mandi Dalam, Kulkas Mini, Balkon Pribadi', 'tersedia'),
(6, 1, 'A-401', 4, 'Standar (4 Orang)', 4, 0, 2250000.00, 4200000.00, 'Sedang dalam proses pengecatan dan perawatan sanitasi rutin.', 'pemeliharaan'),
(7, 2, 'B-101', 1, 'Deluxe (2 Orang)', 2, 1, 3500000.00, 6500000.00, '2 Ranjang Single Springbed, 2 Meja Rias/Belajar, 2 Lemari Pakaian, AC Split, Kamar Mandi Dalam (Water Heater), Jendela Pencahayaan Alami', 'tersedia'),
(8, 2, 'B-102', 1, 'Standar (4 Orang)', 4, 3, 2250000.00, 4200000.00, '2 Ranjang Tingkat, 4 Meja Belajar, 4 Lemari Pribadi, Kipas Angin Gantung, Kamar Mandi Luar Lantai 1', 'tersedia'),
(9, 2, 'B-201', 2, 'Standar (4 Orang)', 4, 0, 2250000.00, 4200000.00, '2 Ranjang Tingkat, 4 Meja Belajar, 4 Lemari Pribadi, Kipas Angin, Kamar Mandi Luar', 'tersedia'),
(10, 2, 'B-301', 3, 'Deluxe (2 Orang)', 2, 0, 3500000.00, 6500000.00, '2 Ranjang Single, 2 Meja Belajar, 2 Lemari Pakaian, AC Split, Kamar Mandi Dalam', 'tersedia');

INSERT INTO `reservasi` (`id`, `kode_reservasi`, `user_id`, `kamar_id`, `periode_id`, `tanggal_pengajuan`, `rencana_checkin`, `durasi_sewa`, `total_biaya`, `status_pembayaran`, `status`, `catatan_admin`, `catatan_mahasiswa`, `diverifikasi_oleh`, `tanggal_verifikasi`) VALUES
(1, 'RSV-2026-001', 2, 1, 1, '2026-10-28 10:15:00', '2026-09-01', '1 Semester', 3500000.00, 'lunas', 'disetujui', 'Berkas administrasi dan pembayaran lunas telah diverifikasi. Kunci kamar dapat diambil di kantor pengelola asrama.', 'Mohon ranjang bagian dekat jendela jika memungkinkan.', 1, '2026-10-29 09:30:00'),
(2, 'RSV-2026-002', 3, 2, 1, '2026-11-02 14:20:00', '2026-11-15', '1 Semester', 2250000.00, 'belum_bayar', 'menunggu_verifikasi', NULL, 'Pengajuan kamar tipe standar untuk perkuliahan semester ganjil.', NULL, NULL),
(3, 'RSV-2026-003', 4, 7, 1, '2026-11-01 11:00:00', '2026-09-01', '1 Semester', 3500000.00, 'menunggu_konfirmasi', 'disetujui', 'Permohonan disetujui. Menunggu konfirmasi pembayaran via transfer bank.', 'Memilih kamar asrama putri lantai 1.', 1, '2026-11-01 16:45:00');

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `kategori`, `tanggal`, `is_active`) VALUES
(1, 'Pembukaan Pendaftaran Reservasi Asrama ULBI Semester Ganjil 2026/2027', 'Diberitahukan kepada seluruh mahasiswa dan calon mahasiswa baru Universitas Logistik dan Bisnis Internasional bahwa reservasi asrama kampus telah resmi dibuka. Kuota kamar terbatas, mahasiswa diimbau segera melengkapi profil dan berkas administrasi.', 'Penting', '2026-10-15', 1),
(2, 'Jadwal Check-in dan Pengambilan Kunci Asrama Mahasiswa', 'Bagi mahasiswa yang permohonan reservasinya telah berstatus DISETUJUI dan telah melunasi pembayaran sewa, proses check-in dan serah terima kunci kamar dapat dilakukan setiap hari kerja (Senin-Jumat) pukul 08.00 - 16.00 WIB di Kantor Pengelola Asrama Gedung Al-Fatih Lantai 1 dengan membawa kartu identitas (KTM/KTP) serta bukti reservasi fisik.', 'Jadwal', '2026-10-25', 1),
(3, 'Tata Tertib Kehidupan Asrama dan Larangan Membawa Alat Elektronik Tertentu', 'Demi keselamatan dan kenyamanan bersama, penghuni asrama wajib mematuhi jam malam (pukul 22.00 WIB) serta dilarang membawa peralatan elektronik berdaya tinggi (seperti kompor listrik/AC portabel) tanpa izin tertulis dari pengelola asrama.', 'Informasi', '2026-11-01', 1);

INSERT INTO `audit_logs` (`id`, `user_id`, `user_role`, `username`, `action`, `description`, `ip_address`) VALUES
(1, 1, 'admin', 'admin', 'SYSTEM_INIT', 'Inisialisasi sistem SIRASMA ULBI dan pembuatan data master asrama & kamar.', '127.0.0.1'),
(2, 2, 'mahasiswa', 'hasan', 'SUBMIT_RESERVATION', 'Mahasiswa Hasan Zubeir Pohan (NPM: 714240045) mengajukan reservasi kamar A-101 (Kode: RSV-2026-001).', '127.0.0.1'),
(3, 1, 'admin', 'admin', 'APPROVE_RESERVATION', 'Admin menyetujui reservasi kode RSV-2026-001 untuk kamar A-101.', '127.0.0.1'),
(4, 3, 'mahasiswa', 'zidan', 'SUBMIT_RESERVATION', 'Mahasiswa Zidan Hairra Ramadhan (NPM: 714240061) mengajukan reservasi kamar A-102 (Kode: RSV-2026-002).', '127.0.0.1');
