<?php
/**
 * SIRASMA ULBI - Helper Functions
 * Format tanggal Indonesia, mata uang, badges, validasi upload, sanitasi input
 */

/**
 * Format mata uang Rupiah
 */
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Format tanggal ke format Indonesia (e.g. 15 Oktober 2026)
 */
function format_tanggal($datetime, $with_time = false) {
    if (empty($datetime)) return '-';

    $timestamp = strtotime($datetime);
    if (!$timestamp) return '-';

    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $hari = date('d', $timestamp);
    $bulan_idx = (int)date('m', $timestamp);
    $tahun = date('Y', $timestamp);

    $formatted = $hari . ' ' . $bulan[$bulan_idx] . ' ' . $tahun;

    if ($with_time) {
        $formatted .= ' ' . date('H:i', $timestamp) . ' WIB';
    }

    return $formatted;
}

/**
 * Render Status Reservasi dalam bentuk Badge HTML
 */
function get_status_badge($status) {
    switch ($status) {
        case 'menunggu_verifikasi':
            return '<span class="badge-soft badge-soft-warning"><i class="bi bi-clock-history"></i>Menunggu Verifikasi</span>';
        case 'disetujui':
            return '<span class="badge-soft badge-soft-success"><i class="bi bi-check-circle-fill"></i>Disetujui</span>';
        case 'ditolak':
            return '<span class="badge-soft badge-soft-danger"><i class="bi bi-x-circle-fill"></i>Ditolak</span>';
        case 'dibatalkan':
            return '<span class="badge-soft badge-soft-navy"><i class="bi bi-slash-circle"></i>Dibatalkan</span>';
        case 'selesai':
            return '<span class="badge-soft badge-soft-info"><i class="bi bi-award-fill"></i>Selesai</span>';
        default:
            return '<span class="badge-soft badge-soft-navy">' . htmlspecialchars(ucfirst(str_replace('_', ' ', (string)$status))) . '</span>';
    }
}

/**
 * Render Status Pembayaran dalam bentuk Badge HTML
 */
function get_payment_badge($status) {
    switch ($status) {
        case 'belum_bayar':
            return '<span class="badge-soft badge-soft-danger"><i class="bi bi-hourglass-split"></i>Belum Bayar</span>';
        case 'menunggu_konfirmasi':
            return '<span class="badge-soft badge-soft-warning"><i class="bi bi-search"></i>Menunggu Konfirmasi</span>';
        case 'lunas':
            return '<span class="badge-soft badge-soft-success"><i class="bi bi-shield-check"></i>Lunas</span>';
        default:
            return '<span class="badge-soft badge-soft-navy">' . htmlspecialchars((string)$status) . '</span>';
    }
}

/**
 * Render Status Kamar dalam bentuk Badge HTML
 */
function get_room_badge($status) {
    switch ($status) {
        case 'tersedia':
            return '<span class="badge-soft badge-soft-success"><i class="bi bi-door-open-fill"></i>Tersedia</span>';
        case 'penuh':
            return '<span class="badge-soft badge-soft-danger"><i class="bi bi-door-closed-fill"></i>Penuh</span>';
        case 'pemeliharaan':
            return '<span class="badge-soft badge-soft-warning"><i class="bi bi-tools"></i>Perbaikan</span>';
        case 'nonaktif':
            return '<span class="badge-soft badge-soft-navy"><i class="bi bi-archive-fill"></i>Diarsipkan</span>';
        default:
            return '<span class="badge-soft badge-soft-navy">' . htmlspecialchars((string)$status) . '</span>';
    }
}

/**
 * Sanitasi string masukan untuk mencegah XSS
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

/**
 * Upload file dengan validasi ekstensi, MIME type, dan ukuran
 */
function upload_file($file, $subfolder = 'ktm', $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'], $max_size = 5242880) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Tidak ada file yang diunggah atau terjadi kesalahan upload.'];
    }

    // Periksa ukuran (default 5MB)
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Ukuran file melebihi batas maksimum (5 MB).'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        return ['success' => false, 'message' => 'Format file tidak diizinkan. Gunakan ' . implode(', ', $allowed_types) . '.'];
    }

    $dest_dir = UPLOAD_PATH . '/' . $subfolder;
    if (!is_dir($dest_dir)) {
        mkdir($dest_dir, 0777, true);
    }

    // Nama file acak unik untuk mencegah penimpaan file dan eksekusi file berbahaya
    $new_name = uniqid($subfolder . '_', true) . '.' . $ext;
    $destination = $dest_dir . '/' . $new_name;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $new_name,
            'path' => 'uploads/' . $subfolder . '/' . $new_name
        ];
    } else {
        return ['success' => false, 'message' => 'Gagal memindahkan file ke server.'];
    }
}
