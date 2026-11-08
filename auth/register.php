<?php
/**
 * SIRASMA ULBI - Register Mahasiswa Page
 */
$page_title = 'Registrasi Akun Mahasiswa';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_logged_in()) {
    header("Location: " . BASE_URL . "/mahasiswa/index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi keamanan (CSRF Token) tidak valid. Silakan coba lagi.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $npm = trim($_POST['npm'] ?? '');
        $prodi = trim($_POST['prodi'] ?? '');
        $gender = trim($_POST['gender'] ?? 'L');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim(strtolower($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $password_confirm = (string)($_POST['password_confirm'] ?? '');

        // Validasi masukan
        if (empty($full_name) || empty($npm) || empty($prodi) || empty($email) || empty($username) || empty($password)) {
            $error = 'Semua field bertanda bintang (*) wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } elseif (strlen($password) < 6) {
            $error = 'Kata sandi minimal harus 6 karakter.';
        } elseif ($password !== $password_confirm) {
            $error = 'Konfirmasi kata sandi tidak cocok.';
        } else {
            $db = get_db();

            // Cek apakah username, email, atau npm sudah terdaftar
            $stmt_check = $db->prepare("SELECT (SELECT COUNT(*) FROM users WHERE username = ?) as user_exist,
                                               (SELECT COUNT(*) FROM users WHERE email = ?) as email_exist,
                                               (SELECT COUNT(*) FROM mahasiswa_profiles WHERE npm = ?) as npm_exist");
            $stmt_check->execute([$username, $email, $npm]);
            $chk = $stmt_check->fetch();

            if ($chk['user_exist'] > 0) {
                $error = 'Username sudah digunakan, silakan pilih username lain.';
            } elseif ($chk['email_exist'] > 0) {
                $error = 'Email ini sudah terdaftar di sistem SIRASMA.';
            } elseif ($chk['npm_exist'] > 0) {
                $error = 'NPM ini sudah terdaftar di sistem SIRASMA.';
            } else {
                try {
                    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
                    
                    // Insert ke tabel users
                    $stmt_ins_user = $db->prepare("INSERT INTO users (username, email, password, role, full_name, phone, status) VALUES (?, ?, ?, 'mahasiswa', ?, ?, 'active')");
                    $stmt_ins_user->execute([$username, $email, $password_hashed, $full_name, $phone]);
                    $user_id = $db->lastInsertId();

                    // Insert ke tabel mahasiswa_profiles
                    $stmt_ins_prof = $db->prepare("INSERT INTO mahasiswa_profiles (user_id, npm, prodi, gender) VALUES (?, ?, ?, ?)");
                    $stmt_ins_prof->execute([$user_id, $npm, $prodi, $gender]);

                    // Catat Audit Log
                    log_activity('REGISTER_STUDENT', "Registrasi akun mahasiswa baru: {$username} (NPM: {$npm})");

                    set_flash('success', 'Pendaftaran akun berhasil! Silakan masuk menggunakan username dan kata sandi Anda.');
                    header("Location: " . BASE_URL . "/auth/login.php");
                    exit;
                } catch (Exception $e) {
                    $error = 'Terjadi kesalahan sistem saat mendaftarkan akun: ' . $e->getMessage();
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
  <div class="auth-card auth-card-wide my-4">
    <div class="auth-header">
      <a href="<?= BASE_URL ?>/index.php" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-3">
        <div class="brand-emblem-badge" style="width: 50px; height: 50px; font-size: 1.5rem;">
          <i class="bi bi-building-fill"></i>
        </div>
        <div class="text-start">
          <div class="brand-text-main" style="font-size: 1.35rem;">SIRASMA <span style="color:var(--ulbi-orange);">ULBI</span></div>
          <div class="brand-text-sub">Reservasi Asrama Kampus</div>
        </div>
      </a>
      <h4 class="fw-bold text-navy mb-1" style="color:var(--ulbi-navy);">Daftar Akun Mahasiswa</h4>
      <p class="text-muted small">Layanan Reservasi Asrama Universitas Logistik dan Bisnis Internasional</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger d-flex align-items-center mb-4 py-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div class="small"><?= htmlspecialchars($error) ?></div>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

      <div class="row g-3">
        <div class="col-md-6">
          <label for="full_name" class="form-label fw-semibold small">Nama Lengkap *</label>
          <input type="text" class="form-control" id="full_name" name="full_name" 
                 placeholder="Sesuai KTM / KTP" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label for="npm" class="form-label fw-semibold small">NPM (Nomor Pokok Mahasiswa) *</label>
          <input type="text" class="form-control" id="npm" name="npm" 
                 placeholder="Contoh: 714240045" value="<?= htmlspecialchars($_POST['npm'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label for="prodi" class="form-label fw-semibold small">Program Studi *</label>
          <select class="form-select" id="prodi" name="prodi" required>
            <option value="">-- Pilih Program Studi --</option>
            <option value="D4 Teknik Informatika">D4 Teknik Informatika</option>
            <option value="D4 Logistik Bisnis">D4 Logistik Bisnis</option>
            <option value="D4 Manajemen Bisnis">D4 Manajemen Bisnis</option>
            <option value="D4 Akuntansi Keuangan">D4 Akuntansi Keuangan</option>
            <option value="D3 Manajemen Informatika">D3 Manajemen Informatika</option>
            <option value="D3 Logistik Bisnis">D3 Logistik Bisnis</option>
            <option value="S1 Sains Data">S1 Sains Data</option>
            <option value="S1 Bisnis Digital">S1 Bisnis Digital</option>
            <option value="S1 Manajemen Rekayasa">S1 Manajemen Rekayasa</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold small">Jenis Kelamin *</label>
          <div class="d-flex gap-4 mt-1">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gender" id="gender_l" value="L" checked>
              <label class="form-check-label small" for="gender_l">Laki-laki (Asrama Putra)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="gender" id="gender_p" value="P">
              <label class="form-check-label small" for="gender_p">Perempuan (Asrama Putri)</label>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <label for="phone" class="form-label fw-semibold small">Nomor WhatsApp Aktif *</label>
          <input type="text" class="form-control" id="phone" name="phone" 
                 placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label for="email" class="form-label fw-semibold small">Alamat Email *</label>
          <input type="email" class="form-control" id="email" name="email" 
                 placeholder="nama@ulbi.ac.id atau gmail" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="col-md-12">
          <label for="username" class="form-label fw-semibold small">Username untuk Login *</label>
          <input type="text" class="form-control" id="username" name="username" 
                 placeholder="Gunakan huruf kecil tanpa spasi" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label for="password" class="form-label fw-semibold small">Kata Sandi *</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required>
        </div>

        <div class="col-md-6">
          <label for="password_confirm" class="form-label fw-semibold small">Konfirmasi Kata Sandi *</label>
          <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Ulangi kata sandi" required>
        </div>
      </div>

      <div class="form-check mt-3 mb-3">
        <input class="form-check-input" type="checkbox" id="terms" required>
        <label class="form-check-label small text-muted" for="terms">
          Saya menyatakan data yang saya masukkan adalah benar dan bersedia menaati seluruh tata tertib asrama ULBI.
        </label>
      </div>

      <button type="submit" class="btn btn-ulbi-primary w-100 py-2">
        <i class="bi bi-person-check-fill me-1"></i> Selesaikan Registrasi
      </button>
    </form>

    <div class="text-center mt-3 pt-2 border-top">
      <p class="small text-muted mb-0">Sudah memiliki akun? 
        <a href="<?= BASE_URL ?>/auth/login.php" class="fw-bold text-decoration-none" style="color:var(--ulbi-orange);">Masuk di sini</a>
      </p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
