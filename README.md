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

## 📄 Lisensi

Aplikasi internal puskesmas. Penggunaan terbatas pada lingkungan instansi terkait.
