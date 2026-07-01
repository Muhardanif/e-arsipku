<p align="center">
  <img src="public/images/logo-earsipku.png" width="120" alt="Logo E-ARSIPKU">
</p>

<h1 align="center">E-ARSIPKU</h1>

<p align="center"><strong>Sistem Pencatatan Arsip Dokumen Puskesmas</strong></p>

---

E-ARSIPKU adalah aplikasi berbasis web untuk **mencatat dan mengelola dokumen administrasi puskesmas** — seperti SOP, SK, Surat Masuk, Surat Keluar, dan dokumen akreditasi. Aplikasi dirancang berjalan di **jaringan lokal (LAN/intranet)** menggunakan XAMPP, sehingga tetap dapat diakses meskipun internet mati.

## ✨ Fitur Utama

- **Manajemen Dokumen** — CRUD dokumen lengkap dengan metadata, pencarian, dan filter
- **Versi & Revisi** — riwayat revisi dokumen tersimpan, versi lama tetap bisa diunduh
- **Sistem Draf** — dokumen dapat disimpan sebagai draf sebelum diterbitkan
- **Peminjaman Dokumen Fisik** — pencatatan peminjaman & pengembalian dengan penanda keterlambatan
- **Review Berkala** — pengingat jatuh tempo review dokumen per kategori
- **Notifikasi In-App** — bell notifikasi real-time untuk admin & petugas
- **Laporan** — daftar dokumen, dokumen kadaluarsa, peminjaman; ekspor PDF & cetak
- **Portal Pencarian** — antarmuka khusus read-only untuk kepala puskesmas/staf
- **Pencarian Isi Dokumen (Full-text + OCR)** — mencari berdasarkan isi berkas, bukan hanya nomor/judul. PDF berbasis teks diekstrak murni-PHP; PDF/gambar hasil scan diproses dengan OCR
- **Audit Akses Berkas** — jejak "siapa melihat & mengunduh dokumen apa", lengkap dengan riwayat akses per dokumen
- **Master Data** — kategori dokumen, klaster, dan manajemen pengguna
- **Log Aktivitas** — pencatatan setiap aksi penting di sistem

## 🛠️ Tech Stack

| Komponen   | Teknologi                                       |
|------------|-------------------------------------------------|
| Backend    | PHP 8.2 + Laravel 12                            |
| Frontend   | Blade + Tailwind CSS v4 (Vite) + Alpine.js      |
| Database   | MySQL 8 (via XAMPP)                             |
| Web Server | Apache (via XAMPP)                              |
| PDF        | barryvdh/laravel-dompdf                         |

## 👥 Role Pengguna

| Role        | Hak Akses                                                  |
|-------------|-----------------------------------------------------------|
| **admin**   | Akses penuh + manajemen user & master data                |
| **petugas** | CRUD dokumen, kelola peminjaman, cetak laporan            |
| **staf**    | Hanya melihat (read-only) daftar & detail dokumen         |

## 🚀 Instalasi

Prasyarat: **XAMPP** (PHP 8.2, MySQL 8, Apache), **Composer**, dan **Node.js**.

```bash
# 1. Clone repository ke dalam htdocs
git clone https://github.com/Muhardanif/e-arsipku.git
cd e-arsipku

# 2. Install dependency
composer install
npm install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate

# 4. Sesuaikan .env (DB & APP_URL)
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=e_arsipku
#    DB_USERNAME=root
#    DB_PASSWORD=
#    APP_URL=http://192.168.1.10

# 5. Buat database "e_arsipku" di phpMyAdmin, lalu migrasi + seed
php artisan migrate:fresh --seed

# 6. Link storage & build aset frontend
php artisan storage:link
npm run build

# 7. Jalankan (development)
php artisan serve
```

Untuk produksi di jaringan lokal, arahkan **DocumentRoot Apache** ke folder `public/`.

## 🔑 Akun Default

| Role  | Username | Password   |
|-------|----------|------------|
| admin | `admin`  | `admin123` |

> ⚠️ Segera ganti password default setelah login pertama.

## ⚙️ Konfigurasi Tambahan

- **Upload file**: sesuaikan `php.ini` XAMPP → `upload_max_filesize=10M`, `post_max_size=12M`
- **Backup database**: `php artisan backup:database` (dapat dijadwalkan via Windows Task Scheduler)

## 🔍 Pencarian Isi Dokumen (Full-text & OCR)

Pencarian dokumen mencakup **isi berkas**, bukan hanya nomor/judul. Strateginya hibrida:

1. **PDF berbasis teks** → lapisan teks diekstrak otomatis (murni-PHP, tanpa instalasi). **Sudah aktif tanpa setup tambahan.**
2. **PDF/gambar hasil scan** → diproses dengan **OCR**, butuh dua alat di server: **Tesseract** (dengan bahasa Indonesia `ind`) dan **Ghostscript**.

Isi berkas diindeks otomatis setiap kali dokumen ditambah, diterbitkan, atau direvisi. Untuk mengindeks berkas yang sudah ada:

```bash
php artisan dokumen:indeks-teks              # indeks berkas yang belum terindeks
php artisan dokumen:indeks-teks --perlu-ocr  # proses ulang berkas scan setelah OCR siap
php artisan dokumen:indeks-teks --ulang      # indeks ulang seluruh berkas
```

### Mengaktifkan OCR

**a) XAMPP / Windows (lokal)**

1. Pasang **Tesseract-OCR** (mis. installer UB Mannheim) — saat instalasi, sertakan paket bahasa **Indonesian (`ind`)**.
2. Pasang **Ghostscript** (versi 64-bit).
3. Bila keduanya tidak masuk PATH, set di `.env`:

   ```env
   TESSERACT_PATH="C:\Program Files\Tesseract-OCR\tesseract.exe"
   GHOSTSCRIPT_PATH="C:\Program Files\gs\gs10.03.0\bin\gswin64c.exe"
   ```

**b) Hosting online (server Linux — Ubuntu/Debian)**

```bash
sudo apt update
sudo apt install -y tesseract-ocr tesseract-ocr-ind ghostscript
```

Di Linux umumnya `tesseract` & `gs` sudah masuk PATH, jadi `.env` cukup:

```env
TESSERACT_PATH=tesseract
GHOSTSCRIPT_PATH=gs
```

> 💡 Saat di-hosting online, ubah juga `APP_URL` ke domain/HTTPS, set `APP_ENV=production` & `APP_DEBUG=false`, dan pastikan folder `storage/` dapat ditulis oleh web server. OCR berjalan saat unggah — bila berkas besar, naikkan `max_execution_time` PHP atau jalankan `dokumen:indeks-teks` secara terjadwal.

Pengaturan lanjutan (bahasa OCR, DPI, batas halaman, dll.) tersedia di `config/arsip.php`.

## 📄 Lisensi

Aplikasi internal puskesmas. Penggunaan terbatas pada lingkungan instansi terkait.
