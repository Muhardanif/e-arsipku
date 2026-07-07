# Panduan Pindah E-ARSIPKU ke Komputer XAMPP Lain via GitHub (LAN)

Memindahkan aplikasi ke server XAMPP lain di jaringan lokal puskesmas
(contoh komputer tujuan ber-IP **`10.10.10.115`**) menggunakan **`git clone`**.

Berbeda dengan `DEPLOY.md` (itu untuk hosting online/cPanel). Dokumen ini untuk
**XAMPP Windows → XAMPP Windows** di LAN, lewat GitHub.

> **Repo ini di-set khusus untuk deploy sederhana:** folder `vendor/` dan
> `public/build/` **ikut di-commit** ke Git. Jadi server tujuan **tidak perlu**
> install Composer maupun Node.js — cukup **Git Bash + XAMPP**, lalu `git clone`.

> **Yang TIDAK ikut Git** (harus ditangani manual di server): `.env`, isi
> **database**, dan **file dokumen** di `storage/app/private/dokumen/`.

---

## A. Sekali saja di komputer sumber (10.10.10.165)

Setiap kali ada perubahan **tampilan/JS**, rebuild lalu commit ulang aset:
```bash
cd /c/xampp/htdocs/e-arsipku
npm run build                 # hasilkan ulang public/build
git add -A
git commit -m "update"
git push
```
> Server hanya `git pull` — tidak pernah build sendiri.

---

## B. Persiapan di komputer tujuan (10.10.10.115)

1. Install **XAMPP** dengan **PHP 8.2+** (samakan versi PHP dengan sumber).
2. **Git Bash** sudah terpasang ✔.
3. Jalankan **Apache** + **MySQL** dari XAMPP Control Panel minimal sekali.
4. Karena repo **PRIVATE**, siapkan **Personal Access Token (PAT)** GitHub:
   github.com ▸ *Settings ▸ Developer settings ▸ Personal access tokens ▸
   Tokens (classic)* → *Generate* dengan scope **`repo`**. Simpan token-nya —
   dipakai sebagai pengganti password saat `git clone`.

---

## C. Clone project (di Git Bash server tujuan)

```bash
cd /c/xampp/htdocs
git clone https://github.com/Muhardanif/e-arsipku.git
```
Saat diminta:
- **Username:** `Muhardanif`
- **Password:** tempel **Personal Access Token** (bukan password akun).

Hasil clone sudah lengkap dengan `vendor/` dan `public/build/` — siap jalan.

> **Catatan `php` di Git Bash:** perintah `php`/`artisan` tidak otomatis dikenal.
> Pakai path lengkap XAMPP di depan setiap perintah:
> ```bash
> /c/xampp/php/php artisan ...
> ```
> (atau tambahkan `C:\xampp\php` ke PATH Windows sekali agar cukup ketik `php`).

---

## D. Buat berkas `.env`

Cara termudah + aman (karena kita membawa data lama): **salin `.env` dari
komputer sumber** ke `C:\xampp\htdocs\e-arsipku\.env` di server tujuan, lalu ubah
**hanya** satu baris:
```env
APP_URL=http://10.10.10.115
```
- **Jangan** menjalankan `key:generate` — biarkan `APP_KEY` sama dengan sumber
  agar sesi/enkripsi data lama tetap valid.
- DB tetap: `DB_DATABASE=e_arsipku`, `DB_HOST=127.0.0.1`, `DB_PASSWORD=` kosong.

---

## E. Pindahkan data lama

**1. Database** (dokumen, user, dll):
- Di komputer sumber: phpMyAdmin (`http://localhost/phpmyadmin`) → pilih DB
  **`e_arsipku`** → **Export** → format **SQL** → simpan file.
- Di server tujuan: phpMyAdmin → **New** → buat database **`e_arsipku`** →
  tab **Import** → unggah file SQL tadi.

**2. File dokumen** (berkas asli yang diunggah — **tidak** ada di Git):
- Copy **isi** folder dari sumber:
  `C:\xampp\htdocs\e-arsipku\storage\app\private\dokumen\`
- Ke lokasi sama di server tujuan:
  `C:\xampp\htdocs\e-arsipku\storage\app\private\dokumen\`
- (via flashdisk / share jaringan). Kalau folder ini tidak dicopy, metadata
  dokumen ada tapi **file/scan-nya hilang**.

---

## F. Finalisasi (di Git Bash server tujuan)

```bash
cd /c/xampp/htdocs/e-arsipku
/c/xampp/php/php artisan storage:link
/c/xampp/php/php artisan migrate --force    # samakan struktur DB bila perlu
/c/xampp/php/php artisan optimize:clear      # bersihkan cache (APP_URL berubah)
```

---

## G. Pasang VirtualHost Apache (WAJIB — tidak ikut Git)

Config Apache ada di **luar** folder project, jadi harus dibuat ulang di server
tujuan. Buka `C:\xampp\apache\conf\extra\httpd-vhosts.conf`, tambahkan:

```apache
# === Host default: localhost tetap ke htdocs (phpMyAdmin, dsb) ===
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "C:/xampp/htdocs"
</VirtualHost>

# === E-ARSIPKU ===
<VirtualHost *:80>
    ServerName earsipku.local
    ServerAlias 10.10.10.115 192.168.1.10
    DocumentRoot "C:/xampp/htdocs/e-arsipku/public"

    <Directory "C:/xampp/htdocs/e-arsipku/public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/earsipku-error.log"
    CustomLog "logs/earsipku-access.log" common
</VirtualHost>
```

Pastikan `mod_rewrite` aktif di `httpd.conf` (baris
`LoadModule rewrite_module modules/mod_rewrite.so` tidak di-comment) dan ada
`Include conf/extra/httpd-vhosts.conf` (default XAMPP sudah aktif).

Cek lalu **restart Apache** (Stop → Start di XAMPP Control Panel):
```
C:\xampp\apache\bin\httpd.exe -t     (harus "Syntax OK")
```

---

## H. Uji akses

- **Di server tujuan & perangkat puskesmas lain:** `http://10.10.10.115`
- Health check: `http://10.10.10.115/up` → harus tampil sehat (bukan 404).
- Login lama tetap berlaku (data dibawa). Bila mulai bersih: `admin` / `admin123`.

---

## Update berikutnya (setelah setup awal)

Di server tujuan cukup:
```bash
cd /c/xampp/htdocs/e-arsipku
git pull
/c/xampp/php/php artisan migrate --force
/c/xampp/php/php artisan optimize:clear
```
(`vendor/` & `public/build/` ikut lewat `git pull`, jadi tak perlu Composer/Node.)

---

## Checklist singkat (server tujuan)

- [ ] XAMPP + PHP 8.2, Apache & MySQL menyala
- [ ] `git clone` (pakai Personal Access Token) ke `C:\xampp\htdocs\e-arsipku`
- [ ] `.env` disalin dari sumber → ubah `APP_URL=http://10.10.10.115` (APP_KEY tetap)
- [ ] DB `e_arsipku` dibuat + import SQL dari sumber
- [ ] `storage/app/private/dokumen/` dicopy dari sumber
- [ ] `storage:link` + `migrate --force` + `optimize:clear`
- [ ] VirtualHost ditambahkan + Apache di-restart
- [ ] Uji `http://10.10.10.115` dan `/up`
