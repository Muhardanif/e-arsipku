<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\KategoriDokumen;
use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        // Staf (kepala puskesmas / bagian lain) memakai Portal Pencarian, bukan dashboard admin.
        if (auth()->user()->isStaf()) {
            return redirect()->route('portal.index');
        }

        $stats = [
            'total_dokumen' => Dokumen::count(),
            'draf' => Dokumen::where('status', 'draf')->count(),
            'dokumen_berlaku' => Dokumen::where('status', 'berlaku')->count(),
            'akan_kadaluarsa' => Dokumen::where('status', 'berlaku')
                ->whereNotNull('tanggal_berakhir')
                ->whereBetween('tanggal_berakhir', [Carbon::today(), Carbon::today()->addDays(30)])
                ->count(),
            'sedang_dipinjam' => Peminjaman::where('status', 'dipinjam')->count(),
            'perlu_review' => Dokumen::butuhTinjauan()->count(),
        ];

        $dokumenTerbaru = Dokumen::with('kategori')
            ->latest()
            ->take(5)
            ->get();

        // Dokumen yang perlu ditinjau (paling mendesak/terlewat di atas).
        $dokumenPerluReview = Dokumen::butuhTinjauan()
            ->with('kategori')
            ->get()
            ->sortBy(fn (Dokumen $d) => $d->sisaHariReview())
            ->take(5)
            ->values();

        // ── Data grafik ──────────────────────────────────────────────
        // Dokumen per kategori
        $perKategori = KategoriDokumen::withCount('dokumen')
            ->orderByDesc('dokumen_count')
            ->get(['id', 'kode', 'nama']);

        $chartKategori = [
            'labels' => $perKategori->pluck('kode'),
            'data' => $perKategori->pluck('dokumen_count'),
        ];

        // Komposisi status dokumen
        $chartStatus = [
            'draf' => $stats['draf'],
            'berlaku' => $stats['dokumen_berlaku'],
            'kadaluarsa' => Dokumen::where('status', 'kadaluarsa')->count(),
            'dicabut' => Dokumen::where('status', 'dicabut')->count(),
        ];

        return view('dashboard', compact(
            'stats', 'dokumenTerbaru', 'dokumenPerluReview',
            'chartKategori', 'chartStatus'
        ));
    }
}
