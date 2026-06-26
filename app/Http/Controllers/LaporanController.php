<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Models\Dokumen;
use App\Models\KategoriDokumen;
use App\Models\Peminjaman;
use App\Support\XlsxWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    use CatatAktivitas;

    public function index(): View
    {
        $stats = [
            'total_dokumen' => Dokumen::count(),
            'akan_kadaluarsa' => Dokumen::whereNotNull('tanggal_berakhir')
                ->whereBetween('tanggal_berakhir', [Carbon::today(), Carbon::today()->addDays(30)])
                ->count(),
            'peminjaman_aktif' => Peminjaman::where('status', 'dipinjam')->count(),
            'perlu_review' => Dokumen::butuhTinjauan()->count(),
        ];

        return view('laporan.index', compact('stats'));
    }

    /**
     * Laporan daftar dokumen (filter kategori / status / periode).
     */
    public function dokumen(Request $request): View|Response
    {
        $filters = $request->only(['kategori_id', 'status', 'dari', 'sampai']);

        if ($pesan = $this->validasiRentangTanggal($filters['dari'] ?? null, $filters['sampai'] ?? null)) {
            return redirect()->route('laporan.dokumen')->with('error', $pesan);
        }

        $dokumen = Dokumen::query()
            ->with('kategori')
            ->when($filters['kategori_id'] ?? null, fn ($q, $id) => $q->where('kategori_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['dari'] ?? null, fn ($q, $d) => $q->whereDate('tanggal_dokumen', '>=', $d))
            ->when($filters['sampai'] ?? null, fn ($q, $s) => $q->whereDate('tanggal_dokumen', '<=', $s))
            ->orderBy('kategori_id')
            ->orderBy('nomor_dokumen')
            ->get();

        $kategori = KategoriDokumen::orderBy('nama')->get();

        if ($request->query('pdf')) {
            return $this->keluaranPdf($request, 'laporan.pdf.dokumen', [
                'dokumen' => $dokumen,
                'filters' => $filters,
                'kategoriList' => $kategori,
            ], 'laporan-dokumen');
        }

        if ($request->query('excel')) {
            return $this->keluaranExcel('laporan-dokumen', 'Daftar Dokumen',
                ['No', 'Nomor Dokumen', 'Judul', 'Kategori', 'Tanggal Dokumen', 'Tanggal Berlaku', 'Tanggal Berakhir', 'Pengesah', 'Status'],
                $dokumen->values()->map(fn (Dokumen $d, int $i) => [
                    $i + 1,
                    $d->nomor_dokumen,
                    $d->judul,
                    $d->kategori?->nama,
                    $d->tanggal_dokumen?->format('d/m/Y'),
                    $d->tanggal_berlaku?->format('d/m/Y'),
                    $d->tanggal_berakhir?->format('d/m/Y') ?? '-',
                    $d->pengesah ?: '-',
                    ucfirst($d->status),
                ])->all()
            );
        }

        return view('laporan.dokumen', compact('dokumen', 'kategori', 'filters'));
    }

    /**
     * Laporan dokumen yang akan / sudah kadaluarsa.
     */
    public function kadaluarsa(Request $request): View|Response
    {
        $hari = (int) $request->query('hari', 30);

        $dokumen = Dokumen::query()
            ->with('kategori')
            ->whereNotNull('tanggal_berakhir')
            ->where('status', '!=', 'dicabut')
            ->whereDate('tanggal_berakhir', '<=', Carbon::today()->addDays($hari))
            ->orderBy('tanggal_berakhir')
            ->get();

        if ($request->query('pdf')) {
            return $this->keluaranPdf($request, 'laporan.pdf.kadaluarsa', [
                'dokumen' => $dokumen,
                'hari' => $hari,
            ], 'laporan-kadaluarsa');
        }

        if ($request->query('excel')) {
            return $this->keluaranExcel('laporan-kadaluarsa', 'Dokumen Kadaluarsa',
                ['No', 'Nomor Dokumen', 'Judul', 'Kategori', 'Tanggal Berakhir', 'Sisa Hari', 'Keterangan', 'Status'],
                $dokumen->values()->map(function (Dokumen $d, int $i) {
                    $sisa = (int) now()->startOfDay()->diffInDays($d->tanggal_berakhir, false);

                    return [
                        $i + 1,
                        $d->nomor_dokumen,
                        $d->judul,
                        $d->kategori?->nama,
                        $d->tanggal_berakhir?->format('d/m/Y'),
                        $sisa,
                        $sisa < 0 ? 'Lewat '.abs($sisa).' hari' : $sisa.' hari lagi',
                        ucfirst($d->status),
                    ];
                })->all()
            );
        }

        return view('laporan.kadaluarsa', compact('dokumen', 'hari'));
    }

    /**
     * Laporan dokumen yang perlu ditinjau berkala (segera / sudah lewat).
     */
    public function review(Request $request): View|Response
    {
        $hari = (int) $request->query('hari', Dokumen::REVIEW_AMBANG_HARI);

        $dokumen = Dokumen::butuhTinjauan($hari)
            ->with('kategori')
            ->get()
            ->sortBy(fn (Dokumen $d) => $d->sisaHariReview())
            ->values();

        if ($request->query('pdf')) {
            return $this->keluaranPdf($request, 'laporan.pdf.review', [
                'dokumen' => $dokumen,
                'hari' => $hari,
            ], 'laporan-review');
        }

        if ($request->query('excel')) {
            return $this->keluaranExcel('laporan-review', 'Dokumen Perlu Review',
                ['No', 'Nomor Dokumen', 'Judul', 'Kategori', 'Periode (th)', 'Dihitung Dari', 'Jatuh Tempo Review', 'Sisa Hari', 'Keterangan'],
                $dokumen->map(function (Dokumen $d, int $i) {
                    $sisa = $d->sisaHariReview();

                    return [
                        $i + 1,
                        $d->nomor_dokumen,
                        $d->judul,
                        $d->kategori?->nama,
                        $d->kategori?->periode_review_tahun,
                        $d->anchorReview()?->format('d/m/Y'),
                        $d->jatuhTempoReview()?->format('d/m/Y'),
                        $sisa,
                        $sisa < 0 ? 'Lewat '.abs($sisa).' hari' : $sisa.' hari lagi',
                    ];
                })->all()
            );
        }

        return view('laporan.review', compact('dokumen', 'hari'));
    }

    /**
     * Laporan peminjaman (aktif / dikembalikan / terlambat).
     */
    public function peminjaman(Request $request): View|Response
    {
        $status = $request->query('status', 'semua');

        $peminjaman = Peminjaman::query()
            ->with(['dokumen', 'peminjam'])
            ->when($status === 'dipinjam', fn ($q) => $q->where('status', 'dipinjam'))
            ->when($status === 'dikembalikan', fn ($q) => $q->where('status', 'dikembalikan'))
            ->when($status === 'terlambat', fn ($q) => $q->where('status', 'dipinjam')
                ->whereDate('tanggal_kembali_rencana', '<', Carbon::today()))
            ->orderByDesc('tanggal_pinjam')
            ->get();

        if ($request->query('pdf')) {
            return $this->keluaranPdf($request, 'laporan.pdf.peminjaman', [
                'peminjaman' => $peminjaman,
                'status' => $status,
            ], 'laporan-peminjaman');
        }

        if ($request->query('excel')) {
            return $this->keluaranExcel('laporan-peminjaman', 'Peminjaman Dokumen',
                ['No', 'Nomor Dokumen', 'Judul Dokumen', 'Peminjam', 'Tujuan', 'Tanggal Pinjam', 'Rencana Kembali', 'Kembali Aktual', 'Status'],
                $peminjaman->values()->map(fn (Peminjaman $p, int $i) => [
                    $i + 1,
                    $p->dokumen?->nomor_dokumen,
                    $p->dokumen?->judul ?? '-',
                    $p->peminjam_nama ?? $p->peminjam?->nama ?? '-',
                    $p->tujuan,
                    $p->tanggal_pinjam?->format('d/m/Y'),
                    $p->tanggal_kembali_rencana?->format('d/m/Y'),
                    $p->tanggal_kembali_aktual?->format('d/m/Y') ?? '-',
                    $p->isTerlambat() ? 'Terlambat' : ucfirst($p->status),
                ])->all()
            );
        }

        return view('laporan.peminjaman', compact('peminjaman', 'status'));
    }

    /**
     * Tangani keluaran PDF dalam 2 mode:
     *  - stream : PDF inline (dipakai sebagai sumber iframe pada modal pratinjau)
     *  - unduh  : PDF sebagai unduhan berkas
     */
    private function keluaranPdf(Request $request, string $view, array $data, string $namaFile): Response
    {
        $mode = $request->query('pdf') === 'unduh' ? 'unduh' : 'lihat';

        $this->catatLog('cetak_laporan', null, ['laporan' => $namaFile, 'mode' => $mode]);

        $pdf = Pdf::loadView($view, array_merge($data, [
            'dicetakOleh' => auth()->user()->nama,
            'dicetakPada' => Carbon::now(),
        ]))->setPaper('a4', 'portrait');

        $namaBerkas = $namaFile.'-'.now()->format('Ymd-His').'.pdf';

        return $mode === 'unduh'
            ? $pdf->download($namaBerkas)
            : $pdf->stream($namaBerkas); // inline, untuk iframe
    }

    /**
     * Keluaran Excel (.xlsx) — selalu sebagai unduhan berkas.
     *
     * @param  array<int,string>            $headers
     * @param  array<int,array<int,mixed>>  $rows
     */
    private function keluaranExcel(string $namaFile, string $judulSheet, array $headers, array $rows): StreamedResponse
    {
        $this->catatLog('export_laporan', null, ['laporan' => $namaFile, 'format' => 'excel']);

        $namaBerkas = $namaFile.'-'.now()->format('Ymd-His').'.xlsx';

        return XlsxWriter::download($namaBerkas, $judulSheet, $headers, $rows);
    }
}
