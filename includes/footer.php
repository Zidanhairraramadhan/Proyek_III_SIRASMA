<?php
/**
 * SIRASMA ULBI - Footer Component
 */
?>
<?php if (!isset($is_dashboard) || !$is_dashboard): ?>
<footer class="footer-sirasma">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5 col-md-6">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="brand-emblem-badge" style="width: 48px; height: 48px; font-size: 1.35rem;">
            <i class="bi bi-building-fill"></i>
          </div>
          <div>
            <h5 class="text-white fw-bold mb-0">SIRASMA <span style="color:var(--ulbi-orange);">ULBI</span></h5>
            <small class="text-warning">Sistem Reservasi Asrama Mahasiswa</small>
          </div>
        </div>
        <p class="text-secondary small mb-3">
          Layanan reservasi asrama mahasiswa terpadu Universitas Logistik dan Bisnis Internasional. Memberikan kemudahan bagi mahasiswa baru maupun mahasiswa aktif dalam mencari tempat tinggal asrama yang aman, tertib, dan nyaman di lingkungan kampus.
        </p>
        <div class="d-flex gap-2">
          <span class="badge bg-dark border border-secondary text-light p-2">PHP Native</span>
          <span class="badge bg-dark border border-secondary text-light p-2">Bootstrap 5</span>
          <span class="badge bg-dark border border-secondary text-light p-2">Role-Based Access Control</span>
        </div>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <h6 class="text-white fw-bold mb-3 border-bottom pb-2 border-secondary">Tautan Cepat</h6>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="<?= BASE_URL ?>/index.php"><i class="bi bi-chevron-right me-1 text-warning"></i> Beranda</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/info_kamar.php"><i class="bi bi-chevron-right me-1 text-warning"></i> Ketersediaan Kamar</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/panduan.php"><i class="bi bi-chevron-right me-1 text-warning"></i> Tata Tertib & Alur</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/auth/login.php"><i class="bi bi-chevron-right me-1 text-warning"></i> Masuk Akun</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/auth/register.php"><i class="bi bi-chevron-right me-1 text-warning"></i> Registrasi Mahasiswa</a></li>
        </ul>
      </div>
      
      <div class="col-lg-4 col-md-12">
        <h6 class="text-white fw-bold mb-3 border-bottom pb-2 border-secondary">Kontak & Lokasi Pengelola</h6>
        <ul class="list-unstyled text-secondary small">
          <li class="mb-2 d-flex align-items-start gap-2">
            <i class="bi bi-geo-alt-fill text-warning mt-1"></i>
            <span>Gedung Pengelola Asrama ULBI, Jl. Sariasih No. 54, Sarijadi, Kec. Sukasari, Kota Bandung, Jawa Barat 40151</span>
          </li>
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-envelope-fill text-warning"></i>
            <span>asrama@ulbi.ac.id / info@ulbi.ac.id</span>
          </li>
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-telephone-fill text-warning"></i>
            <span>(022) 2014032 / WhatsApp: +62 812-3456-7890</span>
          </li>
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-clock-fill text-warning"></i>
            <span>Senin - Jumat: 08.00 - 16.00 WIB</span>
          </li>
        </ul>
      </div>
    </div>
    
    <div class="footer-bottom text-center">
      <div class="row align-items-center">
        <div class="col-md-6 text-md-start mb-2 mb-md-0">
          &copy; <?= date('Y') ?> <strong>SIRASMA ULBI</strong>. Dikembangkan untuk Proyek 3 D4 Teknik Informatika ULBI.
        </div>
        <div class="col-md-6 text-md-end text-secondary">
          Tim Pengembang: Hasan Zubeir Pohan & Zidan Hairra Ramadhan
        </div>
      </div>
    </div>
  </div>
</footer>
<?php endif; ?>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS & Caret Protection -->
<script>
  // Tooltip & Popover Auto Initialization
  document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Perlindungan ekstra anti-tanda sisip kursor teks pada elemen statis
    document.body.addEventListener('click', function (e) {
      if (!e.target.closest('input, textarea, select, [contenteditable="true"]')) {
        if (window.getSelection) {
          window.getSelection().removeAllRanges();
        }
      }
    });
  });
</script>
</body>
</html>
