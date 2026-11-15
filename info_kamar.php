<?php
/**
 * SIRASMA ULBI - Katalog & Ketersediaan Kamar Asrama
 * Modern Minimalist + Elegant Campus SaaS Prototype
 */
$page_title = 'Informasi & Ketersediaan Kamar';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$db = get_db();

// Filter parameters
$asrama_filter = sanitize($_GET['asrama'] ?? '');
$tipe_filter = sanitize($_GET['tipe'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');
$keyword = sanitize($_GET['q'] ?? '');

// Bangun query filter
$sql = "SELECT k.*, a.nama_asrama, a.kategori_gender, a.lokasi 
        FROM kamar k 
        JOIN asrama a ON k.asrama_id = a.id 
        WHERE k.status != 'nonaktif'";
$params = [];

if (!empty($asrama_filter)) {
    $sql .= " AND k.asrama_id = ?";
    $params[] = $asrama_filter;
}

if (!empty($tipe_filter)) {
    $sql .= " AND k.tipe_kamar LIKE ?";
    $params[] = "%" . $tipe_filter . "%";
}

if (!empty($status_filter)) {
    $sql .= " AND k.status = ?";
    $params[] = $status_filter;
}

if (!empty($keyword)) {
    $sql .= " AND (k.nomor_kamar LIKE ? OR k.fasilitas_kamar LIKE ?)";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
}

$sql .= " ORDER BY a.nama_asrama ASC, k.nomor_kamar ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$kamar_list = $stmt->fetchAll();

// Ambil daftar gedung asrama untuk opsi filter dropdown
$daftar_asrama = $db->query("SELECT * FROM asrama ORDER BY nama_asrama ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="py-4 text-white" style="background: linear-gradient(135deg, var(--ulbi-navy) 0%, var(--ulbi-navy-light) 100%); border-bottom: 1px solid rgba(255,255,255,0.1);">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2" style="font-size: 0.85rem;">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-warning text-decoration-none"><i class="bi bi-house me-1"></i>Beranda</a></li>
        <li class="breadcrumb-item active text-light opacity-75" aria-current="page">Informasi Kamar</li>
      </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <h2 class="fw-bold mb-1 text-white">Daftar &amp; Ketersediaan Kamar Asrama</h2>
        <p class="text-light opacity-75 mb-0 small">Pantau kapasitas hunian, spesifikasi fasilitas, dan tarif sewa asrama ULBI secara akurat.</p>
      </div>
      <div class="d-flex gap-2">
        <span class="badge-soft badge-soft-info bg-white bg-opacity-10 text-white border border-white border-opacity-25 px-3 py-2">
          <i class="bi bi-check2-circle me-1"></i> Data Terkoneksi Real-time
        </span>
      </div>
    </div>
  </div>
</div>

<div class="container py-4">
  <!-- Filter Card -->
  <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
    <div class="card-body p-3 p-md-4">
      <form method="GET" action="" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Pencarian Nomor / Fasilitas</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control border-start-0" name="q" placeholder="Contoh: A-101, AC, WiFi..." value="<?= htmlspecialchars($keyword) ?>">
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Gedung Asrama</label>
          <select name="asrama" class="form-select">
            <option value="">Semua Gedung Asrama</option>
            <?php foreach ($daftar_asrama as $a): ?>
              <option value="<?= $a['id'] ?>" <?= ($asrama_filter == $a['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['nama_asrama']) ?> (<?= ($a['kategori_gender'] === 'L') ? 'Putra' : 'Putri' ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Tipe Kamar</label>
          <select name="tipe" class="form-select">
            <option value="">Semua Tipe</option>
            <option value="Standar" <?= ($tipe_filter === 'Standar') ? 'selected' : '' ?>>Standar (4 Orang)</option>
            <option value="Deluxe" <?= ($tipe_filter === 'Deluxe') ? 'selected' : '' ?>>Deluxe (2 Orang)</option>
            <option value="Single" <?= ($tipe_filter === 'Single') ? 'selected' : '' ?>>Single (1 Orang)</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Status Ketersediaan</label>
          <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="tersedia" <?= ($status_filter === 'tersedia') ? 'selected' : '' ?>>Hanya yang Tersedia</option>
            <option value="penuh" <?= ($status_filter === 'penuh') ? 'selected' : '' ?>>Penuh</option>
            <option value="pemeliharaan" <?= ($status_filter === 'pemeliharaan') ? 'selected' : '' ?>>Perbaikan / Pemeliharaan</option>
          </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-ulbi-primary w-100">
            <i class="bi bi-funnel-fill"></i> Terapkan
          </button>
          <a href="<?= BASE_URL ?>/info_kamar.php" class="btn btn-outline-secondary" title="Reset Filter">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Filter Feedback Bar -->
  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
    <div class="text-muted small">
      Ditemukan <strong class="text-navy"><?= count($kamar_list) ?> kamar</strong> yang sesuai kriteria pencarian Anda.
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge-soft badge-soft-success">
        <i class="bi bi-door-open-fill"></i> Kuota Tersedia
      </span>
      <span class="badge-soft badge-soft-danger">
        <i class="bi bi-door-closed-fill"></i> Kamar Penuh
      </span>
    </div>
  </div>

  <!-- Room Grid -->
  <?php if (empty($kamar_list)): ?>
    <div class="text-center py-5 bg-white rounded-4 shadow-sm my-4 border" style="border-color: var(--slate-200) !important;">
      <i class="bi bi-door-closed text-muted" style="font-size: 3.5rem;"></i>
      <h5 class="fw-bold mt-3 mb-1 text-navy">Tidak Ada Kamar yang Sesuai</h5>
      <p class="text-muted small mb-3">Silakan sesuaikan kembali filter gedung, tipe kamar, atau status ketersediaan di atas.</p>
      <a href="<?= BASE_URL ?>/info_kamar.php" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-clockwise me-1"></i> Reset Seluruh Filter
      </a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($kamar_list as $km): 
        $sisa_slot = max(0, $km['kapasitas'] - $km['terisi']);
        $is_male = ($km['kategori_gender'] === 'L');
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="room-card-modern h-100">
          <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom border-light">
            <div>
              <span class="badge-soft <?= $is_male ? 'badge-soft-info' : 'badge-soft-danger' ?> mb-2">
                <i class="bi <?= $is_male ? 'bi-gender-male' : 'bi-gender-female' ?>"></i>
                <?= $is_male ? 'Asrama Putra' : 'Asrama Putri' ?> &bull; <?= htmlspecialchars($km['nama_asrama']) ?>
              </span>
              <h4 class="fw-bold mb-0 text-navy">Kamar <?= htmlspecialchars($km['nomor_kamar']) ?></h4>
              <small class="text-muted">Lantai <?= $km['lantai'] ?> &bull; <?= htmlspecialchars($km['tipe_kamar']) ?></small>
            </div>
            <?= get_room_badge($km['status']) ?>
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1 small">
              <span class="text-muted"><i class="bi bi-people-fill me-1"></i> Kuota Hunian:</span>
              <span class="fw-bold">
                <?= $km['terisi'] ?> / <?= $km['kapasitas'] ?> Orang
                <?php if ($sisa_slot > 0 && $km['status'] === 'tersedia'): ?>
                  <span class="text-success">(Sisa: <?= $sisa_slot ?>)</span>
                <?php endif; ?>
              </span>
            </div>
            <?php $pct = ($km['kapasitas'] > 0) ? ($km['terisi'] / $km['kapasitas']) * 100 : 0; ?>
            <div class="progress" style="height: 6px; border-radius: 4px;">
              <div class="progress-bar <?= ($pct >= 100) ? 'bg-danger' : (($pct >= 50) ? 'bg-warning' : 'bg-success') ?>" 
                   role="progressbar" style="width: <?= $pct ?>%"></div>
            </div>
          </div>

          <!-- Fasilitas Kamar -->
          <div class="mb-3 flex-grow-1">
            <small class="fw-semibold text-muted d-block mb-1">Fasilitas Kamar:</small>
            <div class="p-2 rounded-3 bg-light text-secondary small" style="min-height: 52px; font-size: 0.82rem; border: 1px solid var(--slate-200);">
              <?= nl2br(htmlspecialchars($km['fasilitas_kamar'] ?: 'Fasilitas standar: tempat tidur springbed, meja belajar, kursi, dan lemari pakaian dua pintu.')) ?>
            </div>
          </div>

          <!-- Tarif -->
          <div class="p-2 rounded-3 bg-light mb-3" style="border: 1px solid var(--slate-200);">
            <div class="d-flex justify-content-between align-items-center">
              <span class="small text-muted">Tarif Semester:</span>
              <span class="fw-bold text-success fs-6"><?= format_rupiah($km['tarif_per_semester']) ?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
              <span class="small text-muted">Tarif 1 Tahun:</span>
              <span class="fw-bold text-navy small"><?= format_rupiah($km['tarif_per_tahun']) ?></span>
            </div>
          </div>

          <!-- Action Button -->
          <div class="mt-auto">
            <?php if ($km['status'] === 'tersedia' && $sisa_slot > 0): ?>
              <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php?kamar_id=<?= $km['id'] ?>" class="btn btn-ulbi-primary btn-sm w-100">
                <i class="bi bi-plus-circle me-1"></i> Ajukan Reservasi Kamar
              </a>
            <?php elseif ($km['status'] === 'pemeliharaan'): ?>
              <button class="btn btn-secondary btn-sm w-100" disabled>
                <i class="bi bi-tools me-1"></i> Sedang Dalam Perawatan
              </button>
            <?php else: ?>
              <button class="btn btn-danger btn-sm w-100 opacity-75" disabled>
                <i class="bi bi-slash-circle me-1"></i> Kamar Penuh
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
