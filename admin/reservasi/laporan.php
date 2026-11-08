<?php
/**
 * SIRASMA ULBI - Laporan Rekapitulasi Reservasi Asrama
 * Dokumen cetak laporan resmi untuk pengelola asrama dan pimpinan institusi
 */
$page_title = 'Laporan Reservasi Asrama';
$is_dashboard = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$db = get_db();

// Filter
$periode_id = sanitize($_GET['periode_id'] ?? '');
$asrama_id = sanitize($_GET['asrama_id'] ?? '');
$status = sanitize($_GET['status'] ?? '');

$sql = "SELECT r.*, k.nomor_kamar, a.nama_asrama, u.full_name, mp.npm, mp.prodi, mp.gender, p.nama_periode
        FROM reservasi r
        JOIN kamar k ON r.kamar_id = k.id
        JOIN asrama a ON k.asrama_id = a.id
        JOIN periode p ON r.periode_id = p.id
        JOIN users u ON r.user_id = u.id
        LEFT JOIN mahasiswa_profiles mp ON u.id = mp.user_id
        WHERE 1=1";
$params = [];

if (!empty($periode_id)) {
    $sql .= " AND r.periode_id = ?";
    $params[] = $periode_id;
}

if (!empty($asrama_id)) {
    $sql .= " AND k.asrama_id = ?";
    $params[] = $asrama_id;
}

if (!empty($status)) {
    $sql .= " AND r.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY r.tanggal_pengajuan DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$data_laporan = $stmt->fetchAll();

// Hitung total penerimaan sewa yang disetujui
$total_uang = 0;
foreach ($data_laporan as $d) {
    if ($d['status'] === 'disetujui') {
        $total_uang += $d['total_biaya'];
    }
}

$daftar_periode = $db->query("SELECT * FROM periode ORDER BY id ASC")->fetchAll();
$daftar_asrama = $db->query("SELECT * FROM asrama ORDER BY nama_asrama ASC")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <header class="dashboard-topbar no-print">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light border d-lg-none" type="button" onclick="document.getElementById('dashboardSidebar').classList.toggle('show')">
          <i class="bi bi-list fs-5"></i>
        </button>
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Laporan & Rekapitulasi Reservasi</h5>
      </div>
      <div>
        <button class="btn btn-primary btn-sm" onclick="window.print()">
          <i class="bi bi-printer me-1"></i> Cetak / Simpan Laporan PDF
        </button>
      </div>
    </header>

    <div class="dashboard-content">
      <!-- Filter Bar (Hidden on Print) -->
      <div class="card border-0 shadow-sm rounded-3 mb-4 no-print">
        <div class="card-body p-3">
          <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-3">
              <select name="periode_id" class="form-select form-select-sm">
                <option value="">Semua Periode Akademik</option>
                <?php foreach ($daftar_periode as $p): ?>
                  <option value="<?= $p['id'] ?>" <?= ($periode_id == $p['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama_periode']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3">
              <select name="asrama_id" class="form-select form-select-sm">
                <option value="">Semua Gedung Asrama</option>
                <?php foreach ($daftar_asrama as $a): ?>
                  <option value="<?= $a['id'] ?>" <?= ($asrama_id == $a['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['nama_asrama']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3">
              <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status Pengajuan</option>
                <option value="disetujui" <?= ($status === 'disetujui') ? 'selected' : '' ?>>Disetujui Saja</option>
                <option value="menunggu_verifikasi" <?= ($status === 'menunggu_verifikasi') ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                <option value="ditolak" <?= ($status === 'ditolak') ? 'selected' : '' ?>>Ditolak</option>
              </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
              <button type="submit" class="btn btn-dark btn-sm w-100">
                <i class="bi bi-filter me-1"></i> Tampilkan
              </button>
              <a href="<?= BASE_URL ?>/admin/reservasi/laporan.php" class="btn btn-outline-secondary btn-sm" title="Reset">
                <i class="bi bi-arrow-counterclockwise"></i>
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- Print Paper Container -->
      <div class="card border-0 shadow-sm rounded-3 p-4">
        <!-- Kop Laporan Resmi -->
        <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4">
          <img src="<?= BASE_URL ?>/assets/img/logo-ulbi.png" alt="ULBI" style="height: 60px;">
          <div class="flex-grow-1 text-center">
            <h5 class="fw-bold mb-0 text-uppercase" style="color:var(--ulbi-navy);">UNIVERSITAS LOGISTIK DAN BISNIS INTERNASIONAL</h5>
            <h6 class="fw-bold mb-1">PENGELOLA ASRAMA MAHASISWA (SIRASMA ULBI)</h6>
            <small class="text-muted">Jl. Sariasih No. 54, Sarijadi, Sukasari, Kota Bandung, Jawa Barat 40151</small>
          </div>
        </div>

        <div class="text-center mb-4">
          <h5 class="fw-bold text-decoration-underline mb-1">LAPORAN REKAPITULASI RESERVASI ASRAMA</h5>
          <small class="text-muted">Dicetak pada: <?= date('d F Y, H:i') ?> WIB &bull; Oleh: <?= htmlspecialchars($user['full_name']) ?></small>
        </div>

        <!-- Summary Stat Badges -->
        <div class="row g-3 mb-4 text-center">
          <div class="col-md-4">
            <div class="p-3 bg-light rounded border">
              <small class="text-muted d-block">Total Data Permohonan:</small>
              <strong class="fs-5 text-navy"><?= count($data_laporan) ?> Berkas</strong>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-light rounded border">
              <small class="text-muted d-block">Reservasi Disetujui:</small>
              <strong class="fs-5 text-success">
                <?= count(array_filter($data_laporan, fn($x) => $x['status'] === 'disetujui')) ?> Mahasiswa
              </strong>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-light rounded border">
              <small class="text-muted d-block">Potensi Pendapatan Sewa:</small>
              <strong class="fs-5 text-primary"><?= format_rupiah($total_uang) ?></strong>
            </div>
          </div>
        </div>

        <!-- Report Table -->
        <div class="table-responsive">
          <table class="table table-bordered table-striped small align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th style="width: 40px;" class="text-center">No</th>
                <th>No. Reservasi</th>
                <th>NPM & Mahasiswa</th>
                <th>Prodi</th>
                <th>Gedung / Kamar</th>
                <th>Periode</th>
                <th>Biaya</th>
                <th>Status</th>
                <th>Pembayaran</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($data_laporan)): ?>
                <tr>
                  <td colspan="9" class="text-center py-4 text-muted">Tidak ada data untuk saringan yang dipilih.</td>
                </tr>
              <?php else: ?>
                <?php $no = 1; foreach ($data_laporan as $row): ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td><strong><?= htmlspecialchars($row['kode_reservasi']) ?></strong></td>
                  <td>
                    <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($row['npm'] ?? '-') ?></small>
                  </td>
                  <td><?= htmlspecialchars($row['prodi'] ?? '-') ?></td>
                  <td>
                    <strong>Kamar <?= htmlspecialchars($row['nomor_kamar']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($row['nama_asrama']) ?></small>
                  </td>
                  <td><?= htmlspecialchars($row['nama_periode']) ?></td>
                  <td><?= format_rupiah($row['total_biaya']) ?></td>
                  <td><?= get_status_badge($row['status']) ?></td>
                  <td><?= get_payment_badge($row['status_pembayaran']) ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Signatures in Report -->
        <div class="row mt-5 pt-3">
          <div class="col-6"></div>
          <div class="col-6 text-center">
            <p class="small mb-5">
              Bandung, <?= date('d F Y') ?><br>
              Mengetahui,<br>
              <strong>Kepala Pengelola Asrama Kampus ULBI</strong>
            </p>
            <p class="small fw-bold text-decoration-underline mb-0">
              Dr. (Cand). Roni Habibi, S.Kom., M.T.
            </p>
            <small class="text-muted">NIP. 19800512 200501 1 003</small>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
