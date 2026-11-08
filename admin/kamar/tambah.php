<?php
/**
 * SIRASMA ULBI - Tambah Kamar Asrama Baru
 */
$page_title = 'Tambah Kamar Baru';
$is_dashboard = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$db = get_db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Validasi keamanan CSRF Token tidak valid.';
    } else {
        $asrama_id = (int)($_POST['asrama_id'] ?? 0);
        $nomor_kamar = trim($_POST['nomor_kamar'] ?? '');
        $lantai = (int)($_POST['lantai'] ?? 1);
        $tipe_kamar = trim($_POST['tipe_kamar'] ?? 'Standar (4 Orang)');
        $kapasitas = (int)($_POST['kapasitas'] ?? 4);
        $tarif_per_semester = (float)($_POST['tarif_per_semester'] ?? 0);
        $tarif_per_tahun = (float)($_POST['tarif_per_tahun'] ?? 0);
        $fasilitas_kamar = trim($_POST['fasilitas_kamar'] ?? '');
        $status = trim($_POST['status'] ?? 'tersedia');

        if (empty($nomor_kamar) || $asrama_id <= 0 || $kapasitas <= 0) {
            $error = 'Nomor kamar, gedung asrama, dan kapasitas wajib diisi.';
        } else {
            // Cek duplikasi nomor kamar dalam asrama yang sama
            $stmt_chk = $db->prepare("SELECT COUNT(*) FROM kamar WHERE asrama_id = ? AND nomor_kamar = ?");
            $stmt_chk->execute([$asrama_id, $nomor_kamar]);
            if ($stmt_chk->fetchColumn() > 0) {
                $error = "Nomor kamar {$nomor_kamar} sudah ada di gedung asrama tersebut.";
            } else {
                try {
                    $stmt_ins = $db->prepare("INSERT INTO kamar (asrama_id, nomor_kamar, lantai, tipe_kamar, kapasitas, terisi, tarif_per_semester, tarif_per_tahun, fasilitas_kamar, status) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?)");
                    $stmt_ins->execute([
                        $asrama_id, $nomor_kamar, $lantai, $tipe_kamar, $kapasitas,
                        $tarif_per_semester, $tarif_per_tahun, $fasilitas_kamar, $status
                    ]);

                    $user = current_user();
                    log_activity('ADD_ROOM', "Admin {$user['username']} menambahkan kamar baru: {$nomor_kamar} (Kapasitas: {$kapasitas}).");

                    set_flash('success', "Kamar {$nomor_kamar} berhasil ditambahkan ke dalam database.");
                    header("Location: " . BASE_URL . "/admin/kamar/index.php");
                    exit;
                } catch (Exception $e) {
                    $error = 'Gagal menyimpan data kamar: ' . $e->getMessage();
                }
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
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Tambah Data Kamar Asrama</h5>
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
                <i class="bi bi-plus-circle-fill text-warning me-2"></i>Formulir Input Kamar Baru
              </h6>
            </div>
            <div class="card-body p-4">
              <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Gedung Asrama *</label>
                    <select class="form-select" name="asrama_id" required>
                      <option value="">-- Pilih Gedung Asrama --</option>
                      <?php foreach ($daftar_asrama as $a): ?>
                        <option value="<?= $a['id'] ?>">
                          <?= htmlspecialchars($a['nama_asrama']) ?> (<?= ($a['kategori_gender'] === 'L') ? 'Putra' : 'Putri' ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nomor Kamar *</label>
                    <input type="text" class="form-control" name="nomor_kamar" placeholder="Contoh: A-103 atau B-204" required>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Lantai *</label>
                    <input type="number" class="form-control" name="lantai" min="1" max="10" value="1" required>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tipe Kamar *</label>
                    <select class="form-select" name="tipe_kamar" required>
                      <option value="Standar (4 Orang)">Standar (4 Orang)</option>
                      <option value="Deluxe (2 Orang)">Deluxe (2 Orang)</option>
                      <option value="Single Room (1 Orang)">Single Room (1 Orang)</option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kapasitas (Orang) *</label>
                    <input type="number" class="form-control" name="kapasitas" min="1" max="10" value="4" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tarif per Semester (Rp) *</label>
                    <input type="number" class="form-control" name="tarif_per_semester" value="2250000" step="50000" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tarif per Tahun (Rp) *</label>
                    <input type="number" class="form-control" name="tarif_per_tahun" value="4200000" step="50000" required>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label small fw-semibold">Fasilitas Kamar Lengkap</label>
                    <textarea class="form-control" name="fasilitas_kamar" rows="3" placeholder="Ranjang tidur, meja belajar, lemari, AC/Kipas, kamar mandi dalam..."></textarea>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Status Awal Kamar</label>
                    <select class="form-select" name="status" required>
                      <option value="tersedia">Tersedia (Siap Huni)</option>
                      <option value="pemeliharaan">Pemeliharaan / Perawatan</option>
                    </select>
                  </div>
                </div>

                <div class="mt-4 pt-2 border-top">
                  <button type="submit" class="btn btn-ulbi-primary">
                    <i class="bi bi-save me-1"></i> Simpan Data Kamar
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
