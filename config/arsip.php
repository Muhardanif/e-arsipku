<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ekstraksi & Pencarian Isi Dokumen
    |--------------------------------------------------------------------------
    |
    | Pengaturan untuk mengindeks isi berkas dokumen (PDF/gambar) agar bisa
    | dicari berdasarkan kontennya, bukan hanya nomor/judul. PDF berbasis teks
    | diekstrak murni-PHP (smalot/pdfparser); PDF hasil scan / gambar diproses
    | dengan OCR Tesseract sebagai fallback.
    |
    */

    'ekstraksi' => [
        // Ambang minimal panjang lapisan teks PDF agar dianggap "berbasis teks".
        // Di bawah ini, PDF dianggap hasil scan dan dialihkan ke OCR.
        'min_teks' => (int) env('EKSTRAKSI_MIN_TEKS', 40),

        // Batas panjang teks yang disimpan ke basis data (karakter).
        'maks_simpan' => (int) env('EKSTRAKSI_MAKS_SIMPAN', 200000),
    ],

    'ocr' => [
        // Aktifkan OCR untuk berkas hasil scan. Matikan bila Tesseract belum
        // terpasang di server agar tidak membuang waktu saat unggah.
        'aktif' => filter_var(env('OCR_AKTIF', true), FILTER_VALIDATE_BOOL),

        // Path executable Tesseract. Default mengandalkan PATH sistem.
        // Windows umumnya: C:\Program Files\Tesseract-OCR\tesseract.exe
        'tesseract_path' => env('TESSERACT_PATH', 'tesseract'),

        // Bahasa OCR (dipisah '+'). 'ind' = Indonesia, perlu data bahasa
        // 'ind.traineddata' terpasang di Tesseract.
        'lang' => env('OCR_LANG', 'ind+eng'),

        // Path Ghostscript untuk merasterisasi halaman PDF scan menjadi gambar
        // sebelum OCR. Windows: gswin64c (atau path lengkap ke gswin64c.exe).
        'ghostscript_path' => env('GHOSTSCRIPT_PATH', 'gswin64c'),

        // Resolusi rasterisasi PDF (DPI). 200–300 ideal untuk OCR.
        'dpi' => (int) env('OCR_DPI', 200),

        // Batas jumlah halaman PDF yang di-OCR agar unggah tidak terlalu lama.
        'maks_halaman' => (int) env('OCR_MAKS_HALAMAN', 15),

        // Batas waktu (detik) proses rasterisasi + OCR per berkas.
        'timeout' => (int) env('OCR_TIMEOUT', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Saran Metadata Otomatis (AI)
    |--------------------------------------------------------------------------
    |
    | Membaca isi teks berkas (lapisan teks PDF / OCR) lalu meminta model Claude
    | menyarankan metadata dokumen (judul, tanggal, pengesah, nomor, kategori)
    | untuk mengisi form tambah dokumen. Petugas tetap meninjau sebelum simpan.
    |
    | Fitur ini memanggil API Anthropic lewat internet — nonaktif secara default
    | dan hanya aktif bila AI_METADATA_AKTIF=true DAN ANTHROPIC_API_KEY terisi.
    | Selaras dengan rencana pemindahan aplikasi ke hosting online.
    |
    */

    'ai' => [
        // Sakelar utama. Aktif secara default; tetap aman bila kunci API belum
        // diisi karena tersedia() juga mensyaratkan ANTHROPIC_API_KEY terisi.
        'aktif' => filter_var(env('AI_METADATA_AKTIF', true), FILTER_VALIDATE_BOOL),

        // Kunci API Anthropic. WAJIB di sisi server (.env), jangan masuk Git.
        'api_key' => env('ANTHROPIC_API_KEY'),

        // Model. Opus 4.8 sudah lebih dari cukup untuk ekstraksi 1 dokumen;
        // tidak perlu Fable 5 (jauh lebih mahal) untuk tugas satu-tembakan ini.
        'model' => env('AI_METADATA_MODEL', 'claude-opus-4-8'),

        // Endpoint API (ubah hanya bila memakai proxy/gateway).
        'base_url' => rtrim(env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'), '/'),

        // Batas panjang teks yang dikirim ke model (karakter) — mengendalikan biaya.
        'maks_teks' => (int) env('AI_METADATA_MAKS_TEKS', 12000),

        // Batas waktu panggilan HTTP ke API (detik).
        'timeout' => (int) env('AI_METADATA_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Basis Data
    |--------------------------------------------------------------------------
    |
    | Pengaturan untuk perintah `php artisan backup:database` (mysqldump).
    | Dijadwalkan otomatis setiap pukul 23.00 lewat scheduler (lihat
    | routes/console.php); di server aktifkan dengan satu baris cron
    | `php artisan schedule:run` tiap menit — panduan di DEPLOY.md.
    |
    */

    'backup' => [
        // Path executable mysqldump. Windows/XAMPP: sesuaikan bila tidak di PATH,
        // mis. C:\xampp\mysql\bin\mysqldump.exe. Shared hosting umumnya cukup 'mysqldump'.
        'mysqldump_path' => env('BACKUP_MYSQLDUMP_PATH', 'mysqldump'),

        // Jumlah berkas backup terbaru yang dipertahankan (sisanya dihapus).
        'keep' => (int) env('BACKUP_KEEP', 14),

        // Batas waktu proses dump (detik).
        'timeout' => (int) env('BACKUP_TIMEOUT', 600),
    ],

];
