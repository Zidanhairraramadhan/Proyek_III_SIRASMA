<?php
/**
 * SIRASMA ULBI - Database Installer & Data Seeder
 * Mendukung instalasi ke SQLite dan MySQL
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function init_sqlite_schema($pdo) {
    // 1. users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'mahasiswa',
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(25) DEFAULT NULL,
        avatar VARCHAR(255) DEFAULT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 2. mahasiswa_profiles
    $pdo->exec("CREATE TABLE IF NOT EXISTS mahasiswa_profiles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        npm VARCHAR(20) NOT NULL UNIQUE,
        prodi VARCHAR(100) NOT NULL,
        jurusan VARCHAR(100) DEFAULT 'Sekolah Teknologi Informasi',
        angkatan VARCHAR(10) DEFAULT '2024',
        gender VARCHAR(5) NOT NULL DEFAULT 'L',
        tempat_lahir VARCHAR(100) DEFAULT NULL,
        tanggal_lahir DATE DEFAULT NULL,
        alamat_asal TEXT DEFAULT NULL,
        kota_asal VARCHAR(100) DEFAULT NULL,
        provinsi_asal VARCHAR(100) DEFAULT NULL,
        nama_ortu_wali VARCHAR(100) DEFAULT NULL,
        hp_ortu_wali VARCHAR(30) DEFAULT NULL,
        kontak_darurat VARCHAR(50) DEFAULT NULL,
        ktm_file VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );");

    // 3. asrama
    $pdo->exec("CREATE TABLE IF NOT EXISTS asrama (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_asrama VARCHAR(100) NOT NULL,
        kategori_gender VARCHAR(10) NOT NULL DEFAULT 'L',
        lokasi VARCHAR(255) NOT NULL,
        deskripsi TEXT DEFAULT NULL,
        jumlah_lantai INTEGER DEFAULT 4,
        kapasitas_total INTEGER DEFAULT 120,
        fasilitas_gedung TEXT DEFAULT NULL,
        gambar VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 4. kamar
    $pdo->exec("CREATE TABLE IF NOT EXISTS kamar (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        asrama_id INTEGER NOT NULL,
        nomor_kamar VARCHAR(50) NOT NULL,
        lantai INTEGER NOT NULL DEFAULT 1,
        tipe_kamar VARCHAR(100) NOT NULL DEFAULT 'Standar (4 Orang)',
        kapasitas INTEGER NOT NULL DEFAULT 4,
        terisi INTEGER NOT NULL DEFAULT 0,
        tarif_per_semester REAL NOT NULL DEFAULT 2250000,
        tarif_per_tahun REAL NOT NULL DEFAULT 4200000,
        fasilitas_kamar TEXT DEFAULT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'tersedia',
        gambar VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (asrama_id) REFERENCES asrama(id) ON DELETE CASCADE
    );");

    // 5. periode
    $pdo->exec("CREATE TABLE IF NOT EXISTS periode (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_periode VARCHAR(100) NOT NULL,
        tahun_akademik VARCHAR(20) NOT NULL DEFAULT '2026/2027',
        semester VARCHAR(20) NOT NULL DEFAULT 'Ganjil',
        tanggal_mulai DATE NOT NULL,
        tanggal_selesai DATE NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'aktif',
        keterangan TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 6. reservasi
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservasi (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kode_reservasi VARCHAR(50) NOT NULL UNIQUE,
        user_id INTEGER NOT NULL,
        kamar_id INTEGER NOT NULL,
        periode_id INTEGER NOT NULL,
        tanggal_pengajuan DATETIME DEFAULT CURRENT_TIMESTAMP,
        rencana_checkin DATE NOT NULL,
        durasi_sewa VARCHAR(50) NOT NULL DEFAULT '1 Semester',
        total_biaya REAL NOT NULL DEFAULT 0,
        dokumen_ktm VARCHAR(255) DEFAULT NULL,
        dokumen_pernyataan VARCHAR(255) DEFAULT NULL,
        bukti_pembayaran VARCHAR(255) DEFAULT NULL,
        status_pembayaran VARCHAR(30) NOT NULL DEFAULT 'belum_bayar',
        status VARCHAR(30) NOT NULL DEFAULT 'menunggu_verifikasi',
        catatan_admin TEXT DEFAULT NULL,
        catatan_mahasiswa TEXT DEFAULT NULL,
        diverifikasi_oleh INTEGER DEFAULT NULL,
        tanggal_verifikasi DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (kamar_id) REFERENCES kamar(id) ON DELETE RESTRICT,
        FOREIGN KEY (periode_id) REFERENCES periode(id) ON DELETE RESTRICT
    );");

    // 7. audit_logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER DEFAULT NULL,
        user_role VARCHAR(50) DEFAULT NULL,
        username VARCHAR(100) DEFAULT NULL,
        action VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        ip_address VARCHAR(50) DEFAULT NULL,
        user_agent TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 8. pengumuman
    $pdo->exec("CREATE TABLE IF NOT EXISTS pengumuman (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        judul VARCHAR(255) NOT NULL,
        isi TEXT NOT NULL,
        kategori VARCHAR(50) NOT NULL DEFAULT 'Informasi',
        tanggal DATE NOT NULL,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    seed_initial_data($pdo);
}

function seed_initial_data($pdo) {
    // Password hash standar yang aman
    $admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
    $mhs_pass = password_hash('mahasiswa123', PASSWORD_BCRYPT);

    // 1. Insert Users
    $users = [
        [
            'username' => 'admin',
            'email' => 'admin@ulbi.ac.id',
            'password' => $admin_pass,
            'role' => 'admin',
            'full_name' => 'Pengelola Asrama ULBI',
            'phone' => '081234567890',
            'avatar' => null,
            'status' => 'active'
        ],
        [
            'username' => 'hasan',
            'email' => 'hasan.zubeir@ulbi.ac.id',
            'password' => $mhs_pass,
            'role' => 'mahasiswa',
            'full_name' => 'Hasan Zubeir Pohan',
            'phone' => '082198765432',
            'avatar' => null,
            'status' => 'active'
        ],
        [
            'username' => 'zidan',
            'email' => 'zidan.ramadhan@ulbi.ac.id',
            'password' => $mhs_pass,
            'role' => 'mahasiswa',
            'full_name' => 'Zidan Hairra Ramadhan',
            'phone' => '081377889900',
            'avatar' => null,
            'status' => 'active'
        ],
        [
            'username' => 'sarah',
            'email' => 'sarah.putri@ulbi.ac.id',
            'password' => $mhs_pass,
            'role' => 'mahasiswa',
            'full_name' => 'Sarah Amalia Putri',
            'phone' => '085712348877',
            'avatar' => null,
            'status' => 'active'
        ]
    ];

    $stmt_user = $pdo->prepare("INSERT OR IGNORE INTO users (username, email, password, role, full_name, phone, avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmt_user->execute([$u['username'], $u['email'], $u['password'], $u['role'], $u['full_name'], $u['phone'], $u['avatar'], $u['status']]);
    }

    // Ambil ID User
    $id_hasan = $pdo->query("SELECT id FROM users WHERE username = 'hasan'")->fetchColumn();
    $id_zidan = $pdo->query("SELECT id FROM users WHERE username = 'zidan'")->fetchColumn();
    $id_sarah = $pdo->query("SELECT id FROM users WHERE username = 'sarah'")->fetchColumn();

    // 2. Insert Profil Mahasiswa
    if ($id_hasan) {
        $stmt_prof = $pdo->prepare("INSERT OR IGNORE INTO mahasiswa_profiles (user_id, npm, prodi, jurusan, angkatan, gender, tempat_lahir, tanggal_lahir, alamat_asal, kota_asal, provinsi_asal, nama_ortu_wali, hp_ortu_wali, kontak_darurat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_prof->execute([
            $id_hasan,
            '714240045',
            'D4 Teknik Informatika',
            'Sekolah Teknologi Informasi',
            '2024',
            'L',
            'Medan',
            '2004-05-14',
            'Jl. Dr. Mansyur No. 12',
            'Medan',
            'Sumatera Utara',
            'Bapak Pohan',
            '081299990001',
            '081299990001 (Ayah)'
        ]);
    }

    if ($id_zidan) {
        $stmt_prof = $pdo->prepare("INSERT OR IGNORE INTO mahasiswa_profiles (user_id, npm, prodi, jurusan, angkatan, gender, tempat_lahir, tanggal_lahir, alamat_asal, kota_asal, provinsi_asal, nama_ortu_wali, hp_ortu_wali, kontak_darurat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_prof->execute([
            $id_zidan,
            '714240061',
            'D4 Teknik Informatika',
            'Sekolah Teknologi Informasi',
            '2024',
            'L',
            'Bandung',
            '2004-09-20',
            'Jl. Gegerkalong Hilir No. 45',
            'Bandung',
            'Jawa Barat',
            'Bapak Ramadhan',
            '081388880002',
            '081388880002 (Ayah)'
        ]);
    }

    if ($id_sarah) {
        $stmt_prof = $pdo->prepare("INSERT OR IGNORE INTO mahasiswa_profiles (user_id, npm, prodi, jurusan, angkatan, gender, tempat_lahir, tanggal_lahir, alamat_asal, kota_asal, provinsi_asal, nama_ortu_wali, hp_ortu_wali, kontak_darurat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_prof->execute([
            $id_sarah,
            '714240012',
            'D4 Logistik Niaga Internasional',
            'Sekolah Bisnis dan Manajemen',
            '2024',
            'P',
            'Jakarta',
            '2004-11-08',
            'Jl. Tebet Barat Raya No. 19',
            'Jakarta Selatan',
            'DKI Jakarta',
            'Ibu Lestari',
            '085677770003',
            '085677770003 (Ibu)'
        ]);
    }

    // 3. Insert Asrama
    $asrama_list = [
        [
            'nama_asrama' => 'Gedung Asrama Putra (Tower Al-Fatih)',
            'kategori_gender' => 'L',
            'lokasi' => 'Kompleks Kampus ULBI, Jl. Sariasih No. 54, Sarijadi, Bandung (Zona Barat)',
            'deskripsi' => 'Gedung asrama khusus mahasiswa putra dengan lingkungan asri, aman, dan kondusif untuk mendukung kegiatan akademik serta pembentukan karakter.',
            'jumlah_lantai' => 4,
            'kapasitas_total' => 120,
            'fasilitas_gedung' => 'WiFi Kampus High Speed, Musholla Putra, Dapur Bersama di Setiap Lantai, Ruang Belajar Mandiri & Diskusi, Lapangan Olahraga, Area Parkir Motor, Keamanan & CCTV 24 Jam, Layanan Kebersihan Koridor, Dispenser Air Minum'
        ],
        [
            'nama_asrama' => 'Gedung Asrama Putri (Tower An-Nisa)',
            'kategori_gender' => 'P',
            'lokasi' => 'Kompleks Kampus ULBI, Jl. Sariasih No. 54, Sarijadi, Bandung (Zona Timur)',
            'deskripsi' => 'Gedung asrama khusus mahasiswi putri dengan akses gerbang khusus berpintu magnetik, ruang bersama yang nyaman, dan petugas pengawas ramah.',
            'jumlah_lantai' => 4,
            'kapasitas_total' => 120,
            'fasilitas_gedung' => 'WiFi Kampus High Speed, Musholla Putri, Dapur Bersama Modern, Ruang Komunal Santai, Ruang Belajar Khusus, Akses Kartu RFID, Keamanan & Petugas Jaga Putri 24 Jam, CCTV, Mesin Cuci Koin, Kantin Asrama'
        ]
    ];

    $stmt_asrama = $pdo->prepare("INSERT OR IGNORE INTO asrama (nama_asrama, kategori_gender, lokasi, deskripsi, jumlah_lantai, kapasitas_total, fasilitas_gedung) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($asrama_list as $a) {
        $stmt_asrama->execute([$a['nama_asrama'], $a['kategori_gender'], $a['lokasi'], $a['deskripsi'], $a['jumlah_lantai'], $a['kapasitas_total'], $a['fasilitas_gedung']]);
    }

    $id_asrama_putra = $pdo->query("SELECT id FROM asrama WHERE kategori_gender = 'L'")->fetchColumn();
    $id_asrama_putri = $pdo->query("SELECT id FROM asrama WHERE kategori_gender = 'P'")->fetchColumn();

    // 4. Insert Kamar
    $kamar_list = [
        // Asrama Putra
        [
            'asrama_id' => $id_asrama_putra,
            'nomor_kamar' => 'A-101',
            'lantai' => 1,
            'tipe_kamar' => 'Deluxe (2 Orang)',
            'kapasitas' => 2,
            'terisi' => 1,
            'tarif_per_semester' => 3500000,
            'tarif_per_tahun' => 6500000,
            'fasilitas_kamar' => '2 Ranjang Single Springbed, 2 Meja Belajar + Kursi Ergonomis, 2 Lemari Pakaian 2 Pintu, AC Split, Kamar Mandi Dalam (Shower & Kloset Duduk), Jendela Menghadap Taman',
            'status' => 'tersedia'
        ],
        [
            'asrama_id' => $id_asrama_putra,
            'nomor_kamar' => 'A-102',
            'lantai' => 1,
            'tipe_kamar' => 'Standar (4 Orang)',
            'kapasitas' => 4,
            'terisi' => 2,
            'tarif_per_semester' => 2250000,
            'tarif_per_tahun' => 4200000,
            'fasilitas_kamar' => '2 Ranjang Tingkat Berkualitas, 4 Meja Belajar, 4 Lemari Pribadi Bersekat, Kipas Angin Dinding, Rak Sepatu, Kamar Mandi Luar Khusus Koridor Lantai 1',
            'status' => 'tersedia'
        ],
        [
            'asrama_id' => $id_asrama_putra,
            'nomor_kamar' => 'A-201',
            'lantai' => 2,
            'tipe_kamar' => 'Standar (4 Orang)',
            'kapasitas' => 4,
            'terisi' => 4,
            'tarif_per_semester' => 2250000,
            'tarif_per_tahun' => 4200000,
            'fasilitas_kamar' => '2 Ranjang Tingkat, 4 Meja Belajar, 4 Lemari, Kipas Angin Dinding, Kamar Mandi Luar',
            'status' => 'penuh'
        ],
        [
            'asrama_id' => $id_asrama_putra,
            'nomor_kamar' => 'A-202',
            'lantai' => 2,
            'tipe_kamar' => 'Deluxe (2 Orang)',
            'kapasitas' => 2,
            'terisi' => 0,
            'tarif_per_semester' => 3500000,
            'tarif_per_tahun' => 6500000,
            'fasilitas_kamar' => '2 Ranjang Single, 2 Meja Belajar, 2 Lemari Pakaian, AC Split, Kamar Mandi Dalam',
            'status' => 'tersedia'
        ],
        [
            'asrama_id' => $id_asrama_putra,
            'nomor_kamar' => 'A-301',
            'lantai' => 3,
            'tipe_kamar' => 'Single Room (1 Orang)',
            'kapasitas' => 1,
            'terisi' => 0,
            'tarif_per_semester' => 5000000,
            'tarif_per_tahun' => 9500000,
            'fasilitas_kamar' => '1 Ranjang Queen Springbed, 1 Meja Belajar Eksekutif, 1 Lemari Besar, AC Split, Kamar Mandi Dalam, Kulkas Mini, Balkon Pribadi',
            'status' => 'tersedia'
        ],
        [
            'asrama_id' => $id_asrama_putra,
            'nomor_kamar' => 'A-401',
            'lantai' => 4,
            'tipe_kamar' => 'Standar (4 Orang)',
            'kapasitas' => 4,
            'terisi' => 0,
            'tarif_per_semester' => 2250000,
            'tarif_per_tahun' => 4200000,
            'fasilitas_kamar' => 'Sedang dalam proses pengecatan dan perawatan sanitasi rutin.',
            'status' => 'pemeliharaan'
        ],
        // Asrama Putri
        [
            'asrama_id' => $id_asrama_putri,
            'nomor_kamar' => 'B-101',
            'lantai' => 1,
            'tipe_kamar' => 'Deluxe (2 Orang)',
            'kapasitas' => 2,
            'terisi' => 1,
            'tarif_per_semester' => 3500000,
            'tarif_per_tahun' => 6500000,
            'fasilitas_kamar' => '2 Ranjang Single Springbed, 2 Meja Rias/Belajar, 2 Lemari Pakaian, AC Split, Kamar Mandi Dalam (Water Heater), Jendela Pencahayaan Alami',
            'status' => 'tersedia'
        ],
        [
            'asrama_id' => $id_asrama_putri,
            'nomor_kamar' => 'B-102',
            'lantai' => 1,
            'tipe_kamar' => 'Standar (4 Orang)',
            'kapasitas' => 4,
            'terisi' => 3,
            'tarif_per_semester' => 2250000,
            'tarif_per_tahun' => 4200000,
            'fasilitas_kamar' => '2 Ranjang Tingkat, 4 Meja Belajar, 4 Lemari Pribadi, Kipas Angin Gantung, Kamar Mandi Luar Lantai 1',
            'status' => 'tersedia'
        ],
        [
            'asrama_id' => $id_asrama_putri,
            'nomor_kamar' => 'B-201',
            'lantai' => 2,
            'tipe_kamar' => 'Standar (4 Orang)',
            'kapasitas' => 4,
            'terisi' => 0,
            'tarif_per_semester' => 2250000,
            'tarif_per_tahun' => 4200000,
            'fasilitas_kamar' => '2 Ranjang Tingkat, 4 Meja Belajar, 4 Lemari Pribadi, Kipas Angin, Kamar Mandi Luar',
            'status' => 'tersedia'
        ],
        [
            'asrama_id' => $id_asrama_putri,
            'nomor_kamar' => 'B-301',
            'lantai' => 3,
            'tipe_kamar' => 'Deluxe (2 Orang)',
            'kapasitas' => 2,
            'terisi' => 0,
            'tarif_per_semester' => 3500000,
            'tarif_per_tahun' => 6500000,
            'fasilitas_kamar' => '2 Ranjang Single, 2 Meja Belajar, 2 Lemari Pakaian, AC Split, Kamar Mandi Dalam',
            'status' => 'tersedia'
        ]
    ];

    $stmt_kamar = $pdo->prepare("INSERT OR IGNORE INTO kamar (asrama_id, nomor_kamar, lantai, tipe_kamar, kapasitas, terisi, tarif_per_semester, tarif_per_tahun, fasilitas_kamar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($kamar_list as $k) {
        $stmt_kamar->execute([$k['asrama_id'], $k['nomor_kamar'], $k['lantai'], $k['tipe_kamar'], $k['kapasitas'], $k['terisi'], $k['tarif_per_semester'], $k['tarif_per_tahun'], $k['fasilitas_kamar'], $k['status']]);
    }

    // 5. Insert Periode
    $periode_list = [
        [
            'nama_periode' => 'Semester Ganjil 2026/2027',
            'tahun_akademik' => '2026/2027',
            'semester' => 'Ganjil',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2027-01-31',
            'status' => 'aktif',
            'keterangan' => 'Periode reguler perkuliahan semester ganjil tahun akademik 2026/2027'
        ],
        [
            'nama_periode' => 'Tahun Akademik Penuh 2026/2027',
            'tahun_akademik' => '2026/2027',
            'semester' => 'Penuh (1 Tahun)',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2027-08-31',
            'status' => 'aktif',
            'keterangan' => 'Periode sewa 1 tahun penuh dengan potongan biaya khusus mahasiswa baru/lama'
        ],
        [
            'nama_periode' => 'Semester Genap 2026/2027',
            'tahun_akademik' => '2026/2027',
            'semester' => 'Genap',
            'tanggal_mulai' => '2027-02-01',
            'tanggal_selesai' => '2027-06-30',
            'status' => 'aktif',
            'keterangan' => 'Periode reservasi semester genap 2026/2027'
        ]
    ];

    $stmt_periode = $pdo->prepare("INSERT OR IGNORE INTO periode (nama_periode, tahun_akademik, semester, tanggal_mulai, tanggal_selesai, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($periode_list as $p) {
        $stmt_periode->execute([$p['nama_periode'], $p['tahun_akademik'], $p['semester'], $p['tanggal_mulai'], $p['tanggal_selesai'], $p['status'], $p['keterangan']]);
    }

    $id_periode_ganjil = $pdo->query("SELECT id FROM periode WHERE semester = 'Ganjil'")->fetchColumn();
    $id_kamar_a101 = $pdo->query("SELECT id FROM kamar WHERE nomor_kamar = 'A-101'")->fetchColumn();
    $id_kamar_a102 = $pdo->query("SELECT id FROM kamar WHERE nomor_kamar = 'A-102'")->fetchColumn();
    $id_kamar_b101 = $pdo->query("SELECT id FROM kamar WHERE nomor_kamar = 'B-101'")->fetchColumn();

    // 6. Insert Sample Reservasi
    if ($id_hasan && $id_kamar_a101 && $id_periode_ganjil) {
        $stmt_rsv = $pdo->prepare("INSERT OR IGNORE INTO reservasi (kode_reservasi, user_id, kamar_id, periode_id, tanggal_pengajuan, rencana_checkin, durasi_sewa, total_biaya, status_pembayaran, status, catatan_admin, catatan_mahasiswa, diverifikasi_oleh, tanggal_verifikasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_rsv->execute([
            'RSV-2026-001',
            $id_hasan,
            $id_kamar_a101,
            $id_periode_ganjil,
            '2026-10-28 10:15:00',
            '2026-09-01',
            '1 Semester',
            3500000,
            'lunas',
            'disetujui',
            'Berkas administrasi dan pembayaran lunas telah diverifikasi. Kunci kamar dapat diambil di kantor pengelola asrama.',
            'Mohon ranjang bagian dekat jendela jika memungkinkan.',
            1,
            '2026-10-29 09:30:00'
        ]);
    }

    if ($id_zidan && $id_kamar_a102 && $id_periode_ganjil) {
        $stmt_rsv = $pdo->prepare("INSERT OR IGNORE INTO reservasi (kode_reservasi, user_id, kamar_id, periode_id, tanggal_pengajuan, rencana_checkin, durasi_sewa, total_biaya, status_pembayaran, status, catatan_admin, catatan_mahasiswa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_rsv->execute([
            'RSV-2026-002',
            $id_zidan,
            $id_kamar_a102,
            $id_periode_ganjil,
            '2026-11-02 14:20:00',
            '2026-11-15',
            '1 Semester',
            2250000,
            'belum_bayar',
            'menunggu_verifikasi',
            NULL,
            'Pengajuan kamar tipe standar untuk perkuliahan semester ganjil.'
        ]);
    }

    if ($id_sarah && $id_kamar_b101 && $id_periode_ganjil) {
        $stmt_rsv = $pdo->prepare("INSERT OR IGNORE INTO reservasi (kode_reservasi, user_id, kamar_id, periode_id, tanggal_pengajuan, rencana_checkin, durasi_sewa, total_biaya, status_pembayaran, status, catatan_admin, catatan_mahasiswa, diverifikasi_oleh, tanggal_verifikasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_rsv->execute([
            'RSV-2026-003',
            $id_sarah,
            $id_kamar_b101,
            $id_periode_ganjil,
            '2026-11-01 11:00:00',
            '2026-09-01',
            '1 Semester',
            3500000,
            'menunggu_konfirmasi',
            'disetujui',
            'Permohonan disetujui. Menunggu konfirmasi pembayaran via transfer bank.',
            'Memilih kamar asrama putri lantai 1.',
            1,
            '2026-11-01 16:45:00'
        ]);
    }

    // 7. Insert Pengumuman
    $pengumuman_list = [
        [
            'judul' => 'Pembukaan Pendaftaran Reservasi Asrama ULBI Semester Ganjil 2026/2027',
            'isi' => 'Diberitahukan kepada seluruh mahasiswa dan calon mahasiswa baru Universitas Logistik dan Bisnis Internasional bahwa reservasi asrama kampus telah resmi dibuka. Kuota kamar terbatas, mahasiswa diimbau segera melengkapi profil dan berkas administrasi.',
            'kategori' => 'Penting',
            'tanggal' => '2026-10-15',
            'is_active' => 1
        ],
        [
            'judul' => 'Jadwal Check-in dan Pengambilan Kunci Asrama Mahasiswa',
            'isi' => 'Bagi mahasiswa yang permohonan reservasinya telah berstatus DISETUJUI dan telah melunasi pembayaran sewa, proses check-in dan serah terima kunci kamar dapat dilakukan setiap hari kerja (Senin-Jumat) pukul 08.00 - 16.00 WIB di Kantor Pengelola Asrama Gedung Al-Fatih Lantai 1 dengan membawa kartu identitas (KTM/KTP) serta bukti reservasi fisik.',
            'kategori' => 'Jadwal',
            'tanggal' => '2026-10-25',
            'is_active' => 1
        ],
        [
            'judul' => 'Tata Tertib Kehidupan Asrama dan Larangan Membawa Alat Elektronik Tertentu',
            'isi' => 'Demi keselamatan dan kenyamanan bersama, penghuni asrama wajib mematuhi jam malam (pukul 22.00 WIB) serta dilarang membawa peralatan elektronik berdaya tinggi (seperti kompor listrik/AC portabel) tanpa izin tertulis dari pengelola asrama.',
            'kategori' => 'Informasi',
            'tanggal' => '2026-11-01',
            'is_active' => 1
        ]
    ];

    $stmt_peng = $pdo->prepare("INSERT OR IGNORE INTO pengumuman (judul, isi, kategori, tanggal, is_active) VALUES (?, ?, ?, ?, ?)");
    foreach ($pengumuman_list as $pg) {
        $stmt_peng->execute([$pg['judul'], $pg['isi'], $pg['kategori'], $pg['tanggal'], $pg['is_active']]);
    }

    // 8. Insert Audit Logs Awal
    $logs = [
        [
            'user_id' => 1,
            'user_role' => 'admin',
            'username' => 'admin',
            'action' => 'SYSTEM_INIT',
            'description' => 'Inisialisasi sistem SIRASMA ULBI dan pembuatan data master asrama & kamar.',
            'ip_address' => '127.0.0.1'
        ],
        [
            'user_id' => $id_hasan,
            'user_role' => 'mahasiswa',
            'username' => 'hasan',
            'action' => 'SUBMIT_RESERVATION',
            'description' => 'Mahasiswa Hasan Zubeir Pohan (NPM: 714240045) mengajukan reservasi kamar A-101 (Kode: RSV-2026-001).',
            'ip_address' => '127.0.0.1'
        ],
        [
            'user_id' => 1,
            'user_role' => 'admin',
            'username' => 'admin',
            'action' => 'APPROVE_RESERVATION',
            'description' => 'Admin menyetujui reservasi kode RSV-2026-001 untuk kamar A-101.',
            'ip_address' => '127.0.0.1'
        ],
        [
            'user_id' => $id_zidan,
            'user_role' => 'mahasiswa',
            'username' => 'zidan',
            'action' => 'SUBMIT_RESERVATION',
            'description' => 'Mahasiswa Zidan Hairra Ramadhan (NPM: 714240061) mengajukan reservasi kamar A-102 (Kode: RSV-2026-002).',
            'ip_address' => '127.0.0.1'
        ]
    ];

    $stmt_log = $pdo->prepare("INSERT INTO audit_logs (user_id, user_role, username, action, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($logs as $l) {
        $stmt_log->execute([$l['user_id'], $l['user_role'], $l['username'], $l['action'], $l['description'], $l['ip_address']]);
    }
}

// Jika dijalankan langsung dari CLI
if (php_sapi_name() === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    echo "=== Menjalankan Setup Database SIRASMA ULBI ===\n";
    try {
        $db = get_db();
        echo "Database berhasil diinisialisasi menggunakan driver: " . get_db_driver() . "\n";
        $users_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $rooms_count = $db->query("SELECT COUNT(*) FROM kamar")->fetchColumn();
        $rsv_count = $db->query("SELECT COUNT(*) FROM reservasi")->fetchColumn();
        echo "Total Akun: $users_count | Total Kamar: $rooms_count | Total Reservasi: $rsv_count\n";
        echo "Instalasi selesai sukses!\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
