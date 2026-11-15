<?php
/**
 * SIRASMA ULBI - Beranda Utama (Landing Page)
 * Universitas Logistik dan Bisnis Internasional (ULBI)
 * Modern Minimalist + Elegant Campus SaaS Prototype
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
      <div class="col-lg-7 animate-fade-in-up">
        <div class="hero-badge">
          <span class="pulse-dot"></span>
          <span>Layanan Resmi Hunian Kampus ULBI Bandung</span>
        </div>
        <h1 class="hero-title">
          Hunian Asrama Nyaman &amp; Kondusif untuk <span class="highlight-text">Mahasiswa ULBI</span>
        </h1>
        <p class="hero-subtitle">
          SIRASMA menghadirkan kemudahan eksplorasi fasilitas, pemantauan ketersediaan kamar secara <em>real-time</em>, dan alur pendaftaran terpadu yang aman, tertib, dan transparan bagi mahasiswa.
        </p>
        <div class="d-flex flex-wrap gap-3 pt-2">
          <a href="<?= BASE_URL ?>/info_kamar.php" class="btn btn-ulbi-primary btn-lg">
            <i class="bi bi-door-open-fill"></i> Lihat Ketersediaan Kamar
          </a>
          <a href="<?= BASE_URL ?>/panduan.php" class="btn btn-ulbi-glass btn-lg">
            <i class="bi bi-journal-text"></i> Alur Reservasi &amp; Aturan
          </a>
        </div>
      </div>

      <!-- Modern Hero Showcase Glass Card -->
      <div class="col-lg-5 animate-fade-in-up delay-1">
        <div class="hero-showcase-card">
          <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom border-light border-opacity-10">
            <div>
              <span class="badge-soft badge-soft-success">
                <i class="bi bi-check-circle-fill"></i> Periode Ganjil 2026/2027
              </span>
              <h5 class="fw-bold text-white mb-0 mt-2">Kompleks Asrama Terpadu</h5>
              <small class="text-warning"><i class="bi bi-geo-alt-fill me-1"></i> Kampus ULBI, Sarijadi - Bandung</small>
            </div>
            <span class="badge bg-white text-dark fw-bold px-3 py-2 rounded-3 shadow-sm">
              <i class="bi bi-star-fill text-warning me-1"></i> 4.9 / 5
            </span>
          </div>

          <!-- Dormitory Highlights Cards -->
          <div class="row g-2 mb-3">
            <div class="col-6">
              <div class="p-3 rounded-3" style="background: rgba(2, 132, 199, 0.18); border: 1px solid rgba(2, 132, 199, 0.35);">
                <small class="text-info d-block fw-bold"><i class="bi bi-gender-male me-1"></i> Tower Al-Fatih</small>
                <div class="text-white small fw-bold">Asrama Putra (4 Lantai)</div>
                <div style="font-size: 0.72rem; color: #cbd5e1;" class="mt-1">Kapasitas 120 Penghuni</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 rounded-3" style="background: rgba(236, 72, 153, 0.18); border: 1px solid rgba(236, 72, 153, 0.35);">
                <small class="text-warning d-block fw-bold"><i class="bi bi-gender-female me-1"></i> Tower An-Nisa</small>
                <div class="text-white small fw-bold">Asrama Putri (4 Lantai)</div>
                <div style="font-size: 0.72rem; color: #cbd5e1;" class="mt-1">Kapasitas 120 Penghuni</div>
              </div>
            </div>
          </div>

          <!-- Modern Highlight Chips -->
          <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);">
              <div class="feature-icon-wrapper icon-orange mb-0" style="width: 38px; height: 38px; font-size: 1.1rem;">
                <i class="bi bi-wifi"></i>
              </div>
              <div>
                <div class="fw-bold text-white small">WiFi Kampus High-Speed 100 Mbps</div>
                <div style="font-size: 0.72rem; color: #94a3b8;">Koneksi stabil di seluruh lantai &amp; kamar</div>
              </div>
            </div>

            <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);">
              <div class="feature-icon-wrapper icon-green mb-0" style="width: 38px; height: 38px; font-size: 1.1rem;">
                <i class="bi bi-shield-check"></i>
              </div>
              <div>
                <div class="fw-bold text-white small">Sistem Keamanan &amp; CCTV 24 Jam</div>
                <div style="font-size: 0.72rem; color: #94a3b8;">Akses gerbang RFID dan pengawas asrama</div>
              </div>
            </div>

            <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);">
              <div class="feature-icon-wrapper icon-blue mb-0" style="width: 38px; height: 38px; font-size: 1.1rem;">
                <i class="bi bi-geo-alt-fill"></i>
              </div>
              <div>
                <div class="fw-bold text-white small">3 Menit Jalan Kaki ke Ruang Kuliah</div>
                <div style="font-size: 0.72rem; color: #94a3b8;">Lokasi strategis di dalam kawasan kampus ULBI</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Quick Stat Counters -->
<section class="py-4 bg-white border-bottom shadow-xs">
  <div class="container">
    <div class="row g-3">
      <div class="col-6 col-lg-3">
        <div class="p-3 d-flex align-items-center gap-3 rounded-3" style="background: var(--slate-50); border: 1px solid var(--slate-200);">
          <div class="feature-icon-wrapper icon-orange mb-0">
            <i class="bi bi-building"></i>
          </div>
          <div>
            <div class="stat-number fs-3"><?= $stat_total_asrama ?></div>
            <small class="text-muted fw-semibold">Gedung Tower</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="p-3 d-flex align-items-center gap-3 rounded-3" style="background: var(--slate-50); border: 1px solid var(--slate-200);">
          <div class="feature-icon-wrapper icon-green mb-0">
            <i class="bi bi-door-open-fill"></i>
          </div>
          <div>
            <div class="stat-number fs-3 text-success"><?= $stat_kamar_tersedia ?></div>
            <small class="text-muted fw-semibold">Kamar Tersedia</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="p-3 d-flex align-items-center gap-3 rounded-3" style="background: var(--slate-50); border: 1px solid var(--slate-200);">
          <div class="feature-icon-wrapper icon-blue mb-0">
            <i class="bi bi-people-fill"></i>
          </div>
          <div>
            <div class="stat-number fs-3 text-info"><?= $stat_total_terisi ?></div>
            <small class="text-muted fw-semibold">Slot Terisi</small>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="p-3 d-flex align-items-center gap-3 rounded-3" style="background: var(--slate-50); border: 1px solid var(--slate-200);">
          <div class="feature-icon-wrapper icon-navy mb-0">
            <i class="bi bi-shield-lock-fill"></i>
          </div>
          <div>
            <div class="stat-number fs-3 text-navy">24 Jam</div>
            <small class="text-muted fw-semibold">Keamanan Terpadu</small>
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
      <span class="badge-soft badge-soft-warning mb-2">Kompleks Bangunan</span>
      <h2 class="fw-bold" style="color:var(--ulbi-navy);">Pilihan Gedung Asrama Mahasiswa</h2>
      <p class="text-muted mx-auto" style="max-width: 640px;">
        Asrama ULBI terbagi dalam zona khusus mahasiswa putra dan mahasiswi putri guna menjamin privasi, kenyamanan, dan ketertiban kehidupan kampus.
      </p>
    </div>

    <div class="row g-4">
      <!-- Asrama Putra Card -->
      <div class="col-md-6">
        <div class="dorm-card">
          <div class="dorm-card-img-wrapper" style="background: linear-gradient(135deg, #0F1E35 0%, #1E3A5F 100%);">
            <div class="dorm-card-badge">
              <span class="badge-soft badge-soft-info"><i class="bi bi-gender-male"></i> Khusus Putra</span>
            </div>
            <div class="text-center text-white px-3">
              <i class="bi bi-buildings fs-1 mb-2 text-info opacity-75"></i>
              <h4 class="fw-bold mb-0">Tower Al-Fatih</h4>
              <small class="text-light opacity-75">Gedung Asrama Mahasiswa Putra</small>
            </div>
          </div>
          <div class="dorm-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-muted small"><i class="bi bi-geo-alt-fill text-warning me-1"></i> Zona Barat Kampus ULBI</span>
              <span class="badge bg-light text-dark border">4 Lantai &bull; 120 Kapasitas</span>
            </div>
            <p class="text-secondary small mb-3">
              Dilengkapi ruang belajar komunal, musholla representatif, sarana olahraga santai, dan area parkir sepeda motor dengan sistem satu pintu gerbang.
            </p>
            <div class="d-flex flex-wrap gap-2 mb-4">
              <span class="dorm-feature-pill"><i class="bi bi-wifi text-primary"></i> WiFi Kampus</span>
              <span class="dorm-feature-pill"><i class="bi bi-cup-hot text-danger"></i> Dapur Bersama</span>
              <span class="dorm-feature-pill"><i class="bi bi-shield-check text-success"></i> CCTV 24 Jam</span>
              <span class="dorm-feature-pill"><i class="bi bi-building text-info"></i> Musholla</span>
            </div>
            <div class="mt-auto">
              <a href="<?= BASE_URL ?>/info_kamar.php?asrama=1" class="btn btn-outline-primary w-100">
                <i class="bi bi-door-open me-1"></i> Eksplorasi Kamar Putra
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Asrama Putri Card -->
      <div class="col-md-6">
        <div class="dorm-card">
          <div class="dorm-card-img-wrapper" style="background: linear-gradient(135deg, #831843 0%, #9D174D 100%);">
            <div class="dorm-card-badge">
              <span class="badge-soft badge-soft-danger"><i class="bi bi-gender-female"></i> Khusus Putri</span>
            </div>
            <div class="text-center text-white px-3">
              <i class="bi bi-buildings fs-1 mb-2 text-warning opacity-75"></i>
              <h4 class="fw-bold mb-0">Tower An-Nisa</h4>
              <small class="text-light opacity-75">Gedung Asrama Mahasiswi Putri</small>
            </div>
          </div>
          <div class="dorm-card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-muted small"><i class="bi bi-geo-alt-fill text-warning me-1"></i> Zona Timur Kampus ULBI</span>
              <span class="badge bg-light text-dark border">4 Lantai &bull; 120 Kapasitas</span>
            </div>
            <p class="text-secondary small mb-3">
              Didesain dengan tingkat privasi tinggi, penjagaan staf wanita 24 jam, akses RFID otomatis, dapur higienis di tiap lantai, dan ruang komunal mahasiswi.
            </p>
            <div class="d-flex flex-wrap gap-2 mb-4">
              <span class="dorm-feature-pill"><i class="bi bi-wifi text-primary"></i> WiFi Kampus</span>
              <span class="dorm-feature-pill"><i class="bi bi-key text-warning"></i> Akses Kartu RFID</span>
              <span class="dorm-feature-pill"><i class="bi bi-shield-lock text-success"></i> Pengawas Putri 24 Jam</span>
              <span class="dorm-feature-pill"><i class="bi bi-basket text-info"></i> Fasilitas Laundry</span>
            </div>
            <div class="mt-auto">
              <a href="<?= BASE_URL ?>/info_kamar.php?asrama=2" class="btn btn-outline-danger w-100">
                <i class="bi bi-door-open me-1"></i> Eksplorasi Kamar Putri
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Kamar Siap Huni Terbaru -->
<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
      <div>
        <span class="badge-soft badge-soft-success mb-2">Update Ketersediaan</span>
        <h2 class="fw-bold" style="color:var(--ulbi-navy);">Katalog Kamar Siap Huni</h2>
        <p class="text-muted mb-0">Pilih kamar yang sesuai dengan preferensi privasi dan kebutuhan studi Anda.</p>
      </div>
      <a href="<?= BASE_URL ?>/info_kamar.php" class="btn btn-outline-dark btn-sm mt-3 mt-md-0">
        Lihat Semua Kamar (<?= $stat_kamar_tersedia ?>) <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4">
      <?php foreach ($kamar_unggulan as $km): 
        $sisa_slot = max(0, $km['kapasitas'] - $km['terisi']);
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="room-card-modern">
          <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom border-light">
            <div>
              <span class="badge-soft <?= ($km['kategori_gender'] === 'L') ? 'badge-soft-info' : 'badge-soft-danger' ?> mb-2">
                <?= ($km['kategori_gender'] === 'L') ? 'Putra' : 'Putri' ?> &bull; <?= htmlspecialchars($km['nama_asrama']) ?>
              </span>
              <h4 class="fw-bold mb-0 text-navy">Kamar <?= htmlspecialchars($km['nomor_kamar']) ?></h4>
              <small class="text-muted">Lantai <?= $km['lantai'] ?> &bull; <?= htmlspecialchars($km['tipe_kamar']) ?></small>
            </div>
            <?= get_room_badge($km['status']) ?>
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1 small">
              <span class="text-muted"><i class="bi bi-people me-1"></i>Kapasitas:</span>
              <span class="fw-bold"><?= $km['terisi'] ?> / <?= $km['kapasitas'] ?> Orang (Sisa: <?= $sisa_slot ?>)</span>
            </div>
            <?php $pct = ($km['kapasitas'] > 0) ? ($km['terisi'] / $km['kapasitas']) * 100 : 0; ?>
            <div class="progress" style="height: 6px; border-radius: 4px;">
              <div class="progress-bar <?= ($pct >= 100) ? 'bg-danger' : (($pct >= 50) ? 'bg-warning' : 'bg-success') ?>" 
                   role="progressbar" style="width: <?= $pct ?>%"></div>
            </div>
          </div>

          <div class="p-2 rounded-3 bg-light mb-3 text-secondary small" style="min-height: 48px; font-size: 0.82rem;">
            <?= htmlspecialchars(substr($km['fasilitas_kamar'], 0, 95)) ?>...
          </div>

          <div class="mt-auto pt-2 border-top">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <div class="room-price-sub">Tarif per Semester:</div>
                <div class="room-price-tag"><?= format_rupiah($km['tarif_per_semester']) ?></div>
              </div>
              <div class="text-end">
                <div class="room-price-sub">Tarif per Tahun:</div>
                <div class="fw-bold text-navy small"><?= format_rupiah($km['tarif_per_tahun']) ?></div>
              </div>
            </div>

            <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php?kamar_id=<?= $km['id'] ?>" class="btn btn-ulbi-primary btn-sm w-100">
              <i class="bi bi-plus-circle me-1"></i> Ajukan Reservasi Kamar
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 6 Tahap Alur Reservasi -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <span class="badge-soft badge-soft-navy mb-2">Panduan Cepat</span>
      <h2 class="fw-bold" style="color:var(--ulbi-navy);">6 Tahap Mudah Reservasi Asrama</h2>
      <p class="text-muted mx-auto" style="max-width: 600px;">
        Proses pengajuan terstandarisasi untuk mempermudah calon penghuni baru dan menjamin kelancaran administrasi kampus.
      </p>
    </div>

    <div class="row g-4">
      <div class="col-md-4 col-sm-6">
        <div class="feature-box">
          <div class="feature-icon-wrapper icon-orange">
            <i class="bi bi-person-plus-fill"></i>
          </div>
          <h5 class="fw-bold mb-2">1. Registrasi Akun</h5>
          <p class="text-secondary small mb-0">Daftarkan akun mahasiswa menggunakan NPM, email kampus, dan data kontak aktif Anda.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-box">
          <div class="feature-icon-wrapper icon-blue">
            <i class="bi bi-person-lines-fill"></i>
          </div>
          <h5 class="fw-bold mb-2">2. Lengkapi Profil</h5>
          <p class="text-secondary small mb-0">Isi data diri lengkap, alamat asal, serta nomor kontak darurat orang tua atau wali mahasiswa.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-box">
          <div class="feature-icon-wrapper icon-green">
            <i class="bi bi-door-open-fill"></i>
          </div>
          <h5 class="fw-bold mb-2">3. Pilih Kamar &amp; Durasi</h5>
          <p class="text-secondary small mb-0">Pilih gedung asrama sesuai gender, lantai, tipe kamar tersedia, dan durasi sewa (1 semester / 1 tahun).</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-box">
          <div class="feature-icon-wrapper icon-navy">
            <i class="bi bi-file-earmark-arrow-up-fill"></i>
          </div>
          <h5 class="fw-bold mb-2">4. Unggah KTM / Berkas</h5>
          <p class="text-secondary small mb-0">Unggah berkas Kartu Tanda Mahasiswa (KTM) atau bukti registrasi sah perkuliahan ULBI.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-box">
          <div class="feature-icon-wrapper icon-orange">
            <i class="bi bi-check2-circle"></i>
          </div>
          <h5 class="fw-bold mb-2">5. Verifikasi &amp; Bayar</h5>
          <p class="text-secondary small mb-0">Petugas memvalidasi berkas. Setelah disetujui, lakukan pembayaran sewa dan konfirmasi bukti transfer.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="feature-box">
          <div class="feature-icon-wrapper icon-blue">
            <i class="bi bi-printer-fill"></i>
          </div>
          <h5 class="fw-bold mb-2">6. Cetak Bukti &amp; Check-in</h5>
          <p class="text-secondary small mb-0">Cetak lembar bukti reservasi resmi digital dan bawa saat serah terima kunci di kantor asrama.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Pengumuman Asrama Terbaru -->
<?php if (!empty($pengumuman)): ?>
<section class="py-5 bg-white border-top">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge-soft badge-soft-warning mb-2">Papan Pengumuman</span>
      <h3 class="fw-bold" style="color:var(--ulbi-navy);">Pengumuman &amp; Informasi Terkini</h3>
    </div>

    <div class="row g-4 justify-content-center">
      <?php foreach ($pengumuman as $pg): ?>
      <div class="col-md-4">
        <div class="card border-0 shadow-xs rounded-3 h-100 p-4" style="border: 1px solid var(--slate-200) !important;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge-soft badge-soft-navy"><?= htmlspecialchars($pg['kategori']) ?></span>
            <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= format_tanggal($pg['tanggal']) ?></small>
          </div>
          <h5 class="fw-bold mb-2 text-navy"><?= htmlspecialchars($pg['judul']) ?></h5>
          <p class="text-secondary small mb-0"><?= nl2br(htmlspecialchars($pg['isi'])) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Call To Action -->
<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, var(--ulbi-navy) 0%, var(--ulbi-navy-dark) 100%);">
  <div class="container py-4">
    <h2 class="fw-bold mb-3">Siap Menjadi Bagian dari Asrama Mahasiswa ULBI?</h2>
    <p class="text-light opacity-75 mx-auto mb-4" style="max-width: 620px;">
      Segera amankan slot kamar pilihan Anda untuk semester akademik mendatang sebelum kuota terpenuhi.
    </p>
    <div class="d-flex justify-content-center gap-3">
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-ulbi-primary btn-lg">
        <i class="bi bi-person-plus-fill me-1"></i> Daftar Akun Mahasiswa
      </a>
      <a href="<?= BASE_URL ?>/kontak.php" class="btn btn-ulbi-glass btn-lg">
        <i class="bi bi-question-circle me-1"></i> Hubungi Pengelola
      </a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
