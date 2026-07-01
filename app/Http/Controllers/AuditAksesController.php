<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Audit akses berkas: "siapa melihat/mengunduh dokumen apa". Menyajikan
 * jejak akses berkas (aksi lihat_dokumen & unduh_dokumen) yang sudah dicatat
 * ke log_aktivitas, dalam tampilan khusus dengan filter siapa/apa/kapan.
 */
class AuditAksesController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'jenis', 'user_id', 'dari', 'sampai']);

        $log = LogAktivitas::query()
            ->with('user')
            ->where('tabel', 'dokumen_versi')
            ->whereIn('aksi', ['lihat_dokumen', 'unduh_dokumen'])
            ->when($filters['jenis'] ?? null, fn ($q, $j) => $q->where('aksi', $j === 'unduh' ? 'unduh_dokumen' : 'lihat_dokumen'))
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($filters['q'] ?? null, function ($q, $cari) {
                // Nomor/judul dokumen disimpan di kolom detail (JSON); IP juga dicari.
                $q->where(fn ($sub) => $sub
                    ->where('detail', 'like', "%{$cari}%")
                    ->orWhere('ip_address', 'like', "%{$cari}%"));
            })
            ->when($filters['dari'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['sampai'] ?? null, fn ($q, $s) => $q->whereDate('created_at', '<=', $s))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $pengguna = User::orderBy('nama')->get(['id', 'nama']);

        return view('audit-akses.index', compact('log', 'pengguna', 'filters'));
    }
}
