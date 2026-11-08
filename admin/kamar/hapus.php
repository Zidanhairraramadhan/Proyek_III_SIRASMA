<?php
/**
 * SIRASMA ULBI - Nonaktifkan / Arsipkan Kamar
 * Sesuai Access Control Matrix:
 * "Delete kamar tidak dilakukan secara rutin; gunakan penonaktifan/arsip"
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
$db = get_db();

$stmt = $db->prepare("SELECT * FROM kamar WHERE id = ?");
$stmt->execute([$id]);
$km = $stmt->fetch();

if (!$km) {
    set_flash('danger', 'Data kamar tidak ditemukan.');
} else {
    // Nonaktifkan kamar (Soft delete)
    $stmt_arch = $db->prepare("UPDATE kamar SET status = 'nonaktif', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt_arch->execute([$id]);

    $user = current_user();
    log_activity('ARCHIVE_ROOM', "Admin {$user['username']} menonaktifkan/mengarsipkan kamar {$km['nomor_kamar']}.");

    set_flash('info', "Kamar {$km['nomor_kamar']} telah dinonaktifkan (diarsipkan).");
}

header("Location: " . BASE_URL . "/admin/kamar/index.php");
exit;
