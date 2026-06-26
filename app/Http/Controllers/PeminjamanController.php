<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Http\Requests\StorePeminjamanRequest;
use App\Models\Dokumen;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    use CatatAktivitas;

    public function index(Request $request): View
    {
        $status = $request->query('status', 'dipinjam');

        $peminjaman = Peminjaman::query()
            ->with(['dokumen', 'peminjam'])
            ->when($status === 'dipinjam', fn ($q) => $q->where('status', 'dipinjam'))
            ->when($status === 'dikembalikan', fn ($q) => $q->where('status', 'dikembalikan'))
            ->when($status === 'terlambat', fn ($q) => $q->where('status', 'dipinjam')
                ->whereDate('tanggal_kembali_rencana', '<', Carbon::today()))
            ->orderByRaw("FIELD(status, 'dipinjam', 'dikembalikan')")
            ->orderBy('tanggal_kembali_rencana')
            ->paginate(20)
            ->withQueryString();

        return view('peminjaman.index', compact('peminjaman', 'status'));
    }

    public function create(Request $request): View
    {
        $dokumen = Dokumen::orderBy('judul')->get(['id', 'nomor_dokumen', 'judul']);
        $pengguna = User::where('aktif', true)->orderBy('nama')->get(['id', 'nama']);
        $dokumenTerpilih = $request->query('dokumen_id');

        if ($request->ajax()) {
            return view('peminjaman._modal', compact('dokumen', 'pengguna', 'dokumenTerpilih'));
        }

        return view('peminjaman.create', compact('dokumen', 'pengguna', 'dokumenTerpilih'));
    }

    public function store(StorePeminjamanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = 'dipinjam';

        $peminjaman = Peminjaman::create($data);

        $this->catatLog('tambah_peminjaman', $peminjaman, [
            'dokumen_id' => $peminjaman->dokumen_id,
        ]);

        return redirect()->route('peminjaman.index')
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function kembalikan(Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->status !== 'dipinjam') {
            return back()->with('warning', 'Peminjaman ini sudah dikembalikan.');
        }

        $peminjaman->update([
            'status' => 'dikembalikan',
            'tanggal_kembali_aktual' => Carbon::today(),
        ]);

        $this->catatLog('kembalikan_peminjaman', $peminjaman, [
            'dokumen_id' => $peminjaman->dokumen_id,
        ]);

        return back()->with('success', 'Dokumen berhasil ditandai dikembalikan.');
    }
}
