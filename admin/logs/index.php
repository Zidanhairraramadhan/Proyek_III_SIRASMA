<?php
/**
 * SIRASMA ULBI - Audit Log / Catatan Aktivitas Sistem
 * Memenuhi spesifikasi Matriks Access Control & Manajemen Risiko Keamanan Sistem Informasi
 */
$page_title = 'Audit Log / Catatan Aktivitas';
$is_dashboard = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$db = get_db();

// Filter
$action_filter = sanitize($_GET['action'] ?? '');
$q = sanitize($_GET['q'] ?? '');

$sql = "SELECT * FROM audit_logs WHERE 1=1";
$params = [];

if (!empty($action_filter)) {
    $sql .= " AND action = ?";
    $params[] = $action_filter;
}

if (!empty($q)) {
    $sql .= " AND (username LIKE ? OR description LIKE ? OR ip_address LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY id DESC LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Ambil daftar distinct action untuk dropdown filter
$actions = $db->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);

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
          <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Audit Log & Catatan Aktivitas</h5>
          <span class="text-muted small">Rekaman jejak aktivitas sistem dan integritas akses SIRASMA</span>
        </div>
      </div>
      <div>
        <span class="badge badge-soft-warning px-3 py-2">
          <i class="bi bi-shield-check me-1"></i> Audit Trail Aktif
        </span>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Security Notice Card -->
      <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, rgba(15, 30, 53, 0.03), rgba(249, 115, 22, 0.05)); border-left: 4px solid var(--ulbi-navy) !important;">
        <div class="card-body p-3 p-md-4 d-flex align-items-center">
          <div class="d-flex align-items-center justify-content-center rounded-3 bg-white shadow-sm me-3 flex-shrink-0" style="width:48px;height:48px;">
            <i class="bi bi-shield-lock-fill fs-4 text-warning"></i>
          </div>
          <div class="small">
            <strong class="text-navy d-block mb-1">Kepatuhan Audit & Keamanan Sistem Informasi:</strong>
            <span class="text-muted">Setiap tindakan otentikasi login, pengajuan reservasi oleh mahasiswa, perubahan status oleh administrator, penambahan kamar, serta percobaan akses tanpa izin dicatat otomatis bersama stempel waktu (timestamp) dan alamat IP pemohon untuk audit forensik.</span>
          </div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
          <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-5">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control form-control-sm border-start-0" name="q" placeholder="Cari username, deskripsi, atau IP..." value="<?= htmlspecialchars($q) ?>">
              </div>
            </div>
            <div class="col-md-4">
              <select name="action" class="form-select form-select-sm">
                <option value="">-- Semua Jenis Aksi (Action) --</option>
                <?php foreach ($actions as $act): ?>
                  <option value="<?= htmlspecialchars($act) ?>" <?= ($action_filter === $act) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($act) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
              <button type="submit" class="btn btn-navy btn-sm w-100">
                <i class="bi bi-funnel me-1"></i> Saring Log
              </button>
              <a href="<?= BASE_URL ?>/admin/logs/index.php" class="btn btn-light border btn-sm px-3" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- Log Table Card -->
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-journal-text text-orange me-2"></i>Rekaman Log Audit Terbaru
          </h6>
          <span class="badge bg-light text-muted border">Menampilkan <?= count($logs) ?> entri</span>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-custom align-middle mb-0 small">
              <thead>
                <tr>
                  <th style="width: 170px;">Waktu & Tanggal</th>
                  <th style="width: 160px;">Pengguna (User)</th>
                  <th style="width: 190px;">Jenis Aksi</th>
                  <th>Deskripsi Aktivitas</th>
                  <th style="width: 130px;">IP Address</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($logs)): ?>
                  <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                      <i class="bi bi-inbox fs-2 d-block mb-2 text-muted opacity-50"></i>
                      Belum ada catatan log aktivitas yang cocok dengan filter.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($logs as $l): ?>
                  <tr>
                    <td class="text-muted font-monospace" style="font-size:0.8rem;">
                      <i class="bi bi-clock me-1 opacity-50"></i><?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-1">
                        <strong class="text-navy"><?= htmlspecialchars($l['username'] ?? 'guest') ?></strong>
                        <?php if (($l['user_role'] ?? '') === 'admin'): ?>
                          <span class="badge badge-soft-warning ms-1" style="font-size:0.65rem;">ADMIN</span>
                        <?php elseif (($l['user_role'] ?? '') === 'mahasiswa'): ?>
                          <span class="badge badge-soft-primary ms-1" style="font-size:0.65rem;">MHS</span>
                        <?php else: ?>
                          <span class="badge badge-soft-secondary ms-1" style="font-size:0.65rem;">GUEST</span>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td>
                      <?php 
                        $badge_class = 'badge-soft-secondary';
                        if (str_contains($l['action'], 'LOGIN_SUCCESS')) $badge_class = 'badge-soft-success';
                        elseif (str_contains($l['action'], 'UNAUTHORIZED') || str_contains($l['action'], 'FAILED')) $badge_class = 'badge-soft-danger';
                        elseif (str_contains($l['action'], 'APPROVE')) $badge_class = 'badge-soft-success';
                        elseif (str_contains($l['action'], 'REJECT') || str_contains($l['action'], 'CANCEL')) $badge_class = 'badge-soft-danger';
                        elseif (str_contains($l['action'], 'SUBMIT')) $badge_class = 'badge-soft-primary';
                        elseif (str_contains($l['action'], 'ROOM')) $badge_class = 'badge-soft-info';
                      ?>
                      <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($l['action']) ?></span>
                    </td>
                    <td class="text-secondary"><?= htmlspecialchars($l['description']) ?></td>
                    <td><code class="text-muted px-2 py-1 bg-light rounded" style="font-size:0.75rem;"><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></code></td>
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
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
