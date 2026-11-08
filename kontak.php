<?php
/**
 * SIRASMA ULBI - Kontak & Lokasi Asrama
 */
$page_title = 'Kontak & Lokasi Asrama';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-navy py-4 text-white" style="background: linear-gradient(135deg, var(--ulbi-navy) 0%, #173663 100%);">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb breadcrumb-dark mb-2">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-warning text-decoration-none">Beranda</a></li>
        <li class="breadcrumb-item active text-light" aria-current="page">Kontak Asrama</li>
      </ol>
    </nav>
    <h2 class="fw-bold mb-1">Hubungi Pengelola Asrama ULBI</h2>
    <p class="text-light opacity-75 mb-0">Informasi layanan, konsultasi reservasi hunian, dan lokasi kantor pengelola asrama kampus.</p>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">
    <!-- Contact Info Cards -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
          <h4 class="fw-bold text-navy mb-4">Kantor Pengelola Asrama</h4>
          
          <div class="d-flex align-items-start gap-3 mb-4">
            <div class="feature-icon-wrapper icon-orange mb-0" style="width: 48px; height: 48px;">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1">Alamat Kampus</h6>
              <p class="text-muted small mb-0">
                Gedung Pengelola Asrama ULBI (Lantai 1 Tower Al-Fatih),<br>
                Jl. Sariasih No. 54, Sarijadi, Kec. Sukasari, Kota Bandung, Jawa Barat 40151
              </p>
            </div>
          </div>

          <div class="d-flex align-items-start gap-3 mb-4">
            <div class="feature-icon-wrapper icon-blue mb-0" style="width: 48px; height: 48px;">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1">Layanan Telepon & WhatsApp</h6>
              <p class="text-muted small mb-0">
                Hotline Pengelola: (022) 2014032<br>
                WhatsApp Resmi: +62 812-3456-7890 / +62 813-7788-9900
              </p>
            </div>
          </div>

          <div class="d-flex align-items-start gap-3 mb-4">
            <div class="feature-icon-wrapper icon-green mb-0" style="width: 48px; height: 48px;">
              <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1">Email Resmi Asrama</h6>
              <p class="text-muted small mb-0">
                asrama@ulbi.ac.id<br>
                humas@ulbi.ac.id
              </p>
            </div>
          </div>

          <div class="d-flex align-items-start gap-3">
            <div class="feature-icon-wrapper icon-purple mb-0" style="width: 48px; height: 48px;">
              <i class="bi bi-clock-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1">Jam Operasional Layanan</h6>
              <p class="text-muted small mb-0">
                Senin – Jumat: 08.00 – 16.00 WIB<br>
                Sabtu (Khusus Check-in Awal): 09.00 – 13.00 WIB<br>
                Minggu & Hari Libur Nasional: Tutup (Petugas Keamanan Standby 24 Jam)
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Form & Campus Map Info -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
          <h4 class="fw-bold text-navy mb-2">Pesan & Pertanyaan Pengunjung</h4>
          <p class="text-muted small mb-4">Kirimkan pertanyaan atau permohonan informasi seputar hunian asrama ULBI.</p>

          <form onsubmit="alert('Terima kasih! Pesan Anda telah diterima oleh petugas asrama ULBI.'); return false;">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Nama Lengkap</label>
                <input type="text" class="form-control" placeholder="Nama Anda" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Nomor WhatsApp / HP</label>
                <input type="tel" class="form-control" placeholder="08xxxxxxxxxx" required>
              </div>
              <div class="col-md-12">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" class="form-control" placeholder="nama@email.com" required>
              </div>
              <div class="col-md-12">
                <label class="form-label small fw-semibold">Subjek Pertanyaan</label>
                <input type="text" class="form-control" placeholder="Contoh: Ketersediaan Kamar Single atau Prosedur Check-in" required>
              </div>
              <div class="col-md-12">
                <label class="form-label small fw-semibold">Isi Pesan / Pertanyaan</label>
                <textarea class="form-control" rows="4" placeholder="Tuliskan detail pertanyaan Anda..." required></textarea>
              </div>
              <div class="col-md-12">
                <button type="submit" class="btn btn-ulbi-primary">
                  <i class="bi bi-send-fill me-1"></i> Kirim Pertanyaan
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
