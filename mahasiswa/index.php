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
        <div>
          <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Portal Mahasiswa</h5>
          <span class="text-muted small">Kelola reservasi dan informasi hunian asrama Anda</span>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge badge-soft-primary px-3 py-2 d-none d-md-inline-flex align-items-center">
          <i class="bi bi-person-badge text-primary me-2"></i> NPM: <?= htmlspecialchars($profile['npm'] ?? '-') ?>
        </span>
        <a href="<?= BASE_URL ?>/mahasiswa/profil.php" class="btn btn-light border btn-sm px-3" title="Edit Profil">
          <i class="bi bi-gear-fill me-1"></i> Pengaturan
        </a>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Welcome Banner -->
      <div class="card border-0 rounded-4 text-white p-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, var(--ulbi-navy) 0%, #1e3a5f 100%);">
        <div class="row align-items-center">
          <div class="col-md-8">
            <span class="badge badge-soft-warning px-3 py-1 fw-semibold mb-2">Selamat Datang di SIRASMA</span>
            <h3 class="fw-bold mb-1">Halo, <?= htmlspecialchars($user['full_name']) ?>! 👋</h3>
            <p class="text-white-50 mb-0 small">
              Program Studi: <span class="text-white fw-medium"><?= htmlspecialchars($profile['prodi'] ?? 'Belum Diisi') ?></span> &bull; 
              Kategori: <span class="text-white fw-medium"><?= ($profile['gender'] ?? 'L') === 'L' ? 'Mahasiswa (Putra)' : 'Mahasiswi (Putri)' ?></span>
            </p>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php" class="btn btn-orange shadow-sm px-4 py-2">
              <i class="bi bi-plus-circle-fill me-1"></i> Ajukan Reservasi Baru
            </a>
          </div>
        </div>
      </div>

      <!-- Incomplete Profile Warning if applicable -->
      <?php if (empty($profile['hp_ortu_wali']) || empty($profile['alamat_asal'])): ?>
      <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #FFFBEB; border-left: 4px solid var(--ulbi-warning) !important;">
        <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 bg-white shadow-sm flex-shrink-0" style="width:48px;height:48px;">
              <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
            </div>
            <div>
              <strong class="text-navy d-block">Profil Anda Belum Lengkap!</strong>
              <span class="text-muted small">Silakan lengkapi kontak darurat orang tua/wali dan alamat domisili asal agar permohonan reservasi Anda dapat diverifikasi dengan lancar.</span>
            </div>
          </div>
          <a href="<?= BASE_URL ?>/mahasiswa/profil.php" class="btn btn-warning btn-sm fw-bold px-3 py-2 text-dark">
            Lengkapi Profil Sekarang
          </a>
        </div>
      </div>
      <?php endif; ?>

      <!-- Status Pengajuan Terkini Card -->
      <div class="row g-4 mb-4">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-clock-history text-orange me-2"></i>Status Permohonan Reservasi Terkini
              </h6>
              <a href="<?= BASE_URL ?>/mahasiswa/riwayat.php" class="small text-decoration-none fw-semibold text-orange">
                Lihat Riwayat Lengkap <i class="bi bi-arrow-right"></i>
              </a>
            </div>

            <div class="card-body p-4">
              <?php if ($active_reservation): ?>
                <!-- Stepper Progress Tracker -->
                <?php
                  $s = $active_reservation['status'];
                  $p = $active_reservation['status_pembayaran'];
                  
                  $st1 = 'completed'; // diajukan
                  $st2 = 'waiting';
                  $st3 = 'waiting';
                  $st4 = 'waiting';

                  if ($s === 'menunggu_verifikasi') {
                    $st2 = 'active';
                  } elseif ($s === 'disetujui') {
                    $st2 = 'completed';
                    if ($p === 'lunas') {
                      $st3 = 'completed';
                      $st4 = 'active';
                    } elseif ($p === 'menunggu_konfirmasi') {
                      $st3 = 'active';
                    } else {
                      $st3 = 'active'; // belum bayar
                    }
                  } elseif ($s === 'ditolak' || $s === 'dibatalkan') {
                    $st2 = 'rejected';
                  }
                ?>
                <div class="stepper-wrapper mb-4">
                  <div class="stepper-item <?= $st1 ?>">
                    <div class="stepper-circle"><i class="bi bi-send-fill"></i></div>
                    <div class="stepper-title">Diajukan</div>
                  </div>
                  <div class="stepper-item <?= $st2 ?>">
                    <div class="stepper-circle">
                      <?php if ($st2 === 'rejected'): ?>
                        <i class="bi bi-x-circle-fill"></i>
                      <?php else: ?>
                        <i class="bi bi-person-check-fill"></i>
                      <?php endif; ?>
                    </div>
                    <div class="stepper-title"><?= ($s === 'ditolak') ? 'Ditolak' : (($s === 'dibatalkan') ? 'Dibatalkan' : 'Verifikasi') ?></div>
                  </div>
                  <div class="stepper-item <?= $st3 ?>">
                    <div class="stepper-circle"><i class="bi bi-credit-card-fill"></i></div>
                    <div class="stepper-title">Pembayaran</div>
                  </div>
                  <div class="stepper-item <?= $st4 ?>">
                    <div class="stepper-circle"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="stepper-title">Selesai</div>
                  </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3 pb-3 border-bottom">
                  <div>
                    <span class="text-muted small">Nomor Registrasi:</span>
                    <h5 class="fw-bold mb-0 text-navy"><?= htmlspecialchars($active_reservation['kode_reservasi']) ?></h5>
                    <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>Diajukan pada: <?= format_tanggal($active_reservation['tanggal_pengajuan'], true) ?></small>
                  </div>
                  <div class="d-flex gap-2">
                    <?= get_status_badge($active_reservation['status']) ?>
                    <?= get_payment_badge($active_reservation['status_pembayaran']) ?>
                  </div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 border">
                      <small class="text-muted d-block mb-1"><i class="bi bi-building me-1"></i>Gedung & Nomor Kamar:</small>
                      <strong class="fs-6 text-navy"><?= htmlspecialchars($active_reservation['nama_asrama']) ?></strong>
                      <div class="small text-secondary mt-1">Kamar <strong><?= htmlspecialchars($active_reservation['nomor_kamar']) ?></strong> (Lantai <?= $active_reservation['lantai'] ?>) &bull; <?= htmlspecialchars($active_reservation['tipe_kamar']) ?></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 border">
                      <small class="text-muted d-block mb-1"><i class="bi bi-cash-stack me-1"></i>Periode & Biaya Sewa:</small>
                      <strong class="fs-6 text-success"><?= format_rupiah($active_reservation['total_biaya']) ?></strong>
                      <div class="small text-secondary mt-1"><?= htmlspecialchars($active_reservation['nama_periode']) ?> (<?= htmlspecialchars($active_reservation['durasi_sewa']) ?>)</div>
                    </div>
                  </div>
                </div>

                <?php if (!empty($active_reservation['catatan_admin'])): ?>
                <div class="alert alert-info py-2 small mb-3 rounded-3 border-0" style="background:#EFF6FF;">
                  <strong class="text-navy"><i class="bi bi-chat-left-dots-fill me-1 text-primary"></i> Catatan Petugas Asrama:</strong><br>
                  <span class="text-secondary"><?= nl2br(htmlspecialchars($active_reservation['catatan_admin'])) ?></span>
                </div>
                <?php endif; ?>

                <!-- Action Buttons Based on Status -->
                <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                  <?php if ($active_reservation['status'] === 'disetujui'): ?>
                    <a href="<?= BASE_URL ?>/mahasiswa/cetak_bukti.php?id=<?= $active_reservation['id'] ?>" class="btn btn-navy btn-sm" target="_blank">
                      <i class="bi bi-printer-fill me-1"></i> Cetak Bukti Reservasi Resmi
                    </a>
                    <?php if ($active_reservation['status_pembayaran'] !== 'lunas'): ?>
                      <a href="<?= BASE_URL ?>/mahasiswa/upload_bukti.php?id=<?= $active_reservation['id'] ?>" class="btn btn-orange btn-sm">
                        <i class="bi bi-upload me-1"></i> Unggah Bukti Pembayaran
                      </a>
                    <?php endif; ?>
                  <?php elseif ($active_reservation['status'] === 'menunggu_verifikasi'): ?>
                    <a href="<?= BASE_URL ?>/mahasiswa/batalkan.php?id=<?= $active_reservation['id'] ?>" 
                       class="btn btn-outline-danger btn-sm" 
                       onclick="return confirm('Apakah Anda yakin ingin membatalkan pengajuan reservasi ini?')">
                      <i class="bi bi-x-circle me-1"></i> Batalkan Pengajuan
                    </a>
                    <span class="small text-muted align-self-center ms-2"><i class="bi bi-hourglass-split me-1 text-warning"></i> Sedang dalam proses verifikasi berkas oleh administrator.</span>
                  <?php endif; ?>
                </div>

              <?php else: ?>
                <div class="text-center py-5">
                  <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width:70px;height:70px;">
                    <i class="bi bi-journal-x text-muted" style="font-size: 2rem;"></i>
                  </div>
                  <h6 class="fw-bold mt-1 text-navy">Belum Ada Pengajuan Reservasi Aktif</h6>
                  <p class="text-muted small mb-4 mx-auto" style="max-width:380px;">Anda saat ini belum memiliki permohonan kamar aktif. Silakan pilih kamar sesuai kriteria gedung Anda.</p>
                  <a href="<?= BASE_URL ?>/mahasiswa/kamar.php" class="btn btn-orange px-4 py-2">
                    <i class="bi bi-search me-1"></i> Cari & Pilih Kamar
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Quick Info & Stats -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
              <h6 class="fw-bold text-navy mb-3"><i class="bi bi-person-check text-orange me-2"></i>Status Kemahasiswaan</h6>
              
              <ul class="list-unstyled small mb-0">
                <li class="d-flex justify-content-between py-2 border-bottom">
                  <span class="text-muted">Total Pengajuan:</span>
                  <span class="fw-bold text-navy"><?= $total_reservasi_mhs ?> kali</span>
                </li>
                <li class="d-flex justify-content-between py-2 border-bottom">
                  <span class="text-muted">Status Akun:</span>
                  <span class="badge badge-soft-success">Aktif Terverifikasi</span>
                </li>
                <li class="d-flex justify-content-between py-2 border-bottom">
                  <span class="text-muted">Hak Akses (Role):</span>
                  <span class="badge badge-soft-primary">Mahasiswa</span>
                </li>
                <li class="d-flex justify-content-between py-2 pt-2">
                  <span class="text-muted">Zona Asrama:</span>
                  <span class="fw-bold text-navy">
                    <?= ($profile['gender'] ?? 'L') === 'L' ? 'Asrama Putra (Tower A)' : 'Asrama Putri (Tower B)' ?>
                  </span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Pengumuman Mini -->
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
              <h6 class="fw-bold text-navy mb-3"><i class="bi bi-megaphone-fill text-orange me-2"></i>Pengumuman Asrama</h6>
              <?php if (empty($pengumuman)): ?>
                <p class="small text-muted mb-0">Belum ada pengumuman baru.</p>
              <?php else: ?>
                <?php foreach ($pengumuman as $pg): ?>
                  <div class="mb-3 pb-3 border-bottom last-border-0">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                      <span class="badge badge-soft-warning"><?= htmlspecialchars($pg['kategori']) ?></span>
                      <span class="font-monospace" style="font-size:0.75rem;"><?= date('d/m/Y', strtotime($pg['tanggal'])) ?></span>
                    </div>
                    <strong class="small d-block text-navy"><?= htmlspecialchars($pg['judul']) ?></strong>
                    <p class="small text-muted mb-0 mt-1"><?= htmlspecialchars(substr($pg['isi'], 0, 95)) ?>...</p>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
