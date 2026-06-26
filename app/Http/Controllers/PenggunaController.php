<?php

namespace App\Http\Controllers;

use App\Concerns\CatatAktivitas;
use App\Http\Requests\StorePenggunaRequest;
use App\Http\Requests\UpdatePenggunaRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    use CatatAktivitas;

    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'role']);

        $pengguna = User::query()
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(fn ($sub) => $sub
                    ->where('nama', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%"));
            })
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('pengguna.index', compact('pengguna', 'filters'));
    }

    public function create(Request $request): View
    {
        if ($request->ajax()) {
            return view('pengguna._modal', ['pengguna' => null]);
        }

        return view('pengguna.create');
    }

    public function store(StorePenggunaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['aktif'] = $request->boolean('aktif');

        $pengguna = User::create($data);

        $this->catatLog('tambah_pengguna', $pengguna, ['username' => $pengguna->username]);

        return redirect()->route('pengguna.index')
            ->with('success', "Pengguna {$pengguna->nama} berhasil ditambahkan.");
    }

    public function edit(Request $request, User $pengguna): View
    {
        if ($request->ajax()) {
            return view('pengguna._modal', compact('pengguna'));
        }

        return view('pengguna.edit', compact('pengguna'));
    }

    public function resetPasswordForm(User $pengguna): View
    {
        return view('pengguna._reset_modal', compact('pengguna'));
    }

    public function update(UpdatePenggunaRequest $request, User $pengguna): RedirectResponse
    {
        $data = $request->validated();
        $data['aktif'] = $request->boolean('aktif');

        // Jangan menonaktifkan diri sendiri
        if ($pengguna->is($request->user())) {
            $data['aktif'] = true;
            $data['role'] = $pengguna->role;
        }

        // Kosongkan password agar tidak menimpa bila tidak diisi
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $pengguna->update($data);

        $this->catatLog('ubah_pengguna', $pengguna, ['username' => $pengguna->username]);

        return redirect()->route('pengguna.index')
            ->with('success', "Pengguna {$pengguna->nama} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $pengguna): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], ['password' => 'kata sandi baru']);

        $pengguna->update(['password' => $validated['password']]);

        $this->catatLog('reset_password', $pengguna, ['username' => $pengguna->username]);

        return back()->with('success', "Kata sandi {$pengguna->nama} berhasil direset.");
    }

    public function toggleAktif(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $pengguna->update(['aktif' => ! $pengguna->aktif]);

        $this->catatLog('toggle_pengguna', $pengguna, ['aktif' => $pengguna->aktif]);

        $status = $pengguna->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Pengguna {$pengguna->nama} berhasil {$status}.");
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $nama = $pengguna->nama;
        $pengguna->delete();

        $this->catatLog('hapus_pengguna', $pengguna, ['nama' => $nama]);

        return redirect()->route('pengguna.index')
            ->with('success', "Pengguna {$nama} berhasil dihapus.");
    }
}
