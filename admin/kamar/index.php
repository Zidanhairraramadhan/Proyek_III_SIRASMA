<?php
/**
 * SIRASMA ULBI - Manajemen Kamar & Fasilitas Asrama
 */
$page_title = 'Kelola Kamar & Fasilitas';
$is_dashboard = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$db = get_db();

// Filter
$asrama_id = sanitize($_GET['asrama_id'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$q = sanitize($_GET['q'] ?? '');

$sql = "SELECT k.*, a.nama_asrama, a.kategori_gender 
        FROM kamar k 
        JOIN asrama a ON k.asrama_id = a.id 
        WHERE 1=1";
$params = [];

if (!empty($asrama_id)) {
    $sql .= " AND k.asrama_id = ?";
    $params[] = $asrama_id;
}

if (!empty($status)) {
    $sql .= " AND k.status = ?";
    $params[] = $status;
}

if (!empty($q)) {
    $sql .= " AND (k.nomor_kamar LIKE ? OR k.fasilitas_kamar LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY a.nama_asrama ASC, k.nomor_kamar ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$kamar_list = $stmt->fetchAll();

$daftar_asrama = $db->query("SELECT * FROM asrama ORDER BY nama_asrama ASC")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <header class="dashboard-topbar">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light border d-lg-none" type="button" onclick="document.getElementById('dashboardSidebar').classList.toggle('show')">
          <i class="bi bi-list fs-5"></i>
        </button>
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Kelola Kamar & Fasilitas Asrama</h5>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/admin/kamar/tambah.php" class="btn btn-ulbi-primary btn-sm">
          <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kamar Baru
        </a>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Filter Bar -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
          <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-4">
              <input type="text" class="form-control form-control-sm" name="q" placeholder="Cari nomor kamar..." value="<?= htmlspecialchars($q) ?>">
            </div>
            <div class="col-md-3">
              <select name="asrama_id" class="form-select form-select-sm">
                <option value="">Semua Gedung Asrama</option>
                <?php foreach ($daftar_asrama as $a): ?>
                  <option value="<?= $a['id'] ?>" <?= ($asrama_id == $a['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['nama_asrama']) ?> (<?= ($a['kategori_gender'] === 'L') ? 'Putra' : 'Putri' ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="tersedia" <?= ($status === 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                <option value="penuh" <?= ($status === 'penuh') ? 'selected' : '' ?>>Penuh</option>
                <option value="pemeliharaan" <?= ($status === 'pemeliharaan') ? 'selected' : '' ?>>Pemeliharaan</option>
                <option value="nonaktif" <?= ($status === 'nonaktif') ? 'selected' : '' ?>>Diarsipkan / Nonaktif</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-dark btn-sm w-100">
                <i class="bi bi-filter me-1"></i> Saring Data
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Kamar Table Card -->
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-door-open-fill text-warning me-2"></i>Daftar Seluruh Kamar Asrama (<?= count($kamar_list) ?>)
          </h6>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-light">
                <tr>
                  <th>Gedung & Lantai</th>
                  <th>Nomor Kamar</th>
                  <th>Tipe Kamar</th>
                  <th>Kapasitas / Terisi</th>
                  <th>Tarif per Semester</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($kamar_list as $km): 
                  $sisa = max(0, $km['kapasitas'] - $km['terisi']);
                ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($km['nama_asrama']) ?></strong>
                    <div class="text-muted" style="font-size:0.75rem;">Lantai <?= $km['lantai'] ?> &bull; <?= ($km['kategori_gender'] === 'L') ? 'Putra' : 'Putri' ?></div>
                  </td>
                  <td>
                    <span class="fs-6 fw-bold text-navy"><?= htmlspecialchars($km['nomor_kamar']) ?></span>
                  </td>
                  <td><?= htmlspecialchars($km['tipe_kamar']) ?></td>
                  <td>
                    <strong><?= $km['terisi'] ?> / <?= $km['kapasitas'] ?></strong> Penghuni
                    <span class="text-muted" style="font-size:0.75rem;">(Sisa: <?= $sisa ?>)</span>
                  </td>
                  <td>
                    <strong class="text-success"><?= format_rupiah($km['tarif_per_semester']) ?></strong>
                  </td>
                  <td>
                    <?= get_room_badge($km['status']) ?>
                  </td>
                  <td class="text-end">
                    <div class="btn-group btn-group-sm">
                      <a href="<?= BASE_URL ?>/admin/kamar/edit.php?id=<?= $km['id'] ?>" class="btn btn-outline-primary" title="Edit Kamar">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <?php if ($km['status'] !== 'nonaktif'): ?>
                        <a href="<?= BASE_URL ?>/admin/kamar/hapus.php?id=<?= $km['id'] ?>" 
                           class="btn btn-outline-danger" 
                           onclick="return confirm('Apakah Anda yakin ingin menonaktifkan/mengarsipkan kamar <?= htmlspecialchars($km['nomor_kamar']) ?>?')" 
                           title="Nonaktifkan Kamar">
                          <i class="bi bi-archive"></i>
                        </a>
                      <?php else: ?>
                        <a href="<?= BASE_URL ?>/admin/kamar/edit.php?id=<?= $km['id'] ?>&restore=1" class="btn btn-outline-success" title="Pulihkan Kamar">
                          <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
