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

];
