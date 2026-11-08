<?php
/**
 * SIRASMA ULBI - Direktori Mahasiswa & Penghuni Asrama (Admin)
 */
$page_title = 'Data Mahasiswa & Penghuni';
$is_dashboard = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$db = get_db();

$q = sanitize($_GET['q'] ?? '');

$sql = "SELECT u.id as user_id, u.username, u.full_name, u.email, u.phone, u.status as user_status,
               mp.npm, mp.prodi, mp.gender, mp.kota_asal, mp.nama_ortu_wali, mp.hp_ortu_wali,
               (SELECT k.nomor_kamar FROM reservasi r JOIN kamar k ON r.kamar_id = k.id WHERE r.user_id = u.id AND r.status = 'disetujui' ORDER BY r.id DESC LIMIT 1) as kamar_aktif,
               (SELECT a.nama_asrama FROM reservasi r JOIN kamar k ON r.kamar_id = k.id JOIN asrama a ON k.asrama_id = a.id WHERE r.user_id = u.id AND r.status = 'disetujui' ORDER BY r.id DESC LIMIT 1) as asrama_aktif
        FROM users u
        LEFT JOIN mahasiswa_profiles mp ON u.id = mp.user_id
        WHERE u.role = 'mahasiswa'";

$params = [];
if (!empty($q)) {
    $sql .= " AND (u.full_name LIKE ? OR mp.npm LIKE ? OR u.email LIKE ? OR mp.prodi LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY u.full_name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$mahasiswa_list = $stmt->fetchAll();

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
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Direktori Mahasiswa & Penghuni Asrama</h5>
      </div>
      <div>
        <span class="badge bg-light text-dark border p-2">
          <i class="bi bi-people-fill text-primary me-1"></i> Total: <?= count($mahasiswa_list) ?> Mahasiswa
        </span>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Search Filter -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
          <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-9">
              <input type="text" class="form-control form-control-sm" name="q" placeholder="Cari nama mahasiswa, NPM, atau prodi..." value="<?= htmlspecialchars($q) ?>">
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-dark btn-sm w-100">
                <i class="bi bi-search me-1"></i> Cari Mahasiswa
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Table Card -->
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-person-lines-fill text-warning me-2"></i>Daftar Akun Mahasiswa Terdaftar di SIRASMA
          </h6>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-light">
                <tr>
                  <th>NPM & Nama Mahasiswa</th>
                  <th>Program Studi</th>
                  <th>Gender</th>
                  <th>Kontak / WhatsApp</th>
                  <th>Orang Tua / Wali</th>
                  <th>Status Hunian Asrama</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($mahasiswa_list)): ?>
                  <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Tidak ada data mahasiswa ditemukan.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($mahasiswa_list as $m): ?>
                  <tr>
                    <td>
                      <strong class="text-navy"><?= htmlspecialchars($m['full_name']) ?></strong>
                      <div class="text-muted" style="font-size: 0.75rem;">NPM: <?= htmlspecialchars($m['npm'] ?? '-') ?> &bull; User: <?= htmlspecialchars($m['username']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($m['prodi'] ?? '-') ?></td>
                    <td>
                      <span class="badge bg-<?= ($m['gender'] === 'L') ? 'primary' : 'danger' ?>">
                        <?= ($m['gender'] === 'L') ? 'Putra' : 'Putri' ?>
                      </span>
                    </td>
                    <td>
                      <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $m['phone']) ?>" target="_blank" class="text-decoration-none">
                        <i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($m['phone'] ?? '-') ?>
                      </a>
                    </td>
                    <td>
                      <?= htmlspecialchars($m['nama_ortu_wali'] ?? '-') ?>
                      <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($m['hp_ortu_wali'] ?? '') ?></div>
                    </td>
                    <td>
                      <?php if (!empty($m['kamar_aktif'])): ?>
                        <span class="badge bg-success"><i class="bi bi-house-check me-1"></i>Kamar <?= htmlspecialchars($m['kamar_aktif']) ?></span>
                        <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($m['asrama_aktif']) ?></div>
                      <?php else: ?>
                        <span class="badge bg-secondary">Bukan Penghuni Aktif</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
