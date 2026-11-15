<?php
/**
 * SIRASMA ULBI - Panduan & Tata Tertib Asrama
 * Modern Minimalist + Elegant Campus SaaS Prototype
 */
$page_title = 'Panduan & Tata Tertib Asrama';
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
        <li class="breadcrumb-item active text-light opacity-75" aria-current="page">Panduan &amp; Tata Tertib</li>
      </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <h2 class="fw-bold mb-1 text-white">Panduan Alur Reservasi &amp; Tata Tertib Asrama</h2>
        <p class="text-light opacity-75 mb-0 small">Pedoman komprehensif pendaftaran, dokumen persyaratan, dan ketentuan operasional hunian kampus ULBI.</p>
      </div>
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-ulbi-primary btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Mulai Pendaftaran Akun
      </a>
    </div>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">
    <!-- Left Navigation Sticky -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 95px; border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <div class="feature-icon-wrapper icon-orange mb-0" style="width: 32px; height: 32px; font-size: 1rem;">
              <i class="bi bi-list-nested"></i>
            </div>
            <h6 class="fw-bold text-navy mb-0">Daftar Isi Panduan</h6>
          </div>
          <div class="list-group list-group-flush small">
            <a href="#alur" class="list-group-item list-group-item-action border-0 py-2 rounded-2 mb-1 fw-semibold text-secondary d-flex align-items-center gap-2">
              <i class="bi bi-diagram-3 text-warning"></i> 1. Alur Reservasi
            </a>
            <a href="#syarat" class="list-group-item list-group-item-action border-0 py-2 rounded-2 mb-1 fw-semibold text-secondary d-flex align-items-center gap-2">
              <i class="bi bi-file-earmark-check text-info"></i> 2. Dokumen Persyaratan
            </a>
            <a href="#tata-tertib" class="list-group-item list-group-item-action border-0 py-2 rounded-2 mb-1 fw-semibold text-secondary d-flex align-items-center gap-2">
              <i class="bi bi-shield-exclamation text-danger"></i> 3. Tata Tertib Hunian
            </a>
            <a href="#keamanan" class="list-group-item list-group-item-action border-0 py-2 rounded-2 mb-1 fw-semibold text-secondary d-flex align-items-center gap-2">
              <i class="bi bi-lock text-success"></i> 4. Keamanan &amp; RBAC
            </a>
            <a href="#faq" class="list-group-item list-group-item-action border-0 py-2 rounded-2 fw-semibold text-secondary d-flex align-items-center gap-2">
              <i class="bi bi-question-circle text-primary"></i> 5. Tanya Jawab (FAQ)
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Content Column -->
    <div class="col-lg-9">
      <!-- Section 1: Alur Reservasi -->
      <section id="alur" class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-4 p-md-5">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="feature-icon-wrapper icon-orange mb-0">
              <i class="bi bi-diagram-3-fill"></i>
            </div>
            <div>
              <span class="badge-soft badge-soft-warning mb-1">Tahapan Sistem</span>
              <h4 class="fw-bold text-navy mb-0">1. Alur Lengkap Proses Reservasi</h4>
            </div>
          </div>
          <p class="text-secondary small mb-4">
            Pengajuan kamar asrama ULBI dirancang secara terstruktur melalui 6 tahapan digital berikut:
          </p>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="p-3 rounded-3 bg-light border h-100">
                <span class="badge bg-navy text-white px-2 py-1 mb-2" style="font-size:0.75rem;">Tahap 1</span>
                <h6 class="fw-bold mb-1 text-navy">Registrasi Akun Mahasiswa</h6>
                <p class="small text-muted mb-0">Mahasiswa mendaftarkan akun di portal SIRASMA menggunakan NPM, email aktif kampus, dan kata sandi yang kuat.</p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="p-3 rounded-3 bg-light border h-100">
                <span class="badge bg-navy text-white px-2 py-1 mb-2" style="font-size:0.75rem;">Tahap 2</span>
                <h6 class="fw-bold mb-1 text-navy">Pengisian Data Profil &amp; Kontak Darurat</h6>
                <p class="small text-muted mb-0">Lengkapi data pribadi, program studi, kota asal, serta nomor kontak darurat orang tua/wali untuk verifikasi.</p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="p-3 rounded-3 bg-light border h-100">
                <span class="badge bg-navy text-white px-2 py-1 mb-2" style="font-size:0.75rem;">Tahap 3</span>
                <h6 class="fw-bold mb-1 text-navy">Pemilihan Kamar &amp; Periode Tinggal</h6>
                <p class="small text-muted mb-0">Pilih gedung asrama sesuai gender (Tower Al-Fatih/An-Nisa), tentukan nomor kamar dan durasi sewa (1 semester / 1 tahun).</p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="p-3 rounded-3 bg-light border h-100">
                <span class="badge bg-navy text-white px-2 py-1 mb-2" style="font-size:0.75rem;">Tahap 4</span>
                <h6 class="fw-bold mb-1 text-navy">Pengiriman Berkas &amp; Dokumen KTM</h6>
                <p class="small text-muted mb-0">Kirim formulir pengajuan disertai berkas KTM. Sistem otomatis menerbitkan <strong>Kode Registrasi unik (RSV-2026-XXXX)</strong>.</p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="p-3 rounded-3 bg-light border h-100">
                <span class="badge bg-navy text-white px-2 py-1 mb-2" style="font-size:0.75rem;">Tahap 5</span>
                <h6 class="fw-bold mb-1 text-navy">Verifikasi Petugas Asrama</h6>
                <p class="small text-muted mb-0">Pengelola memeriksa kelengkapan administrasi dan kuota hunian secara online. Status diperbarui ke "Disetujui" atau "Ditolak".</p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="p-3 rounded-3 bg-light border h-100">
                <span class="badge bg-navy text-white px-2 py-1 mb-2" style="font-size:0.75rem;">Tahap 6</span>
                <h6 class="fw-bold mb-1 text-navy">Pembayaran, Cetak Bukti &amp; Check-in</h6>
                <p class="small text-muted mb-0">Setelah disetujui, lakukan transfer sewa, upload bukti bayar, cetak Surat Bukti Digital, dan ambil kunci kamar di kantor asrama.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 2: Dokumen Persyaratan -->
      <section id="syarat" class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-4 p-md-5">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="feature-icon-wrapper icon-blue mb-0">
              <i class="bi bi-file-earmark-check-fill"></i>
            </div>
            <div>
              <span class="badge-soft badge-soft-info mb-1">Administrasi</span>
              <h4 class="fw-bold text-navy mb-0">2. Dokumen Persyaratan Masuk Asrama</h4>
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <div class="p-3 rounded-3 border bg-light h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-credit-card-2-front text-primary fs-5"></i>
                  <h6 class="fw-bold mb-0 text-navy">Kartu Tanda Mahasiswa (KTM)</h6>
                </div>
                <p class="small text-secondary mb-0">
                  Salinan digital KTM aktif atau Bukti Registrasi Mahasiswa Baru bagi mahasiswa angkatan baru ULBI (format PDF, JPG, atau PNG maks 5 MB).
                </p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="p-3 rounded-3 border bg-light h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-telephone-inbound text-success fs-5"></i>
                  <h6 class="fw-bold mb-0 text-navy">Kontak Darurat Orang Tua / Wali</h6>
                </div>
                <p class="small text-secondary mb-0">
                  Nomor kontak aktif orang tua atau wali yang dapat dihubungi sewaktu-waktu oleh pihak kampus jika terjadi kondisi darurat kesehatan atau kedisiplinan.
                </p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="p-3 rounded-3 border bg-light h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-file-text text-danger fs-5"></i>
                  <h6 class="fw-bold mb-0 text-navy">Pernyataan Kepatuhan Tata Tertib</h6>
                </div>
                <p class="small text-secondary mb-0">
                  Persetujuan digital yang disetujui saat mengisi formulir pengajuan reservasi untuk mematuhi jam malam dan ketentuan hunian asrama.
                </p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="p-3 rounded-3 border bg-light h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-receipt-cutoff text-info fs-5"></i>
                  <h6 class="fw-bold mb-0 text-navy">Bukti Pembayaran Sewa Asrama</h6>
                </div>
                <p class="small text-secondary mb-0">
                  Struk transfer bank atau bukti pelunasan biaya sewa asrama resmi yang diunggah ke portal setelah permohonan disetujui pengelola.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 3: Tata Tertib Asrama -->
      <section id="tata-tertib" class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-4 p-md-5">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="feature-icon-wrapper icon-orange mb-0">
              <i class="bi bi-shield-exclamation"></i>
            </div>
            <div>
              <span class="badge-soft badge-soft-warning mb-1">Ketertiban Kampus</span>
              <h4 class="fw-bold text-navy mb-0">3. Tata Tertib &amp; Ketentuan Penghuni Asrama</h4>
            </div>
          </div>

          <div class="table-responsive mt-3">
            <table class="table table-custom align-middle">
              <thead>
                <tr>
                  <th style="width: 60px;">No</th>
                  <th style="width: 220px;">Ketentuan Pokok</th>
                  <th>Uraian Peraturan &amp; Sanksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-center fw-bold">1</td>
                  <td><strong>Pemberlakuan Jam Malam</strong></td>
                  <td>Pintu gerbang asrama ditutup pukul <strong>22.00 WIB</strong> setiap hari. Kegiatan akademik di luar jam tersebut memerlukan izin tertulis dari pimpinan prodi / pengelola asrama.</td>
                </tr>
                <tr>
                  <td class="text-center fw-bold">2</td>
                  <td><strong>Pemisahan Zona Gender</strong></td>
                  <td>Dilarang keras memasuki gedung asrama lawan jenis tanpa izin resmi. Tamu hanya boleh diterima di ruang lobi komunal lantai 1. Pelanggaran berat berakibat pencabutan hak hunian.</td>
                </tr>
                <tr>
                  <td class="text-center fw-bold">3</td>
                  <td><strong>Peralatan Daya Listrik Tinggi</strong></td>
                  <td>Dilarang membawa peralatan elektronik berdaya tinggi (seperti kompor listrik/AC portabel rakitan) tanpa izin teknis demi pencegahan bahaya korsleting dan kebakaran.</td>
                </tr>
                <tr>
                  <td class="text-center fw-bold">4</td>
                  <td><strong>Kebersihan &amp; Fasilitas Bersama</strong></td>
                  <td>Penghuni wajib menjaga kebersihan kamar pribadi dan fasilitas bersama (musholla, dapur koridor, toilet umum, dan tempat pembuangan sampah terpilah).</td>
                </tr>
                <tr>
                  <td class="text-center fw-bold">5</td>
                  <td><strong>Bebas Rokok &amp; Narkoba</strong></td>
                  <td>Seluruh area gedung asrama merupakan <strong>Kawasan Bebas Asap Rokok, Miras, dan Narkotika</strong>. Pelanggaran diproses melalui Komisi Disiplin ULBI.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Section 4: Access Control & Privasi Data -->
      <section id="keamanan" class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-4 p-md-5">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="feature-icon-wrapper icon-green mb-0">
              <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
              <span class="badge-soft badge-soft-success mb-1">Keamanan Informasi</span>
              <h4 class="fw-bold text-navy mb-0">4. Kebijakan Keamanan Sistem &amp; RBAC</h4>
            </div>
          </div>
          <p class="text-secondary small mb-3">
            Sistem SIRASMA menerapkan standar kepatuhan kontrol akses <em>Role-Based Access Control (RBAC)</em> dan prinsip <em>Least Privilege</em>:
          </p>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-navy small mb-1"><i class="bi bi-person-check-fill text-primary me-2"></i>Pemisahan Peran Tegas</h6>
                <p class="small text-muted mb-0">Mahasiswa hanya dapat mengakses data miliknya sendiri. Data kamar dan verifikasi mutlak berada dalam wewenang Administrator.</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-navy small mb-1"><i class="bi bi-shield-shaded text-success me-2"></i>Perlindungan Anti-IDOR</h6>
                <p class="small text-muted mb-0">Setiap permintaan unduh atau edit data diverifikasi kepemilikannya di sisi server, mencegah manipulasi ID parameter URL.</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-navy small mb-1"><i class="bi bi-key-fill text-warning me-2"></i>Enkripsi Password BCRYPT</h6>
                <p class="small text-muted mb-0">Kata sandi akun dienkripsi satu arah menggunakan algoritma hashing standar industri sehingga aman dari kebocoran.</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-navy small mb-1"><i class="bi bi-journal-text text-danger me-2"></i>Audit Trail Terpadu</h6>
                <p class="small text-muted mb-0">Setiap aktivitas penting (login, submit reservasi, approval, perubahan tarif) dicatat stempel waktu dan IP address secara otomatis.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 5: FAQ -->
      <section id="faq" class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid var(--slate-200) !important;">
        <div class="card-body p-4 p-md-5">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="feature-icon-wrapper icon-blue mb-0">
              <i class="bi bi-question-circle-fill"></i>
            </div>
            <div>
              <span class="badge-soft badge-soft-info mb-1">Bantuan Mahasiswa</span>
              <h4 class="fw-bold text-navy mb-0">5. Pertanyaan yang Sering Diajukan (FAQ)</h4>
            </div>
          </div>

          <div class="accordion accordion-sirasma mt-3" id="accordionFaq">
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq1">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                  Berapa lama proses verifikasi pengajuan reservasi?
                </button>
              </h2>
              <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body">
                  Proses verifikasi dokumen administrasi oleh pengelola asrama umumnya memerlukan waktu 1 hingga 2 hari kerja setelah mahasiswa melengkapi seluruh data dan berkas KTM di portal SIRASMA.
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header" id="faq2">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                  Apakah saya dapat membatalkan pengajuan yang sudah dikirim?
                </button>
              </h2>
              <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body">
                  Ya, mahasiswa dapat membatalkan pengajuan secara mandiri melalui menu <strong>Riwayat Reservasi</strong> selama status permohonan masih dalam tahap <em>Menunggu Verifikasi</em>. Jika sudah disetujui, pembatalan harus dikoordinasikan langsung dengan petugas pengelola asrama.
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header" id="faq3">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                  Apakah tarif sewa asrama sudah termasuk fasilitas listrik dan air?
                </button>
              </h2>
              <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body">
                  Biaya sewa standar sudah mencakup pemakaian air bersih harian, fasilitas jaringan WiFi kampus, serta listrik reguler untuk penerangan dan pengisian daya laptop/ponsel.
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
