<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Http\Requests\StoreKlasterRequest;
use App\Http\Requests\UpdateKlasterRequest;
use App\Models\Klaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KlasterController extends Controller
{
    use CatatAktivitas;

    public function index(): View
    {
        $klaster = Klaster::query()
            ->withCount('dokumen')
            ->orderBy('urutan')
            ->orderBy('kode')
            ->paginate(20);

        return view('master.klaster.index', compact('klaster'));
    }

    public function create(Request $request): View
    {
        if ($request->ajax()) {
            return view('master.klaster._modal', ['klaster' => null]);
        }

        return view('master.klaster.create');
    }

    public function store(StoreKlasterRequest $request): RedirectResponse
    {
        $klaster = Klaster::create($request->validated());

        $this->catatLog('tambah_klaster', $klaster, ['kode' => $klaster->kode]);

        return redirect()->route('master.klaster.index')
            ->with('success', "Klaster {$klaster->kode} berhasil ditambahkan.");
    }

    public function edit(Request $request, Klaster $klaster): View
    {
        if ($request->ajax()) {
            return view('master.klaster._modal', compact('klaster'));
        }

        return view('master.klaster.edit', compact('klaster'));
    }

    public function update(UpdateKlasterRequest $request, Klaster $klaster): RedirectResponse
    {
        $klaster->update($request->validated());

        $this->catatLog('ubah_klaster', $klaster, ['kode' => $klaster->kode]);

        return redirect()->route('master.klaster.index')
            ->with('success', "Klaster {$klaster->kode} berhasil diperbarui.");
    }

    public function destroy(Klaster $klaster): RedirectResponse
    {
        if ($klaster->dokumen()->exists()) {
            return back()->with('error',
                "Klaster {$klaster->kode} tidak dapat dihapus karena masih digunakan oleh dokumen.");
        }

        $kode = $klaster->kode;
        $klaster->delete();

        $this->catatLog('hapus_klaster', $klaster, ['kode' => $kode]);

        return redirect()->route('master.klaster.index')
            ->with('success', "Klaster {$kode} berhasil dihapus.");
    }
}
