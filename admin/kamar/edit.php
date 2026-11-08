<?php
/**
 * SIRASMA ULBI - Edit Data Kamar Asrama
 */
$page_title = 'Edit Data Kamar';
$is_dashboard = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$db = get_db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM kamar WHERE id = ?");
$stmt->execute([$id]);
$km = $stmt->fetch();

if (!$km) {
    set_flash('danger', 'Data kamar tidak ditemukan.');
    header("Location: " . BASE_URL . "/admin/kamar/index.php");
    exit;
}

// Restore option if triggered
if (isset($_GET['restore']) && $_GET['restore'] == 1) {
    $db->prepare("UPDATE kamar SET status = 'tersedia' WHERE id = ?")->execute([$id]);
    log_activity('RESTORE_ROOM', "Admin memulihkan kamar {$km['nomor_kamar']} menjadi aktif.");
    set_flash('success', "Kamar {$km['nomor_kamar']} berhasil diaktifkan kembali.");
    header("Location: " . BASE_URL . "/admin/kamar/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Validasi keamanan CSRF Token tidak valid.';
    } else {
        $asrama_id = (int)($_POST['asrama_id'] ?? 0);
        $nomor_kamar = trim($_POST['nomor_kamar'] ?? '');
        $lantai = (int)($_POST['lantai'] ?? 1);
        $tipe_kamar = trim($_POST['tipe_kamar'] ?? '');
        $kapasitas = (int)($_POST['kapasitas'] ?? 1);
        $terisi = (int)($_POST['terisi'] ?? 0);
        $tarif_per_semester = (float)($_POST['tarif_per_semester'] ?? 0);
        $tarif_per_tahun = (float)($_POST['tarif_per_tahun'] ?? 0);
        $fasilitas_kamar = trim($_POST['fasilitas_kamar'] ?? '');
        $status = trim($_POST['status'] ?? 'tersedia');

        if ($kapasitas < $terisi) {
            $error = "Kapasitas baru ({$kapasitas}) tidak boleh lebih kecil dari jumlah penghuni saat ini ({$terisi}).";
        } else {
            try {
                // Jika terisi sama dengan kapasitas dan status dipilih tersedia, otomatis set penuh
                if ($status === 'tersedia' && $terisi >= $kapasitas) {
                    $status = 'penuh';
                }

                $stmt_upd = $db->prepare("UPDATE kamar SET 
                    asrama_id = ?, nomor_kamar = ?, lantai = ?, tipe_kamar = ?, 
                    kapasitas = ?, terisi = ?, tarif_per_semester = ?, tarif_per_tahun = ?, 
                    fasilitas_kamar = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?");
                $stmt_upd->execute([
                    $asrama_id, $nomor_kamar, $lantai, $tipe_kamar,
                    $kapasitas, $terisi, $tarif_per_semester, $tarif_per_tahun,
                    $fasilitas_kamar, $status, $id
                ]);

                $user = current_user();
                log_activity('UPDATE_ROOM', "Admin {$user['username']} memperbarui data kamar {$nomor_kamar}.");

                set_flash('success', "Data kamar {$nomor_kamar} berhasil diperbarui.");
                header("Location: " . BASE_URL . "/admin/kamar/index.php");
                exit;
            } catch (Exception $e) {
                $error = 'Gagal memperbarui data kamar: ' . $e->getMessage();
            }
        }
    }
}

$daftar_asrama = $db->query("SELECT * FROM asrama ORDER BY nama_asrama ASC")->fetchAll();

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
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Edit Data Kamar <?= htmlspecialchars($km['nomor_kamar']) ?></h5>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/admin/kamar/index.php" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>
    </header>

    <div class="dashboard-content">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4 py-2" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
          <div class="small"><?= htmlspecialchars($error) ?></div>
        </div>
      <?php endif; ?>

      <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-pencil-square text-warning me-2"></i>Perbarui Rincian Kamar
              </h6>
            </div>
            <div class="card-body p-4">
              <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Gedung Asrama *</label>
                    <select class="form-select" name="asrama_id" required>
                      <?php foreach ($daftar_asrama as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($km['asrama_id'] == $a['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($a['nama_asrama']) ?> (<?= ($a['kategori_gender'] === 'L') ? 'Putra' : 'Putri' ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nomor Kamar *</label>
                    <input type="text" class="form-control" name="nomor_kamar" value="<?= htmlspecialchars($km['nomor_kamar']) ?>" required>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Lantai *</label>
                    <input type="number" class="form-control" name="lantai" min="1" max="10" value="<?= $km['lantai'] ?>" required>
                  </div>

                  <div class="col-md-5">
                    <label class="form-label small fw-semibold">Tipe Kamar *</label>
                    <input type="text" class="form-control" name="tipe_kamar" value="<?= htmlspecialchars($km['tipe_kamar']) ?>" required>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label small fw-semibold">Kapasitas</label>
                    <input type="number" class="form-control" name="kapasitas" min="1" max="10" value="<?= $km['kapasitas'] ?>" required>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label small fw-semibold">Terisi</label>
                    <input type="number" class="form-control" name="terisi" min="0" max="10" value="<?= $km['terisi'] ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tarif per Semester (Rp) *</label>
                    <input type="number" class="form-control" name="tarif_per_semester" value="<?= $km['tarif_per_semester'] ?>" step="50000" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tarif per Tahun (Rp) *</label>
                    <input type="number" class="form-control" name="tarif_per_tahun" value="<?= $km['tarif_per_tahun'] ?>" step="50000" required>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label small fw-semibold">Fasilitas Kamar</label>
                    <textarea class="form-control" name="fasilitas_kamar" rows="3"><?= htmlspecialchars($km['fasilitas_kamar']) ?></textarea>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Status Operasional Kamar</label>
                    <select class="form-select" name="status" required>
                      <option value="tersedia" <?= ($km['status'] === 'tersedia') ? 'selected' : '' ?>>Tersedia (Siap Huni)</option>
                      <option value="penuh" <?= ($km['status'] === 'penuh') ? 'selected' : '' ?>>Penuh</option>
                      <option value="pemeliharaan" <?= ($km['status'] === 'pemeliharaan') ? 'selected' : '' ?>>Pemeliharaan / Perbaikan</option>
                      <option value="nonaktif" <?= ($km['status'] === 'nonaktif') ? 'selected' : '' ?>>Diarsipkan / Nonaktif</option>
                    </select>
                  </div>
                </div>

                <div class="mt-4 pt-2 border-top">
                  <button type="submit" class="btn btn-ulbi-primary">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                  </button>
                  <a href="<?= BASE_URL ?>/admin/kamar/index.php" class="btn btn-outline-secondary ms-2">Batal</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
