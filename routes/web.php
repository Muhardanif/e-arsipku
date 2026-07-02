<?php

use App\Http\Controllers\AuditAksesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\DokumenReviewController;
use App\Http\Controllers\DokumenVersiController;
use App\Http\Controllers\KategoriDokumenController;
use App\Http\Controllers\KlasterController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route(auth()->user()->homeRoute())
    : redirect()->route('login'));

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Area terproteksi
Route::middleware(['auth', 'harus.ganti.password'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Ganti kata sandi sendiri (semua role). Juga tujuan paksaan bila
    // akun ditandai harus_ganti_password.
    Route::get('profil/password', [ProfilController::class, 'editPassword'])
        ->name('profil.password.edit');
    Route::patch('profil/password', [ProfilController::class, 'updatePassword'])
        ->name('profil.password.update');

    // Portal Pencarian Dokumen — tampilan front-end bersih (semua role).
    // Kepala puskesmas/staf diarahkan ke sini setelah login.
    Route::get('portal', [PortalController::class, 'index'])->name('portal.index');
    Route::get('portal/{dokumen}', [PortalController::class, 'show'])->name('portal.show');

    // Dokumen — lihat & unduh berkas (semua role)
    Route::get('dokumen/versi/{versi}/view', [DokumenController::class, 'lihatVersi'])
        ->name('dokumen.versi.view');
    Route::get('dokumen/versi/{versi}/download', [DokumenController::class, 'downloadVersi'])
        ->name('dokumen.versi.download');

    // Dokumen — kelola (admin & petugas). Didaftarkan lebih dulu agar
    // /dokumen/create & /dokumen/{dokumen}/edit tidak tertangkap route show.
    Route::middleware('role:admin,petugas')->group(function () {
        // Notifikasi in-app — tandai sudah dibaca (admin & petugas)
        Route::post('notifikasi/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
        Route::post('notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');

        // Revisi/versi dokumen
        Route::get('dokumen/{dokumen}/versi/create', [DokumenVersiController::class, 'create'])
            ->name('dokumen.versi.create');
        Route::post('dokumen/{dokumen}/versi', [DokumenVersiController::class, 'store'])
            ->name('dokumen.versi.store');
        Route::delete('dokumen/versi/{versi}', [DokumenVersiController::class, 'destroy'])
            ->name('dokumen.versi.destroy');

        // Review berkala dokumen
        Route::get('dokumen/{dokumen}/review/create', [DokumenReviewController::class, 'create'])
            ->name('dokumen.review.create');
        Route::post('dokumen/{dokumen}/review', [DokumenReviewController::class, 'store'])
            ->name('dokumen.review.store');

        // Saran metadata otomatis (AI) dari isi berkas — mengisi form tambah.
        Route::post('dokumen/saran-metadata', [DokumenController::class, 'saranMetadata'])
            ->name('dokumen.saran-metadata');

        // Terbitkan draf: unggah berkas final dokumen yang masih draf
        Route::get('dokumen/{dokumen}/terbitkan', [DokumenController::class, 'terbitkanForm'])
            ->name('dokumen.terbitkan.form');
        Route::post('dokumen/{dokumen}/terbitkan', [DokumenController::class, 'terbitkan'])
            ->name('dokumen.terbitkan');

        Route::resource('dokumen', DokumenController::class)
            ->except(['index', 'show'])
            ->parameters(['dokumen' => 'dokumen']);

        // Peminjaman dokumen fisik (admin & petugas)
        Route::get('peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
        Route::get('peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
        Route::post('peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
        Route::patch('peminjaman/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])
            ->name('peminjaman.kembalikan');

        // Laporan + export PDF (admin & petugas)
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', [LaporanController::class, 'index'])->name('index');
            Route::get('dokumen', [LaporanController::class, 'dokumen'])->name('dokumen');
            Route::get('kadaluarsa', [LaporanController::class, 'kadaluarsa'])->name('kadaluarsa');
            Route::get('review', [LaporanController::class, 'review'])->name('review');
            Route::get('peminjaman', [LaporanController::class, 'peminjaman'])->name('peminjaman');
        });
    });

    // Master data & manajemen pengguna (admin saja)
    Route::middleware('role:admin')->group(function () {
        Route::resource('master/kategori', KategoriDokumenController::class)
            ->except('show')
            ->names('master.kategori')
            ->parameters(['kategori' => 'kategori']);

        Route::resource('master/klaster', KlasterController::class)
            ->except('show')
            ->names('master.klaster')
            ->parameters(['klaster' => 'klaster']);

        // Log aktivitas sistem
        Route::get('log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas.index');

        // Audit akses berkas — siapa melihat/mengunduh dokumen apa
        Route::get('audit-akses', [AuditAksesController::class, 'index'])->name('audit-akses.index');

        Route::get('pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPasswordForm'])
            ->name('pengguna.reset-password.form');
        Route::patch('pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPassword'])
            ->name('pengguna.reset-password');
        Route::patch('pengguna/{pengguna}/toggle-aktif', [PenggunaController::class, 'toggleAktif'])
            ->name('pengguna.toggle-aktif');
        Route::resource('pengguna', PenggunaController::class)->except('show');
    });

    // Dokumen — lihat daftar/detail (semua role)
    Route::resource('dokumen', DokumenController::class)
        ->only(['index', 'show'])
        ->parameters(['dokumen' => 'dokumen']);
});
