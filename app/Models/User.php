<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'username',
        'password',
        'role',
        'jabatan',
        'aktif',
        'harus_ganti_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'aktif' => 'boolean',
            'harus_ganti_password' => 'boolean',
        ];
    }

    /**
     * Role helpers.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isStaf(): bool
    {
        return $this->role === 'staf';
    }

    /**
     * Cek hak akses menu dari tabel menu_akses
     * (diatur admin di Master > Hak Akses Menu). Admin selalu boleh.
     */
    public function bolehMenu(string $menu): bool
    {
        return MenuAkses::bolehAkses($this->role, $menu);
    }

    /**
     * Punya akses ke panel admin bila boleh minimal satu menu terkelola.
     * Dipakai untuk menampilkan pintasan "Panel Admin" di portal.
     */
    public function bolehPanelAdmin(): bool
    {
        foreach (array_keys(MenuAkses::MENUS) as $menu) {
            if ($this->bolehMenu($menu)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Halaman beranda sesuai role. Staf (mis. kepala puskesmas / bagian lain)
     * diarahkan ke Portal Pencarian yang bersih, bukan dashboard admin.
     */
    public function homeRoute(): string
    {
        return $this->isStaf() ? 'portal.index' : 'dashboard';
    }

    /**
     * Relationships.
     */
    public function dokumenDibuat()
    {
        return $this->hasMany(Dokumen::class, 'created_by');
    }

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'peminjam_id');
    }

    public function logAktivitas()
    {
        return $this->hasMany(LogAktivitas::class);
    }
}
