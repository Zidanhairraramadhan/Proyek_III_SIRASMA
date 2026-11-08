<?php
/**
 * SIRASMA ULBI - Login Page
 * Otentikasi aman untuk Mahasiswa dan Administrator
 */
$page_title = 'Masuk Akun';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Jika sudah login, langsung arahkan ke dashboard masing-masing
if (is_logged_in()) {
    $role = current_user_role();
    header("Location: " . (($role === 'admin') ? BASE_URL . "/admin/index.php" : BASE_URL . "/mahasiswa/index.php"));
    exit;
}

$error = '';
$redirect_url = sanitize($_GET['redirect'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Validasi keamanan (CSRF Token) tidak valid. Silakan coba lagi.';
    } else {
        $username_or_email = trim($_POST['username_or_email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (empty($username_or_email) || empty($password)) {
            $error = 'Silakan isi username/email dan kata sandi Anda.';
        } else {
            $db = get_db();
            $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1");
            $stmt->execute([$username_or_email, $username_or_email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID untuk mencegah session fixation
                session_regenerate_id(true);

                // Simpan ke session (tanpa password hash demi keamanan)
                unset($user['password']);
                $_SESSION['user'] = $user;

                // Catat log aktivitas login
                log_activity('LOGIN_SUCCESS', "Pengguna {$user['username']} ({$user['role']}) berhasil login.");

                set_flash('success', "Selamat datang kembali, {$user['full_name']}!");

                if (!empty($redirect_url) && strpos($redirect_url, 'http') === false) {
                    header("Location: " . $redirect_url);
                } else {
                    if ($user['role'] === 'admin') {
                        header("Location: " . BASE_URL . "/admin/index.php");
                    } else {
                        header("Location: " . BASE_URL . "/mahasiswa/index.php");
                    }
                }
                exit;
            } else {
                $error = 'Username/email atau kata sandi yang Anda masukkan salah.';
                log_activity('LOGIN_FAILED', "Percobaan login gagal untuk akun: {$username_or_email}");
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
  <div class="auth-card">
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
      <h4 class="fw-bold text-navy mb-1" style="color:var(--ulbi-navy);">Masuk ke SIRASMA</h4>
      <p class="text-muted small">Sistem Informasi &amp; Reservasi Asrama ULBI</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger d-flex align-items-center mb-4 py-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div class="small"><?= htmlspecialchars($error) ?></div>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      
      <div class="mb-3">
        <label for="username_or_email" class="form-label fw-semibold small">Username atau Email Kampus</label>
        <div class="input-group">
          <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="username_or_email" name="username_or_email" 
                 placeholder="Contoh: hasan atau admin" value="<?= htmlspecialchars($_POST['username_or_email'] ?? '') ?>" required autofocus>
        </div>
      </div>

      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
          <label for="password" class="form-label fw-semibold small">Kata Sandi</label>
        </div>
        <div class="input-group">
          <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
            <i class="bi bi-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-ulbi-primary w-100 py-2 mt-2">
        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
      </button>
    </form>

    <div class="auth-divider">Akun Demo Cepat</div>
    <div class="d-flex flex-column gap-2">
      <button class="btn btn-sm btn-outline-primary text-start d-flex justify-content-between align-items-center py-2" 
              onclick="fillAccount('admin', 'admin123')">
        <div>
          <strong class="d-block"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Admin Asrama</strong>
          <small class="text-muted">User: admin | Pass: admin123</small>
        </div>
        <span class="badge bg-primary">Pilih</span>
      </button>

      <button class="btn btn-sm btn-outline-secondary text-start d-flex justify-content-between align-items-center py-2" 
              onclick="fillAccount('hasan', 'mahasiswa123')">
        <div>
          <strong class="d-block"><i class="bi bi-person-fill text-info me-1"></i> Mahasiswa: Hasan (Penghuni)</strong>
          <small class="text-muted">User: hasan | Pass: mahasiswa123</small>
        </div>
        <span class="badge bg-secondary">Pilih</span>
      </button>

      <button class="btn btn-sm btn-outline-secondary text-start d-flex justify-content-between align-items-center py-2" 
              onclick="fillAccount('zidan', 'mahasiswa123')">
        <div>
          <strong class="d-block"><i class="bi bi-person-fill text-info me-1"></i> Mahasiswa: Zidan (Pending)</strong>
          <small class="text-muted">User: zidan | Pass: mahasiswa123</small>
        </div>
        <span class="badge bg-secondary">Pilih</span>
      </button>
    </div>

    <div class="text-center mt-4 pt-2 border-top">
      <p class="small text-muted mb-0">Belum memiliki akun mahasiswa? 
        <a href="<?= BASE_URL ?>/auth/register.php" class="fw-bold text-decoration-none" style="color:var(--ulbi-orange);">Daftar di sini</a>
      </p>
    </div>
  </div>
</div>

<script>
function togglePassword() {
  const pInput = document.getElementById('password');
  const icon = document.getElementById('toggleIcon');
  if (pInput.type === 'password') {
    pInput.type = 'text';
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  } else {
    pInput.type = 'password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
}

function fillAccount(user, pass) {
  document.getElementById('username_or_email').value = user;
  document.getElementById('password').value = pass;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
