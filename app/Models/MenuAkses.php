<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuAkses extends Model
{
    protected $table = 'menu_akses';

    protected $fillable = ['role', 'menu'];

    /**
     * Daftar menu yang hak aksesnya bisa diatur per role.
     * key => [label, keterangan]. Dashboard, lihat dokumen, dan portal
     * selalu terbuka untuk semua role sehingga tidak masuk daftar ini.
     */
    public const MENUS = [
        'dokumen-kelola' => ['Kelola Dokumen', 'Tambah, ubah, revisi, terbitkan, dan hapus dokumen'],
        'peminjaman' => ['Peminjaman', 'Catat dan kembalikan peminjaman dokumen fisik'],
        'laporan' => ['Laporan', 'Lihat dan cetak laporan dokumen serta peminjaman'],
        'master-kategori' => ['Master Kategori', 'Kelola kategori dokumen'],
        'master-klaster' => ['Master Klaster', 'Kelola klaster'],
        'pengguna' => ['Pengguna', 'Kelola akun pengguna dan reset kata sandi'],
        'audit-akses' => ['Audit Akses', 'Riwayat siapa melihat atau mengunduh berkas'],
        'log-aktivitas' => ['Log Aktivitas', 'Riwayat seluruh aktivitas sistem'],
    ];

    /** Role yang bisa diatur. Admin selalu punya akses penuh. */
    public const ROLES = [
        'petugas' => 'Petugas Arsip',
        'staf' => 'Staf',
    ];

    /** Cache per-request: role => [menu, ...] */
    private static ?array $petaAkses = null;

    public static function bolehAkses(string $role, string $menu): bool
    {
        if ($role === 'admin') {
            return true;
        }

        self::$petaAkses ??= self::query()->get()
            ->groupBy('role')
            ->map(fn ($baris) => $baris->pluck('menu')->all())
            ->all();

        return in_array($menu, self::$petaAkses[$role] ?? [], true);
    }

    /** Kosongkan cache setelah hak akses diubah dalam request yang sama. */
    public static function segarkanCache(): void
    {
        self::$petaAkses = null;
    }
}
