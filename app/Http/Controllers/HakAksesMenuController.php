<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Http\Requests\UpdateHakAksesRequest;
use App\Models\MenuAkses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HakAksesMenuController extends Controller
{
    use CatatAktivitas;

    public function index(): View
    {
        // role => [menu, ...] untuk menandai checkbox yang tercentang
        $akses = MenuAkses::query()->get()
            ->groupBy('role')
            ->map(fn ($baris) => $baris->pluck('menu')->all());

        return view('master.hak-akses.index', compact('akses'));
    }

    public function update(UpdateHakAksesRequest $request): RedirectResponse
    {
        $akses = $request->validated()['akses'] ?? [];

        DB::transaction(function () use ($akses) {
            MenuAkses::query()->delete();

            foreach (array_keys(MenuAkses::ROLES) as $role) {
                foreach (array_unique($akses[$role] ?? []) as $menu) {
                    MenuAkses::create(['role' => $role, 'menu' => $menu]);
                }
            }
        });

        MenuAkses::segarkanCache();

        $this->catatLog('ubah_hak_akses_menu', null, $akses);

        return redirect()->route('master.hak-akses.index')
            ->with('success', 'Hak akses menu berhasil disimpan.');
    }
}
