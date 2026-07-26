<?php

namespace Tests\Feature;

use App\Models\MenuAkses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAksesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cache peta akses bersifat static, harus dikosongkan tiap tes
        // karena seluruh tes berjalan dalam satu proses PHP.
        MenuAkses::segarkanCache();
    }

    public function test_petugas_tanpa_hak_menu_ditolak(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $this->actingAs($petugas)->get('/laporan')->assertForbidden();
    }

    public function test_petugas_dengan_hak_menu_diizinkan(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        MenuAkses::create(['role' => 'petugas', 'menu' => 'laporan']);

        $this->actingAs($petugas)->get('/laporan')->assertOk();
    }

    public function test_admin_selalu_boleh_walau_tanpa_baris_hak_akses(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/laporan')->assertOk();
    }

    public function test_hanya_admin_yang_bisa_mengubah_hak_akses(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        MenuAkses::create(['role' => 'petugas', 'menu' => 'pengguna']);

        $this->actingAs($petugas)->get('/master/hak-akses')->assertForbidden();
    }
}
