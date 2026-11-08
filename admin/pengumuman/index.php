<?php
/**
 * SIRASMA ULBI - Kelola Pengumuman Asrama (Admin)
 */
$page_title = 'Kelola Pengumuman Asrama';
$is_dashboard = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$db = get_db();
$error = '';

// Tambah / Hapus Pengumuman
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Validasi keamanan CSRF Token tidak valid.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $judul = trim($_POST['judul'] ?? '');
            $isi = trim($_POST['isi'] ?? '');
            $kategori = trim($_POST['kategori'] ?? 'Informasi');
            $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));

            if (empty($judul) || empty($isi)) {
                $error = 'Judul dan isi pengumuman tidak boleh kosong.';
            } else {
                $stmt = $db->prepare("INSERT INTO pengumuman (judul, isi, kategori, tanggal, is_active) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$judul, $isi, $kategori, $tanggal]);

                $user = current_user();
                log_activity('ADD_ANNOUNCEMENT', "Admin {$user['username']} menerbitkan pengumuman: {$judul}");

                set_flash('success', 'Pengumuman baru berhasil dipublikasikan!');
                header("Location: " . BASE_URL . "/admin/pengumuman/index.php");
                exit;
            }
        } elseif ($action === 'delete') {
            $del_id = (int)($_POST['id'] ?? 0);
            $db->prepare("DELETE FROM pengumuman WHERE id = ?")->execute([$del_id]);

            log_activity('DELETE_ANNOUNCEMENT', "Admin menghapus pengumuman ID: {$del_id}");
            set_flash('info', 'Pengumuman berhasil dihapus.');
            header("Location: " . BASE_URL . "/admin/pengumuman/index.php");
            exit;
        }
    }
}

$pengumuman_list = $db->query("SELECT * FROM pengumuman ORDER BY tanggal DESC")->fetchAll();

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
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Kelola Pengumuman Asrama</h5>
      </div>
      <div>
        <button class="btn btn-ulbi-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
          <i class="bi bi-plus-circle-fill me-1"></i> Tulis Pengumuman Baru
        </button>
      </div>
    </header>

    <div class="dashboard-content">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4 py-2" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
          <div class="small"><?= htmlspecialchars($error) ?></div>
        </div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
          <h6 class="fw-bold mb-0 text-navy">
            <i class="bi bi-megaphone-fill text-warning me-2"></i>Daftar Pengumuman Aktif di Portal & Landing Page
          </h6>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-light">
                <tr>
                  <th style="width: 140px;">Tanggal</th>
                  <th style="width: 120px;">Kategori</th>
                  <th>Judul Pengumuman</th>
                  <th>Ringkasan Isi</th>
                  <th style="width: 100px;" class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pengumuman_list as $p): ?>
                <tr>
                  <td class="text-muted"><?= format_tanggal($p['tanggal']) ?></td>
                  <td>
                    <span class="badge bg-secondary"><?= htmlspecialchars($p['kategori']) ?></span>
                  </td>
                  <td>
                    <strong class="text-navy"><?= htmlspecialchars($p['judul']) ?></strong>
                  </td>
                  <td><?= htmlspecialchars(substr($p['isi'], 0, 100)) ?>...</td>
                  <td class="text-end">
                    <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                      <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
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

<!-- Modal Tambah Pengumuman -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="modalTambahLabel">Tulis Pengumuman Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Judul Pengumuman *</label>
            <input type="text" class="form-control" name="judul" placeholder="Contoh: Jadwal Pembagian Kunci Kamar" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Kategori</label>
            <select class="form-select" name="kategori">
              <option value="Informasi">Informasi</option>
              <option value="Penting">Penting</option>
              <option value="Jadwal">Jadwal</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Tanggal</label>
            <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Isi Lengkap Pengumuman *</label>
            <textarea class="form-control" name="isi" rows="4" placeholder="Tuliskan isi pengumuman..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-ulbi-primary btn-sm">Publikasikan Pengumuman</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
