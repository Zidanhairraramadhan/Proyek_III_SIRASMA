<?php
/**
 * SIRASMA ULBI - Profil Mahasiswa
 * Memenuhi spesifikasi: Update data profil mahasiswa milik sendiri
 */
$page_title = 'Profil Mahasiswa';
$is_dashboard = true;
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['mahasiswa']);

$user = current_user();
$db = get_db();

$error = '';
$success = '';

// Proses Simpan Profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Validasi keamanan (CSRF Token) tidak valid.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $prodi = trim($_POST['prodi'] ?? '');
        $angkatan = trim($_POST['angkatan'] ?? '');
        $gender = trim($_POST['gender'] ?? 'L');
        $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
        $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
        $alamat_asal = trim($_POST['alamat_asal'] ?? '');
        $kota_asal = trim($_POST['kota_asal'] ?? '');
        $provinsi_asal = trim($_POST['provinsi_asal'] ?? '');
        $nama_ortu = trim($_POST['nama_ortu_wali'] ?? '');
        $hp_ortu = trim($_POST['hp_ortu_wali'] ?? '');
        $kontak_darurat = trim($_POST['kontak_darurat'] ?? '');

        if (empty($full_name) || empty($phone) || empty($prodi)) {
            $error = 'Nama Lengkap, Nomor HP, dan Program Studi tidak boleh kosong.';
        } else {
            try {
                // Update tabel users
                $stmt_u = $db->prepare("UPDATE users SET full_name = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt_u->execute([$full_name, $phone, $user['id']]);

                // Update session
                $_SESSION['user']['full_name'] = $full_name;
                $_SESSION['user']['phone'] = $phone;

                // Update tabel mahasiswa_profiles
                $stmt_p = $db->prepare("UPDATE mahasiswa_profiles SET 
                    prodi = ?, angkatan = ?, gender = ?, tempat_lahir = ?, tanggal_lahir = ?,
                    alamat_asal = ?, kota_asal = ?, provinsi_asal = ?, nama_ortu_wali = ?,
                    hp_ortu_wali = ?, kontak_darurat = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = ?");
                $stmt_p->execute([
                    $prodi, $angkatan, $gender, $tempat_lahir, $tanggal_lahir,
                    $alamat_asal, $kota_asal, $provinsi_asal, $nama_ortu,
                    $hp_ortu, $kontak_darurat, $user['id']
                ]);

                log_activity('UPDATE_PROFILE', "Mahasiswa {$user['username']} memperbarui informasi profil pribadi.");
                set_flash('success', 'Profil Anda berhasil diperbarui!');
                header("Location: " . BASE_URL . "/mahasiswa/profil.php");
                exit;
            } catch (Exception $e) {
                $error = 'Gagal menyimpan perubahan profil: ' . $e->getMessage();
            }
        }
    }
}

// Proses Ganti Kata Sandi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Validasi keamanan CSRF Token tidak valid.';
    } else {
        $old_pass = (string)($_POST['old_password'] ?? '');
        $new_pass = (string)($_POST['new_password'] ?? '');
        $confirm_pass = (string)($_POST['confirm_password'] ?? '');

        // Cek password lama
        $stmt_chk = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt_chk->execute([$user['id']]);
        $current_hash = $stmt_chk->fetchColumn();

        if (!password_verify($old_pass, $current_hash)) {
            $error = 'Kata sandi lama yang Anda masukkan tidak sesuai.';
        } elseif (strlen($new_pass) < 6) {
            $error = 'Kata sandi baru minimal harus 6 karakter.';
        } elseif ($new_pass !== $confirm_pass) {
            $error = 'Konfirmasi kata sandi baru tidak cocok.';
        } else {
            $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $stmt_upd = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_upd->execute([$new_hash, $user['id']]);

            log_activity('CHANGE_PASSWORD', "Mahasiswa {$user['username']} mengganti kata sandi akun.");
            set_flash('success', 'Kata sandi berhasil diperbarui!');
            header("Location: " . BASE_URL . "/mahasiswa/profil.php");
            exit;
        }
    }
}

// Ambil data profil terbaru
$stmt_prof = $db->prepare("SELECT p.*, u.username, u.email, u.full_name, u.phone 
                          FROM users u 
                          LEFT JOIN mahasiswa_profiles p ON u.id = p.user_id 
                          WHERE u.id = ?");
$stmt_prof->execute([$user['id']]);
$data = $stmt_prof->fetch();

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
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Profil & Data Mahasiswa</h5>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/mahasiswa/index.php" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
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

      <div class="row g-4">
        <!-- Main Form -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-person-lines-fill text-warning me-2"></i>Informasi Biodata Pribadi
              </h6>
            </div>
            <div class="card-body p-4">
              <form method="POST" action="">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <h6 class="fw-bold small text-muted text-uppercase mb-3">Data Akademik & Akun:</h6>
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nomor Pokok Mahasiswa (NPM)</label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data['npm'] ?? '') ?>" readonly>
                    <small class="text-muted" style="font-size: 0.75rem;">NPM bersifat unik dan tidak dapat diubah.</small>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Email Kampus / Akun</label>
                    <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($data['email'] ?? '') ?>" readonly>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama Lengkap *</label>
                    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($data['full_name'] ?? '') ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nomor WhatsApp / HP Aktif *</label>
                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Program Studi *</label>
                    <select class="form-select" name="prodi" required>
                      <option value="D4 Teknik Informatika" <?= (($data['prodi'] ?? '') === 'D4 Teknik Informatika') ? 'selected' : '' ?>>D4 Teknik Informatika</option>
                      <option value="D4 Logistik Bisnis" <?= (($data['prodi'] ?? '') === 'D4 Logistik Bisnis') ? 'selected' : '' ?>>D4 Logistik Bisnis</option>
                      <option value="D4 Manajemen Bisnis" <?= (($data['prodi'] ?? '') === 'D4 Manajemen Bisnis') ? 'selected' : '' ?>>D4 Manajemen Bisnis</option>
                      <option value="D4 Akuntansi Keuangan" <?= (($data['prodi'] ?? '') === 'D4 Akuntansi Keuangan') ? 'selected' : '' ?>>D4 Akuntansi Keuangan</option>
                      <option value="D3 Manajemen Informatika" <?= (($data['prodi'] ?? '') === 'D3 Manajemen Informatika') ? 'selected' : '' ?>>D3 Manajemen Informatika</option>
                      <option value="D3 Logistik Bisnis" <?= (($data['prodi'] ?? '') === 'D3 Logistik Bisnis') ? 'selected' : '' ?>>D3 Logistik Bisnis</option>
                      <option value="S1 Sains Data" <?= (($data['prodi'] ?? '') === 'S1 Sains Data') ? 'selected' : '' ?>>S1 Sains Data</option>
                      <option value="S1 Bisnis Digital" <?= (($data['prodi'] ?? '') === 'S1 Bisnis Digital') ? 'selected' : '' ?>>S1 Bisnis Digital</option>
                      <option value="S1 Manajemen Rekayasa" <?= (($data['prodi'] ?? '') === 'S1 Manajemen Rekayasa') ? 'selected' : '' ?>>S1 Manajemen Rekayasa</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Angkatan</label>
                    <input type="text" class="form-control" name="angkatan" value="<?= htmlspecialchars($data['angkatan'] ?? '2024') ?>">
                  </div>

                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Jenis Kelamin</label>
                    <select class="form-select" name="gender" required>
                      <option value="L" <?= (($data['gender'] ?? 'L') === 'L') ? 'selected' : '' ?>>Laki-laki (Putra)</option>
                      <option value="P" <?= (($data['gender'] ?? '') === 'P') ? 'selected' : '' ?>>Perempuan (Putri)</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tempat Lahir</label>
                    <input type="text" class="form-control" name="tempat_lahir" value="<?= htmlspecialchars($data['tempat_lahir'] ?? '') ?>">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Tanggal Lahir</label>
                    <input type="date" class="form-control" name="tanggal_lahir" value="<?= htmlspecialchars($data['tanggal_lahir'] ?? '') ?>">
                  </div>
                </div>

                <h6 class="fw-bold small text-muted text-uppercase mb-3">Alamat Asal Mahasiswa:</h6>
                <div class="row g-3 mb-4">
                  <div class="col-md-12">
                    <label class="form-label small fw-semibold">Alamat Domisili Asal (Sesuai KTP)</label>
                    <textarea class="form-control" name="alamat_asal" rows="2"><?= htmlspecialchars($data['alamat_asal'] ?? '') ?></textarea>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Kota / Kabupaten</label>
                    <input type="text" class="form-control" name="kota_asal" value="<?= htmlspecialchars($data['kota_asal'] ?? '') ?>">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Provinsi</label>
                    <input type="text" class="form-control" name="provinsi_asal" value="<?= htmlspecialchars($data['provinsi_asal'] ?? '') ?>">
                  </div>
                </div>

                <h6 class="fw-bold small text-muted text-uppercase mb-3">Kontak Orang Tua / Wali (Darurat):</h6>
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama Orang Tua / Wali *</label>
                    <input type="text" class="form-control" name="nama_ortu_wali" value="<?= htmlspecialchars($data['nama_ortu_wali'] ?? '') ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nomor HP Orang Tua / Wali *</label>
                    <input type="text" class="form-control" name="hp_ortu_wali" value="<?= htmlspecialchars($data['hp_ortu_wali'] ?? '') ?>" required>
                  </div>

                  <div class="col-md-12">
                    <label class="form-label small fw-semibold">Kontak Tambahan / Keterangan Darurat</label>
                    <input type="text" class="form-control" name="kontak_darurat" placeholder="Contoh: Paman di Bandung - 08129999xxxx" value="<?= htmlspecialchars($data['kontak_darurat'] ?? '') ?>">
                  </div>
                </div>

                <button type="submit" class="btn btn-ulbi-primary">
                  <i class="bi bi-save me-1"></i> Simpan Pembaruan Profil
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Sidebar Password & Security Card -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-key-fill text-warning me-2"></i>Ganti Kata Sandi</h6>
            </div>
            <div class="card-body p-4">
              <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="mb-3">
                  <label class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                  <input type="password" class="form-control" name="old_password" required>
                </div>

                <div class="mb-3">
                  <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                  <input type="password" class="form-control" name="new_password" placeholder="Min. 6 karakter" required>
                </div>

                <div class="mb-3">
                  <label class="form-label small fw-semibold">Konfirmasi Kata Sandi Baru</label>
                  <input type="password" class="form-control" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-outline-dark w-100 btn-sm">
                  <i class="bi bi-shield-lock me-1"></i> Perbarui Kata Sandi
                </button>
              </form>
            </div>
          </div>

          <div class="card border-0 shadow-sm rounded-3 bg-light">
            <div class="card-body p-4">
              <h6 class="fw-bold text-navy mb-2"><i class="bi bi-shield-check text-success me-1"></i> Keamanan Akun</h6>
              <p class="small text-muted mb-0">
                Data Anda dilindungi dengan standar keamanan sistem informasi ULBI. Akses terbatas hanya untuk pengelola asrama yang berwenang.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
