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
  
  <!-- Google Fonts: Plus Jakarta Sans & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Custom Stylesheets -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= file_exists(BASE_PATH . '/assets/css/style.css') ? filemtime(BASE_PATH . '/assets/css/style.css') : '1.0' ?>">
  <?php if (isset($is_dashboard) && $is_dashboard): ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= file_exists(BASE_PATH . '/assets/css/dashboard.css') ? filemtime(BASE_PATH . '/assets/css/dashboard.css') : '1.0' ?>">
  <?php endif; ?>
</head>
<body>

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
