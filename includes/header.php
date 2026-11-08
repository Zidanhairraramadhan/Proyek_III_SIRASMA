<?php
/**
 * SIRASMA ULBI - Header Component
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$page_title = $page_title ?? APP_NAME;
$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SIRASMA ULBI - Sistem Reservasi Asrama Mahasiswa Universitas Logistik dan Bisnis Internasional">
  <title><?= htmlspecialchars($page_title) ?> | <?= APP_NAME ?></title>
  
  <!-- Favicon / Logo -->
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo-ulbi.png">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- Custom Stylesheets dengan Cache Buster Otomatis -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
  <?php if (isset($is_dashboard) && $is_dashboard): ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time() ?>">
  <?php endif; ?>

  <!-- Global Anti-Caret & Cursor Protection -->
  <style>
    /* 1. Global Reset: Kursor panah standar, matikan tanda sisip ketik (caret), matikan seleksi teks */
    html, body, *, *::before, *::after {
      cursor: default !important;
      caret-color: transparent !important;
      -webkit-user-select: none !important;
      -moz-user-select: none !important;
      -ms-user-select: none !important;
      user-select: none !important;
      -webkit-touch-callout: none !important;
    }

    /* 2. Semua tag teks secara eksplisit diwajibkan kursor default (panah biasa) */
    p, h1, h2, h3, h4, h5, h6, span, label, div, li, td, th, b, strong, small, em, i, section, article, header, footer, nav {
      cursor: default !important;
      caret-color: transparent !important;
      -webkit-user-select: none !important;
      user-select: none !important;
    }

    /* 3. Elemen yang dapat diklik wajib memiliki kursor pointer (tangan penunjuk) */
    a, a *, button, button *, .btn, .btn *, [role="button"], select, .form-check-input, .nav-link, .nav-link *, .dropdown-item, .page-link, .badge-clickable {
      cursor: pointer !important;
    }

    /* 4. Khusus formulir isian (input & textarea) tetap normal untuk mengetik data */
    input:not([type="button"]):not([type="submit"]):not([type="checkbox"]):not([type="radio"]):not([type="reset"]),
    textarea {
      cursor: text !important;
      caret-color: #f37021 !important;
      -webkit-user-select: text !important;
      -moz-user-select: text !important;
      -ms-user-select: text !important;
      user-select: text !important;
    }
  </style>

  <!-- Anti-Caret Browsing Script -->
  <script>
    (function () {
      // Menolak penempatan tanda sisip kursor (Caret) pada seluruh teks saat diklik
      document.addEventListener('mousedown', function (e) {
        var isFormInput = e.target.closest('input:not([type="button"]):not([type="submit"]):not([type="checkbox"]):not([type="radio"]), textarea, select, [contenteditable="true"]');
        if (!isFormInput) {
          var isClickable = e.target.closest('a, button, .btn, [role="button"], select, .form-check-input, label');
          if (!isClickable) {
            e.preventDefault();
          }
          if (window.getSelection) {
            window.getSelection().removeAllRanges();
          }
        }
      }, true);

      // Memblokir seleksi blok teks
      document.addEventListener('selectstart', function (e) {
        if (!e.target.closest('input, textarea, [contenteditable="true"]')) {
          e.preventDefault();
          return false;
        }
      }, true);

      // Menghapus kursor jika Caret Browsing browser mencoba menaruh kursor pada teks
      document.addEventListener('selectionchange', function () {
        var activeEl = document.activeElement;
        var isFormInput = activeEl && (['INPUT', 'TEXTAREA'].indexOf(activeEl.tagName) !== -1 || activeEl.isContentEditable);
        if (!isFormInput) {
          var sel = window.getSelection();
          if (sel && sel.rangeCount > 0) {
            sel.removeAllRanges();
          }
        }
      });

      // Mencegah tombol F7 memicu fitur Caret Browsing bawaan browser
      window.addEventListener('keydown', function (e) {
        if (e.key === 'F7' || e.keyCode === 118) {
          e.preventDefault();
          e.stopPropagation();
        }
      }, true);
    })();
  </script>
</head>
<body style="cursor: default !important; -webkit-user-select: none !important; user-select: none !important; caret-color: transparent !important;">

<?php if (!isset($is_dashboard) || !$is_dashboard): ?>
<!-- Public Topbar & Navbar -->
<nav class="navbar navbar-expand-lg navbar-sirasma sticky-top">
  <div class="container">
    <a class="navbar-brand-wrapper" href="<?= BASE_URL ?>/index.php">
      <div class="brand-emblem-badge">
        <i class="bi bi-building-fill"></i>
      </div>
      <div>
        <div class="brand-text-main">SIRASMA <span style="color:var(--ulbi-orange);">ULBI</span></div>
        <div class="brand-text-sub">Reservasi Asrama Kampus</div>
      </div>
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link fw-semibold px-3 <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'text-primary' : '' ?>" href="<?= BASE_URL ?>/index.php">Beranda</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-semibold px-3 <?= (basename($_SERVER['PHP_SELF']) == 'info_kamar.php') ? 'text-primary' : '' ?>" href="<?= BASE_URL ?>/info_kamar.php">Informasi Kamar</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-semibold px-3 <?= (basename($_SERVER['PHP_SELF']) == 'panduan.php') ? 'text-primary' : '' ?>" href="<?= BASE_URL ?>/panduan.php">Panduan & Tata Tertib</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-semibold px-3 <?= (basename($_SERVER['PHP_SELF']) == 'kontak.php') ? 'text-primary' : '' ?>" href="<?= BASE_URL ?>/kontak.php">Kontak Asrama</a>
        </li>
      </ul>
      
      <div class="d-flex align-items-center gap-2">
        <?php if ($user): ?>
          <a href="<?= ($user['role'] === 'admin') ? BASE_URL . '/admin/index.php' : BASE_URL . '/mahasiswa/index.php' ?>" class="btn btn-ulbi-primary btn-sm">
            <i class="bi bi-grid-fill me-1"></i> Dashboard (<?= htmlspecialchars($user['username']) ?>)
          </a>
          <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline-danger btn-sm" title="Keluar">
            <i class="bi bi-box-arrow-right"></i>
          </a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline-dark btn-sm px-3 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
          </a>
          <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-ulbi-primary btn-sm px-3">
            <i class="bi bi-person-plus-fill me-1"></i> Daftar Akun
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>

<!-- Flash Message Notifications -->
<?php
$flash = get_flash();
if ($flash):
?>
<div class="container mt-3">
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
    <i class="bi bi-info-circle-fill fs-5 me-2"></i>
    <div><?= htmlspecialchars($flash['message']) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
</div>
<?php endif; ?>
