# Panduan Deploy E-ARSIPKU ke Shared Hosting (cPanel)

Panduan langkah demi langkah memindahkan E-ARSIPKU dari XAMPP lokal ke hosting
online berbasis cPanel. Ikuti berurutan. Bagian bertanda **⚠️** wajib untuk
keamanan produksi.

---

## 0. Prasyarat di hosting

- **PHP 8.2+** (atur via cPanel ▸ *MultiPHP Manager* / *Select PHP Version*).
- Ekstensi PHP aktif: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`,
  `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `gd` (cek di *Select PHP Version ▸ Extensions*).
- **MySQL 8** (atau MariaDB 10.4+).
- Akses **Terminal/SSH** (disarankan; untuk composer & artisan). Bila tidak ada,
  tersedia jalur alternatif upload manual di tiap langkah.

---

## 1. Struktur folder (penting untuk Laravel di cPanel)

Web root cPanel adalah `public_html`, sedangkan web root Laravel adalah `public/`.
**Jangan** menaruh seluruh aplikasi di dalam `public_html`. Pilih salah satu:

### Cara A — ubah Document Root (paling bersih, disarankan)
1. Taruh seluruh proyek di luar `public_html`, mis. `~/e-arsipku`.
2. cPanel ▸ *Domains* → set **Document Root** domain ke `~/e-arsipku/public`.

### Cara B — bila Document Root tidak bisa diubah
1. Taruh proyek di `~/e-arsipku`.
2. Pindahkan **isi** folder `~/e-arsipku/public/*` ke `public_html/`.
3. Edit `public_html/index.php`, arahkan dua path ini ke folder aplikasi:
   ```php
   require __DIR__.'/../e-arsipku/vendor/autoload.php';
   $app = require_once __DIR__.'/../e-arsipku/bootstrap/app.php';
   ```

> Panduan di bawah mengasumsikan **Cara A** (docroot = `public/`).

---

## 2. Unggah kode

**Via Git (bila ada Terminal):**
```bash
cd ~
git clone https://github.com/Muhardanif/e-arsipku.git
cd e-arsipku
```

**Via upload manual:** kompres proyek lokal **tanpa** `node_modules/`, `vendor/`,
`.git/`, `.env`, lalu upload zip ke `~/e-arsipku` dan Extract lewat File Manager.

---

## 3. Dependensi Composer

**Via Terminal:**
```bash
cd ~/e-arsipku
composer install --no-dev --optimize-autoloader
```

**Tanpa Composer di server:** jalankan perintah di atas di komputer lokal, lalu
upload folder `vendor/` hasilnya ke `~/e-arsipku/vendor`.

---

## 4. Aset frontend (Vite) — ⚠️ wajib upload manual

`public/build` **tidak** ikut Git (ada di `.gitignore`) dan shared hosting
umumnya **tidak punya Node.js**. Maka:

1. Di komputer lokal: `npm run build` (menghasilkan `public/build/`).
2. Upload folder `public/build/` ke server: `~/e-arsipku/public/build/`.

> Setiap kali ada perubahan tampilan/JS, ulangi build lokal + upload `public/build`.

---

## 5. Berkas .env produksi

1. Salin template: `cp .env.production.example .env`
2. Isi nilai bertanda `<ISI>`: `APP_URL`, kredensial DB, `ANTHROPIC_API_KEY`.
3. Buat **APP_KEY**:
   ```bash
   php artisan key:generate
   ```
   (Tanpa Terminal: generate `base64:...` di lokal via `php artisan key:generate --show`,
   lalu tempel ke `APP_KEY=` pada `.env` server.)

⚠️ Pastikan `APP_ENV=production` dan `APP_DEBUG=false`.

---

## 6. Basis data

1. cPanel ▸ *MySQL Databases*: buat database + user, beri **ALL PRIVILEGES**.
   Masukkan nama/user/pass ke `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
2. Jalankan migrasi + seeder data awal (kategori + admin):
   ```bash
   php artisan migrate --force --seed
   ```
   ⚠️ **Segera ganti** password admin default (`admin` / `admin123`) setelah login.

   (Tanpa Terminal: export DB lokal via phpMyAdmin, import ke DB server. Tapi
   `migrate --seed` lebih bersih bila Terminal tersedia.)

---

## 7. Storage & izin

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```
- Folder berkas dokumen (`storage/app/dokumen`) **tidak** boleh diakses publik —
  ini sudah aman karena unduhan lewat controller berotorisasi, bukan link langsung.
- Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis web server.

---

## 8. Cache produksi (percepat aplikasi)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> Ulangi ketiganya setiap kali `.env` atau kode berubah. Bila mengubah `.env`
> tapi lupa `config:cache` ulang, nilai lama masih terpakai.

---

## 9. Batas unggahan (PHP) — untuk berkas s/d 10 MB

Atur lewat cPanel ▸ *MultiPHP INI Editor* (pilih domain), atau buat berkas
`public/.user.ini` berisi:
```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 120
```

---

## 10. Backup otomatis (cron) — ⚠️ penting

Aplikasi punya perintah `php artisan backup:database` (dump MySQL ke
`storage/app/backups`, menyimpan 14 berkas terbaru) yang **sudah dijadwalkan**
harian pukul 23.00 lewat scheduler Laravel.

Aktifkan dengan **satu** cron di cPanel ▸ *Cron Jobs* (setiap menit):
```
* * * * * cd ~/e-arsipku && php artisan schedule:run >> /dev/null 2>&1
```
> Gunakan path PHP 8.2 yang benar bila perlu (mis. `/usr/local/bin/ea-php82`).
> Uji manual dulu: `php artisan backup:database`. Bila `mysqldump` tidak
> ditemukan, isi `BACKUP_MYSQLDUMP_PATH` di `.env`. Bila `proc_open` dinonaktifkan
> host, pakai *Backup Wizard* bawaan cPanel sebagai gantinya.
>
> **Catatan pengujian di XAMPP lokal (Windows):** mysqldump XAMPP ada di
> `C:\xampp\mysql\bin\mysqldump.exe` (set `BACKUP_MYSQLDUMP_PATH` ke path itu).
> Bila muncul galat koneksi (2002/2013), itu kekhasan jaringan MariaDB XAMPP,
> bukan masalah kode — di MySQL hosting produksi backup berjalan normal.

Disarankan juga unduh berkas dari `storage/app/backups` secara berkala ke luar
server (mis. Google Drive) agar aman dari kegagalan disk.

---

## 11. HTTPS / SSL — ⚠️

1. cPanel ▸ *SSL/TLS Status* → aktifkan **AutoSSL** (Let's Encrypt) untuk domain.
2. Di `.env`: `APP_URL=https://domain-anda` dan `SESSION_SECURE_COOKIE=true`.
3. `php artisan config:cache` ulang.

---

## 12. Checklist keamanan sebelum go-live ⚠️

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` sudah di-generate
- [ ] Password admin default sudah diganti
- [ ] HTTPS aktif + `SESSION_SECURE_COOKIE=true`
- [ ] `.env` tidak dapat diakses dari browser (otomatis aman bila docroot = `public/`)
- [ ] `storage/app/dokumen` tidak bisa diakses langsung via URL
- [ ] `ANTHROPIC_API_KEY` hanya di `.env` server (tidak di Git/browser)
- [ ] Cron `schedule:run` berjalan (cek muncul berkas di `storage/app/backups`)
- [ ] Buka `https://domain-anda/up` → tampil status sehat (health check Laravel)

---

## 13. Update versi berikutnya (redeploy)

```bash
cd ~/e-arsipku
git pull                      # atau upload berkas yang berubah
composer install --no-dev --optimize-autoloader
php artisan migrate --force
# upload ulang public/build bila tampilan/JS berubah (build di lokal)
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## Catatan fitur AI (Saran Metadata)

Fitur "Isi otomatis dari berkas" memanggil API Anthropic (butuh internet — aman
di hosting online). Bekerja pada **teks** berkas: PDF berbasis teks langsung
terbaca; hasil scan perlu OCR yang biasanya tak tersedia di shared hosting
(karena itu `OCR_AKTIF=false` di produksi). Model default `claude-opus-4-8`.
Pastikan `ANTHROPIC_API_KEY` terisi agar tombol muncul.
