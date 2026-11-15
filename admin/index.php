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
        <div>
          <h5 class="fw-bold mb-0 text-navy">Dashboard Pengelola Asrama</h5>
          <small class="text-muted d-none d-md-block">Ringkasan operasional hunian kampus ULBI</small>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge-soft badge-soft-success px-3 py-2">
          <i class="bi bi-shield-check"></i> Petugas: <?= htmlspecialchars($user['username']) ?>
        </span>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Stat Counters Grid -->
      <div class="row g-3 mb-4">
        <!-- Card 1: Menunggu Verifikasi -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card">
            <div>
              <div class="stat-title text-warning">Perlu Verifikasi</div>
              <h3 class="stat-number text-warning"><?= $rsv_pending ?></h3>
              <small class="text-muted">Pengajuan Baru</small>
            </div>
            <div class="stat-icon icon-orange">
              <i class="bi bi-clock-history"></i>
            </div>
          </div>
        </div>

        <!-- Card 2: Konfirmasi Bayar -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card">
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
          <div class="stat-card">
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
          <div class="stat-card">
            <div>
              <div class="stat-title text-navy">Kamar Siap Huni</div>
              <h3 class="stat-number text-navy"><?= $kamar_tersedia ?> <span class="fs-6 fw-normal text-muted">/ <?= $total_kamar ?></span></h3>
              <small class="text-muted">Kamar Status Tersedia</small>
            </div>
            <div class="stat-icon icon-navy">
              <i class="bi bi-door-open-fill"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <!-- Pengajuan Membutuhkan Verifikasi Segera -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 h-100" style="border: 1px solid var(--slate-200) !important;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>Pengajuan Reservasi Menunggu Verifikasi
              </h6>
              <a href="<?= BASE_URL ?>/admin/reservasi/index.php?status=menunggu_verifikasi" class="small text-decoration-none fw-semibold" style="color:var(--ulbi-orange);">
                Lihat Semua (<?= $rsv_pending ?>) <i class="bi bi-arrow-right"></i>
              </a>
            </div>

            <div class="card-body p-0">
              <?php if (empty($list_pending)): ?>
                <div class="text-center py-5">
                  <div class="feature-icon-wrapper icon-green mx-auto mb-2" style="width:48px;height:48px;">
                    <i class="bi bi-check2-circle fs-4"></i>
                  </div>
                  <h6 class="fw-bold mt-2 text-navy">Semua Pengajuan Telah Ditinjau</h6>
                  <p class="text-muted small mb-0">Tidak ada permohonan reservasi yang menunggu verifikasi saat ini.</p>
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-custom align-middle mb-0">
                    <thead>
                      <tr>
                        <th>No. Reservasi</th>
                        <th>Mahasiswa / NPM</th>
                        <th>Kamar &amp; Gedung</th>
                        <th>Tanggal Pengajuan</th>
                        <th class="text-end">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($list_pending as $lp): ?>
                      <tr>
                        <td>
                          <strong class="text-navy"><?= htmlspecialchars($lp['kode_reservasi']) ?></strong>
                        </td>
                        <td>
                          <div class="fw-bold text-navy"><?= htmlspecialchars($lp['full_name']) ?></div>
                          <div class="text-muted small">NPM: <?= htmlspecialchars($lp['npm'] ?? '-') ?> &bull; <?= htmlspecialchars($lp['prodi'] ?? '-') ?></div>
                        </td>
                        <td>
                          <div class="fw-bold">Kamar <?= htmlspecialchars($lp['nomor_kamar']) ?></div>
                          <div class="text-muted small"><?= htmlspecialchars($lp['nama_asrama']) ?></div>
                        </td>
                        <td>
                          <span class="text-secondary small"><?= date('d M Y H:i', strtotime($lp['tanggal_pengajuan'])) ?></span>
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
          <div class="card border-0 shadow-sm rounded-4 h-100" style="border: 1px solid var(--slate-200) !important;">
            <div class="card-header bg-white border-bottom py-3 px-4">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-pie-chart-fill text-primary me-2"></i>Keterisian Gedung Asrama
              </h6>
            </div>
            <div class="card-body p-4">
              <?php foreach ($asrama_summary as $as): 
                $kap = $as['total_kapasitas'] ?: 1;
                $ter = $as['total_terisi'] ?: 0;
                $pct = round(($ter / $kap) * 100);
                $is_l = ($as['kategori_gender'] === 'L');
              ?>
                <div class="mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="small text-navy"><?= htmlspecialchars($as['nama_asrama']) ?></strong>
                    <span class="badge-soft <?= $is_l ? 'badge-soft-info' : 'badge-soft-danger' ?>">
                      <i class="bi <?= $is_l ? 'bi-gender-male' : 'bi-gender-female' ?>"></i>
                      <?= $is_l ? 'Putra' : 'Putri' ?>
                    </span>
                  </div>
                  <div class="d-flex justify-content-between small text-muted mb-2">
                    <span>Terisi: <?= $ter ?> / <?= $kap ?> Penghuni</span>
                    <span class="fw-bold text-navy"><?= $pct ?>%</span>
                  </div>
                  <div class="progress" style="height: 8px; border-radius: 4px;">
                    <div class="progress-bar <?= ($pct >= 90) ? 'bg-danger' : (($pct >= 60) ? 'bg-warning' : 'bg-success') ?>" 
                         style="width: <?= $pct ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>

              <div class="p-3 bg-light rounded-3 border mt-3">
                <div class="d-flex justify-content-between align-items-center small">
                  <span class="text-secondary"><i class="bi bi-people me-1"></i>Total Mahasiswa Terdaftar:</span>
                  <strong class="text-navy"><?= $total_mahasiswa ?> Akun</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Audit Log Terkini -->
      <div class="card border-0 shadow-sm rounded-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-shield-lock-fill text-warning me-2"></i>Catatan Aktivitas Sistem (Audit Trail)
          </h6>
          <a href="<?= BASE_URL ?>/admin/logs/index.php" class="small text-decoration-none fw-semibold" style="color:var(--ulbi-orange);">
            Lihat Semua Log <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
              <thead>
                <tr>
                  <th style="width: 170px;">Waktu &amp; Tanggal</th>
                  <th>Pengguna</th>
                  <th>Jenis Aksi</th>
                  <th>Deskripsi Aktivitas</th>
                  <th>IP Address</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_logs as $lg): ?>
                <tr>
                  <td class="text-muted small"><?= date('d/m/Y H:i:s', strtotime($lg['created_at'])) ?></td>
                  <td>
                    <div class="fw-bold text-navy"><?= htmlspecialchars($lg['username'] ?? 'guest') ?></div>
                    <span class="badge-soft badge-soft-navy" style="font-size:0.7rem;"><?= htmlspecialchars($lg['user_role'] ?? 'system') ?></span>
                  </td>
                  <td>
                    <span class="badge-soft badge-soft-warning"><?= htmlspecialchars($lg['action']) ?></span>
                  </td>
                  <td class="small text-secondary"><?= htmlspecialchars($lg['description']) ?></td>
                  <td class="text-muted"><code class="small text-dark"><?= htmlspecialchars($lg['ip_address'] ?? '127.0.0.1') ?></code></td>
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
