<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Http\Requests\StoreDokumenVersiRequest;
use App\Models\Dokumen;
use App\Models\DokumenVersi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DokumenVersiController extends Controller
{
    use CatatAktivitas;

    public function create(Request $request, Dokumen $dokumen): View
    {
        $dokumen->load(['kategori', 'versi' => fn ($q) => $q->orderByDesc('nomor_versi')]);

        $versiBerikutnya = ((int) $dokumen->versi->max('nomor_versi')) + 1;
        $revisiBerikutnya = DokumenVersi::kodeRevisiDari($versiBerikutnya);

        if ($request->ajax()) {
            return view('dokumen.versi._modal', compact('dokumen', 'versiBerikutnya', 'revisiBerikutnya'));
        }

        return view('dokumen.versi.create', compact('dokumen', 'versiBerikutnya', 'revisiBerikutnya'));
    }

    public function store(StoreDokumenVersiRequest $request, Dokumen $dokumen): RedirectResponse
    {
        $data = $request->validated();

        $versi = DB::transaction(function () use ($request, $dokumen, $data) {
            // Kunci baris dokumen agar nomor versi aman dari race condition.
            $dokumen = Dokumen::whereKey($dokumen->id)->lockForUpdate()->first();
            $nomorVersi = ((int) $dokumen->versi()->max('nomor_versi')) + 1;

            $file = $request->file('file');
            $path = $file->storeAs(
                'dokumen',
                Str::uuid().'.'.$file->getClientOriginalExtension(),
                'local'
            );

            $versi = DokumenVersi::create([
                'dokumen_id' => $dokumen->id,
                'nomor_versi' => $nomorVersi,
                'catatan_revisi' => $data['catatan_revisi'],
                'tanggal_revisi' => $data['tanggal_revisi'],
                'file_path' => $path,
                'ukuran_file' => $file->getSize(),
                'diunggah_oleh' => $request->user()->id,
            ]);

            // Revisi baru menyegarkan dokumen → reset siklus review berkala.
            $dokumen->update([
                'versi_terkini' => $nomorVersi,
                'tanggal_review_terakhir' => now()->toDateString(),
                'updated_by' => $request->user()->id,
            ]);

            return $versi;
        });

        $this->catatLog('tambah_versi', $versi, [
            'dokumen' => $dokumen->nomor_dokumen,
            'revisi' => $versi->kodeRevisi(),
        ]);

        return redirect()->route('dokumen.show', $dokumen)
            ->with('success', "Revisi {$versi->kodeRevisi()} berhasil diunggah.");
    }

    public function destroy(DokumenVersi $versi): RedirectResponse
    {
        $versi->loadMissing('dokumen');
        $dokumen = $versi->dokumen;

        // Dokumen harus selalu menyimpan minimal satu berkas/versi.
        if ($dokumen->versi()->count() <= 1) {
            return redirect()->route('dokumen.show', $dokumen)
                ->with('error', 'Versi terakhir tidak dapat dihapus. Dokumen harus memiliki minimal satu berkas.');
        }

        $nomorDihapus = $versi->nomor_versi;
        $kodeDihapus = $versi->kodeRevisi();
        $filePath = $versi->file_path;

        DB::transaction(function () use ($versi, $dokumen, $nomorDihapus) {
            $versi->delete();

            // Bila versi terkini ikut terhapus, mundurkan ke versi tertinggi yang tersisa.
            if ($dokumen->versi_terkini === $nomorDihapus) {
                $dokumen->update([
                    'versi_terkini' => (int) $dokumen->versi()->max('nomor_versi'),
                    'updated_by' => auth()->id(),
                ]);
            }
        });

        // Hapus berkas fisik setelah perubahan basis data berhasil di-commit.
        Storage::disk('local')->delete($filePath);

        $this->catatLog('hapus_versi', $dokumen, [
            'dokumen' => $dokumen->nomor_dokumen,
            'revisi' => $kodeDihapus,
        ]);

        return redirect()->route('dokumen.show', $dokumen)
            ->with('success', "Revisi {$kodeDihapus} berhasil dihapus.");
    }
}
