<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Models\KategoriDokumen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriDokumenController extends Controller
{
    use CatatAktivitas;

    public function index(): View
    {
        $kategori = KategoriDokumen::query()
            ->withCount('dokumen')
            ->orderBy('kode')
            ->paginate(20);

        return view('master.kategori.index', compact('kategori'));
    }

    public function create(Request $request): View
    {
        if ($request->ajax()) {
            return view('master.kategori._modal', ['kategori' => null]);
        }

        return view('master.kategori.create');
    }

    public function store(StoreKategoriRequest $request): RedirectResponse
    {
        $kategori = KategoriDokumen::create($request->validated());

        $this->catatLog('tambah_kategori', $kategori, ['kode' => $kategori->kode]);

        return redirect()->route('master.kategori.index')
            ->with('success', "Kategori {$kategori->kode} berhasil ditambahkan.");
    }

    public function edit(Request $request, KategoriDokumen $kategori): View
    {
        if ($request->ajax()) {
            return view('master.kategori._modal', compact('kategori'));
        }

        return view('master.kategori.edit', compact('kategori'));
    }

    public function update(UpdateKategoriRequest $request, KategoriDokumen $kategori): RedirectResponse
    {
        $kategori->update($request->validated());

        $this->catatLog('ubah_kategori', $kategori, ['kode' => $kategori->kode]);

        return redirect()->route('master.kategori.index')
            ->with('success', "Kategori {$kategori->kode} berhasil diperbarui.");
    }

    public function destroy(KategoriDokumen $kategori): RedirectResponse
    {
        if ($kategori->dokumen()->exists()) {
            return back()->with('error',
                "Kategori {$kategori->kode} tidak dapat dihapus karena masih digunakan oleh dokumen.");
        }

        $kode = $kategori->kode;
        $kategori->delete();

        $this->catatLog('hapus_kategori', $kategori, ['kode' => $kode]);

        return redirect()->route('master.kategori.index')
            ->with('success', "Kategori {$kode} berhasil dihapus.");
    }
}
