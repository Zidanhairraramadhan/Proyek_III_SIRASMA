<?php
/**
 * SIRASMA ULBI - Dashboard Sidebar
 * Dinamis berdasarkan Role (Admin / Mahasiswa)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';

$user = current_user();
$role = current_user_role();
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<aside class="dashboard-sidebar" id="dashboardSidebar">
  <!-- Brand Header -->
  <div class="sidebar-header">
    <div class="brand-emblem-badge" style="width: 38px; height: 38px; font-size: 1.15rem; border-radius: 10px;">
      <i class="bi bi-building-fill"></i>
    </div>
    <div>
      <h6 class="sidebar-brand-name">SIRASMA <span style="color:var(--ulbi-orange);">ULBI</span></h6>
      <p class="sidebar-brand-sub"><?= ($role === 'admin') ? 'Portal Administrator' : 'Portal Mahasiswa' ?></p>
    </div>
  </div>

  <!-- User Profile Info -->
  <div class="sidebar-user">
    <div class="sidebar-user-avatar">
      <?= strtoupper(substr($user['full_name'] ?? $user['username'] ?? 'U', 0, 1)) ?>
    </div>
    <div class="overflow-hidden">
      <div class="sidebar-user-name" title="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
        <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>
      </div>
      <div class="sidebar-user-role">
        <i class="bi bi-shield-lock-fill text-warning me-1"></i>
        <?= ($role === 'admin') ? 'Administrator Asrama' : 'Mahasiswa ULBI' ?>
      </div>
    </div>
  </div>

  <!-- Navigation Menu -->
  <ul class="sidebar-menu">
    <?php if ($role === 'admin'): ?>
      <!-- Menu Administrator -->
      <li class="sidebar-category">Menu Utama</li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/admin/index.php" class="sidebar-link <?= ($current_page == 'index.php' && $current_dir == 'admin') ? 'active' : '' ?>">
          <i class="bi bi-speedometer2"></i>
          <span>Dashboard Admin</span>
        </a>
      </li>

      <li class="sidebar-category">Operasional Asrama</li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/admin/reservasi/index.php" class="sidebar-link <?= ($current_dir == 'reservasi' && $current_page != 'laporan.php') ? 'active' : '' ?>">
          <i class="bi bi-card-checklist"></i>
          <span>Verifikasi Reservasi</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/admin/kamar/index.php" class="sidebar-link <?= ($current_dir == 'kamar') ? 'active' : '' ?>">
          <i class="bi bi-door-open"></i>
          <span>Kelola Kamar & Fasilitas</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/admin/mahasiswa/index.php" class="sidebar-link <?= ($current_dir == 'mahasiswa') ? 'active' : '' ?>">
          <i class="bi bi-people-fill"></i>
          <span>Data Mahasiswa & Penghuni</span>
        </a>
      </li>

      <li class="sidebar-category">Laporan & Publikasi</li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/admin/reservasi/laporan.php" class="sidebar-link <?= ($current_page == 'laporan.php') ? 'active' : '' ?>">
          <i class="bi bi-file-earmark-bar-graph"></i>
          <span>Laporan Reservasi</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/admin/pengumuman/index.php" class="sidebar-link <?= ($current_dir == 'pengumuman') ? 'active' : '' ?>">
          <i class="bi bi-megaphone"></i>
          <span>Pengumuman Asrama</span>
        </a>
      </li>

      <li class="sidebar-category">Keamanan & Sistem</li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/admin/logs/index.php" class="sidebar-link <?= ($current_dir == 'logs') ? 'active' : '' ?>">
          <i class="bi bi-journal-text"></i>
          <span>Audit Log / Catatan Aktivitas</span>
        </a>
      </li>

    <?php else: ?>
      <!-- Menu Mahasiswa -->
      <li class="sidebar-category">Portal Mahasiswa</li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/mahasiswa/index.php" class="sidebar-link <?= ($current_page == 'index.php' && $current_dir == 'mahasiswa') ? 'active' : '' ?>">
          <i class="bi bi-grid-fill"></i>
          <span>Dashboard Saya</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/mahasiswa/profil.php" class="sidebar-link <?= ($current_page == 'profil.php') ? 'active' : '' ?>">
          <i class="bi bi-person-badge-fill"></i>
          <span>Profil Mahasiswa</span>
        </a>
      </li>

      <li class="sidebar-category">Layanan Reservasi</li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/mahasiswa/kamar.php" class="sidebar-link <?= ($current_page == 'kamar.php') ? 'active' : '' ?>">
          <i class="bi bi-search"></i>
          <span>Cari Kamar Asrama</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/mahasiswa/reservasi.php" class="sidebar-link <?= ($current_page == 'reservasi.php') ? 'active' : '' ?>">
          <i class="bi bi-plus-circle-fill"></i>
          <span>Ajukan Reservasi</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/mahasiswa/riwayat.php" class="sidebar-link <?= ($current_page == 'riwayat.php' || $current_page == 'cetak_bukti.php' || $current_page == 'upload_bukti.php') ? 'active' : '' ?>">
          <i class="bi bi-clock-history"></i>
          <span>Riwayat & Status Pengajuan</span>
        </a>
      </li>

      <li class="sidebar-category">Informasi Kampus</li>
      <li class="sidebar-item">
        <a href="<?= BASE_URL ?>/panduan.php" class="sidebar-link" target="_blank">
          <i class="bi bi-book"></i>
          <span>Panduan & Aturan Asrama <i class="bi bi-box-arrow-up-right small ms-1"></i></span>
        </a>
      </li>
    <?php endif; ?>

    <li class="sidebar-category">Akun</li>
    <li class="sidebar-item">
      <a href="<?= BASE_URL ?>/index.php" class="sidebar-link">
        <i class="bi bi-globe"></i>
        <span>Lihat Halaman Utama</span>
      </a>
    </li>
    <li class="sidebar-item">
      <a href="<?= BASE_URL ?>/auth/logout.php" class="sidebar-link text-danger">
        <i class="bi bi-box-arrow-right"></i>
        <span>Keluar Sistem</span>
      </a>
    </li>
  </ul>

  <!-- Sidebar Footer -->
  <div class="sidebar-footer text-muted small">
    <div class="d-flex justify-content-between align-items-center">
      <span>SIRASMA v1.0</span>
      <span class="badge bg-secondary">ULBI 2026</span>
    </div>
  </div>
</aside>
