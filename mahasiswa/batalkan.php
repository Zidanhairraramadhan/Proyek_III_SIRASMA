<?php
/**
 * SIRASMA ULBI - Pembatalan Reservasi oleh Mahasiswa
 * Sesuai Access Control Matrix:
 * "Update reservasi hanya berlaku untuk perubahan yang masih diizinkan,
 * misalnya membatalkan pengajuan sebelum diproses (status: menunggu_verifikasi)."
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['mahasiswa']);

$id = (int)($_GET['id'] ?? 0);

if (!check_reservation_ownership($id)) {
    http_response_code(403);
    set_flash('danger', 'Akses ditolak: Anda tidak memiliki izin untuk membatalkan reservasi ini.');
    header("Location: " . BASE_URL . "/mahasiswa/riwayat.php");
    exit;
}

$user = current_user();
$db = get_db();

// Cek status saat ini
$stmt = $db->prepare("SELECT * FROM reservasi WHERE id = ?");
$stmt->execute([$id]);
$rsv = $stmt->fetch();

if (!$rsv) {
    set_flash('danger', 'Data reservasi tidak ditemukan.');
} elseif ($rsv['status'] !== 'menunggu_verifikasi') {
    set_flash('warning', 'Pengajuan tidak dapat dibatalkan secara mandiri karena sudah diproses oleh administrator.');
} else {
    try {
        $stmt_cancel = $db->prepare("UPDATE reservasi SET status = 'dibatalkan', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt_cancel->execute([$id]);

        log_activity(
            'CANCEL_RESERVATION',
            "Mahasiswa {$user['username']} membatalkan pengajuan reservasi dengan nomor: {$rsv['kode_reservasi']}."
        );

        set_flash('success', "Permohonan reservasi nomor {$rsv['kode_reservasi']} telah berhasil dibatalkan.");
    } catch (Exception $e) {
        set_flash('danger', 'Terjadi kesalahan sistem saat membatalkan permohonan.');
    }
}

header("Location: " . BASE_URL . "/mahasiswa/riwayat.php");
exit;
