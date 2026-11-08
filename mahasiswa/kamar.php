<?php
/**
 * SIRASMA ULBI - Cari & Pilih Kamar (Portal Mahasiswa)
 */
$page_title = 'Cari Kamar Asrama';
$is_dashboard = true;
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['mahasiswa']);

$user = current_user();
$db = get_db();

// Dapatkan profil mahasiswa untuk mendeteksi gender otomatis
$stmt_prof = $db->prepare("SELECT gender FROM mahasiswa_profiles WHERE user_id = ?");
$stmt_prof->execute([$user['id']]);
$mhs_gender = $stmt_prof->fetchColumn() ?: 'L';

$tipe_filter = sanitize($_GET['tipe'] ?? '');
$status_filter = sanitize($_GET['status'] ?? 'tersedia');

$sql = "SELECT k.*, a.nama_asrama, a.kategori_gender, a.lokasi 
        FROM kamar k 
        JOIN asrama a ON k.asrama_id = a.id 
        WHERE a.kategori_gender = ? AND k.status != 'nonaktif'";
$params = [$mhs_gender];

if (!empty($tipe_filter)) {
    $sql .= " AND k.tipe_kamar LIKE ?";
    $params[] = "%" . $tipe_filter . "%";
}

if (!empty($status_filter)) {
    $sql .= " AND k.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY k.nomor_kamar ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$kamar_list = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <header class="dashboard-topbar">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light border d-lg-none" type="button" onclick="document.getElementById('dashboardSidebar').classList.toggle('show')">
          <i class="bi bi-list fs-5"></i>
        </button>
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Katalog Kamar Asrama</h5>
      </div>
      <div>
        <span class="badge bg-<?= ($mhs_gender === 'L') ? 'primary' : 'danger' ?> px-3 py-2">
          <i class="bi bi-building me-1"></i> Khusus <?= ($mhs_gender === 'L') ? 'Asrama Putra (Tower Al-Fatih)' : 'Asrama Putri (Tower An-Nisa)' ?>
        </span>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Info Alert -->
      <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
        <div class="small">
          Sesuai tata tertib asrama kampus ULBI, sistem secara otomatis menampilkan kamar yang sesuai dengan gender mahasiswa (<?= ($mhs_gender === 'L') ? 'Putra' : 'Putri' ?>).
        </div>
      </div>

      <!-- Filter Controls -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
          <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-5">
              <select name="tipe" class="form-select form-select-sm">
                <option value="">-- Semua Tipe Kamar --</option>
                <option value="Standar" <?= ($tipe_filter === 'Standar') ? 'selected' : '' ?>>Standar (4 Orang)</option>
                <option value="Deluxe" <?= ($tipe_filter === 'Deluxe') ? 'selected' : '' ?>>Deluxe (2 Orang)</option>
                <option value="Single" <?= ($tipe_filter === 'Single') ? 'selected' : '' ?>>Single (1 Orang)</option>
              </select>
            </div>
            <div class="col-md-5">
              <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status Ketersediaan</option>
                <option value="tersedia" <?= ($status_filter === 'tersedia') ? 'selected' : '' ?>>Hanya yang Tersedia</option>
                <option value="penuh" <?= ($status_filter === 'penuh') ? 'selected' : '' ?>>Penuh</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-ulbi-primary btn-sm w-100">
                <i class="bi bi-funnel-fill me-1"></i> Terapkan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Room Cards Grid -->
      <div class="row g-4">
        <?php foreach ($kamar_list as $km): 
          $sisa_slot = max(0, $km['kapasitas'] - $km['terisi']);
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="room-card shadow-sm h-100">
            <div class="room-card-header border-bottom">
              <div>
                <h4 class="fw-bold mb-0 text-navy">Kamar <?= htmlspecialchars($km['nomor_kamar']) ?></h4>
                <small class="text-muted">Lantai <?= $km['lantai'] ?> &bull; <?= htmlspecialchars($km['tipe_kamar']) ?></small>
              </div>
              <div>
                <?= get_room_badge($km['status']) ?>
              </div>
            </div>

            <div class="room-card-body pt-3 d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-muted"><i class="bi bi-people-fill me-1"></i> Keterisian:</span>
                <span class="fw-bold small"><?= $km['terisi'] ?> / <?= $km['kapasitas'] ?> Orang (Sisa <?= $sisa_slot ?>)</span>
              </div>

              <div class="progress mb-3" style="height: 6px;">
                <?php $pct = ($km['kapasitas'] > 0) ? ($km['terisi'] / $km['kapasitas']) * 100 : 0; ?>
                <div class="progress-bar <?= ($pct >= 100) ? 'bg-danger' : (($pct >= 50) ? 'bg-warning' : 'bg-success') ?>" 
                     role="progressbar" style="width: <?= $pct ?>%"></div>
              </div>

              <p class="small text-secondary mb-3 flex-grow-1">
                <?= htmlspecialchars(substr($km['fasilitas_kamar'], 0, 95)) ?>...
              </p>

              <div class="room-price-box mb-3">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="small text-muted">Tarif Semester:</span>
                  <strong class="text-success"><?= format_rupiah($km['tarif_per_semester']) ?></strong>
                </div>
              </div>

              <div class="mt-auto">
                <?php if ($km['status'] === 'tersedia' && $sisa_slot > 0): ?>
                  <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php?kamar_id=<?= $km['id'] ?>" class="btn btn-ulbi-primary w-100 btn-sm">
                    <i class="bi bi-bookmark-plus-fill me-1"></i> Pilih Kamar Ini
                  </a>
                <?php else: ?>
                  <button class="btn btn-secondary w-100 btn-sm" disabled>Kamar Tidak Tersedia</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
