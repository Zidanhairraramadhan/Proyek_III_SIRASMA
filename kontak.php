<?php
/**
 * SIRASMA ULBI - Kontak & Lokasi Asrama
 * Modern Minimalist + Elegant Campus SaaS Prototype
 */
$page_title = 'Kontak & Lokasi Asrama';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="py-4 text-white" style="background: linear-gradient(135deg, var(--ulbi-navy) 0%, var(--ulbi-navy-light) 100%); border-bottom: 1px solid rgba(255,255,255,0.1);">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2" style="font-size: 0.85rem;">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-warning text-decoration-none"><i class="bi bi-house me-1"></i>Beranda</a></li>
        <li class="breadcrumb-item active text-light opacity-75" aria-current="page">Kontak Asrama</li>
      </ol>
    </nav>
    <h2 class="fw-bold mb-1 text-white">Hubungi Pengelola Asrama ULBI</h2>
    <p class="text-light opacity-75 mb-0 small">Pusat informasi layanan, konsultasi reservasi hunian mahasiswa, dan kontak kantor pengelola kampus.</p>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">
    <!-- Contact Info Cards -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-4 p-md-5">
          <span class="badge-soft badge-soft-warning mb-2">Pusat Layanan</span>
          <h4 class="fw-bold text-navy mb-4">Kantor Pengelola Asrama</h4>
          
          <div class="d-flex align-items-start gap-3 mb-4">
            <div class="feature-icon-wrapper icon-orange mb-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-navy">Alamat Kampus</h6>
              <p class="text-secondary small mb-0">
                Gedung Pengelola Asrama ULBI (Lantai 1 Tower Al-Fatih),<br>
                Jl. Sariasih No. 54, Sarijadi, Kec. Sukasari, Kota Bandung, Jawa Barat 40151
              </p>
            </div>
          </div>

          <div class="d-flex align-items-start gap-3 mb-4">
            <div class="feature-icon-wrapper icon-blue mb-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-navy">Layanan Telepon &amp; WhatsApp</h6>
              <p class="text-secondary small mb-0">
                Hotline Kantor: (022) 2014032<br>
                WhatsApp Resmi: +62 812-3456-7890 / +62 813-7788-9900
              </p>
            </div>
          </div>

          <div class="d-flex align-items-start gap-3 mb-4">
            <div class="feature-icon-wrapper icon-green mb-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
              <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-navy">Email Resmi Asrama</h6>
              <p class="text-secondary small mb-0">
                asrama@ulbi.ac.id<br>
                humas@ulbi.ac.id
              </p>
            </div>
          </div>

          <div class="d-flex align-items-start gap-3">
            <div class="feature-icon-wrapper icon-navy mb-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
              <i class="bi bi-clock-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-navy">Jam Pelayanan Kantor</h6>
              <p class="text-secondary small mb-0">
                Senin – Jumat: 08.00 – 16.00 WIB<br>
                Sabtu (Khusus Check-in Awal): 09.00 – 13.00 WIB<br>
                Minggu &amp; Libur: Tutup (Petugas Pengawas Siaga 24 Jam)
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-4 p-md-5">
          <span class="badge-soft badge-soft-navy mb-2">Formulir Pesan</span>
          <h4 class="fw-bold text-navy mb-1">Pertanyaan atau Konsultasi Hunian</h4>
          <p class="text-muted small mb-4">Kirimkan pertanyaan seputar kuota kamar, masa tinggal, atau persyaratan sewa.</p>

          <form onsubmit="alert('Terima kasih! Pesan Anda telah diterima oleh petugas pengelola asrama ULBI.'); return false;">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" placeholder="Nama Anda" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Nomor WhatsApp / HP</label>
                <input type="tel" class="form-control" placeholder="08xxxxxxxxxx" required>
              </div>
              <div class="col-md-12">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" placeholder="nama@email.com" required>
              </div>
              <div class="col-md-12">
                <label class="form-label">Subjek Pertanyaan</label>
                <input type="text" class="form-control" placeholder="Contoh: Ketersediaan Kamar Single atau Prosedur Check-in" required>
              </div>
              <div class="col-md-12">
                <label class="form-label">Isi Pesan / Pertanyaan</label>
                <textarea class="form-control" rows="4" placeholder="Tuliskan detail pertanyaan Anda..." required></textarea>
              </div>
              <div class="col-md-12 pt-2">
                <button type="submit" class="btn btn-ulbi-primary">
                  <i class="bi bi-send-fill me-1"></i> Kirim Pesan Pertanyaan
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
