<?php
/**
 * SIRASMA ULBI - Daftar & Verifikasi Reservasi (Admin)
 */
$page_title = 'Daftar & Verifikasi Reservasi';
$is_dashboard = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$db = get_db();

// Filter
$status = sanitize($_GET['status'] ?? '');
$payment_status = sanitize($_GET['payment'] ?? '');
$q = sanitize($_GET['q'] ?? '');

$sql = "SELECT r.*, k.nomor_kamar, a.nama_asrama, u.full_name, u.phone, mp.npm, mp.prodi, p.nama_periode
        FROM reservasi r
        JOIN kamar k ON r.kamar_id = k.id
        JOIN asrama a ON k.asrama_id = a.id
        JOIN periode p ON r.periode_id = p.id
        JOIN users u ON r.user_id = u.id
        LEFT JOIN mahasiswa_profiles mp ON u.id = mp.user_id
        WHERE 1=1";
$params = [];

if (!empty($status)) {
    $sql .= " AND r.status = ?";
    $params[] = $status;
}

if (!empty($payment_status)) {
    $sql .= " AND r.status_pembayaran = ?";
    $params[] = $payment_status;
}

if (!empty($q)) {
    $sql .= " AND (r.kode_reservasi LIKE ? OR u.full_name LIKE ? OR mp.npm LIKE ? OR k.nomor_kamar LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY r.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$reservasi_list = $stmt->fetchAll();

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
          <h5 class="fw-bold mb-0 text-navy">Verifikasi Permohonan Reservasi</h5>
          <small class="text-muted d-none d-md-block">Kelola persetujuan kuota kamar dan validasi bukti bayar</small>
        </div>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/admin/reservasi/laporan.php" class="btn btn-outline-dark btn-sm">
          <i class="bi bi-file-earmark-bar-graph me-1"></i> Rekap Laporan
        </a>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Filter Card -->
      <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-3 p-md-4">
          <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-4">
              <label class="form-label mb-1">Cari Pemohon / Kamar</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-start-0" name="q" placeholder="Cari kode, nama mhs, NPM..." value="<?= htmlspecialchars($q) ?>">
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label mb-1">Status Pengajuan</label>
              <select name="status" class="form-select">
                <option value="">Semua Status Pengajuan</option>
                <option value="menunggu_verifikasi" <?= ($status === 'menunggu_verifikasi') ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                <option value="disetujui" <?= ($status === 'disetujui') ? 'selected' : '' ?>>Disetujui</option>
                <option value="ditolak" <?= ($status === 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
                <option value="dibatalkan" <?= ($status === 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label mb-1">Status Pembayaran</label>
              <select name="payment" class="form-select">
                <option value="">Semua Status Bayar</option>
                <option value="menunggu_konfirmasi" <?= ($payment_status === 'menunggu_konfirmasi') ? 'selected' : '' ?>>Menunggu Konfirmasi Bukti</option>
                <option value="belum_bayar" <?= ($payment_status === 'belum_bayar') ? 'selected' : '' ?>>Belum Bayar</option>
                <option value="lunas" <?= ($payment_status === 'lunas') ? 'selected' : '' ?>>Lunas</option>
              </select>
            </div>
            <div class="col-md-2 pt-md-4">
              <button type="submit" class="btn btn-ulbi-primary w-100">
                <i class="bi bi-funnel-fill"></i> Terapkan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Table Card -->
      <div class="card border-0 shadow-sm rounded-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-card-checklist text-warning me-2"></i>Daftar Pengajuan Reservasi Masuk (<?= count($reservasi_list) ?>)
          </h6>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
              <thead>
                <tr>
                  <th>No. Reservasi</th>
                  <th>Mahasiswa Pemohon</th>
                  <th>Kamar &amp; Asrama</th>
                  <th>Periode &amp; Check-in</th>
                  <th>Status Permohonan</th>
                  <th>Status Bayar</th>
                  <th class="text-end">Aksi Verifikasi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($reservasi_list)): ?>
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <i class="bi bi-folder-x fs-1 d-block mb-2 text-muted"></i>
                      Tidak ada permohonan reservasi yang sesuai dengan kriteria saringan.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($reservasi_list as $r): ?>
                  <tr>
                    <td>
                      <strong class="text-navy"><?= htmlspecialchars($r['kode_reservasi']) ?></strong>
                      <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($r['tanggal_pengajuan'])) ?></div>
                    </td>
                    <td>
                      <div class="fw-bold text-navy"><?= htmlspecialchars($r['full_name']) ?></div>
                      <div class="text-muted small">NPM: <?= htmlspecialchars($r['npm'] ?? '-') ?> &bull; <?= htmlspecialchars($r['prodi'] ?? '-') ?></div>
                    </td>
                    <td>
                      <div class="fw-bold">Kamar <?= htmlspecialchars($r['nomor_kamar']) ?></div>
                      <div class="text-muted small"><?= htmlspecialchars($r['nama_asrama']) ?></div>
                    </td>
                    <td>
                      <div class="small fw-semibold"><?= htmlspecialchars($r['nama_periode']) ?></div>
                      <small class="text-muted">Masuk: <?= format_tanggal($r['rencana_checkin']) ?></small>
                    </td>
                    <td>
                      <?= get_status_badge($r['status']) ?>
                    </td>
                    <td>
                      <?= get_payment_badge($r['status_pembayaran']) ?>
                    </td>
                    <td class="text-end">
                      <a href="<?= BASE_URL ?>/admin/reservasi/detail.php?id=<?= $r['id'] ?>" class="btn btn-ulbi-navy btn-sm">
                        <i class="bi bi-pencil-square me-1"></i> Periksa &amp; Verifikasi
                      </a>
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
