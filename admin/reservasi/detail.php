<?php
/**
 * SIRASMA ULBI - Detail & Verifikasi Pengajuan Reservasi (Admin)
 */
$page_title = 'Verifikasi Pengajuan Reservasi';
$is_dashboard = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
$db = get_db();
$admin = current_user();

$stmt = $db->prepare("SELECT r.*, k.nomor_kamar, k.tipe_kamar, k.lantai, k.kapasitas, k.terisi, k.fasilitas_kamar,
                             a.nama_asrama, a.kategori_gender, a.lokasi, p.nama_periode,
                             u.username, u.full_name, u.email, u.phone,
                             mp.npm, mp.prodi, mp.gender, mp.tempat_lahir, mp.tanggal_lahir,
                             mp.alamat_asal, mp.kota_asal, mp.provinsi_asal,
                             mp.nama_ortu_wali, mp.hp_ortu_wali, mp.kontak_darurat
                      FROM reservasi r
                      JOIN kamar k ON r.kamar_id = k.id
                      JOIN asrama a ON k.asrama_id = a.id
                      JOIN periode p ON r.periode_id = p.id
                      JOIN users u ON r.user_id = u.id
                      LEFT JOIN mahasiswa_profiles mp ON u.id = mp.user_id
                      WHERE r.id = ?");
$stmt->execute([$id]);
$rsv = $stmt->fetch();

if (!$rsv) {
    set_flash('danger', 'Data reservasi tidak ditemukan.');
    header("Location: " . BASE_URL . "/admin/reservasi/index.php");
    exit;
}

$error = '';

// Proses Verifikasi (Persetujuan / Penolakan / Status Bayar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Validasi keamanan CSRF Token tidak valid.';
    } else {
        $action_type = $_POST['action_type'] ?? '';
        $catatan_admin = trim($_POST['catatan_admin'] ?? '');

        try {
            if ($action_type === 'approve') {
                // Pastikan kamar masih ada slot jika belum pernah disetujui sebelumnya
                if ($rsv['status'] !== 'disetujui') {
                    // Update jumlah terisi pada kamar
                    $new_terisi = $rsv['terisi'] + 1;
                    $room_status = ($new_terisi >= $rsv['kapasitas']) ? 'penuh' : 'tersedia';
                    
                    $db->prepare("UPDATE kamar SET terisi = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                       ->execute([$new_terisi, $room_status, $rsv['kamar_id']]);
                }

                $stmt_app = $db->prepare("UPDATE reservasi SET 
                    status = 'disetujui', 
                    catatan_admin = ?, 
                    diverifikasi_oleh = ?, 
                    tanggal_verifikasi = CURRENT_TIMESTAMP, 
                    updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?");
                $stmt_app->execute([$catatan_admin, $admin['id'], $id]);

                log_activity('APPROVE_RESERVATION', "Admin {$admin['username']} menyetujui reservasi {$rsv['kode_reservasi']} untuk kamar {$rsv['nomor_kamar']}.");
                set_flash('success', "Permohonan reservasi nomor {$rsv['kode_reservasi']} berhasil DISETUJUI.");
                header("Location: " . BASE_URL . "/admin/reservasi/detail.php?id=" . $id);
                exit;

            } elseif ($action_type === 'reject') {
                if (empty($catatan_admin)) {
                    $error = 'Harap sertakan alasan penolakan pada kolom Catatan Petugas.';
                } else {
                    // Jika sebelumnya pernah disetujui, kurangi kembali kuota terisi
                    if ($rsv['status'] === 'disetujui' && $rsv['terisi'] > 0) {
                        $new_terisi = max(0, $rsv['terisi'] - 1);
                        $db->prepare("UPDATE kamar SET terisi = ?, status = 'tersedia', updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                           ->execute([$new_terisi, $rsv['kamar_id']]);
                    }

                    $stmt_rej = $db->prepare("UPDATE reservasi SET 
                        status = 'ditolak', 
                        catatan_admin = ?, 
                        diverifikasi_oleh = ?, 
                        tanggal_verifikasi = CURRENT_TIMESTAMP, 
                        updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?");
                    $stmt_rej->execute([$catatan_admin, $admin['id'], $id]);

                    log_activity('REJECT_RESERVATION', "Admin {$admin['username']} menolak reservasi {$rsv['kode_reservasi']}. Alasan: {$catatan_admin}");
                    set_flash('warning', "Permohonan reservasi nomor {$rsv['kode_reservasi']} telah DITOLAK.");
                    header("Location: " . BASE_URL . "/admin/reservasi/detail.php?id=" . $id);
                    exit;
                }

            } elseif ($action_type === 'confirm_payment') {
                $stmt_pay = $db->prepare("UPDATE reservasi SET status_pembayaran = 'lunas', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt_pay->execute([$id]);

                log_activity('CONFIRM_PAYMENT', "Admin {$admin['username']} mengonfirmasi pembayaran LUNAS untuk reservasi {$rsv['kode_reservasi']}.");
                set_flash('success', "Pembayaran untuk reservasi {$rsv['kode_reservasi']} telah dikonfirmasi LUNAS.");
                header("Location: " . BASE_URL . "/admin/reservasi/detail.php?id=" . $id);
                exit;
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

  <main class="dashboard-main">
    <header class="dashboard-topbar">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light border d-lg-none" type="button" onclick="document.getElementById('dashboardSidebar').classList.toggle('show')">
          <i class="bi bi-list fs-5"></i>
        </button>
        <h5 class="fw-bold mb-0 text-navy" style="color:var(--ulbi-navy);">Detail & Verifikasi: <?= htmlspecialchars($rsv['kode_reservasi']) ?></h5>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/admin/reservasi/index.php" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>
    </header>

    <div class="dashboard-content">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4 py-2" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
          <div class="small"><?= htmlspecialchars($error) ?></div>
        </div>
      <?php endif; ?>

      <!-- Header Status Banner -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <span class="small text-muted">Nomor Registrasi:</span>
            <h4 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($rsv['kode_reservasi']) ?></h4>
            <small class="text-muted">Diajukan pada: <?= format_tanggal($rsv['tanggal_pengajuan'], true) ?></small>
          </div>
          <div class="text-md-end">
            <div class="mb-1">
              <span class="small text-muted me-1">Status Permohonan:</span>
              <?= get_status_badge($rsv['status']) ?>
            </div>
            <div>
              <span class="small text-muted me-1">Status Pembayaran:</span>
              <?= get_payment_badge($rsv['status_pembayaran']) ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Biodata Mahasiswa -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-person-badge-fill text-warning me-2"></i>Identitas Mahasiswa Pemohon
              </h6>
            </div>
            <div class="card-body p-4">
              <table class="table table-sm table-borderless small mb-0">
                <tr>
                  <td class="text-muted" style="width: 140px;">Nama Lengkap:</td>
                  <td><strong><?= htmlspecialchars($rsv['full_name']) ?></strong></td>
                </tr>
                <tr>
                  <td class="text-muted">NPM:</td>
                  <td><strong><?= htmlspecialchars($rsv['npm'] ?? '-') ?></strong></td>
                </tr>
                <tr>
                  <td class="text-muted">Program Studi:</td>
                  <td><?= htmlspecialchars($rsv['prodi'] ?? '-') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Jenis Kelamin:</td>
                  <td><?= ($rsv['gender'] === 'L') ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' ?></td>
                </tr>
                <tr>
                  <td class="text-muted">WhatsApp / HP:</td>
                  <td><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $rsv['phone']) ?>" target="_blank" class="text-decoration-none"><i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($rsv['phone']) ?></a></td>
                </tr>
                <tr>
                  <td class="text-muted">Email:</td>
                  <td><?= htmlspecialchars($rsv['email']) ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Alamat Asal:</td>
                  <td><?= nl2br(htmlspecialchars($rsv['alamat_asal'] ?? '-')) ?> (<?= htmlspecialchars($rsv['kota_asal'] ?? '') ?>)</td>
                </tr>
                <tr>
                  <td class="text-muted">Orang Tua / Wali:</td>
                  <td><?= htmlspecialchars($rsv['nama_ortu_wali'] ?? '-') ?> (<?= htmlspecialchars($rsv['hp_ortu_wali'] ?? '-') ?>)</td>
                </tr>
                <tr>
                  <td class="text-muted">Catatan Pemohon:</td>
                  <td><em><?= nl2br(htmlspecialchars($rsv['catatan_mahasiswa'] ?: 'Tidak ada catatan tambahan.')) ?></em></td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Detail Alokasi Kamar & Biaya -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-door-open-fill text-warning me-2"></i>Rincian Kamar & Biaya
              </h6>
            </div>
            <div class="card-body p-4">
              <table class="table table-sm table-borderless small mb-0">
                <tr>
                  <td class="text-muted" style="width: 140px;">Gedung Asrama:</td>
                  <td><strong><?= htmlspecialchars($rsv['nama_asrama']) ?></strong></td>
                </tr>
                <tr>
                  <td class="text-muted">Nomor Kamar:</td>
                  <td><span class="fs-6 fw-bold text-navy">Kamar <?= htmlspecialchars($rsv['nomor_kamar']) ?></span> (Lantai <?= $rsv['lantai'] ?>)</td>
                </tr>
                <tr>
                  <td class="text-muted">Tipe Kamar:</td>
                  <td><?= htmlspecialchars($rsv['tipe_kamar']) ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Kapasitas Kamar:</td>
                  <td><?= $rsv['terisi'] ?> / <?= $rsv['kapasitas'] ?> Orang</td>
                </tr>
                <tr>
                  <td class="text-muted">Periode Sewa:</td>
                  <td><?= htmlspecialchars($rsv['nama_periode']) ?> (<?= htmlspecialchars($rsv['durasi_sewa']) ?>)</td>
                </tr>
                <tr>
                  <td class="text-muted">Rencana Masuk:</td>
                  <td><strong><?= format_tanggal($rsv['rencana_checkin']) ?></strong></td>
                </tr>
                <tr>
                  <td class="text-muted">Total Tagihan:</td>
                  <td><h5 class="fw-bold text-success mb-0"><?= format_rupiah($rsv['total_biaya']) ?></h5></td>
                </tr>
              </table>

              <!-- Berkas Lampiran -->
              <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold text-navy small mb-2">Berkas yang Diunggah Mahasiswa:</h6>
                <div class="d-flex flex-wrap gap-2">
                  <?php if (!empty($rsv['dokumen_ktm'])): ?>
                    <a href="<?= BASE_URL ?>/uploads/ktm/<?= htmlspecialchars($rsv['dokumen_ktm']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-file-earmark-person me-1"></i> Buka Salinan KTM
                    </a>
                  <?php else: ?>
                    <span class="badge bg-light text-muted border p-2">KTM belum diunggah</span>
                  <?php endif; ?>

                  <?php if (!empty($rsv['bukti_pembayaran'])): ?>
                    <a href="<?= BASE_URL ?>/uploads/pembayaran/<?= htmlspecialchars($rsv['bukti_pembayaran']) ?>" target="_blank" class="btn btn-outline-success btn-sm">
                      <i class="bi bi-receipt me-1"></i> Buka Bukti Transfer Bayar
                    </a>
                  <?php else: ?>
                    <span class="badge bg-light text-muted border p-2">Bukti bayar belum diunggah</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Aksi Verifikasi Administrator -->
        <div class="col-lg-12">
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-navy">
                <i class="bi bi-check2-square text-warning me-2"></i>Aksi Keputusan Verifikasi Administrator
              </h6>
            </div>
            <div class="card-body p-4">
              <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="mb-3">
                  <label class="form-label small fw-semibold">Catatan Petugas Verifikator (Terlihat oleh Mahasiswa):</label>
                  <textarea class="form-control" name="catatan_admin" rows="3" placeholder="Tuliskan catatan verifikasi, nomor rekening jika belum bayar, atau alasan penolakan jika permohonan ditolak..."><?= htmlspecialchars($rsv['catatan_admin'] ?? '') ?></textarea>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-2">
                  <!-- Tombol Setujui -->
                  <button type="submit" name="action_type" value="approve" class="btn btn-success" 
                          onclick="return confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan reservasi ini?')">
                    <i class="bi bi-check-circle-fill me-1"></i> Setujui Permohonan (Approve)
                  </button>

                  <!-- Tombol Tolak -->
                  <button type="submit" name="action_type" value="reject" class="btn btn-danger" 
                          onclick="return confirm('Apakah Anda yakin ingin MENOLAK pengajuan reservasi ini?')">
                    <i class="bi bi-x-circle-fill me-1"></i> Tolak Permohonan (Reject)
                  </button>

                  <!-- Tombol Konfirmasi Bayar Lunas -->
                  <?php if ($rsv['status_pembayaran'] !== 'lunas'): ?>
                    <button type="submit" name="action_type" value="confirm_payment" class="btn btn-primary"
                            onclick="return confirm('Konfirmasi bahwa pembayaran sewa telah lunas dan diverifikasi bank?')">
                      <i class="bi bi-shield-check me-1"></i> Konfirmasi Pembayaran LUNAS
                    </button>
                  <?php endif; ?>

                  <!-- Cetak Bukti -->
                  <a href="<?= BASE_URL ?>/mahasiswa/cetak_bukti.php?id=<?= $rsv['id'] ?>" target="_blank" class="btn btn-outline-dark ms-auto">
                    <i class="bi bi-printer me-1"></i> Cetak Lembar Reservasi
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
