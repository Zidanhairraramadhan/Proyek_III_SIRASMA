<?php
/**
 * SIRASMA ULBI - Beranda Utama (Landing Page)
 * Universitas Logistik dan Bisnis Internasional (ULBI)
 */
$page_title = 'Beranda - Sistem Reservasi Asrama Mahasiswa';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$db = get_db();

// Ambil statistik ringkas untuk ditampilkan di landing page
$stat_kamar_tersedia = $db->query("SELECT COUNT(*) FROM kamar WHERE status = 'tersedia'")->fetchColumn();
$stat_total_kapasitas = $db->query("SELECT SUM(kapasitas) FROM kamar WHERE status != 'nonaktif'")->fetchColumn() ?: 0;
$stat_total_terisi = $db->query("SELECT SUM(terisi) FROM kamar WHERE status != 'nonaktif'")->fetchColumn() ?: 0;
$stat_total_asrama = $db->query("SELECT COUNT(*) FROM asrama")->fetchColumn();

// Ambil 6 kamar unggulan
$stmt_kamar = $db->query("SELECT k.*, a.nama_asrama, a.kategori_gender FROM kamar k 
                          JOIN asrama a ON k.asrama_id = a.id 
                          WHERE k.status = 'tersedia' 
                          ORDER BY k.nomor_kamar ASC LIMIT 6");
$kamar_unggulan = $stmt_kamar->fetchAll();

// Ambil pengumuman aktif
$pengumuman = $db->query("SELECT * FROM pengumuman WHERE is_active = 1 ORDER BY tanggal DESC LIMIT 3")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container position-relative" style="z-index: 2;">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <div class="hero-badge">
          <span class="pulse-dot"></span>
          <span>Layanan Resmi Asrama Mahasiswa ULBI Bandung</span>
        </div>
        <h1 class="hero-title">
          Hunian Asrama Nyaman &amp; Kondusif untuk <span class="highlight-text">Mahasiswa ULBI</span>
        </h1>
        <p class="hero-subtitle">
          SIRASMA menyatukan informasi fasilitas, ketersediaan kamar secara real-time, dan pengajuan reservasi asrama dalam satu layanan terpadu, aman, dan transparan bagi mahasiswa.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="<?= BASE_URL ?>/info_kamar.php" class="btn btn-ulbi-primary btn-lg px-4">
            <i class="bi bi-door-open-fill me-2"></i> Lihat Ketersediaan Kamar
          </a>
          <a href="<?= BASE_URL ?>/panduan.php" class="btn btn-ulbi-glass btn-lg px-4">
            <i class="bi bi-journal-text me-2"></i> Panduan Alur Reservasi
          </a>
        </div>
      </div>

      <!-- Modern Hero Showcase Glass Card -->
      <div class="col-lg-5">
        <div class="hero-showcase-card">
          <div class="hero-card-header">
            <div>
              <span class="badge bg-success-subtle text-success border border-success px-2 py-1 small rounded-pill">
                <i class="bi bi-check-circle-fill me-1"></i> Periode Ganjil 2026/2027
              </span>
              <h5 class="fw-bold text-white mb-0 mt-2">Kompleks Asrama Kampus</h5>
              <small class="text-warning"><i class="bi bi-geo-alt-fill me-1"></i> Jl. Sariasih No. 54, Sarijadi Bandung</small>
            </div>
            <div class="text-end">
              <span class="badge bg-light text-dark fw-bold px-3 py-2 rounded-3">
                <i class="bi bi-star-fill text-warning me-1"></i> 4.9 / 5
              </span>
            </div>
          </div>

          <!-- Dormitory Mini Highlights -->
          <div class="row g-2 mb-3">
            <div class="col-6">
              <div class="p-2 rounded-3" style="background: rgba(2, 132, 199, 0.2); border: 1px solid rgba(2, 132, 199, 0.35);">
                <small class="text-info d-block fw-bold"><i class="bi bi-gender-male me-1"></i> Tower Al-Fatih</small>
                <div class="text-white small fw-semibold">Asrama Putra (4 Lt)</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-2 rounded-3" style="background: rgba(236, 72, 153, 0.2); border: 1px solid rgba(236, 72, 153, 0.35);">
                <small class="text-warning d-block fw-bold"><i class="bi bi-gender-female me-1"></i> Tower An-Nisa</small>
                <div class="text-white small fw-semibold">Asrama Putri (4 Lt)</div>
              </div>
            </div>
          </div>

          <!-- Modern Floating Benefit Chips -->
          <div class="hero-floating-chip">
            <i class="bi bi-wifi text-warning fs-5"></i>
            <div>
              <div class="fw-bold text-white">WiFi Kampus High-Speed 100 Mbps</div>
              <div style="font-size: 0.72rem; color: #94a3b8;">Koneksi stabil untuk perkuliahan &amp; tugas</div>
            </div>
          </div>

          <div class="hero-floating-chip">
            <i class="bi bi-shield-check text-success fs-5"></i>
            <div>
              <div class="fw-bold text-white">Keamanan &amp; CCTV 24 Jam Nonstop</div>
              <div style="font-size: 0.72rem; color: #94a3b8;">Akses gerbang RFID dan pengawas asrama</div>
            </div>
          </div>

          <div class="hero-floating-chip mb-0">
            <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
            <div>
              <div class="fw-bold text-white">3 Menit Jalan Kaki ke Ruang Kuliah</div>
              <div style="font-size: 0.72rem; color: #94a3b8;">Tepat di dalam lingkungan kampus ULBI</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Quick Stat Counters -->
<section class="py-4 bg-white border-bottom shadow-sm">
  <div class="container">
    <div class="row g-3">
      <div class="col-6 col-lg-3">
        <div class="stat-pill-box d-flex align-items-center gap-3">
          <div class="feature-icon-wrapper icon-orange mb-0">
            <i class="bi bi-building"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0 text-navy" style="font-size: 1.6rem;"><?= $stat_total_asrama ?> Gedung</h3>
            <small class="text-muted fw-semibold">Tower Putra &amp; Putri</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-pill-box d-flex align-items-center gap-3">
          <div class="feature-icon-wrapper icon-green mb-0">
            <i class="bi bi-door-open"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0 text-success" style="font-size: 1.6rem;"><?= $stat_kamar_tersedia ?> Kamar</h3>
            <small class="text-muted fw-semibold">Siap Huni (Tersedia)</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-pill-box d-flex align-items-center gap-3">
          <div class="feature-icon-wrapper icon-blue mb-0">
            <i class="bi bi-people"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0 text-primary" style="font-size: 1.6rem;"><?= $stat_total_terisi ?> Slot</h3>
            <small class="text-muted fw-semibold">Mahasiswa Terisi</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-pill-box d-flex align-items-center gap-3">
          <div class="feature-icon-wrapper icon-purple mb-0">
            <i class="bi bi-shield-check"></i>
          </div>
          <div>
            <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.6rem;">24 Jam</h3>
            <small class="text-muted fw-semibold">Keamanan &amp; CCTV</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Pilihan Gedung Asrama -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <span class="badge bg-warning text-dark px-3 py-2 fw-semibold rounded-pill mb-2">Lokasi & Bangunan</span>
      <h2 class="fw-bold" style="color:var(--ulbi-navy);">Pilihan Gedung Asrama Kampus ULBI</h2>
      <p class="text-muted mx-auto" style="max-width: 650px;">
        Asrama ULBI terbagi dalam zona khusus mahasiswa putra dan mahasiswi putri yang menjamin privasi, keamanan, dan ketertiban.
      </p>
    </div>

    <div class="row g-4">
      <!-- Asrama Putra -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
          <div class="p-4" style="background: linear-gradient(135deg, #0d2040 0%, #1a365d 100%); color: #fff;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-primary px-3 py-1">Khusus Laki-laki</span>
              <span class="badge bg-light text-dark">4 Lantai</span>
            </div>
            <h3 class="fw-bold mb-1">Gedung Asrama Putra</h3>
            <p class="text-warning small mb-0"><i class="bi bi-tag-fill me-1"></i> Tower Al-Fatih (Zona Barat Kampus)</p>
          </div>
          <div class="card-body p-4">
            <p class="text-secondary small mb-3">
              Dilengkapi dengan ruang belajar bersama, musholla representatif, akses parkir kendaraan roda dua, serta lingkungan tenang yang berjarak hanya 3 menit jalan kaki ke gedung rektorat dan perkuliahan.
            </p>
            <h6 class="fw-bold small text-uppercase text-muted mb-2">Fasilitas Utama:</h6>
            <div class="d-flex flex-wrap gap-2 mb-4">
              <span class="badge bg-light text-dark border"><i class="bi bi-wifi text-primary me-1"></i> WiFi Kampus</span>
              <span class="badge bg-light text-dark border"><i class="bi bi-cup-hot text-danger me-1"></i> Dapur Bersama</span>
              <span class="badge bg-light text-dark border"><i class="bi bi-building text-info me-1"></i> Musholla</span>
              <span class="badge bg-light text-dark border"><i class="bi bi-shield-check text-success me-1"></i> CCTV 24 Jam</span>
              <span class="badge bg-light text-dark border"><i class="bi bi-p-square text-secondary me-1"></i> Parkir Motor</span>
            </div>
            <a href="<?= BASE_URL ?>/info_kamar.php?asrama=1" class="btn btn-outline-primary w-100">
              <i class="bi bi-search me-1"></i> Lihat Kamar Asrama Putra
            </a>
          </div>
        </div>
      </div>

      <!-- Asrama Putri -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
          <div class="p-4" style="background: linear-gradient(135deg, #831843 0%, #be185d 100%); color: #fff;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-danger px-3 py-1">Khusus Perempuan</span>
              <span class="badge bg-light text-dark">4 Lantai</span>
            </div>
            <h3 class="fw-bold mb-1">Gedung Asrama Putri</h3>
            <p class="text-warning small mb-0"><i class="bi bi-tag-fill me-1"></i> Tower An-Nisa (Zona Timur Kampus)</p>
          </div>
          <div class="card-body p-4">
            <p class="text-secondary small mb-3">
              Didesain dengan tingkat privasi tinggi, penjagaan staf putri 24 jam, akses gerbang sistem kontrol otomatis, dapur modern di setiap lantai, dan ruang santai komunal mahasiswi.
            </p>
            <h6 class="fw-bold small text-uppercase text-muted mb-2">Fasilitas Utama:</h6>
            <div class="d-flex flex-wrap gap-2 mb-4">
              <span class="badge bg-light text-dark border"><i class="bi bi-wifi text-primary me-1"></i> WiFi Kampus</span>
              <span class="badge bg-light text-dark border"><i class="bi bi-key text-warning me-1"></i> Akses Kartu RFID</span>
              <span class="badge bg-light text-dark border"><i class="bi bi-cup-hot text-danger me-1"></i> Dapur Modern</span>
              <span class="badge bg-light text-dark border"><i class="bi bi-shield-lock text-success me-1"></i> Security Putri 24 Jam</span>
              <span class="badge bg-light text-dark border"><i class="bi bi-basket text-info me-1"></i> Laundry Mesin Cuci</span>
            </div>
            <a href="<?= BASE_URL ?>/info_kamar.php?asrama=2" class="btn btn-outline-danger w-100">
              <i class="bi bi-search me-1"></i> Lihat Kamar Asrama Putri
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Kamar Siap Huni Terbaru -->
<section class="py-5 bg-light border-top border-bottom">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
      <div>
        <span class="badge bg-success text-white px-3 py-1 mb-2">Update Ketersediaan</span>
        <h2 class="fw-bold" style="color:var(--ulbi-navy);">Kamar Tersedia untuk Reservasi</h2>
        <p class="text-muted mb-0">Pilih tipe kamar yang sesuai dengan kebutuhan studi Anda.</p>
      </div>
      <a href="<?= BASE_URL ?>/info_kamar.php" class="btn btn-ulbi-navy mt-3 mt-md-0">
        Lihat Semua Kamar (<?= $stat_kamar_tersedia ?>) <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4">
      <?php foreach ($kamar_unggulan as $km): 
        $sisa_slot = $km['kapasitas'] - $km['terisi'];
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="room-card shadow-sm">
          <div class="room-card-header border-bottom">
            <div>
              <span class="badge bg-<?= ($km['kategori_gender'] === 'L') ? 'primary' : 'danger' ?> mb-1">
                <?= ($km['kategori_gender'] === 'L') ? 'Asrama Putra' : 'Asrama Putri' ?>
              </span>
              <h4 class="fw-bold mb-0 text-navy">Kamar <?= htmlspecialchars($km['nomor_kamar']) ?></h4>
              <small class="text-muted">Lantai <?= $km['lantai'] ?> &bull; <?= htmlspecialchars($km['tipe_kamar']) ?></small>
            </div>
            <div>
              <?= get_room_badge($km['status']) ?>
            </div>
          </div>
          
          <div class="room-card-body pt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="small text-muted"><i class="bi bi-person-fill"></i> Kapasitas:</span>
              <span class="fw-bold small"><?= $km['kapasitas'] ?> Orang (Sisa <?= $sisa_slot ?> Slot)</span>
            </div>

            <!-- Progress bar keterisian -->
            <div class="progress mb-3" style="height: 6px;">
              <?php $pct = ($km['kapasitas'] > 0) ? ($km['terisi'] / $km['kapasitas']) * 100 : 0; ?>
              <div class="progress-bar <?= ($pct >= 100) ? 'bg-danger' : (($pct >= 50) ? 'bg-warning' : 'bg-success') ?>" 
                   role="progressbar" style="width: <?= $pct ?>%"></div>
            </div>

            <p class="small text-secondary mb-3" style="min-height: 48px;">
              <?= htmlspecialchars(substr($km['fasilitas_kamar'], 0, 95)) ?>...
            </p>

            <div class="room-price-box">
              <div class="d-flex justify-content-between align-items-center">
                <span class="small text-muted">Tarif Sewa:</span>
                <span class="fw-bold text-success fs-6"><?= format_rupiah($km['tarif_per_semester']) ?></span>
              </div>
              <small class="text-muted d-block text-end" style="font-size: 0.75rem;">per semester / mahasiswa</small>
            </div>

            <div class="d-grid mt-3">
              <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php?kamar_id=<?= $km['id'] ?>" class="btn btn-ulbi-primary btn-sm">
                <i class="bi bi-check2-circle me-1"></i> Ajukan Reservasi Kamar Ini
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Alur 6 Langkah Reservasi -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <span class="badge bg-primary text-white px-3 py-2 fw-semibold rounded-pill mb-2">Alur Layanan</span>
      <h2 class="fw-bold" style="color:var(--ulbi-navy);">6 Tahap Mudah Reservasi Asrama</h2>
      <p class="text-muted mx-auto" style="max-width: 600px;">
        Proses pengajuan terstandarisasi untuk mempermudah calon penghuni dan menjaga akurasi administrasi asrama.
      </p>
    </div>

    <div class="row g-4">
      <div class="col-md-4 col-sm-6">
        <div class="feature-card text-center">
          <div class="step-number">1</div>
          <h5 class="fw-bold">Registrasi Akun</h5>
          <p class="text-secondary small mb-0">Daftarkan akun mahasiswa menggunakan NPM dan data kontak aktif untuk memperoleh akses sistem.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-card text-center">
          <div class="step-number">2</div>
          <h5 class="fw-bold">Lengkapi Profil</h5>
          <p class="text-secondary small mb-0">Isi data identitas diri, prodi, alamat asal, serta kontak darurat orang tua/wali mahasiswa.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-card text-center">
          <div class="step-number">3</div>
          <h5 class="fw-bold">Pilih Kamar & Periode</h5>
          <p class="text-secondary small mb-0">Pilih gedung asrama yang sesuai gender, tipe kamar yang tersedia, serta periode sewa.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-card text-center">
          <div class="step-number">4</div>
          <h5 class="fw-bold">Unggah Berkas</h5>
          <p class="text-secondary small mb-0">Unggah salinan KTM atau bukti registrasi mahasiswa ULBI serta surat pernyataan kepatuhan.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-card text-center">
          <div class="step-number">5</div>
          <h5 class="fw-bold">Verifikasi Admin</h5>
          <p class="text-secondary small mb-0">Pengelola asrama memeriksa validitas dokumen dan ketersediaan kuota secara real-time.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-card text-center">
          <div class="step-number">6</div>
          <h5 class="fw-bold">Cetak Bukti & Check-in</h5>
          <p class="text-secondary small mb-0">Setelah disetujui, cetak bukti reservasi digital untuk serah terima kunci kamar di kantor asrama.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Pengumuman Asrama Terbaru -->
<?php if (!empty($pengumuman)): ?>
<section class="py-5 bg-light border-top">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-warning text-dark px-3 py-1 mb-2">Informasi Resmi</span>
      <h3 class="fw-bold" style="color:var(--ulbi-navy);">Pengumuman & Pemberitahuan Asrama</h3>
    </div>

    <div class="row g-4">
      <?php foreach ($pengumuman as $pg): ?>
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-3">
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-secondary"><?= htmlspecialchars($pg['kategori']) ?></span>
              <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= format_tanggal($pg['tanggal']) ?></small>
            </div>
            <h5 class="fw-bold mb-2"><?= htmlspecialchars($pg['judul']) ?></h5>
            <p class="text-secondary small mb-0"><?= nl2br(htmlspecialchars($pg['isi'])) ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Call To Action -->
<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, var(--ulbi-navy) 0%, #173663 100%);">
  <div class="container py-3">
    <h2 class="fw-bold mb-3">Siap Menjadi Bagian dari Asrama Mahasiswa ULBI?</h2>
    <p class="text-light opacity-75 mx-auto mb-4" style="max-width: 620px;">
      Segera ajukan reservasi Anda sebelum kuota kamar untuk semester mendatang terpenuhi.
    </p>
    <div class="d-flex justify-content-center gap-3">
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-ulbi-primary btn-lg px-4">
        <i class="bi bi-pencil-square me-2"></i> Daftar Akun Mahasiswa
      </a>
      <a href="<?= BASE_URL ?>/kontak.php" class="btn btn-outline-light btn-lg px-4">
        <i class="bi bi-question-circle me-2"></i> Hubungi Pengelola
      </a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
