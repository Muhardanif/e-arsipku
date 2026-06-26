<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Http\Requests\StoreDokumenReviewRequest;
use App\Models\Dokumen;
use App\Models\DokumenReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Pencatatan review berkala dokumen. Menandai dokumen sudah ditinjau akan
 * mereset siklus review (jatuh tempo berikutnya dihitung dari tanggal review ini).
 */
class DokumenReviewController extends Controller
{
    use CatatAktivitas;

    public function create(Dokumen $dokumen): View
    {
        abort_if($dokumen->isDraf(), 404, 'Dokumen draf belum perlu ditinjau.');

        $dokumen->load('kategori');

        return view('dokumen.review._modal', compact('dokumen'));
    }

    public function store(StoreDokumenReviewRequest $request, Dokumen $dokumen): RedirectResponse
    {
        abort_if($dokumen->isDraf(), 422, 'Dokumen draf belum perlu ditinjau.');

        $data = $request->validated();

        DB::transaction(function () use ($request, $dokumen, $data) {
            DokumenReview::create([
                'dokumen_id' => $dokumen->id,
                'tanggal_review' => $data['tanggal_review'],
                'ditinjau_oleh' => $request->user()->id,
                'hasil' => $data['hasil'],
                'catatan' => $data['catatan'] ?? null,
            ]);

            // Reset siklus review: jatuh tempo berikutnya dihitung dari tanggal review ini.
            $dokumen->update([
                'tanggal_review_terakhir' => $data['tanggal_review'],
                'updated_by' => $request->user()->id,
            ]);
        });

        $this->catatLog('review_dokumen', $dokumen, [
            'nomor_dokumen' => $dokumen->nomor_dokumen,
            'hasil' => $data['hasil'],
        ]);

        $pesan = $data['hasil'] === 'perlu_revisi'
            ? 'Review dicatat. Dokumen ditandai perlu revisi — silakan unggah revisi baru.'
            : 'Review dicatat. Siklus review berikutnya dihitung dari tanggal review ini.';

        return redirect()->route('dokumen.show', $dokumen)->with('success', $pesan);
    }
}
