<?php
/**
 * SIRASMA ULBI - Dashboard Portal Mahasiswa
 */
$page_title = 'Dashboard Mahasiswa';
$is_dashboard = true;
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Wajibkan login role mahasiswa
require_role(['mahasiswa']);

$user = current_user();
$db = get_db();

// Ambil data profil mahasiswa
$stmt_prof = $db->prepare("SELECT * FROM mahasiswa_profiles WHERE user_id = ?");
$stmt_prof->execute([$user['id']]);
$profile = $stmt_prof->fetch();

// Ambil reservasi terkini mahasiswa
$stmt_rsv = $db->prepare("SELECT r.*, k.nomor_kamar, k.tipe_kamar, k.lantai, a.nama_asrama, p.nama_periode 
                          FROM reservasi r
                          JOIN kamar k ON r.kamar_id = k.id
                          JOIN asrama a ON k.asrama_id = a.id
                          JOIN periode p ON r.periode_id = p.id
                          WHERE r.user_id = ? 
                          ORDER BY r.id DESC LIMIT 1");
$stmt_rsv->execute([$user['id']]);
$active_reservation = $stmt_rsv->fetch();

// Hitung total reservasi milik mahasiswa
$stmt_count = $db->prepare("SELECT COUNT(*) FROM reservasi WHERE user_id = ?");
$stmt_count->execute([$user['id']]);
$total_reservasi_mhs = $stmt_count->fetchColumn();

// Ambil pengumuman aktif
$pengumuman = $db->query("SELECT * FROM pengumuman WHERE is_active = 1 ORDER BY tanggal DESC LIMIT 2")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <!-- Sidebar -->
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="dashboard-main">
    <!-- Topbar -->
    <header class="dashboard-topbar">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light border d-lg-none" type="button" onclick="document.getElementById('dashboardSidebar').classList.toggle('show')">
          <i class="bi bi-list fs-5"></i>
        </button>
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Portal Mahasiswa</h5>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span class="badge bg-light text-dark border d-none d-md-inline-block">
          <i class="bi bi-person-badge text-primary me-1"></i> NPM: <?= htmlspecialchars($profile['npm'] ?? '-') ?>
        </span>
        <a href="<?= BASE_URL ?>/mahasiswa/profil.php" class="btn btn-outline-secondary btn-sm" title="Edit Profil">
          <i class="bi bi-gear-fill"></i>
        </a>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Welcome Banner -->
      <div class="card border-0 rounded-4 text-white p-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, var(--ulbi-navy) 0%, #1a365d 100%);">
        <div class="row align-items-center">
          <div class="col-md-8">
            <span class="badge bg-warning text-dark px-3 py-1 fw-semibold mb-2">Selamat Datang</span>
            <h3 class="fw-bold mb-1">Halo, <?= htmlspecialchars($user['full_name']) ?>!</h3>
            <p class="text-light opacity-75 mb-0 small">
              Program Studi: <?= htmlspecialchars($profile['prodi'] ?? 'Belum Diisi') ?> &bull; 
              Gender: <?= ($profile['gender'] ?? 'L') === 'L' ? 'Mahasiswa (Putra)' : 'Mahasiswi (Putri)' ?>
            </p>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php" class="btn btn-ulbi-primary shadow">
              <i class="bi bi-plus-circle-fill me-1"></i> Ajukan Reservasi Baru
            </a>
          </div>
        </div>
      </div>

      <!-- Incomplete Profile Warning if applicable -->
      <?php if (empty($profile['hp_ortu_wali']) || empty($profile['alamat_asal'])): ?>
      <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between mb-4 p-3 rounded-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
          <div>
            <strong class="d-block">Profil Anda Belum Lengkap!</strong>
            <small class="text-dark">Silakan lengkapi kontak darurat orang tua dan alamat asal agar proses verifikasi reservasi berjalan lancar.</small>
          </div>
        </div>
        <a href="<?= BASE_URL ?>/mahasiswa/profil.php" class="btn btn-warning btn-sm fw-bold">Lengkapi Profil</a>
      </div>
      <?php endif; ?>

      <!-- Status Pengajuan Terkini Card -->
      <div class="row g-4 mb-4">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-clock-history text-warning me-2"></i>Status Permohonan Reservasi Terkini
              </h6>
              <a href="<?= BASE_URL ?>/mahasiswa/riwayat.php" class="small text-decoration-none fw-semibold">
                Lihat Riwayat Lengkap <i class="bi bi-arrow-right"></i>
              </a>
            </div>

            <div class="card-body p-4">
              <?php if ($active_reservation): ?>
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3 pb-3 border-bottom">
                  <div>
                    <span class="text-muted small">Nomor Registrasi:</span>
                    <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($active_reservation['kode_reservasi']) ?></h5>
                    <small class="text-muted">Diajukan pada: <?= format_tanggal($active_reservation['tanggal_pengajuan'], true) ?></small>
                  </div>
                  <div>
                    <?= get_status_badge($active_reservation['status']) ?>
                    <span class="ms-1"><?= get_payment_badge($active_reservation['status_pembayaran']) ?></span>
                  </div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                      <small class="text-muted d-block">Gedung & Nomor Kamar:</small>
                      <strong class="fs-6 text-navy"><?= htmlspecialchars($active_reservation['nama_asrama']) ?></strong>
                      <div class="small text-secondary">Kamar <?= htmlspecialchars($active_reservation['nomor_kamar']) ?> (Lantai <?= $active_reservation['lantai'] ?>) - <?= htmlspecialchars($active_reservation['tipe_kamar']) ?></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                      <small class="text-muted d-block">Periode & Biaya Sewa:</small>
                      <strong class="fs-6 text-success"><?= format_rupiah($active_reservation['total_biaya']) ?></strong>
                      <div class="small text-secondary"><?= htmlspecialchars($active_reservation['nama_periode']) ?> (<?= htmlspecialchars($active_reservation['durasi_sewa']) ?>)</div>
                    </div>
                  </div>
                </div>

                <?php if (!empty($active_reservation['catatan_admin'])): ?>
                <div class="alert alert-info py-2 small mb-3">
                  <strong><i class="bi bi-chat-left-dots-fill me-1"></i> Catatan Petugas Asrama:</strong><br>
                  <?= nl2br(htmlspecialchars($active_reservation['catatan_admin'])) ?>
                </div>
                <?php endif; ?>

                <!-- Action Buttons Based on Status -->
                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                  <?php if ($active_reservation['status'] === 'disetujui'): ?>
                    <a href="<?= BASE_URL ?>/mahasiswa/cetak_bukti.php?id=<?= $active_reservation['id'] ?>" class="btn btn-ulbi-navy btn-sm" target="_blank">
                      <i class="bi bi-printer-fill me-1"></i> Cetak Bukti Reservasi Resmi
                    </a>
                    <?php if ($active_reservation['status_pembayaran'] !== 'lunas'): ?>
                      <a href="<?= BASE_URL ?>/mahasiswa/upload_bukti.php?id=<?= $active_reservation['id'] ?>" class="btn btn-success btn-sm">
                        <i class="bi bi-upload me-1"></i> Unggah Bukti Pembayaran
                      </a>
                    <?php endif; ?>
                  <?php elseif ($active_reservation['status'] === 'menunggu_verifikasi'): ?>
                    <a href="<?= BASE_URL ?>/mahasiswa/batalkan.php?id=<?= $active_reservation['id'] ?>" 
                       class="btn btn-outline-danger btn-sm" 
                       onclick="return confirm('Apakah Anda yakin ingin membatalkan pengajuan reservasi ini?')">
                      <i class="bi bi-x-circle me-1"></i> Batalkan Pengajuan
                    </a>
                    <span class="small text-muted align-self-center ms-2"><i class="bi bi-hourglass-split"></i> Sedang ditinjau oleh administrator asrama.</span>
                  <?php endif; ?>
                </div>

              <?php else: ?>
                <div class="text-center py-4">
                  <i class="bi bi-journal-x text-muted" style="font-size: 3rem;"></i>
                  <h6 class="fw-bold mt-2">Belum Ada Pengajuan Reservasi</h6>
                  <p class="text-muted small mb-3">Anda belum memiliki pengajuan reservasi aktif. Silakan pilih kamar asrama yang tersedia.</p>
                  <a href="<?= BASE_URL ?>/mahasiswa/kamar.php" class="btn btn-ulbi-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Cari & Pilih Kamar
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Quick Info & Stats -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
              <h6 class="fw-bold text-navy mb-3">Ringkasan Mahasiswa</h6>
              
              <ul class="list-unstyled small mb-0">
                <li class="d-flex justify-content-between py-2 border-bottom">
                  <span class="text-muted">Total Riwayat Pengajuan:</span>
                  <span class="fw-bold"><?= $total_reservasi_mhs ?> kali</span>
                </li>
                <li class="d-flex justify-content-between py-2 border-bottom">
                  <span class="text-muted">Status Akun:</span>
                  <span class="badge bg-success">Aktif Terverifikasi</span>
                </li>
                <li class="d-flex justify-content-between py-2 border-bottom">
                  <span class="text-muted">Hak Akses (Role):</span>
                  <span class="badge bg-primary">Mahasiswa</span>
                </li>
                <li class="d-flex justify-content-between py-2 pt-2">
                  <span class="text-muted">Zona Asrama Diizinkan:</span>
                  <span class="fw-bold text-navy">
                    <?= ($profile['gender'] ?? 'L') === 'L' ? 'Asrama Putra (Tower A)' : 'Asrama Putri (Tower B)' ?>
                  </span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Pengumuman Mini -->
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
              <h6 class="fw-bold text-navy mb-3"><i class="bi bi-megaphone-fill text-warning me-1"></i> Pengumuman</h6>
              <?php foreach ($pengumuman as $pg): ?>
                <div class="mb-3 pb-3 border-bottom">
                  <div class="d-flex justify-content-between small text-muted mb-1">
                    <span class="badge bg-secondary"><?= htmlspecialchars($pg['kategori']) ?></span>
                    <span><?= date('d/m/Y', strtotime($pg['tanggal'])) ?></span>
                  </div>
                  <strong class="small d-block text-navy"><?= htmlspecialchars($pg['judul']) ?></strong>
                  <p class="small text-muted mb-0 mt-1"><?= htmlspecialchars(substr($pg['isi'], 0, 95)) ?>...</p>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
