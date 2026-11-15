<?php
/**
 * SIRASMA ULBI - Formulir Pengajuan Reservasi Kamar
 */
$page_title = 'Ajukan Reservasi Kamar';
$is_dashboard = true;
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['mahasiswa']);

$user = current_user();
$db = get_db();

// Ambil profil mahasiswa
$stmt_prof = $db->prepare("SELECT * FROM mahasiswa_profiles WHERE user_id = ?");
$stmt_prof->execute([$user['id']]);
$profile = $stmt_prof->fetch();

$mhs_gender = $profile['gender'] ?? 'L';
$preselect_kamar_id = (int)($_GET['kamar_id'] ?? 0);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Validasi keamanan CSRF Token tidak valid.';
    } else {
        $kamar_id = (int)($_POST['kamar_id'] ?? 0);
        $periode_id = (int)($_POST['periode_id'] ?? 0);
        $durasi_sewa = trim($_POST['durasi_sewa'] ?? '1 Semester');
        $rencana_checkin = trim($_POST['rencana_checkin'] ?? '');
        $catatan_mahasiswa = trim($_POST['catatan_mahasiswa'] ?? '');

        // Validasi masukan dasar
        if ($kamar_id <= 0 || $periode_id <= 0 || empty($rencana_checkin)) {
            $error = 'Silakan pilih kamar, periode akademik, dan tanggal rencana check-in.';
        } else {
            // Cek apakah mahasiswa sudah memiliki permohonan yang aktif (menunggu verifikasi / disetujui) pada periode ini
            $stmt_chk_rsv = $db->prepare("SELECT COUNT(*) FROM reservasi WHERE user_id = ? AND periode_id = ? AND status IN ('menunggu_verifikasi', 'disetujui')");
            $stmt_chk_rsv->execute([$user['id'], $periode_id]);
            if ($stmt_chk_rsv->fetchColumn() > 0) {
                $error = 'Anda sudah memiliki permohonan reservasi yang sedang aktif atau telah disetujui pada periode ini.';
            } else {
                // Ambil data kamar & validasi ketersediaan
                $stmt_kamar = $db->prepare("SELECT k.*, a.nama_asrama, a.kategori_gender FROM kamar k JOIN asrama a ON k.asrama_id = a.id WHERE k.id = ?");
                $stmt_kamar->execute([$kamar_id]);
                $kamar = $stmt_kamar->fetch();

                if (!$kamar) {
                    $error = 'Kamar yang dipilih tidak valid.';
                } elseif ($kamar['status'] !== 'tersedia' || $kamar['terisi'] >= $kamar['kapasitas']) {
                    $error = 'Maaf, kuota kamar ini sudah penuh atau sedang dalam perawatan.';
                } elseif ($kamar['kategori_gender'] !== $mhs_gender) {
                    $error = 'Kamar tidak sesuai dengan kategori gender akun Anda.';
                } else {
                    // Hitung total biaya
                    $total_biaya = ($durasi_sewa === '1 Tahun') ? $kamar['tarif_per_tahun'] : $kamar['tarif_per_semester'];

                    // Proses unggah dokumen KTM jika ada
                    $ktm_filename = null;
                    if (isset($_FILES['dokumen_ktm']) && $_FILES['dokumen_ktm']['error'] === UPLOAD_ERR_OK) {
                        $up = upload_file($_FILES['dokumen_ktm'], 'ktm', ['jpg', 'jpeg', 'png', 'pdf']);
                        if ($up['success']) {
                            $ktm_filename = $up['filename'];
                        } else {
                            $error = 'Gagal mengunggah berkas KTM: ' . $up['message'];
                        }
                    }

                    if (empty($error)) {
                        try {
                            // Generate kode reservasi unik: RSV-2026-XXXX
                            $kode_reservasi = 'RSV-' . date('Y') . '-' . str_pad((string)mt_rand(100, 9999), 4, '0', STR_PAD_LEFT);
                            
                            $stmt_ins = $db->prepare("INSERT INTO reservasi (kode_reservasi, user_id, kamar_id, periode_id, tanggal_pengajuan, rencana_checkin, durasi_sewa, total_biaya, dokumen_ktm, status_pembayaran, status, catatan_mahasiswa) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?, ?, 'belum_bayar', 'menunggu_verifikasi', ?)");
                            $stmt_ins->execute([
                                $kode_reservasi,
                                $user['id'],
                                $kamar_id,
                                $periode_id,
                                $rencana_checkin,
                                $durasi_sewa,
                                $total_biaya,
                                $ktm_filename,
                                $catatan_mahasiswa
                            ]);

                            $rsv_id = $db->lastInsertId();

                            log_activity(
                                'SUBMIT_RESERVATION',
                                "Mahasiswa {$user['username']} ({$user['full_name']}) mengajukan reservasi kamar {$kamar['nomor_kamar']} dengan kode: {$kode_reservasi}"
                            );

                            set_flash('success', "Permohonan reservasi kamar {$kamar['nomor_kamar']} berhasil diajukan dengan Kode Registrasi: {$kode_reservasi}. Mohon tunggu verifikasi oleh administrator.");
                            header("Location: " . BASE_URL . "/mahasiswa/riwayat.php");
                            exit;
                        } catch (Exception $e) {
                            $error = 'Gagal menyimpan pengajuan reservasi: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}

// Ambil daftar kamar yang tersedia untuk gender mahasiswa
$stmt_kmr = $db->prepare("SELECT k.*, a.nama_asrama FROM kamar k 
                          JOIN asrama a ON k.asrama_id = a.id 
                          WHERE a.kategori_gender = ? AND k.status = 'tersedia' AND k.terisi < k.kapasitas 
                          ORDER BY k.nomor_kamar ASC");
$stmt_kmr->execute([$mhs_gender]);
$available_rooms = $stmt_kmr->fetchAll();

// Ambil daftar periode akademik yang aktif
$periods = $db->query("SELECT * FROM periode WHERE status = 'aktif' ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <header class="dashboard-topbar">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light border d-lg-none" type="button" onclick="document.getElementById('dashboardSidebar').classList.toggle('show')">
          <i class="bi bi-list fs-5"></i>
        </button>
        <div>
          <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Pengajuan Reservasi Kamar</h5>
          <span class="text-muted small">Pilih kamar dan lengkapi jadwal rencana hunian asrama Anda</span>
        </div>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/mahasiswa/index.php" class="btn btn-light border btn-sm px-3">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
      </div>
    </header>

    <div class="dashboard-content">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4 py-3 rounded-4 shadow-sm border-0" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-3 fs-4 text-danger"></i>
          <div>
            <strong class="d-block">Gagal Mengajukan Reservasi</strong>
            <span class="small"><?= htmlspecialchars($error) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-pencil-square text-orange me-2"></i>Formulir Permohonan Reservasi
              </h6>
            </div>

            <div class="card-body p-4">
              <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <!-- Info Pemohon Ringkas -->
                <div class="p-3 bg-light rounded-4 border mb-4">
                  <div class="row g-2 small">
                    <div class="col-sm-6">
                      <span class="text-muted d-block">Nama Lengkap Pemohon:</span>
                      <strong class="text-navy"><?= htmlspecialchars($user['full_name']) ?></strong>
                    </div>
                    <div class="col-sm-6">
                      <span class="text-muted d-block">NPM / Identitas:</span>
                      <strong class="text-navy"><?= htmlspecialchars($profile['npm'] ?? '-') ?></strong>
                    </div>
                    <div class="col-sm-6">
                      <span class="text-muted d-block">Program Studi:</span>
                      <strong class="text-navy"><?= htmlspecialchars($profile['prodi'] ?? '-') ?></strong>
                    </div>
                    <div class="col-sm-6">
                      <span class="text-muted d-block">Gedung Dialokasikan:</span> 
                      <span class="badge <?= ($mhs_gender === 'L') ? 'badge-soft-primary' : 'badge-soft-danger' ?>">
                        <i class="bi bi-building me-1"></i><?= ($mhs_gender === 'L') ? 'Asrama Putra (Tower Al-Fatih)' : 'Asrama Putri (Tower An-Nisa)' ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="row g-3">
                  <!-- Section 1: Pemilihan Kamar -->
                  <div class="col-12">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <span class="d-inline-flex align-items-center justify-content-center bg-orange text-white rounded-circle fw-bold" style="width:24px;height:24px;font-size:0.75rem;">1</span>
                      <h6 class="fw-bold text-navy mb-0">Pilih Kamar & Periode Akademik</h6>
                    </div>
                  </div>

                  <!-- Pilih Kamar -->
                  <div class="col-md-12">
                    <label class="form-label small fw-semibold">Pilih Kamar Asrama yang Tersedia <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg" id="kamar_id" name="kamar_id" required onchange="updateRoomDetails()" style="font-size:0.95rem;">
                      <option value="">-- Pilih Nomor Kamar Asrama --</option>
                      <?php foreach ($available_rooms as $kr): 
                        $selected = ($preselect_kamar_id === (int)$kr['id']) ? 'selected' : '';
                        $sisa = $kr['kapasitas'] - $kr['terisi'];
                      ?>
                        <option value="<?= $kr['id'] ?>" 
                                data-sem="<?= $kr['tarif_per_semester'] ?>" 
                                data-thn="<?= $kr['tarif_per_tahun'] ?>"
                                data-tipe="<?= htmlspecialchars($kr['tipe_kamar']) ?>"
                                data-fas="<?= htmlspecialchars($kr['fasilitas_kamar']) ?>"
                                <?= $selected ?>>
                          Kamar <?= htmlspecialchars($kr['nomor_kamar']) ?> (Lt. <?= $kr['lantai'] ?>) - <?= htmlspecialchars($kr['tipe_kamar']) ?> &bull; Sisa <?= $sisa ?> Slot &bull; <?= format_rupiah($kr['tarif_per_semester']) ?>/sem
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Detail Kamar Terpilih Box -->
                  <div class="col-md-12" id="roomDetailBox" style="display: none;">
                    <div class="p-3 rounded-4 shadow-sm" style="background:#F0FDF4; border: 1px solid #BBF7D0;">
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-door-open-fill text-success"></i>
                        <h6 class="fw-bold text-success mb-0" id="detailTipe">-</h6>
                      </div>
                      <p class="small text-muted mb-0" id="detailFasilitas">-</p>
                    </div>
                  </div>

                  <!-- Pilih Periode -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Periode Akademik <span class="text-danger">*</span></label>
                    <select class="form-select" name="periode_id" required>
                      <?php foreach ($periods as $p): ?>
                        <option value="<?= $p['id'] ?>">
                          <?= htmlspecialchars($p['nama_periode']) ?> (Mulai <?= date('M Y', strtotime($p['tanggal_mulai'])) ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Durasi Sewa -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Durasi Sewa <span class="text-danger">*</span></label>
                    <select class="form-select" id="durasi_sewa" name="durasi_sewa" required onchange="calculatePrice()">
                      <option value="1 Semester">1 Semester (6 Bulan)</option>
                      <option value="1 Tahun">1 Tahun Penuh (12 Bulan)</option>
                    </select>
                  </div>

                  <!-- Section 2: Jadwal & Berkas -->
                  <div class="col-12 mt-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <span class="d-inline-flex align-items-center justify-content-center bg-orange text-white rounded-circle fw-bold" style="width:24px;height:24px;font-size:0.75rem;">2</span>
                      <h6 class="fw-bold text-navy mb-0">Jadwal Masuk & Berkas Identitas</h6>
                    </div>
                  </div>

                  <!-- Rencana Checkin -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Rencana Tanggal Masuk (Check-in) <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="rencana_checkin" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Estimasi tanggal tiba pertama kali di asrama.</small>
                  </div>

                  <!-- Dokumen KTM / Bukti -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Unggah Salinan KTM / Tanda Registrasi</label>
                    <input type="file" class="form-control" name="dokumen_ktm" accept=".jpg,.jpeg,.png,.pdf">
                    <small class="text-muted" style="font-size: 0.75rem;">Format: JPG, PNG, atau PDF (Maksimal 5MB).</small>
                  </div>

                  <!-- Catatan Tambahan -->
                  <div class="col-md-12">
                    <label class="form-label small fw-semibold">Catatan Khusus / Kebutuhan Tambahan (Opsional)</label>
                    <textarea class="form-control" name="catatan_mahasiswa" rows="3" placeholder="Contoh: Mengajukan ranjang bagian bawah karena pertimbangan kesehatan fisik, atau jadwal perkiraan tiba larut malam..."></textarea>
                  </div>
                </div>

                <!-- Estimasi Biaya Box -->
                <div class="p-4 bg-light rounded-4 border mt-4 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                  <div>
                    <span class="small text-muted d-block">Estimasi Total Biaya Sewa:</span>
                    <strong class="fs-3 text-success fw-bold" id="displayTotal">Rp 0</strong>
                  </div>
                  <div class="text-md-end text-muted small" style="max-width:320px;">
                    <i class="bi bi-info-circle text-primary me-1"></i> Pembayaran dapat ditransfer setelah permohonan disetujui oleh pengelola asrama.
                  </div>
                </div>

                <div class="form-check mb-4">
                  <input class="form-check-input" type="checkbox" id="agree" required>
                  <label class="form-check-label small text-muted" for="agree">
                    Saya menyatakan data permohonan ini diisi dengan benar serta bersedia mematuhi semua tata tertib hunian asrama ULBI.
                  </label>
                </div>

                <button type="submit" class="btn btn-orange btn-lg w-100 shadow-sm py-3 fw-bold">
                  <i class="bi bi-send-check-fill me-2"></i> Kirim Pengajuan Reservasi Sekarang
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy"><i class="bi bi-shield-check text-orange me-2"></i>Proses & Ketentuan</h6>
            </div>
            <div class="card-body p-4">
              <ul class="list-unstyled small text-secondary mb-0">
                <li class="mb-3 d-flex gap-3">
                  <div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-success flex-shrink-0" style="width:32px;height:32px;">
                    <i class="bi bi-check2"></i>
                  </div>
                  <div>
                    <strong class="text-navy d-block">Verifikasi Cepat:</strong>
                    <span>Permohonan diverifikasi staf asrama dalam 1-2 hari kerja sesuai ketersediaan kuota.</span>
                  </div>
                </li>
                <li class="mb-3 d-flex gap-3">
                  <div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-success flex-shrink-0" style="width:32px;height:32px;">
                    <i class="bi bi-bell"></i>
                  </div>
                  <div>
                    <strong class="text-navy d-block">Pelacakan Real-time:</strong>
                    <span>Status reservasi dapat dipantau langsung dari menu Riwayat & Status Pengajuan.</span>
                  </div>
                </li>
                <li class="mb-3 d-flex gap-3">
                  <div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-success flex-shrink-0" style="width:32px;height:32px;">
                    <i class="bi bi-x-circle"></i>
                  </div>
                  <div>
                    <strong class="text-navy d-block">Fleksibilitas Batal:</strong>
                    <span>Dapat dibatalkan secara mandiri sebelum status disetujui oleh admin.</span>
                  </div>
                </li>
                <li class="d-flex gap-3">
                  <div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-success flex-shrink-0" style="width:32px;height:32px;">
                    <i class="bi bi-printer"></i>
                  </div>
                  <div>
                    <strong class="text-navy d-block">Cetak Bukti Digital:</strong>
                    <span>Setelah disetujui dan lunas, cetak bukti reservasi resmi sebagai pas check-in asrama.</span>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
    </div>
  </main>
</div>

<script>
function updateRoomDetails() {
  const select = document.getElementById('kamar_id');
  const opt = select.options[select.selectedIndex];
  const box = document.getElementById('roomDetailBox');
  
  if (opt && opt.value) {
    document.getElementById('detailTipe').innerText = opt.getAttribute('data-tipe');
    document.getElementById('detailFasilitas').innerText = 'Fasilitas: ' + opt.getAttribute('data-fas');
    box.style.display = 'block';
  } else {
    box.style.display = 'none';
  }
  calculatePrice();
}

function calculatePrice() {
  const select = document.getElementById('kamar_id');
  const opt = select.options[select.selectedIndex];
  const durasi = document.getElementById('durasi_sewa').value;
  let price = 0;

  if (opt && opt.value) {
    if (durasi === '1 Tahun') {
      price = parseFloat(opt.getAttribute('data-thn')) || 0;
    } else {
      price = parseFloat(opt.getAttribute('data-sem')) || 0;
    }
  }

  document.getElementById('displayTotal').innerText = 'Rp ' + price.toLocaleString('id-ID');
}

document.addEventListener('DOMContentLoaded', function() {
  updateRoomDetails();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
