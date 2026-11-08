<?php
/**
 * SIRASMA ULBI - Katalog & Ketersediaan Kamar Asrama
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

<div class="bg-navy py-4 text-white" style="background: linear-gradient(135deg, var(--ulbi-navy) 0%, #173663 100%);">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb breadcrumb-dark mb-2">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-warning text-decoration-none">Beranda</a></li>
        <li class="breadcrumb-item active text-light" aria-current="page">Informasi Kamar</li>
      </ol>
    </nav>
    <h2 class="fw-bold mb-1">Daftar & Ketersediaan Kamar Asrama</h2>
    <p class="text-light opacity-75 mb-0">Informasi spesifikasi kamar, kapasitas, ketersediaan slot, dan tarif sewa asrama ULBI.</p>
  </div>
</div>

<div class="container py-4">
  <!-- Filter Box Card -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3 p-md-4">
      <form method="GET" action="" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Pencarian Nomor / Fasilitas</label>
          <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" name="q" placeholder="Cari kamar..." value="<?= htmlspecialchars($keyword) ?>">
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label small fw-semibold">Gedung Asrama</label>
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
          <label class="form-label small fw-semibold">Tipe Kamar</label>
          <select name="tipe" class="form-select">
            <option value="">Semua Tipe</option>
            <option value="Standar" <?= ($tipe_filter === 'Standar') ? 'selected' : '' ?>>Standar (4 Orang)</option>
            <option value="Deluxe" <?= ($tipe_filter === 'Deluxe') ? 'selected' : '' ?>>Deluxe (2 Orang)</option>
            <option value="Single" <?= ($tipe_filter === 'Single') ? 'selected' : '' ?>>Single (1 Orang)</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small fw-semibold">Status Ketersediaan</label>
          <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="tersedia" <?= ($status_filter === 'tersedia') ? 'selected' : '' ?>>Tersedia Saja</option>
            <option value="penuh" <?= ($status_filter === 'penuh') ? 'selected' : '' ?>>Penuh</option>
            <option value="pemeliharaan" <?= ($status_filter === 'pemeliharaan') ? 'selected' : '' ?>>Pemeliharaan</option>
          </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-ulbi-primary w-100">
            <i class="bi bi-filter me-1"></i> Filter
          </button>
          <a href="<?= BASE_URL ?>/info_kamar.php" class="btn btn-outline-secondary" title="Reset Filter">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Results Count -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">
      Menampilkan <strong><?= count($kamar_list) ?></strong> kamar yang sesuai kriteria pencarian.
    </div>
    <div class="d-flex gap-2">
      <span class="badge bg-success-subtle text-success border border-success px-2 py-1 small">
        <i class="bi bi-check-circle-fill me-1"></i> Tersedia: Kamar dengan sisa slot
      </span>
    </div>
  </div>

  <!-- Room Grid -->
  <?php if (empty($kamar_list)): ?>
    <div class="text-center py-5 bg-white rounded-3 shadow-sm my-4">
      <i class="bi bi-door-closed text-muted" style="font-size: 3.5rem;"></i>
      <h5 class="fw-bold mt-3 mb-1">Tidak Ada Kamar yang Ditemukan</h5>
      <p class="text-muted small mb-3">Coba ubah filter atau kata kunci pencarian Anda untuk melihat kamar lain.</p>
      <a href="<?= BASE_URL ?>/info_kamar.php" class="btn btn-outline-primary btn-sm">Reset Pencarian</a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($kamar_list as $km): 
        $sisa_slot = max(0, $km['kapasitas'] - $km['terisi']);
        $is_male = ($km['kategori_gender'] === 'L');
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="room-card shadow-sm h-100">
          <div class="room-card-header border-bottom">
            <div>
              <span class="badge bg-<?= $is_male ? 'primary' : 'danger' ?> mb-1">
                <i class="bi bi-building me-1"></i> <?= $is_male ? 'Asrama Putra' : 'Asrama Putri' ?>
              </span>
              <h4 class="fw-bold mb-0 text-navy">Kamar <?= htmlspecialchars($km['nomor_kamar']) ?></h4>
              <small class="text-muted">Lantai <?= $km['lantai'] ?> &bull; <?= htmlspecialchars($km['tipe_kamar']) ?></small>
            </div>
            <div>
              <?= get_room_badge($km['status']) ?>
            </div>
          </div>

          <div class="room-card-body pt-3 d-flex flex-column">
            <!-- Kapasitas & Slot -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="small text-muted"><i class="bi bi-people-fill me-1"></i> Kuota Hunian:</span>
              <span class="fw-bold small">
                <?= $km['terisi'] ?> / <?= $km['kapasitas'] ?> Penghuni 
                <?php if ($sisa_slot > 0 && $km['status'] === 'tersedia'): ?>
                  <span class="text-success">(Sisa <?= $sisa_slot ?>)</span>
                <?php endif; ?>
              </span>
            </div>

            <!-- Progress Bar -->
            <div class="progress mb-3" style="height: 7px;">
              <?php $pct = ($km['kapasitas'] > 0) ? ($km['terisi'] / $km['kapasitas']) * 100 : 0; ?>
              <div class="progress-bar <?= ($pct >= 100) ? 'bg-danger' : (($pct >= 50) ? 'bg-warning' : 'bg-success') ?>" 
                   role="progressbar" style="width: <?= $pct ?>%"></div>
            </div>

            <!-- Fasilitas Kamar -->
            <div class="mb-3 flex-grow-1">
              <small class="fw-semibold text-muted d-block mb-1">Fasilitas Kamar:</small>
              <p class="small text-secondary mb-0 bg-light p-2 rounded border">
                <?= nl2br(htmlspecialchars($km['fasilitas_kamar'] ?: 'Fasilitas standar tempat tidur, meja belajar, dan lemari.')) ?>
              </p>
            </div>

            <!-- Tarif -->
            <div class="room-price-box mb-3">
              <div class="d-flex justify-content-between align-items-center">
                <span class="small text-muted">Tarif Semester:</span>
                <span class="fw-bold text-success fs-6"><?= format_rupiah($km['tarif_per_semester']) ?></span>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-1">
                <span class="small text-muted">Tarif 1 Tahun:</span>
                <span class="fw-bold text-navy fs-6"><?= format_rupiah($km['tarif_per_tahun']) ?></span>
              </div>
            </div>

            <!-- Action Button -->
            <div class="mt-auto">
              <?php if ($km['status'] === 'tersedia' && $sisa_slot > 0): ?>
                <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php?kamar_id=<?= $km['id'] ?>" class="btn btn-ulbi-primary w-100">
                  <i class="bi bi-bookmark-plus-fill me-1"></i> Ajukan Reservasi Kamar
                </a>
              <?php elseif ($km['status'] === 'pemeliharaan'): ?>
                <button class="btn btn-secondary w-100 btn-sm" disabled>
                  <i class="bi bi-tools me-1"></i> Sedang Dalam Perawatan
                </button>
              <?php else: ?>
                <button class="btn btn-danger w-100 btn-sm opacity-75" disabled>
                  <i class="bi bi-slash-circle me-1"></i> Kamar Penuh
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
