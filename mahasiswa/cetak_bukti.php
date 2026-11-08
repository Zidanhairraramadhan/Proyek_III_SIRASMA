<?php
/**
 * SIRASMA ULBI - Cetak Bukti Pengajuan / Reservasi Resmi
 * Dokumen siap cetak (Print-Ready) dengan Kop Surat ULBI dan Anti-IDOR Security
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();

$id = (int)($_GET['id'] ?? 0);

// Validasi Kepemilikan (Anti-IDOR)
if (!check_reservation_ownership($id)) {
    http_response_code(403);
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h2>403 - Akses Ditolak</h2><p>Anda tidak memiliki hak untuk mencetak bukti reservasi milik pengguna lain.</p><a href='" . BASE_URL . "'>Kembali</a></div>");
}

$db = get_db();
$stmt = $db->prepare("SELECT r.*, k.nomor_kamar, k.tipe_kamar, k.lantai, k.fasilitas_kamar,
                             a.nama_asrama, a.lokasi, p.nama_periode,
                             u.full_name, u.email, u.phone,
                             mp.npm, mp.prodi, mp.gender, mp.nama_ortu_wali, mp.hp_ortu_wali
                      FROM reservasi r
                      JOIN kamar k ON r.kamar_id = k.id
                      JOIN asrama a ON k.asrama_id = a.id
                      JOIN periode p ON r.periode_id = p.id
                      JOIN users u ON r.user_id = u.id
                      LEFT JOIN mahasiswa_profiles mp ON u.id = mp.user_id
                      WHERE r.id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    die("Data reservasi tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Bukti_Reservasi_<?= htmlspecialchars($data['kode_reservasi']) ?>_ULBI</title>
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo-ulbi.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body {
      background: #f1f5f9;
      font-family: 'Times New Roman', Times, serif;
      color: #000;
    }
    .paper-sheet {
      background: #fff;
      max-width: 800px;
      margin: 30px auto;
      padding: 40px 50px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border-radius: 4px;
    }
    .kop-surat {
      display: flex;
      align-items: center;
      gap: 20px;
      border-bottom: 3px double #000;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }
    .kop-logo {
      height: 75px;
    }
    .kop-text {
      text-align: center;
      flex-grow: 1;
    }
    .kop-inst {
      font-size: 14pt;
      font-weight: bold;
      text-transform: uppercase;
      margin: 0;
      line-height: 1.2;
    }
    .kop-sub {
      font-size: 11pt;
      font-weight: bold;
      margin: 2px 0;
    }
    .kop-addr {
      font-size: 9pt;
      margin: 0;
      font-family: Arial, sans-serif;
    }
    .doc-title {
      text-align: center;
      font-size: 13pt;
      font-weight: bold;
      text-decoration: underline;
      margin-bottom: 5px;
      text-transform: uppercase;
    }
    .doc-num {
      text-align: center;
      font-size: 10pt;
      margin-bottom: 25px;
      font-family: Arial, sans-serif;
    }
    .table-data {
      width: 100%;
      margin-bottom: 20px;
      font-size: 10.5pt;
    }
    .table-data td {
      padding: 4px 6px;
      vertical-align: top;
    }
    .field-name {
      width: 200px;
      font-weight: bold;
    }
    .qr-badge {
      display: inline-block;
      border: 2px solid #000;
      padding: 10px 14px;
      font-family: monospace;
      font-size: 11pt;
      text-align: center;
      background: #fafafa;
    }
    @media print {
      body {
        background: #fff !important;
        margin: 0;
      }
      .paper-sheet {
        margin: 0 !important;
        padding: 20px !important;
        box-shadow: none !important;
        max-width: 100% !important;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

<div class="container text-center my-3 no-print">
  <button class="btn btn-primary btn-sm px-3 shadow" onclick="window.print()">
    <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
  </button>
  <button class="btn btn-outline-secondary btn-sm px-3 ms-2" onclick="window.close()">
    Tutup Halaman
  </button>
</div>

<div class="paper-sheet">
  <!-- Kop Surat Resmi ULBI -->
  <div class="kop-surat">
    <img src="<?= BASE_URL ?>/assets/img/logo-ulbi.png" alt="Logo ULBI" class="kop-logo">
    <div class="kop-text">
      <div class="kop-inst">UNIVERSITAS LOGISTIK DAN BISNIS INTERNASIONAL</div>
      <div class="kop-sub">DIREKTORAT PENGELOLAAN SARANA & ASRAMA KAMPUS</div>
      <div class="kop-addr">
        Jl. Sariasih No. 54, Sarijadi, Sukasari, Kota Bandung, Jawa Barat 40151<br>
        Telepon: (022) 2014032 | Email: asrama@ulbi.ac.id | Website: https://ulbi.ac.id
      </div>
    </div>
  </div>

  <div class="doc-title">SURAT BUKTI RESERVASI ASRAMA MAHASISWA</div>
  <div class="doc-num">Nomor Registrasi: <strong><?= htmlspecialchars($data['kode_reservasi']) ?></strong></div>

  <p style="font-size: 10.5pt; text-align: justify; margin-bottom: 15px;">
    Pengelola Asrama Universitas Logistik dan Bisnis Internasional menerangkan bahwa mahasiswa di bawah ini telah tercatat dalam Sistem Reservasi Asrama Mahasiswa (SIRASMA) dengan rincian sebagai berikut:
  </p>

  <!-- Data Mahasiswa -->
  <table class="table-data">
    <tr>
      <td class="field-name">Nama Mahasiswa</td>
      <td style="width: 10px;">:</td>
      <td><strong><?= strtoupper(htmlspecialchars($data['full_name'])) ?></strong></td>
    </tr>
    <tr>
      <td class="field-name">NPM</td>
      <td>:</td>
      <td><?= htmlspecialchars($data['npm'] ?? '-') ?></td>
    </tr>
    <tr>
      <td class="field-name">Program Studi</td>
      <td>:</td>
      <td><?= htmlspecialchars($data['prodi'] ?? '-') ?></td>
    </tr>
    <tr>
      <td class="field-name">Jenis Kelamin</td>
      <td>:</td>
      <td><?= ($data['gender'] === 'L') ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' ?></td>
    </tr>
    <tr>
      <td class="field-name">Nomor Kontak / WhatsApp</td>
      <td>:</td>
      <td><?= htmlspecialchars($data['phone'] ?? '-') ?></td>
    </tr>
    <tr>
      <td class="field-name">Kontak Darurat (Ortu/Wali)</td>
      <td>:</td>
      <td><?= htmlspecialchars($data['nama_ortu_wali'] ?? '-') ?> (<?= htmlspecialchars($data['hp_ortu_wali'] ?? '-') ?>)</td>
    </tr>
  </table>

  <!-- Data Kamar & Reservasi -->
  <h6 style="font-family:Arial, sans-serif; font-weight:bold; font-size:10pt; border-bottom:1px solid #000; padding-bottom:3px; margin-top:20px;">
    RINCIAN ALOKASI KAMAR & BIAYA SEWA
  </h6>
  <table class="table-data">
    <tr>
      <td class="field-name">Gedung Asrama</td>
      <td style="width: 10px;">:</td>
      <td><strong><?= htmlspecialchars($data['nama_asrama']) ?></strong></td>
    </tr>
    <tr>
      <td class="field-name">Nomor & Tipe Kamar</td>
      <td>:</td>
      <td>Kamar <strong><?= htmlspecialchars($data['nomor_kamar']) ?></strong> (Lantai <?= $data['lantai'] ?>) &bull; <?= htmlspecialchars($data['tipe_kamar']) ?></td>
    </tr>
    <tr>
      <td class="field-name">Periode Tinggal</td>
      <td>:</td>
      <td><?= htmlspecialchars($data['nama_periode']) ?> (Durasi: <?= htmlspecialchars($data['durasi_sewa']) ?>)</td>
    </tr>
    <tr>
      <td class="field-name">Rencana Tanggal Check-in</td>
      <td>:</td>
      <td><strong><?= format_tanggal($data['rencana_checkin']) ?></strong></td>
    </tr>
    <tr>
      <td class="field-name">Total Biaya Sewa</td>
      <td>:</td>
      <td><strong style="font-size: 11pt;"><?= format_rupiah($data['total_biaya']) ?></strong></td>
    </tr>
    <tr>
      <td class="field-name">Status Permohonan</td>
      <td>:</td>
      <td><span style="font-weight:bold; text-transform:uppercase; color:green;"><?= htmlspecialchars($data['status']) ?></span></td>
    </tr>
    <tr>
      <td class="field-name">Status Pembayaran</td>
      <td>:</td>
      <td><span style="font-weight:bold; text-transform:uppercase;"><?= htmlspecialchars($data['status_pembayaran']) ?></span></td>
    </tr>
  </table>

  <!-- Catatan & Ketentuan Checkin -->
  <div style="font-size: 9pt; font-family: Arial, sans-serif; background: #fdfdfd; border: 1px dashed #666; padding: 10px; margin: 20px 0;">
    <strong>Instruksi Pengambilan Kunci & Check-in:</strong>
    <ol style="margin-bottom: 0; padding-left: 20px;">
      <li>Tunjukkan lembar bukti reservasi fisik ini kepada petugas jaga di Kantor Pengelola Asrama.</li>
      <li>Membawa identitas resmi (KTM aktif / KTP asli).</li>
      <li>Memastikan status pembayaran sewa lunas sebelum penyerahan kunci kamar dan inventaris.</li>
    </ol>
  </div>

  <!-- Signatures -->
  <div style="display:flex; justify-content:space-between; margin-top:35px; font-size:10pt;">
    <div style="text-align:center; width:220px;">
      Mahasiswa Pemohon,<br><br><br><br>
      <strong><u><?= htmlspecialchars($data['full_name']) ?></u></strong><br>
      NPM: <?= htmlspecialchars($data['npm'] ?? '-') ?>
    </div>

    <div style="text-align:center;">
      <div class="qr-badge">
        <i class="bi bi-qr-code" style="font-size: 30pt; display:block;"></i>
        VERIFIED DIGITAL<br>
        <small style="font-size:8pt;"><?= htmlspecialchars($data['kode_reservasi']) ?></small>
      </div>
    </div>

    <div style="text-align:center; width:220px;">
      Bandung, <?= date('d F Y') ?><br>
      Pengelola Asrama ULBI,<br><br><br><br>
      <strong><u>Petugas Administrasi Asrama</u></strong><br>
      NIP. 19840215 201101 1 002
    </div>
  </div>
</div>

</body>
</html>
