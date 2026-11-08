<?php
/**
 * SIRASMA ULBI - Konfigurasi Utama Sistem
 * Sistem Reservasi Asrama Mahasiswa Berbasis Web
 * Universitas Logistik dan Bisnis Internasional (ULBI)
 */

if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    // Pastikan session directory lokal tersedia jika direktori sistem tidak dapat ditulis
    $session_dir = __DIR__ . '/../uploads/sessions';
    if (!is_dir($session_dir)) {
        @mkdir($session_dir, 0777, true);
    }
    if (is_writable($session_dir)) {
        session_save_path($session_dir);
    }

    // Pengaturan session aman
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Set zona waktu Indonesia
date_default_timezone_set('Asia/Jakarta');

// Informasi Aplikasi
define('APP_NAME', 'SIRASMA ULBI');
define('APP_LONG_NAME', 'Sistem Informasi & Reservasi Asrama Mahasiswa');
define('APP_INSTITUTION', 'Universitas Logistik dan Bisnis Internasional (ULBI)');
define('APP_VERSION', '1.0.0 Proyek 3');
define('APP_YEAR', '2026');

// Path konstanta
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// Deteksi Base URL secara otomatis dan akurat untuk XAMPP & PHP CLI Server
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$script_dir = dirname($script_path);
$rel_path = preg_replace('#/(admin|mahasiswa|auth|includes|config|database)(/.*)?$#i', '', $script_dir);
$rel_path = rtrim($rel_path, '/\\');
if ($rel_path === '/' || $rel_path === '.' || $rel_path === '\\') {
    $rel_path = '';
}
$app_url = $protocol . $host . $rel_path;

define('BASE_URL', rtrim($app_url, '/'));

// Konfigurasi Database MySQL (XAMPP Default)
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'sirasma_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// File SQLite alternatif (jika MySQL tidak aktif)
define('SQLITE_FILE', BASE_PATH . '/database/sirasma.sqlite');

/**
 * Generate CSRF Token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF Token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message untuk notifikasi antar halaman
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Tampilkan flash message jika ada
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
