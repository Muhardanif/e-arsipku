<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\KategoriDokumen;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Portal Pencarian Dokumen — tampilan front-end yang bersih untuk
 * kepala puskesmas / bagian lain. Fokus: cari → lihat detail → buka/unduh.
 * Hanya menampilkan dokumen yang sudah diterbitkan (bukan draf).
 */
class PortalController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'kategori_id', 'status']);

        $dokumen = Dokumen::query()
            ->with('kategori')
            ->where('status', '!=', 'draf')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nomor_dokumen', 'like', "%{$q}%")
                        ->orWhere('judul', 'like', "%{$q}%")
                        ->orWhere('deskripsi', 'like', "%{$q}%")
                        // Pencarian penuh terhadap isi berkas (lapisan teks PDF / OCR).
                        ->orWhereHas('versi', fn ($v) => $v->where('isi_teks', 'like', "%{$q}%"));
                });
            })
            ->when($filters['kategori_id'] ?? null, fn ($query, $id) => $query->where('kategori_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('tanggal_dokumen')
            ->paginate(12)
            ->withQueryString();

        $kategori = KategoriDokumen::orderBy('nama')->get();

        return view('portal.index', compact('dokumen', 'kategori', 'filters'));
    }

    public function show(Dokumen $dokumen): View
    {
        // Draf belum punya berkas final — tidak relevan untuk portal.
        abort_if($dokumen->isDraf(), 404, 'Dokumen tidak tersedia.');

        $dokumen->load([
            'kategori',
            'klaster',
            'versi' => fn ($q) => $q->with('pengunggah')->orderByDesc('nomor_versi'),
        ]);

        return view('portal.show', compact('dokumen'));
    }
}
