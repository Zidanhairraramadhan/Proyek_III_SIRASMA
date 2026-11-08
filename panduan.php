<?php
/**
 * SIRASMA ULBI - Panduan & Tata Tertib Asrama
 */
$page_title = 'Panduan & Tata Tertib Asrama';
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
        <li class="breadcrumb-item active text-light" aria-current="page">Panduan & Tata Tertib</li>
      </ol>
    </nav>
    <h2 class="fw-bold mb-1">Panduan Alur Reservasi & Tata Tertib Asrama</h2>
    <p class="text-light opacity-75 mb-0">Petunjuk teknis pendaftaran, dokumen persyaratan, dan ketentuan operasional hunian asrama ULBI.</p>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">
    <!-- Left Navigation Sticky -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 90px;">
        <div class="card-body p-3">
          <h6 class="fw-bold text-navy mb-3"><i class="bi bi-list-nested me-2"></i>Daftar Isi Panduan</h6>
          <div class="list-group list-group-flush small">
            <a href="#alur" class="list-group-item list-group-item-action border-0 py-2"><i class="bi bi-diagram-3 me-2 text-warning"></i> Alur Reservasi</a>
            <a href="#syarat" class="list-group-item list-group-item-action border-0 py-2"><i class="bi bi-file-earmark-check me-2 text-warning"></i> Dokumen & Syarat</a>
            <a href="#tata-tertib" class="list-group-item list-group-item-action border-0 py-2"><i class="bi bi-shield-exclamation me-2 text-warning"></i> Tata Tertib Asrama</a>
            <a href="#keamanan" class="list-group-item list-group-item-action border-0 py-2"><i class="bi bi-lock me-2 text-warning"></i> Kebijakan Privasi & RBAC</a>
            <a href="#faq" class="list-group-item list-group-item-action border-0 py-2"><i class="bi bi-question-circle me-2 text-warning"></i> Tanya Jawab (FAQ)</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Content -->
    <div class="col-lg-9">
      <!-- Section 1: Alur Reservasi -->
      <section id="alur" class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
          <h4 class="fw-bold text-navy mb-3">
            <i class="bi bi-diagram-3-fill text-warning me-2"></i>1. Alur Lengkap Proses Reservasi
          </h4>
          <p class="text-muted small">
            Berikut adalah tahapan dari pendaftaran akun hingga check-in ke kamar asrama ULBI:
          </p>

          <div class="timeline mt-4">
            <div class="timeline-item">
              <div class="timeline-marker"></div>
              <h6 class="fw-bold mb-1">Tahap 1: Registrasi Akun Mahasiswa</h6>
              <p class="small text-muted mb-0">
                Mahasiswa membuat akun di SIRASMA dengan mengisi NPM, Program Studi, Email, dan kata sandi. Akun ini memiliki role <code>mahasiswa</code> dengan hak akses terbatas pada data miliknya sendiri.
              </p>
            </div>

            <div class="timeline-item">
              <div class="timeline-marker"></div>
              <h6 class="fw-bold mb-1">Tahap 2: Pengisian Data Profil & Kontak Darurat</h6>
              <p class="small text-muted mb-0">
                Sebelum mengajukan reservasi, mahasiswa wajib melengkapi data profil termasuk nomor kontak orang tua/wali serta alamat asal pemohon.
              </p>
            </div>

            <div class="timeline-item">
              <div class="timeline-marker"></div>
              <h6 class="fw-bold mb-1">Tahap 3: Pemilihan Kamar & Periode Tinggal</h6>
              <p class="small text-muted mb-0">
                Mahasiswa melihat ketersediaan kamar yang aktif, memilih gedung asrama sesuai gender (Tower Al-Fatih untuk Putra atau Tower An-Nisa untuk Putri), dan menentukan periode tinggal (1 Semester atau 1 Tahun).
              </p>
            </div>

            <div class="timeline-item">
              <div class="timeline-marker"></div>
              <h6 class="fw-bold mb-1">Tahap 4: Pengiriman Pengajuan & Unggah Berkas</h6>
              <p class="small text-muted mb-0">
                Mahasiswa mengirimkan formulir permohonan reservasi disertai unggahan dokumen persyaratan (KTM / Surat Keterangan Mahasiswa Aktif). Sistem secara otomatis menghasilkan Nomor Reservasi unik.
              </p>
            </div>

            <div class="timeline-item">
              <div class="timeline-marker"></div>
              <h6 class="fw-bold mb-1">Tahap 5: Verifikasi Dokumen oleh Administrator</h6>
              <p class="small text-muted mb-0">
                Pengelola asrama memeriksa keabsahan dokumen dan mencocokkan kapasitas kamar. Admin berwenang untuk Menyetujui (Approve) atau Menolak (Reject) dengan alasan penolakan yang dapat dipantau oleh pemohon.
              </p>
            </div>

            <div class="timeline-item">
              <div class="timeline-marker"></div>
              <h6 class="fw-bold mb-1">Tahap 6: Pembayaran, Cetak Bukti & Serah Terima Kunci</h6>
              <p class="small text-muted mb-0">
                Setelah status dinyatakan <strong>Disetujui</strong>, mahasiswa mengunggah bukti pembayaran sewa, mencetak Surat Bukti Reservasi Digital, dan menyerahkannya ke kantor pengelola asrama untuk serah terima kunci kamar fisik.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 2: Dokumen & Syarat -->
      <section id="syarat" class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
          <h4 class="fw-bold text-navy mb-3">
            <i class="bi bi-file-earmark-check-fill text-warning me-2"></i>2. Dokumen Persyaratan
          </h4>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold mb-2"><i class="bi bi-credit-card-2-front text-primary me-2"></i>1. Kartu Tanda Mahasiswa (KTM)</h6>
                <p class="small text-muted mb-0">
                  Salinan KTM aktif bagi mahasiswa lama, atau Bukti Tanda Kelulusan / Registrasi Mahasiswa Baru bagi calon mahasiswa baru ULBI (format PDF atau JPG/PNG maks. 5 MB).
                </p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold mb-2"><i class="bi bi-telephone-inbound text-success me-2"></i>2. Kontak Darurat Orang Tua / Wali</h6>
                <p class="small text-muted mb-0">
                  Data nomor telepon dan alamat orang tua/wali yang dapat dihubungi sewaktu-waktu oleh pihak pengelola asrama kampus dalam keadaan mendesak.
                </p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold mb-2"><i class="bi bi-file-text text-danger me-2"></i>3. Surat Pernyataan Kesediaan</h6>
                <p class="small text-muted mb-0">
                  Persetujuan secara digital maupun tertulis untuk menaati seluruh tata tertib kehidupan di lingkungan asrama ULBI.
                </p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold mb-2"><i class="bi bi-receipt-cutoff text-info me-2"></i>4. Bukti Pembayaran Sewa</h6>
                <p class="small text-muted mb-0">
                  Bukti transfer atau slip pembayaran biaya sewa asrama sesuai periode yang dipilih setelah permohonan disetujui.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 3: Tata Tertib Asrama -->
      <section id="tata-tertib" class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
          <h4 class="fw-bold text-navy mb-3">
            <i class="bi bi-shield-exclamation text-warning me-2"></i>3. Tata Tertib & Ketentuan Penghuni Asrama
          </h4>
          <div class="alert alert-warning py-2 small d-flex align-items-center mb-3">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>Setiap penghuni wajib mematuhi ketentuan tata tertib demi kenyamanan bersama di lingkungan akademik ULBI.</div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-striped small mb-0">
              <thead class="table-dark">
                <tr>
                  <th style="width: 50px;">No</th>
                  <th>Ketentuan Tata Tertib</th>
                  <th>Penjelasan / Sanksi</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-center fw-bold">1</td>
                  <td><strong>Jam Malam Asrama</strong></td>
                  <td>Pintu gerbang asrama ditutup pukul <strong>22.00 WIB</strong>. Penghuni yang memiliki keperluan akademik mendesak di luar jam tersebut wajib membawa surat izin tertulis dari pimpinan prodi / pengelola asrama.</td>
                </tr>
                <tr>
                  <td class="text-center fw-bold">2</td>
                  <td><strong>Pemisahan Zona Gender</strong></td>
                  <td>Mahasiswa dilarang keras memasuki gedung asrama lawan jenis tanpa izin resmi pengelola asrama. Tamu hanya diizinkan di ruang komunal lobi utama. Pelanggaran berat akan dikenakan sanksi skorsing asrama.</td>
                </tr>
                <tr>
                  <td class="text-center fw-bold">3</td>
                  <td><strong>Penggunaan Peralatan Listrik</strong></td>
                  <td>Dilarang menggunakan peralatan berdaya listrik tinggi yang berpotensi menyebabkan kebakaran (misal kompor induksi tanpa izin, heater pemanas rakitan).</td>
                </tr>
                <tr>
                  <td class="text-center fw-bold">4</td>
                  <td><strong>Kebersihan & Ketertiban</strong></td>
                  <td>Setiap penghuni bertanggung jawab menjaga kebersihan kamar pribadi dan fasilitas bersama (dapur koridor, musholla, toilet).</td>
                </tr>
                <tr>
                  <td class="text-center fw-bold">5</td>
                  <td><strong>Larangan Narkoba & Rokok</strong></td>
                  <td>Kawasan asrama merupakan Kawasan Bebas Asap Rokok dan Narkotika. Pelanggaran akan dilaporkan ke Komisi Disiplin ULBI dan pihak berwajib.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Section 4: Access Control & Privasi Data -->
      <section id="keamanan" class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
          <h4 class="fw-bold text-navy mb-3">
            <i class="bi bi-lock-fill text-warning me-2"></i>4. Kebijakan Keamanan Sistem & Hak Akses (RBAC)
          </h4>
          <p class="text-muted small">
            Sistem SIRASMA menerapkan prinsip <strong>Least Privilege</strong> dan perlindungan data sensitif sesuai dokumen analisis hak akses:
          </p>

          <ul class="small text-secondary">
            <li><strong>Pemisahan Peran (Role Separation):</strong> Mahasiswa hanya dapat melihat dan mengelola reservasi miliknya sendiri, sedangkan Admin mengelola data kamar dan memproses permohonan.</li>
            <li><strong>Perlindungan IDOR (Insecure Direct Object References):</strong> Setiap upaya akses langsung melalui URL atau parameter manipulasi secara ketat dicek di sisi server.</li>
            <li><strong>Enkripsi Kata Sandi:</strong> Kata sandi akun tidak pernah disimpan dalam bentuk teks biasa melainkan menggunakan algoritma <code>BCRYPT</code> satu arah.</li>
            <li><strong>Audit Logging:</strong> Seluruh aktivitas penting (login, pengajuan reservasi, perubahan status kamar, persetujuan admin) direkam dalam log audit sistem untuk pertanggungjawaban operasional.</li>
          </ul>
        </div>
      </section>

      <!-- Section 5: FAQ -->
      <section id="faq" class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
          <h4 class="fw-bold text-navy mb-3">
            <i class="bi bi-question-circle-fill text-warning me-2"></i>5. Tanya Jawab (FAQ)
          </h4>

          <div class="accordion" id="accordionFaq">
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq1">
                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                  Berapa lama proses verifikasi pengajuan reservasi?
                </button>
              </h2>
              <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body small text-muted">
                  Proses verifikasi dokumen oleh administrator asrama biasanya memakan waktu 1 hingga 2 hari kerja setelah formulir dan berkas diunggah secara lengkap oleh mahasiswa.
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header" id="faq2">
                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                  Apakah saya dapat membatalkan pengajuan yang sudah saya kirim?
                </button>
              </h2>
              <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body small text-muted">
                  Ya, mahasiswa berhak membatalkan pengajuan reservasi mandiri selama status permohonan masih dalam tahap <strong>Menunggu Verifikasi</strong>. Jika sudah disetujui, pembatalan harus dikoordinasikan langsung ke petugas pengelola.
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header" id="faq3">
                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                  Apakah biaya sewa asrama mencakup fasilitas listrik dan air?
                </button>
              </h2>
              <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                <div class="accordion-body small text-muted">
                  Biaya sewa standar sudah mencakup pemakaian air bersih, fasilitas WiFi kampus, serta listrik reguler (penerangan dan pengisian daya laptop/ponsel). Pemakaian pendingin ruangan (AC) mengikuti ketentuan paket kamar masing-masing.
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
