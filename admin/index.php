<?php
/**
 * SIRASMA ULBI - Dashboard Portal Administrator
 */
$page_title = 'Dashboard Administrator';
$is_dashboard = true;
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Wajibkan login role admin
require_role(['admin']);

$user = current_user();
$db = get_db();

// 1. Statistik Operasional Utama
$total_kamar = $db->query("SELECT COUNT(*) FROM kamar WHERE status != 'nonaktif'")->fetchColumn() ?: 0;
$total_kapasitas = $db->query("SELECT SUM(kapasitas) FROM kamar WHERE status != 'nonaktif'")->fetchColumn() ?: 0;
$total_terisi = $db->query("SELECT SUM(terisi) FROM kamar WHERE status != 'nonaktif'")->fetchColumn() ?: 0;
$kamar_tersedia = $db->query("SELECT COUNT(*) FROM kamar WHERE status = 'tersedia'")->fetchColumn() ?: 0;

$rsv_pending = $db->query("SELECT COUNT(*) FROM reservasi WHERE status = 'menunggu_verifikasi'")->fetchColumn() ?: 0;
$rsv_approved = $db->query("SELECT COUNT(*) FROM reservasi WHERE status = 'disetujui'")->fetchColumn() ?: 0;
$bayar_pending = $db->query("SELECT COUNT(*) FROM reservasi WHERE status_pembayaran = 'menunggu_konfirmasi'")->fetchColumn() ?: 0;
$total_mahasiswa = $db->query("SELECT COUNT(*) FROM users WHERE role = 'mahasiswa'")->fetchColumn() ?: 0;

// 2. Daftar Pengajuan Menunggu Verifikasi (Pending)
$stmt_pending = $db->query("SELECT r.*, k.nomor_kamar, a.nama_asrama, u.full_name, mp.npm, mp.prodi 
                            FROM reservasi r
                            JOIN kamar k ON r.kamar_id = k.id
                            JOIN asrama a ON k.asrama_id = a.id
                            JOIN users u ON r.user_id = u.id
                            LEFT JOIN mahasiswa_profiles mp ON u.id = mp.user_id
                            WHERE r.status = 'menunggu_verifikasi'
                            ORDER BY r.tanggal_pengajuan ASC LIMIT 5");
$list_pending = $stmt_pending->fetchAll();

// 3. Ringkasan Keterisian Gedung Asrama
$stmt_asrama = $db->query("SELECT a.*, 
                           COUNT(k.id) as jml_kamar,
                           SUM(k.kapasitas) as total_kapasitas,
                           SUM(k.terisi) as total_terisi
                           FROM asrama a
                           LEFT JOIN kamar k ON a.id = k.asrama_id AND k.status != 'nonaktif'
                           GROUP BY a.id");
$asrama_summary = $stmt_asrama->fetchAll();

// 4. Catatan Aktivitas Terkini (Audit Log)
$recent_logs = $db->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 6")->fetchAll();

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
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Dashboard Pengelola Asrama ULBI</h5>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success py-2 px-3">
          <i class="bi bi-shield-check me-1"></i> Admin Online: <?= htmlspecialchars($user['username']) ?>
        </span>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Stat Counters Grid -->
      <div class="row g-3 mb-4">
        <!-- Card 1: Menunggu Verifikasi -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card border-start border-warning border-4">
            <div>
              <div class="stat-title text-warning">Perlu Verifikasi</div>
              <h3 class="stat-number text-warning"><?= $rsv_pending ?></h3>
              <small class="text-muted">Pengajuan Reservasi</small>
            </div>
            <div class="stat-icon icon-orange">
              <i class="bi bi-clock-history"></i>
            </div>
          </div>
        </div>

        <!-- Card 2: Konfirmasi Bayar -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card border-start border-info border-4">
            <div>
              <div class="stat-title text-info">Verifikasi Bayar</div>
              <h3 class="stat-number text-info"><?= $bayar_pending ?></h3>
              <small class="text-muted">Bukti Pembayaran</small>
            </div>
            <div class="stat-icon icon-blue">
              <i class="bi bi-receipt"></i>
            </div>
          </div>
        </div>

        <!-- Card 3: Penghuni Aktif -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card border-start border-success border-4">
            <div>
              <div class="stat-title text-success">Penghuni Terisi</div>
              <h3 class="stat-number text-success"><?= $total_terisi ?> <span class="fs-6 fw-normal text-muted">/ <?= $total_kapasitas ?></span></h3>
              <small class="text-muted">Slot Mahasiswa Aktif</small>
            </div>
            <div class="stat-icon icon-green">
              <i class="bi bi-person-check-fill"></i>
            </div>
          </div>
        </div>

        <!-- Card 4: Kamar Tersedia -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card border-start border-primary border-4">
            <div>
              <div class="stat-title text-primary">Kamar Siap Huni</div>
              <h3 class="stat-number text-navy"><?= $kamar_tersedia ?> <span class="fs-6 fw-normal text-muted">/ <?= $total_kamar ?></span></h3>
              <small class="text-muted">Kamar Berstatus Tersedia</small>
            </div>
            <div class="stat-icon icon-purple">
              <i class="bi bi-door-open-fill"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <!-- Pengajuan Membutuhkan Verifikasi Segera -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-exclamation-circle text-warning me-2"></i>Pengajuan Reservasi Menunggu Verifikasi
              </h6>
              <a href="<?= BASE_URL ?>/admin/reservasi/index.php?status=menunggu_verifikasi" class="small text-decoration-none fw-semibold">
                Lihat Semua (<?= $rsv_pending ?>) <i class="bi bi-arrow-right"></i>
              </a>
            </div>

            <div class="card-body p-0">
              <?php if (empty($list_pending)): ?>
                <div class="text-center py-5">
                  <i class="bi bi-check2-circle text-success" style="font-size: 3rem;"></i>
                  <h6 class="fw-bold mt-2">Semua Pengajuan Telah Ditinjau</h6>
                  <p class="text-muted small mb-0">Tidak ada permohonan reservasi yang menunggu verifikasi saat ini.</p>
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                      <tr>
                        <th>No. Reservasi</th>
                        <th>Mahasiswa / NPM</th>
                        <th>Kamar & Gedung</th>
                        <th>Tanggal Pengajuan</th>
                        <th class="text-end">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($list_pending as $lp): ?>
                      <tr>
                        <td>
                          <strong class="text-primary"><?= htmlspecialchars($lp['kode_reservasi']) ?></strong>
                        </td>
                        <td>
                          <strong><?= htmlspecialchars($lp['full_name']) ?></strong>
                          <div class="text-muted" style="font-size: 0.75rem;">NPM: <?= htmlspecialchars($lp['npm'] ?? '-') ?> &bull; <?= htmlspecialchars($lp['prodi'] ?? '-') ?></div>
                        </td>
                        <td>
                          <strong>Kamar <?= htmlspecialchars($lp['nomor_kamar']) ?></strong>
                          <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($lp['nama_asrama']) ?></div>
                        </td>
                        <td>
                          <?= date('d M Y H:i', strtotime($lp['tanggal_pengajuan'])) ?>
                        </td>
                        <td class="text-end">
                          <a href="<?= BASE_URL ?>/admin/reservasi/detail.php?id=<?= $lp['id'] ?>" class="btn btn-warning btn-sm fw-bold">
                            <i class="bi bi-search me-1"></i> Verifikasi
                          </a>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Ringkasan Keterisian Gedung Asrama -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-pie-chart-fill text-primary me-2"></i>Keterisian Gedung Asrama
              </h6>
            </div>
            <div class="card-body p-4">
              <?php foreach ($asrama_summary as $as): 
                $kap = $as['total_kapasitas'] ?: 1;
                $ter = $as['total_terisi'] ?: 0;
                $pct = round(($ter / $kap) * 100);
              ?>
                <div class="mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="small text-navy"><?= htmlspecialchars($as['nama_asrama']) ?></strong>
                    <span class="badge bg-<?= ($as['kategori_gender'] === 'L') ? 'primary' : 'danger' ?>">
                      <?= ($as['kategori_gender'] === 'L') ? 'Putra' : 'Putri' ?>
                    </span>
                  </div>
                  <div class="d-flex justify-content-between small text-muted mb-2">
                    <span>Terisi: <?= $ter ?> / <?= $kap ?> Penghuni</span>
                    <span class="fw-bold"><?= $pct ?>%</span>
                  </div>
                  <div class="progress" style="height: 8px;">
                    <div class="progress-bar <?= ($pct >= 90) ? 'bg-danger' : (($pct >= 60) ? 'bg-warning' : 'bg-success') ?>" 
                         style="width: <?= $pct ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>

              <div class="p-3 bg-light rounded-3 border mt-3">
                <div class="d-flex justify-content-between align-items-center small">
                  <span>Total Mahasiswa Terdaftar:</span>
                  <strong><?= $total_mahasiswa ?> Akun</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Audit Log Terkini -->
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-shield-lock-fill text-warning me-2"></i>Catatan Aktivitas Sistem (Audit Trail Log)
          </h6>
          <a href="<?= BASE_URL ?>/admin/logs/index.php" class="small text-decoration-none fw-semibold">
            Lihat Semua Log <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-light">
                <tr>
                  <th style="width: 160px;">Waktu & Tanggal</th>
                  <th>Pengguna</th>
                  <th>Aksi (Action)</th>
                  <th>Deskripsi Aktivitas</th>
                  <th>IP Address</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_logs as $lg): ?>
                <tr>
                  <td class="text-muted"><?= date('d/m/Y H:i:s', strtotime($lg['created_at'])) ?></td>
                  <td>
                    <strong><?= htmlspecialchars($lg['username'] ?? 'guest') ?></strong>
                    <span class="badge bg-secondary" style="font-size:0.7rem;"><?= htmlspecialchars($lg['user_role'] ?? 'system') ?></span>
                  </td>
                  <td>
                    <span class="badge bg-dark"><?= htmlspecialchars($lg['action']) ?></span>
                  </td>
                  <td><?= htmlspecialchars($lg['description']) ?></td>
                  <td class="text-muted"><code><?= htmlspecialchars($lg['ip_address'] ?? '127.0.0.1') ?></code></td>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
