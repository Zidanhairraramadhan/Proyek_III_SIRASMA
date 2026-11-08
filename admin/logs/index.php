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
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Catatan Aktivitas Sistem (Audit Trail)</h5>
      </div>
      <div>
        <span class="badge bg-dark border border-secondary text-warning p-2">
          <i class="bi bi-shield-check me-1"></i> Keamanan SI: Least Privilege & Logging Aktif
        </span>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Security Notice Card -->
      <div class="alert alert-secondary border-0 shadow-sm d-flex align-items-center mb-4 p-3 rounded-3">
        <i class="bi bi-shield-lock-fill fs-3 text-warning me-3"></i>
        <div class="small">
          <strong>Modul Kepatuhan Audit Keamanan:</strong> Setiap tindakan otentikasi login, pengajuan reservasi oleh mahasiswa, perubahan status oleh administrator, penambahan kamar, serta percobaan akses tanpa izin dicatat otomatis bersama stempel waktu (timestamp) dan alamat IP pemohon.
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
          <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-5">
              <input type="text" class="form-control form-control-sm" name="q" placeholder="Cari username, deskripsi, atau IP..." value="<?= htmlspecialchars($q) ?>">
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
              <button type="submit" class="btn btn-dark btn-sm w-100">
                <i class="bi bi-search me-1"></i> Saring Log
              </button>
              <a href="<?= BASE_URL ?>/admin/logs/index.php" class="btn btn-outline-secondary btn-sm" title="Reset">
                <i class="bi bi-arrow-counterclockwise"></i>
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- Log Table Card -->
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-journal-text text-warning me-2"></i>Rekaman Log Audit Terbaru (Menampilkan <?= count($logs) ?> entri)
          </h6>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-light">
                <tr>
                  <th style="width: 170px;">Waktu & Tanggal</th>
                  <th style="width: 160px;">Pengguna (User)</th>
                  <th style="width: 190px;">Jenis Aksi</th>
                  <th>Deskripsi Aktivitas</th>
                  <th style="width: 120px;">IP Address</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($logs)): ?>
                  <tr>
                    <td colspan="5" class="text-center py-4 text-muted">Belum ada catatan log aktivitas yang cocok.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($logs as $l): ?>
                  <tr>
                    <td class="text-muted font-monospace" style="font-size:0.8rem;">
                      <?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?>
                    </td>
                    <td>
                      <strong><?= htmlspecialchars($l['username'] ?? 'guest') ?></strong>
                      <?php if (($l['user_role'] ?? '') === 'admin'): ?>
                        <span class="badge bg-warning text-dark" style="font-size:0.65rem;">ADMIN</span>
                      <?php elseif (($l['user_role'] ?? '') === 'mahasiswa'): ?>
                        <span class="badge bg-primary" style="font-size:0.65rem;">MHS</span>
                      <?php else: ?>
                        <span class="badge bg-secondary" style="font-size:0.65rem;">GUEST</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php 
                        $badge_class = 'bg-secondary';
                        if (str_contains($l['action'], 'LOGIN_SUCCESS')) $badge_class = 'bg-success';
                        elseif (str_contains($l['action'], 'UNAUTHORIZED') || str_contains($l['action'], 'FAILED')) $badge_class = 'bg-danger';
                        elseif (str_contains($l['action'], 'APPROVE')) $badge_class = 'bg-success';
                        elseif (str_contains($l['action'], 'REJECT') || str_contains($l['action'], 'CANCEL')) $badge_class = 'bg-danger';
                        elseif (str_contains($l['action'], 'SUBMIT')) $badge_class = 'bg-primary';
                        elseif (str_contains($l['action'], 'ROOM')) $badge_class = 'bg-info text-dark';
                      ?>
                      <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($l['action']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($l['description']) ?></td>
                    <td><code class="text-muted"><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></code></td>
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
