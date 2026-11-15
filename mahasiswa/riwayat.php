<?php
/**
 * SIRASMA ULBI - Riwayat & Status Pengajuan Mahasiswa
 * Memenuhi Access Control Matrix:
 * "Mahasiswa hanya dapat melihat reservasi miliknya sendiri (WHERE user_id = ?)"
 */
$page_title = 'Riwayat Reservasi Saya';
$is_dashboard = true;
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['mahasiswa']);

$user = current_user();
$db = get_db();

// Ambil riwayat reservasi HANYA milik user yang login (STRICT LEAST PRIVILEGE)
$stmt = $db->prepare("SELECT r.*, k.nomor_kamar, k.tipe_kamar, k.lantai, a.nama_asrama, p.nama_periode 
                      FROM reservasi r
                      JOIN kamar k ON r.kamar_id = k.id
                      JOIN asrama a ON k.asrama_id = a.id
                      JOIN periode p ON r.periode_id = p.id
                      WHERE r.user_id = ?
                      ORDER BY r.id DESC");
$stmt->execute([$user['id']]);
$riwayat = $stmt->fetchAll();

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
          <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Riwayat & Status Reservasi</h5>
          <span class="text-muted small">Pantau status verifikasi dan pembayaran kamar asrama Anda</span>
        </div>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php" class="btn btn-orange btn-sm px-3 shadow-sm">
          <i class="bi bi-plus-circle-fill me-1"></i> Ajukan Reservasi Baru
        </a>
      </div>
    </header>

    <div class="dashboard-content">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-list-check text-orange me-2"></i>Daftar Pengajuan Reservasi Anda
          </h6>
          <span class="badge bg-light text-muted border">Total: <?= count($riwayat) ?> Permohonan</span>
        </div>

        <div class="card-body p-0">
          <?php if (empty($riwayat)): ?>
            <div class="text-center py-5">
              <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width:70px;height:70px;">
                <i class="bi bi-folder-x text-muted" style="font-size: 2rem;"></i>
              </div>
              <h6 class="fw-bold text-navy mt-1">Belum Ada Riwayat Reservasi</h6>
              <p class="text-muted small mb-4 mx-auto" style="max-width:350px;">Anda belum pernah mengirimkan pengajuan reservasi asrama kampus ULBI.</p>
              <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php" class="btn btn-orange btn-sm px-4 py-2">
                <i class="bi bi-plus-circle-fill me-1"></i> Ajukan Sekarang
              </a>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-custom align-middle mb-0">
                <thead>
                  <tr>
                    <th>No. Reservasi</th>
                    <th>Kamar & Gedung</th>
                    <th>Periode & Durasi</th>
                    <th>Biaya Sewa</th>
                    <th>Status Pengajuan</th>
                    <th>Pembayaran</th>
                    <th class="text-end">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($riwayat as $r): ?>
                  <tr>
                    <td>
                      <strong class="text-navy"><?= htmlspecialchars($r['kode_reservasi']) ?></strong>
                      <div class="text-muted small font-monospace" style="font-size:0.75rem;"><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y H:i', strtotime($r['tanggal_pengajuan'])) ?></div>
                    </td>
                    <td>
                      <strong class="text-navy">Kamar <?= htmlspecialchars($r['nomor_kamar']) ?></strong> <span class="badge badge-soft-secondary ms-1">Lt. <?= $r['lantai'] ?></span>
                      <div class="small text-muted"><?= htmlspecialchars($r['nama_asrama']) ?></div>
                    </td>
                    <td>
                      <div class="small fw-semibold text-navy"><?= htmlspecialchars($r['nama_periode']) ?></div>
                      <small class="text-muted">Durasi: <?= htmlspecialchars($r['durasi_sewa']) ?></small>
                    </td>
                    <td>
                      <strong class="text-success"><?= format_rupiah($r['total_biaya']) ?></strong>
                    </td>
                    <td>
                      <?= get_status_badge($r['status']) ?>
                      <?php if (!empty($r['catatan_admin'])): ?>
                        <div class="small text-muted mt-1" style="max-width: 200px;">
                          <i class="bi bi-chat-quote me-1 text-primary"></i><?= htmlspecialchars(substr($r['catatan_admin'], 0, 50)) ?>...
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?= get_payment_badge($r['status_pembayaran']) ?>
                    </td>
                    <td class="text-end">
                      <div class="btn-group btn-group-sm">
                        <?php if ($r['status'] === 'disetujui'): ?>
                          <a href="<?= BASE_URL ?>/mahasiswa/cetak_bukti.php?id=<?= $r['id'] ?>" class="btn btn-light border text-navy" target="_blank" title="Cetak Bukti Resmi">
                            <i class="bi bi-printer"></i>
                          </a>
                          <?php if ($r['status_pembayaran'] !== 'lunas'): ?>
                            <a href="<?= BASE_URL ?>/mahasiswa/upload_bukti.php?id=<?= $r['id'] ?>" class="btn btn-orange text-white" title="Upload Bukti Transfer">
                              <i class="bi bi-upload me-1"></i> Bayar
                            </a>
                          <?php endif; ?>
                        <?php elseif ($r['status'] === 'menunggu_verifikasi'): ?>
                          <a href="<?= BASE_URL ?>/mahasiswa/batalkan.php?id=<?= $r['id'] ?>" 
                             class="btn btn-outline-danger" 
                             onclick="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')"
                             title="Batalkan Pengajuan">
                            <i class="bi bi-x-circle me-1"></i> Batal
                          </a>
                        <?php else: ?>
                          <button class="btn btn-light border text-muted opacity-50" disabled>
                            <i class="bi bi-lock"></i>
                          </button>
                        <?php endif; ?>
                      </div>
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
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
