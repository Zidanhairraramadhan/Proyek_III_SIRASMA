<?php
/**
 * SIRASMA ULBI - Database Connection Handler
 * Mendukung MySQL (XAMPP) dan SQLite Fallback otomatis
 */

require_once __DIR__ . '/config.php';

function get_db() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $driver = 'mysql';

    // Coba koneksi MySQL terlebih dahulu
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 2, // Timeout cepat jika MySQL offline
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->query("SELECT 1"); // Test koneksi
        return $pdo;
    } catch (PDOException $e) {
        // Jika MySQL gagal terhubung atau database belum dibuat, alihkan otomatis ke SQLite
        // Ini memastikan website bisa langsung berjalan seketika saat diuji/demo
        $driver = 'sqlite';
    }

    // Koneksi ke SQLite
    try {
        $sqlite_dir = dirname(SQLITE_FILE);
        if (!is_dir($sqlite_dir)) {
            mkdir($sqlite_dir, 0777, true);
        }

        $dsn = "sqlite:" . SQLITE_FILE;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Aktifkan foreign key support di SQLite
        $pdo->exec("PRAGMA foreign_keys = ON;");

        // Cek apakah tabel utama sudah ada, jika belum inisialisasi otomatis
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if (!$stmt->fetch()) {
            require_once BASE_PATH . '/database/setup_database.php';
            init_sqlite_schema($pdo);
        }

        return $pdo;
    } catch (PDOException $e) {
        die("Gagal menghubungkan ke basis data: " . htmlspecialchars($e->getMessage()));
    }
}

/**
 * Cek jenis database aktif ('mysql' atau 'sqlite')
 */
function get_db_driver() {
    $db = get_db();
    return $db->getAttribute(PDO::ATTR_DRIVER_NAME);
}
