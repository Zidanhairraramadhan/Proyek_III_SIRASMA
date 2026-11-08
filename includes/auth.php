<?php
/**
 * SIRASMA ULBI - Authentication & Access Control Handler
 * Implementasi Role-Based Access Control (RBAC) & Audit Logging
 * Sesuai Tugas Access Control & Keamanan Sistem Informasi ULBI
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Cek apakah pengguna saat ini sudah login
 */
function is_logged_in() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Dapatkan data user saat ini
 */
function current_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Dapatkan role user saat ini
 */
function current_user_role() {
    return $_SESSION['user']['role'] ?? null;
}

/**
 * Wajibkan login sebelum mengakses halaman.
 * Jika belum login, redirect ke halaman login dengan pesan yang aman.
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan masuk ke akun Anda terlebih dahulu untuk mengakses halaman ini.');
        $redirect_to = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header("Location: " . BASE_URL . "/auth/login.php?redirect=" . $redirect_to);
        exit;
    }
}

/**
 * Wajibkan role tertentu (misal: 'admin' atau 'mahasiswa').
 * Memenuhi spesifikasi Server-Side Role Checking:
 * "Pemeriksaan hak akses dilakukan di sisi server pada setiap permintaan,
 * termasuk ketika pengguna mengetik URL secara langsung."
 */
function require_role($allowed_roles) {
    require_login();

    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    $user_role = current_user_role();

    if (!in_array($user_role, $allowed_roles, true)) {
        // Catat insiden akses tidak sah ke dalam audit_log (Keamanan SI)
        $user = current_user();
        log_activity(
            'UNAUTHORIZED_ACCESS_ATTEMPT',
            "Pengguna {$user['username']} (Role: {$user_role}) mencoba mengakses halaman terbatas: " . ($_SERVER['REQUEST_URI'] ?? '-')
        );

        // Berikan respon penolakan aman
        http_response_code(403);
        set_flash('danger', 'Akses Ditolak: Anda tidak memiliki izin untuk membuka halaman tersebut.');

        if ($user_role === 'admin') {
            header("Location: " . BASE_URL . "/admin/index.php");
        } else {
            header("Location: " . BASE_URL . "/mahasiswa/index.php");
        }
        exit;
    }
}

/**
 * Catat aktivitas pengguna ke tabel audit_logs
 * Memenuhi prinsip Least Privilege & Audit Trail pada Dokumen Access Control
 */
function log_activity($action, $description) {
    try {
        $db = get_db();
        $user = current_user();
        $user_id = $user['id'] ?? null;
        $user_role = $user['role'] ?? 'guest';
        $username = $user['username'] ?? 'guest';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250);

        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, user_role, username, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_role, $username, $action, $description, $ip_address, $user_agent]);
    } catch (Exception $e) {
        // Fallback silent agar sistem tidak crash jika logging terkendala
        error_log("Failed to write audit log: " . $e->getMessage());
    }
}

/**
 * Validasi Kepemilikan Data (Anti-IDOR)
 * Memastikan mahasiswa HANYA dapat melihat/mengubah reservasi miliknya sendiri
 */
function check_reservation_ownership($reservation_id) {
    $user = current_user();
    if (!$user) return false;

    // Admin memiliki hak melihat seluruh reservasi
    if ($user['role'] === 'admin') {
        return true;
    }

    // Mahasiswa hanya boleh mengakses miliknya sendiri
    $db = get_db();
    $stmt = $db->prepare("SELECT user_id FROM reservasi WHERE id = ?");
    $stmt->execute([$reservation_id]);
    $owner_id = $stmt->fetchColumn();

    if ((int)$owner_id !== (int)$user['id']) {
        log_activity('IDOR_VIOLATION_ATTEMPT', "Percobaan akses tidak sah ke reservasi ID {$reservation_id} oleh user {$user['username']}");
        return false;
    }

    return true;
}
