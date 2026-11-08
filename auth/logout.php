<?php
/**
 * SIRASMA ULBI - Logout Handler
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    $user = current_user();
    log_activity('LOGOUT', "Pengguna {$user['username']} telah keluar sistem.");
}

// Hapus semua data sesi
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Mulai sesi baru untuk flash message
session_start();
set_flash('info', 'Anda telah berhasil keluar dari akun.');
header("Location: " . BASE_URL . "/auth/login.php");
exit;
