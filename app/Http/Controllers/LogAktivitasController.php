<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'aksi', 'user_id', 'dari', 'sampai']);

        $log = LogAktivitas::query()
            ->with('user')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(fn ($sub) => $sub
                    ->where('aksi', 'like', "%{$q}%")
                    ->orWhere('detail', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%"));
            })
            ->when($filters['aksi'] ?? null, fn ($query, $aksi) => $query->where('aksi', $aksi))
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('user_id', $id))
            ->when($filters['dari'] ?? null, fn ($query, $d) => $query->whereDate('created_at', '>=', $d))
            ->when($filters['sampai'] ?? null, fn ($query, $s) => $query->whereDate('created_at', '<=', $s))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        // Daftar aksi unik untuk dropdown filter
        $daftarAksi = LogAktivitas::query()
            ->distinct()
            ->orderBy('aksi')
            ->pluck('aksi');

        $pengguna = User::orderBy('nama')->get(['id', 'nama']);

        return view('log-aktivitas.index', compact('log', 'daftarAksi', 'pengguna', 'filters'));
    }
}
