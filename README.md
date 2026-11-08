# SIRASMA ULBI
### Sistem Reservasi Asrama Mahasiswa Berbasis Web
**Universitas Logistik dan Bisnis Internasional (ULBI)**  
*Mata Kuliah: Proyek 3 & Keamanan Sistem Informasi*

---

## 👥 Identitas Tim Pengembang
* **Ketua Kelompok:** Hasan Zubeir Pohan (NPM: 714240045)
* **Anggota Kelompok:** Zidan Hairra Ramadhan (NPM: 714240061)
* **Kelas:** 3C - D4 Teknik Informatika
* **Dosen Pembimbing:** Dr. (Cand). Roni Habibi, S.Kom., M.T.
* **Tahun Akademik:** 2026

---

## 📌 Gambaran Umum Sistem
**SIRASMA (Sistem Reservasi Asrama Mahasiswa)** adalah aplikasi web yang dirancang untuk menyatukan informasi fasilitas asrama kampus, ketersediaan kamar secara real-time, serta proses pengajuan dan verifikasi reservasi asrama dalam satu layanan terpadu, aman, dan transparan bagi civitas akademika Universitas Logistik dan Bisnis Internasional (ULBI).

Aplikasi dibangun menggunakan arsitektur **PHP Native** murni dengan antarmuka modern responsif berbasis **Bootstrap 5**, serta menerapkan prinsip **Role-Based Access Control (RBAC)** dan **Least Privilege** untuk melindungi integritas data operasional dan kerahasiaan informasi pribadi mahasiswa.

---

## 🛡️ Matriks Hak Akses (Access Control Matrix)

| Role | Resource / Fitur | Create (C) | Read (R) | Update (U) | Delete (D) | Keterangan & Kontrol Akses |
| :--- | :--- | :---: | :---: | :---: | :---: | :--- |
| **Mahasiswa** | Profil Pribadi | ✓ | ✓ | ✓ | — | Hanya dapat melihat & mengubah data profil milik sendiri. |
| **Mahasiswa** | Reservasi Milik Sendiri | ✓ | ✓ | ✓* | — | Update hanya diizinkan untuk pembatalan saat status masih `menunggu_verifikasi`. |
| **Mahasiswa** | Informasi Kamar & Fasilitas | — | ✓ | — | — | Hanya dapat melihat ketersediaan dan fasilitas umum. |
| **Admin** | Data Kamar & Asrama | ✓ | ✓ | ✓ | ✓* | Delete kamar dilakukan melalui pengarsipan / penonaktifan (`soft-delete`). |
| **Admin** | Verifikasi Reservasi | — | ✓ | ✓ | — | Menyetujui, menolak dengan alasan, atau memverifikasi pembayaran. |
| **Admin** | Data Mahasiswa / Penghuni | — | ✓ | — | — | Memantau direktori seluruh mahasiswa dan penghuni aktif asrama. |
| **Admin** | Catatan Aktivitas (Audit Log) | — | ✓ | — | — | Audit trail seluruh aksi penting dan percobaan akses ilegal. |

---

## 🔑 Akun Demo untuk Pengujian

Aplikasi telah dilengkapi dengan data master dan akun demo siap pakai:

| Role | Username | Password | Keterangan Akun |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Pengelola Asrama ULBI (Akses Penuh Operasional) |
| **Mahasiswa (Putra)** | `hasan` | `mahasiswa123` | Hasan Zubeir Pohan (Penghuni Kamar A-101 / Status: Disetujui & Lunas) |
| **Mahasiswa (Putra)** | `zidan` | `mahasiswa123` | Zidan Hairra Ramadhan (Pengajuan Kamar A-102 / Status: Menunggu Verifikasi) |
| **Mahasiswa (Putri)** | `sarah` | `mahasiswa123` | Sarah Amalia Putri (Pengajuan Kamar B-101 Asrama Putri / Status: Disetujui) |

> **Catatan:** Pada halaman Login, tersedia tombol pintas *Akun Demo Cepat* untuk pengisian otomatis 1-klik tanpa perlu mengetik manual.

---

## 🚀 Panduan Menjalankan Aplikasi

Aplikasi dirancang secara cerdas dengan **Database Hybrid Auto-Fallback** (Mendukung MySQL XAMPP dan SQLite secara otomatis tanpa konfigurasi rumit).

### Cara 1: Menggunakan PHP Built-in Server (Paling Cepat / Instan)
1. Buka Terminal / PowerShell di folder proyek `c:\Proyek_III`
2. Jalankan perintah:
   ```bash
   php -S localhost:8000
   ```
3. Buka peramban (browser) dan akses:
   ```
   http://localhost:8000
   ```
   *(Sistem otomatis menggunakan basis data SQLite `database/sirasma.sqlite` yang sudah lengkap dengan tabel dan data uji).*

---

### Cara 2: Menggunakan XAMPP (Apache & MySQL)
1. Pastikan folder proyek berada di direktori `htdocs` XAMPP (misal: `C:\xampp\htdocs\Proyek_III` atau direktori saat ini jika virtual host aktif).
2. Buka **XAMPP Control Panel**, lalu jalankan modul **Apache** dan **MySQL**.
3. Buka phpMyAdmin di peramban: `http://localhost/phpmyadmin`
4. Buat database baru bernama: `sirasma_db`
5. Impor file skrip SQL yang telah disediakan:
   ```
   database/sirasma_ulbi.sql
   ```
6. Akses aplikasi melalui URL:
   ```
   http://localhost/Proyek_III/
   ```

---

## 📂 Struktur Direktori Proyek

```
c:\Proyek_III\
│
├── config/
│   ├── config.php                 # Konfigurasi aplikasi, sesi aman, CSRF, flash message
│   └── database.php               # Handler PDO Database (Auto-detect MySQL / SQLite)
│
├── includes/
│   ├── auth.php                   # Autentikasi, require_role(), anti-IDOR, audit logger
│   ├── header.php                 # Komponen navigasi atas & flash alert
│   ├── footer.php                 # Footer resmi dengan identitas kontak asrama ULBI
│   ├── sidebar.php                # Sidebar dinamis untuk Portal Mahasiswa & Admin
│   └── helpers.php                # Format mata uang rupiah, tanggal Indonesia, badge status
│
├── assets/
│   ├── css/
│   │   ├── style.css              # Gaya visual resmi ULBI, tipografi, kartu hunian
│   │   └── dashboard.css          # Styling portal dashboard, timeline, dan data table
│   └── img/
│       └── logo-ulbi.png          # Logo resmi ULBI beresolusi tinggi
│
├── database/
│   ├── sirasma_ulbi.sql           # Skrip SQL lengkap untuk XAMPP phpMyAdmin
│   ├── setup_database.php         # Inisialisator & Seeder data awal otomatis
│   └── sirasma.sqlite             # Database SQLite lokal siap jalan
│
├── uploads/
│   ├── ktm/                       # Direktori berkas salinan KTM mahasiswa
│   ├── pembayaran/                # Direktori berkas bukti transfer pembayaran sewa
│   └── sessions/                  # Direktori sesi aman lokal
│
├── auth/
│   ├── login.php                  # Halaman masuk dengan proteksi CSRF & akun demo
│   ├── register.php               # Pendaftaran akun mahasiswa baru ULBI
│   └── logout.php                 # Penanganan keluar sistem & pencatatan audit
│
├── mahasiswa/
│   ├── index.php                  # Dashboard ringkasan status & peringatan profil
│   ├── profil.php                 # Formulir pembaruan data diri & kontak darurat orang tua
│   ├── kamar.php                  # Katalog kamar terfilter otomatis sesuai gender pemohon
│   ├── reservasi.php              # Formulir pengajuan sewa, pilih kamar & durasi sewa
│   ├── riwayat.php                # Daftar riwayat reservasi (Strict Least Privilege)
│   ├── cetak_bukti.php            # Surat Bukti Reservasi Resmi dengan Kop Surat ULBI & QR Code
│   ├── upload_bukti.php           # Unggah bukti struk transfer pembayaran
│   └── batalkan.php               # Pembatalan pengajuan mandiri sebelum diproses admin
│
├── admin/
│   ├── index.php                  # Dashboard analitik keterisian kamar & verifikasi cepat
│   ├── kamar/
│   │   ├── index.php              # Manajemen kamar asrama (Daftar, Filter, Status)
│   │   ├── tambah.php             # Tambah data kamar baru
│   │   ├── edit.php               # Perbarui spesifikasi, tarif, & kapasitas kamar
│   │   └── hapus.php              # Pengarsipan kamar (Soft-delete terjaga integritasnya)
│   ├── reservasi/
│   │   ├── index.php              # Daftar seluruh pengajuan mahasiswa
│   │   ├── detail.php             # Panel verifikasi berkas KTM, Setujui/Tolak, & Cek Bayar
│   │   └── laporan.php            # Laporan rekapitulasi reservasi siap cetak (Print-Ready)
│   ├── mahasiswa/
│   │   └── index.php              # Direktori mahasiswa terdaftar & penghuni aktif
│   ├── pengumuman/
│   │   └── index.php              # Publikasi pengumuman & tata tertib asrama
│   └── logs/
│       └── index.php              # Modul Catatan Aktivitas Keamanan (Audit Trail Log)
│
├── index.php                      # Beranda publik (Hero, info kamar, 6 alur reservasi)
├── info_kamar.php                 # Katalog publik ketersediaan kamar interaktif
├── panduan.php                    # Panduan tata tertib, berkas syarat, & tanya jawab (FAQ)
├── kontak.php                     # Informasi kontak pengelola & peta lokasi kampus ULBI
└── README.md                      # Dokumentasi teknis proyek
```

---

## 🔒 Fitur Keamanan & Kepatuhan Praktikum Keamanan SI

1. **Role-Based Access Control (RBAC):**
   * Pemeriksaan hak akses dilakukan secara ketat di sisi server (`require_role()`), bukan sekadar menyembunyikan tombol antarmuka. Akses langsung melalui pengetikan URL ke direktori terlarang akan langsung ditolak (`HTTP 403`).
2. **Proteksi Anti-IDOR (Insecure Direct Object Reference):**
   * Mahasiswa dilarang melihat, mencetak, atau membatalkan reservasi mahasiswa lain melalui fungsi verifikasi kepemilikan data `check_reservation_ownership()`.
3. **Penyimpanan Kredensial Aman:**
   * Kata sandi tidak pernah disimpan dalam format teks biasa, melainkan di-hash menggunakan algoritma standar industri `BCRYPT` (`password_hash` & `password_verify`).
4. **Perlindungan Terhadap Serangan Web:**
   * **SQL Injection:** Seluruh query basis data menggunakan *PDO Prepared Statements* dengan *parameter binding*.
   * **Cross-Site Scripting (XSS):** Seluruh output masukan pengguna disaring menggunakan fungsi `htmlspecialchars()` dengan encoding `UTF-8`.
   * **Cross-Site Request Forgery (CSRF):** Setiap formulir POST dilindungi dengan token acak unik berbasis sesi (`verify_csrf_token`).
5. **Audit Trail Logging (Catatan Aktivitas):**
   * Seluruh aktivitas penting (Login berhasil/gagal, registrasi mahasiswa, pengajuan reservasi, persetujuan admin, penolakan, perubahan kamar, dan upaya akses ilegal) direkam ke tabel `audit_logs` lengkap dengan stempel waktu, identitas pengguna, aksi, dan IP address.

---

## 🎓 Selesai & Terverifikasi
Aplikasi SIRASMA ULBI telah diuji fungsionalitasnya secara menyeluruh dan siap didemonstrasikan dalam sidang evaluasi Proyek 3.
