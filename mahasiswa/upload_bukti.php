<?php
/**
 * SIRASMA ULBI - Upload Bukti Pembayaran Sewa Asrama
 */
$page_title = 'Unggah Bukti Pembayaran';
$is_dashboard = true;
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['mahasiswa']);

$id = (int)($_GET['id'] ?? 0);

if (!check_reservation_ownership($id)) {
    http_response_code(403);
    set_flash('danger', 'Akses ditolak: Anda tidak memiliki izin untuk mengunggah bukti pada data ini.');
    header("Location: " . BASE_URL . "/mahasiswa/riwayat.php");
    exit;
}

$user = current_user();
$db = get_db();

$stmt = $db->prepare("SELECT r.*, k.nomor_kamar, a.nama_asrama, p.nama_periode 
                      FROM reservasi r
                      JOIN kamar k ON r.kamar_id = k.id
                      JOIN asrama a ON k.asrama_id = a.id
                      JOIN periode p ON r.periode_id = p.id
                      WHERE r.id = ?");
$stmt->execute([$id]);
$rsv = $stmt->fetch();

if (!$rsv) {
    set_flash('danger', 'Data reservasi tidak ditemukan.');
    header("Location: " . BASE_URL . "/mahasiswa/riwayat.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Validasi keamanan CSRF Token tidak valid.';
    } else {
        if (!isset($_FILES['bukti_bayar']) || $_FILES['bukti_bayar']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Silakan pilih file bukti pembayaran transfer bank.';
        } else {
            $up = upload_file($_FILES['bukti_bayar'], 'pembayaran', ['jpg', 'jpeg', 'png', 'pdf']);
            if (!$up['success']) {
                $error = $up['message'];
            } else {
                try {
                    $stmt_upd = $db->prepare("UPDATE reservasi SET 
                        bukti_pembayaran = ?, 
                        status_pembayaran = 'menunggu_konfirmasi', 
                        updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?");
                    $stmt_upd->execute([$up['filename'], $id]);

                    log_activity(
                        'UPLOAD_PAYMENT_PROOF',
                        "Mahasiswa {$user['username']} mengunggah bukti transfer untuk reservasi {$rsv['kode_reservasi']}."
                    );

                    set_flash('success', 'Bukti pembayaran berhasil diunggah! Petugas pengelola akan segera memverifikasi.');
                    header("Location: " . BASE_URL . "/mahasiswa/riwayat.php");
                    exit;
                } catch (Exception $e) {
                    $error = 'Gagal menyimpan status pembayaran: ' . $e->getMessage();
                }
            }
        }
    }
}

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
          <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Konfirmasi Pembayaran</h5>
          <span class="text-muted small">Unggah bukti transfer pembayaran sewa asrama kampus</span>
        </div>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/mahasiswa/riwayat.php" class="btn btn-light border btn-sm px-3">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
        </a>
      </div>
    </header>

    <div class="dashboard-content">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4 py-3 rounded-4 shadow-sm border-0" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-3 fs-4 text-danger"></i>
          <div>
            <strong class="d-block">Gagal Mengunggah Berkas</strong>
            <span class="small"><?= htmlspecialchars($error) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <div class="row g-4 justify-content-center">
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-credit-card-2-front-fill text-orange me-2"></i>Unggah Bukti Transfer Pembayaran
              </h6>
            </div>

            <div class="card-body p-4">
              <!-- Invoice Detail Box -->
              <div class="p-4 bg-light rounded-4 border mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                  <div>
                    <span class="small text-muted d-block">Nomor Registrasi:</span>
                    <strong class="fs-6 text-navy"><?= htmlspecialchars($rsv['kode_reservasi']) ?></strong>
                  </div>
                  <div class="text-end">
                    <span class="small text-muted d-block">Total Tagihan:</span>
                    <h4 class="fw-bold text-success mb-0"><?= format_rupiah($rsv['total_biaya']) ?></h4>
                  </div>
                </div>

                <div class="small text-secondary d-flex flex-column gap-1">
                  <div><i class="bi bi-door-open me-2 text-navy"></i><strong>Alokasi Kamar:</strong> Kamar <?= htmlspecialchars($rsv['nomor_kamar']) ?> &bull; <?= htmlspecialchars($rsv['nama_asrama']) ?></div>
                  <div><i class="bi bi-calendar3 me-2 text-navy"></i><strong>Periode Sewa:</strong> <?= htmlspecialchars($rsv['nama_periode']) ?> (<?= htmlspecialchars($rsv['durasi_sewa']) ?>)</div>
                </div>
              </div>

              <!-- Bank Account Info -->
              <div class="card border-0 rounded-4 p-3 mb-4" style="background:#EFF6FF; border-left: 4px solid var(--ulbi-navy) !important;">
                <h6 class="fw-bold text-navy mb-2"><i class="bi bi-bank text-primary me-2"></i>Rekening Resmi Asrama ULBI:</h6>
                <div class="p-3 bg-white rounded-3 shadow-sm small">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted">Nama Bank:</span>
                    <strong class="text-navy">Bank Mandiri (Persero) Tbk</strong>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted">Nomor Rekening:</span>
                    <strong class="font-monospace text-navy fs-6">131-00-1234567-8</strong>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Atas Nama:</span>
                    <strong class="text-navy">Yayasan Pendidikan Logistik / Asrama ULBI</strong>
                  </div>
                  <div class="pt-2 border-top text-muted" style="font-size:0.75rem;">
                    <i class="bi bi-info-circle me-1 text-primary"></i>Cantumkan berita transfer dengan format: <strong><?= htmlspecialchars($rsv['kode_reservasi']) ?> - <?= htmlspecialchars($user['full_name']) ?></strong>
                  </div>
                </div>
              </div>

              <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="mb-4">
                  <label class="form-label small fw-semibold">Pilih Berkas Struk / Bukti Transfer <span class="text-danger">*</span></label>
                  <input type="file" class="form-control form-control-lg" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf" required style="font-size:0.95rem;">
                  <small class="text-muted d-block mt-1">Format file yang didukung: JPG, PNG, atau PDF (Maksimal 5 MB).</small>
                </div>

                <button type="submit" class="btn btn-orange btn-lg w-100 py-3 fw-bold shadow-sm">
                  <i class="bi bi-cloud-arrow-up-fill me-2"></i> Unggah & Konfirmasi Pembayaran
                </button>
              </form>
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
