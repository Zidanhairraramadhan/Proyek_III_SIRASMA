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
        <div>
          <h5 class="fw-bold mb-0 text-navy">Direktori Mahasiswa &amp; Penghuni</h5>
          <small class="text-muted d-none d-md-block">Data akun mahasiswa terdaftar dan status kamar aktif</small>
        </div>
      </div>
      <div>
        <span class="badge-soft badge-soft-navy px-3 py-2">
          <i class="bi bi-people-fill me-1"></i> Total: <?= count($mahasiswa_list) ?> Mahasiswa
        </span>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Search Filter -->
      <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-3 p-md-4">
          <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-9">
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-start-0" name="q" placeholder="Cari nama mahasiswa, NPM, atau program studi..." value="<?= htmlspecialchars($q) ?>">
              </div>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-ulbi-primary w-100">
                <i class="bi bi-search me-1"></i> Cari Mahasiswa
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Table Card -->
      <div class="card border-0 shadow-sm rounded-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-header bg-white border-bottom py-3 px-4">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-person-lines-fill text-warning me-2"></i>Daftar Akun Mahasiswa di SIRASMA
          </h6>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
              <thead>
                <tr>
                  <th>NPM &amp; Nama Mahasiswa</th>
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
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="bi bi-person-x fs-1 d-block mb-2 text-muted"></i>
                      Tidak ada data mahasiswa yang sesuai dengan pencarian.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($mahasiswa_list as $m): 
                    $is_l = ($m['gender'] === 'L');
                  ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-navy"><?= htmlspecialchars($m['full_name']) ?></div>
                      <div class="text-muted small">NPM: <?= htmlspecialchars($m['npm'] ?? '-') ?> &bull; User: <?= htmlspecialchars($m['username']) ?></div>
                    </td>
                    <td>
                      <span class="small fw-semibold"><?= htmlspecialchars($m['prodi'] ?? '-') ?></span>
                    </td>
                    <td>
                      <span class="badge-soft <?= $is_l ? 'badge-soft-info' : 'badge-soft-danger' ?>">
                        <i class="bi <?= $is_l ? 'bi-gender-male' : 'bi-gender-female' ?>"></i>
                        <?= $is_l ? 'Putra' : 'Putri' ?>
                      </span>
                    </td>
                    <td>
                      <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $m['phone']) ?>" target="_blank" class="text-decoration-none fw-semibold">
                        <i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($m['phone'] ?? '-') ?>
                      </a>
                    </td>
                    <td>
                      <div class="small fw-semibold text-navy"><?= htmlspecialchars($m['nama_ortu_wali'] ?? '-') ?></div>
                      <div class="text-muted small"><?= htmlspecialchars($m['hp_ortu_wali'] ?? '') ?></div>
                    </td>
                    <td>
                      <?php if (!empty($m['kamar_aktif'])): ?>
                        <span class="badge-soft badge-soft-success"><i class="bi bi-house-check-fill"></i>Kamar <?= htmlspecialchars($m['kamar_aktif']) ?></span>
                        <div class="text-muted small"><?= htmlspecialchars($m['asrama_aktif']) ?></div>
                      <?php else: ?>
                        <span class="badge-soft badge-soft-navy">Bukan Penghuni Aktif</span>
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
