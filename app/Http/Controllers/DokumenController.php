<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Http\Requests\StoreDokumenRequest;
use App\Http\Requests\TerbitkanDokumenRequest;
use App\Http\Requests\UpdateDokumenRequest;
use App\Models\Dokumen;
use App\Models\DokumenVersi;
use App\Models\KategoriDokumen;
use App\Models\Klaster;
use App\Models\LogAktivitas;
use App\Services\NotifikasiService;
use App\Support\EkstraksiTeks;
use App\Support\PenomoranDokumen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumenController extends Controller
{
    use CatatAktivitas;

    public function index(Request $request): View|RedirectResponse
    {
        $filters = $request->only(['q', 'kategori_id', 'status', 'dari', 'sampai']);

        if ($pesan = $this->validasiRentangTanggal($filters['dari'] ?? null, $filters['sampai'] ?? null)) {
            return redirect()->route('dokumen.index')->with('error', $pesan);
        }

        $dokumen = Dokumen::query()
            ->with('kategori')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nomor_dokumen', 'like', "%{$q}%")
                        ->orWhere('judul', 'like', "%{$q}%")
                        // Pencarian penuh terhadap isi berkas (lapisan teks PDF / OCR).
                        ->orWhereHas('versi', fn ($v) => $v->where('isi_teks', 'like', "%{$q}%"));
                });
            })
            ->when($filters['kategori_id'] ?? null, fn ($query, $id) => $query->where('kategori_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['dari'] ?? null, fn ($query, $dari) => $query->whereDate('tanggal_dokumen', '>=', $dari))
            ->when($filters['sampai'] ?? null, fn ($query, $sampai) => $query->whereDate('tanggal_dokumen', '<=', $sampai))
            ->latest('tanggal_dokumen')
            ->paginate(20)
            ->withQueryString();

        $kategori = KategoriDokumen::orderBy('nama')->get();

        return view('dokumen.index', compact('dokumen', 'kategori', 'filters'));
    }

    public function create(Request $request): View
    {
        $this->authorizeKelola();

        $kategori = KategoriDokumen::orderBy('nama')->get();
        $klaster = Klaster::orderBy('urutan')->orderBy('kode')->get();
        $formatKategori = $this->formatKategori($kategori, $klaster);

        $data = ['kategori' => $kategori, 'klaster' => $klaster, 'formatKategori' => $formatKategori];

        if ($request->ajax()) {
            return view('dokumen._modal', $data + ['dokumen' => null]);
        }

        return view('dokumen.create', $data);
    }

    public function store(StoreDokumenRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Tanpa berkas → dokumen disimpan sebagai draf (nomor dipesan, berkas menyusul).
        $adaBerkas = $request->hasFile('file');

        $dokumen = DB::transaction(function () use ($request, $data, $adaBerkas) {
            $nomorDokumen = $data['nomor_dokumen'] ?? null;
            $nomorUrut = null;
            $tahunNomor = null;

            $klasterId = null;

            if (($data['mode_nomor'] ?? 'manual') === 'otomatis') {
                // Kunci baris kategori agar increment aman dari race condition.
                $kategori = KategoriDokumen::whereKey($data['kategori_id'])->lockForUpdate()->firstOrFail();
                $tahunNomor = Carbon::parse($data['tanggal_dokumen'])->year;

                // Klaster hanya relevan bila template kategori memuat token {KLASTER}.
                $klaster = PenomoranDokumen::pakaiKlaster($kategori->format_nomor) && ! empty($data['klaster_id'])
                    ? Klaster::find($data['klaster_id'])
                    : null;
                $klasterId = $klaster?->id;

                [$nomorDokumen, $nomorUrut] = PenomoranDokumen::generate($kategori, $tahunNomor, $klaster);
            }

            $dokumen = Dokumen::create([
                'nomor_dokumen' => $nomorDokumen,
                'nomor_urut' => $nomorUrut,
                'tahun_nomor' => $tahunNomor,
                'klaster_id' => $klasterId,
                'judul' => $data['judul'],
                'kategori_id' => $data['kategori_id'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'tanggal_dokumen' => $data['tanggal_dokumen'],
                'tanggal_berlaku' => $data['tanggal_berlaku'] ?? null,
                'tanggal_berakhir' => $data['tanggal_berakhir'] ?? null,
                'pengesah' => $data['pengesah'] ?? null,
                'status' => $adaBerkas ? $data['status'] : 'draf',
                'versi_terkini' => $adaBerkas ? 1 : 0,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            if ($adaBerkas) {
                $path = $this->simpanBerkas($request->file('file'));

                DokumenVersi::create([
                    'dokumen_id' => $dokumen->id,
                    'nomor_versi' => 1,
                    'catatan_revisi' => $data['catatan_revisi'] ?? 'Versi awal',
                    'tanggal_revisi' => $data['tanggal_dokumen'],
                    'file_path' => $path,
                    'ukuran_file' => $request->file('file')->getSize(),
                    'diunggah_oleh' => $request->user()->id,
                ]);
            }

            return $dokumen;
        });

        $this->catatLog($adaBerkas ? 'tambah_dokumen' : 'tambah_draf', $dokumen, ['nomor_dokumen' => $dokumen->nomor_dokumen]);

        app(NotifikasiService::class)->lupakanCache();

        // Indeks isi berkas untuk pencarian penuh (di luar transaksi: berkas
        // sudah tersimpan & OCR bisa memakan waktu).
        if ($adaBerkas) {
            EkstraksiTeks::indeks($dokumen->versi()->latest('id')->first());
        }

        return redirect()->route('dokumen.show', $dokumen)
            ->with('success', $adaBerkas
                ? 'Dokumen berhasil ditambahkan.'
                : 'Draf dokumen dibuat. Nomor sudah dipesan — unggah berkas final untuk menerbitkan.');
    }

    /**
     * Form unggah berkas final untuk menerbitkan dokumen yang masih draf.
     */
    public function terbitkanForm(Request $request, Dokumen $dokumen): View
    {
        $this->authorizeKelola();

        abort_unless($dokumen->isDraf(), 404, 'Dokumen ini sudah diterbitkan.');

        if ($request->ajax()) {
            return view('dokumen._terbitkan', compact('dokumen'));
        }

        return view('dokumen.terbitkan', compact('dokumen'));
    }

    /**
     * Terbitkan draf: unggah berkas final (versi 1) dan ubah status menjadi berlaku.
     */
    public function terbitkan(TerbitkanDokumenRequest $request, Dokumen $dokumen): RedirectResponse
    {
        $this->authorizeKelola();

        abort_unless($dokumen->isDraf(), 422, 'Dokumen ini sudah diterbitkan.');

        $data = $request->validated();

        DB::transaction(function () use ($request, $dokumen, $data) {
            $file = $request->file('file');
            $path = $this->simpanBerkas($file);

            DokumenVersi::create([
                'dokumen_id' => $dokumen->id,
                'nomor_versi' => 1,
                'catatan_revisi' => $data['catatan_revisi'] ?? 'Berkas final',
                'tanggal_revisi' => $dokumen->tanggal_dokumen?->toDateString() ?? now()->toDateString(),
                'file_path' => $path,
                'ukuran_file' => $file->getSize(),
                'diunggah_oleh' => $request->user()->id,
            ]);

            $dokumen->update([
                'status' => 'berlaku',
                'versi_terkini' => 1,
                'updated_by' => $request->user()->id,
            ]);
        });

        $this->catatLog('terbitkan_dokumen', $dokumen, ['nomor_dokumen' => $dokumen->nomor_dokumen]);

        app(NotifikasiService::class)->lupakanCache();

        EkstraksiTeks::indeks($dokumen->versi()->latest('id')->first());

        return redirect()->route('dokumen.show', $dokumen)
            ->with('success', "Dokumen {$dokumen->nomor_dokumen} berhasil diterbitkan.");
    }

    public function show(Dokumen $dokumen): View
    {
        $dokumen->load([
            'kategori',
            'klaster',
            'pembuat',
            'versi' => fn ($q) => $q->with('pengunggah')->orderByDesc('nomor_versi'),
            'peminjaman' => fn ($q) => $q->with('peminjam')->latest('tanggal_pinjam'),
            'review' => fn ($q) => $q->with('peninjau')->latest('tanggal_review'),
        ]);

        // Riwayat akses berkas (siapa melihat/mengunduh) untuk dokumen ini.
        // Log dicatat per versi (record_id = id versi), jadi telusuri lewat id versi.
        $idVersi = $dokumen->versi->pluck('id');
        $riwayatAkses = $idVersi->isEmpty()
            ? collect()
            : LogAktivitas::query()
                ->with('user')
                ->where('tabel', 'dokumen_versi')
                ->whereIn('aksi', ['lihat_dokumen', 'unduh_dokumen'])
                ->whereIn('record_id', $idVersi)
                ->latest()
                ->limit(20)
                ->get();

        return view('dokumen.show', compact('dokumen', 'riwayatAkses'));
    }

    public function edit(Request $request, Dokumen $dokumen): View
    {
        $this->authorizeKelola();

        $kategori = KategoriDokumen::orderBy('nama')->get();

        if ($request->ajax()) {
            return view('dokumen._modal', ['dokumen' => $dokumen, 'kategori' => $kategori]);
        }

        return view('dokumen.edit', compact('dokumen', 'kategori'));
    }

    public function update(UpdateDokumenRequest $request, Dokumen $dokumen): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        $dokumen->update($data);

        $this->catatLog('ubah_dokumen', $dokumen, ['nomor_dokumen' => $dokumen->nomor_dokumen]);

        app(NotifikasiService::class)->lupakanCache();

        return redirect()->route('dokumen.show', $dokumen)
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Dokumen $dokumen): RedirectResponse
    {
        $this->authorizeKelola();

        $nomor = $dokumen->nomor_dokumen;

        // Apa pun cabang penghapusannya, kandidat notifikasi bisa berubah.
        app(NotifikasiService::class)->lupakanCache();

        // Draf yang dibatalkan dihapus permanen (tidak punya berkas/peminjaman)
        // agar nomornya kembali menjadi celah dan dipakai ulang dokumen berikutnya.
        if ($dokumen->isDraf() && $dokumen->versi()->count() === 0 && $dokumen->peminjaman()->count() === 0) {
            $dokumen->forceDelete();

            $this->catatLog('batal_draf', $dokumen, ['nomor_dokumen' => $nomor]);

            return redirect()->route('dokumen.index')
                ->with('success', "Draf {$nomor} dibatalkan. Nomor dapat dipakai ulang dokumen berikutnya.");
        }

        $dokumen->delete();

        $this->catatLog('hapus_dokumen', $dokumen, ['nomor_dokumen' => $nomor]);

        return redirect()->route('dokumen.index')
            ->with('success', "Dokumen {$nomor} berhasil dihapus.");
    }

    /**
     * Unduh berkas sebuah versi dokumen (akses dicek di sini, bukan akses langsung ke storage).
     */
    public function downloadVersi(DokumenVersi $versi): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($versi->file_path), 404, 'Berkas tidak ditemukan.');

        $versi->loadMissing('dokumen');
        $ext = pathinfo($versi->file_path, PATHINFO_EXTENSION);
        $namaUnduh = Str::slug($versi->dokumen->nomor_dokumen.' rev '.$versi->kodeRevisi()).'.'.$ext;

        $this->catatLog('unduh_dokumen', $versi, [
            'nomor_dokumen' => $versi->dokumen->nomor_dokumen,
            'judul' => $versi->dokumen->judul,
            'revisi' => $versi->kodeRevisi(),
        ]);

        return Storage::disk('local')->download($versi->file_path, $namaUnduh);
    }

    /**
     * Tampilkan berkas versi dokumen secara inline (untuk pratinjau di modal),
     * tanpa memaksa unduhan. Akses dicek di sini, bukan akses langsung ke storage.
     */
    public function lihatVersi(DokumenVersi $versi): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($versi->file_path), 404, 'Berkas tidak ditemukan.');

        $versi->loadMissing('dokumen');
        $ext = pathinfo($versi->file_path, PATHINFO_EXTENSION);
        $nama = Str::slug($versi->dokumen->nomor_dokumen.' rev '.$versi->kodeRevisi()).'.'.$ext;

        $this->catatLog('lihat_dokumen', $versi, [
            'nomor_dokumen' => $versi->dokumen->nomor_dokumen,
            'judul' => $versi->dokumen->judul,
            'revisi' => $versi->kodeRevisi(),
        ]);

        return Storage::disk('local')->response($versi->file_path, $nama, [], 'inline');
    }

    /**
     * Peta data penomoran per kategori untuk pratinjau di form (JS).
     * Untuk kategori berklaster, sediakan "next" per klaster (tahun berjalan).
     */
    private function formatKategori(Collection $kategori, Collection $klaster): array
    {
        $tahun = now()->year;

        return $kategori->mapWithKeys(function (KategoriDokumen $k) use ($tahun, $klaster) {
            $pakaiKlaster = PenomoranDokumen::pakaiKlaster($k->format_nomor);

            $entry = [
                'format' => $k->format_nomor,
                'digit' => (int) ($k->digit_nomor ?: 3),
                'useKlaster' => $pakaiKlaster,
                'next' => null,
                'nextKlaster' => null,
            ];

            if ($k->punyaFormatNomor()) {
                if ($pakaiKlaster) {
                    $entry['nextKlaster'] = $klaster
                        ->mapWithKeys(fn (Klaster $kl) => [$kl->id => PenomoranDokumen::nextUrut($k, $tahun, $kl)])
                        ->all();
                } else {
                    $entry['next'] = PenomoranDokumen::nextUrut($k, $tahun);
                }
            }

            return [$k->id => $entry];
        })->all();
    }

    /**
     * Simpan berkas unggahan dengan nama acak (UUID) + ekstensi asli.
     */
    private function simpanBerkas($file): string
    {
        $namaFile = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('dokumen', $namaFile, 'local');
    }

    private function authorizeKelola(): void
    {
        $user = auth()->user();

        abort_unless($user && ($user->isAdmin() || $user->isPetugas()), 403,
            'Anda tidak memiliki akses untuk mengelola dokumen.');
    }
}
