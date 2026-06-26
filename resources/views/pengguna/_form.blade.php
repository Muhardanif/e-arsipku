@php
    $pengguna = $pengguna ?? null;
    $isEdit = (bool) $pengguna;
@endphp

<div x-data="{ show: false }" class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
    {{-- Nama --}}
    <div>
        <label for="nama" class="block text-sm font-medium text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
        <input type="text" id="nama" name="nama" value="{{ old('nama', $pengguna?->nama) }}"
            class="input mt-1.5 {{ $errors->has('nama') ? '!border-red-300 focus:!ring-red-200' : '' }}"
            placeholder="mis. Budi Santoso">
        @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Username --}}
    <div>
        <label for="username" class="block text-sm font-medium text-slate-700">Nama Pengguna <span class="text-red-500">*</span></label>
        <input type="text" id="username" name="username" value="{{ old('username', $pengguna?->username) }}"
            class="input mt-1.5 {{ $errors->has('username') ? '!border-red-300 focus:!ring-red-200' : '' }}"
            placeholder="mis. budi" autocomplete="off">
        <p class="mt-1 text-xs text-slate-400">Huruf, angka, garis bawah, dan strip.</p>
        @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Password --}}
    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">
            Kata Sandi @if (! $isEdit)<span class="text-red-500">*</span>@endif
        </label>
        <div class="relative mt-1.5">
            <input id="password" name="password" :type="show ? 'text' : 'password'"
                class="input pr-11 {{ $errors->has('password') ? '!border-red-300 focus:!ring-red-200' : '' }}"
                placeholder="{{ $isEdit ? 'Kosongkan bila tidak diubah' : 'Minimal 6 karakter' }}" autocomplete="new-password">
            <button type="button" @click="show = !show"
                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-400 transition hover:text-slate-600">
                @svg('heroicon-o-eye', 'h-5 w-5', ['x-show' => '!show'])
                @svg('heroicon-o-eye-slash', 'h-5 w-5', ['x-show' => 'show', 'x-cloak' => true])
            </button>
        </div>
        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Konfirmasi password --}}
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
            class="input mt-1.5" placeholder="Ulangi kata sandi" autocomplete="new-password">
    </div>

    {{-- Role --}}
    <div>
        <label for="role" class="block text-sm font-medium text-slate-700">Role <span class="text-red-500">*</span></label>
        <select id="role" name="role" class="input mt-1.5 {{ $errors->has('role') ? '!border-red-300 focus:!ring-red-200' : '' }}">
            @foreach (['admin' => 'Administrator', 'petugas' => 'Petugas', 'staf' => 'Staf'] as $v => $l)
                <option value="{{ $v }}" @selected(old('role', $pengguna?->role ?? 'staf') === $v)>{{ $l }}</option>
            @endforeach
        </select>
        @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Jabatan --}}
    <div>
        <label for="jabatan" class="block text-sm font-medium text-slate-700">Jabatan</label>
        <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $pengguna?->jabatan) }}"
            class="input mt-1.5 {{ $errors->has('jabatan') ? '!border-red-300 focus:!ring-red-200' : '' }}"
            placeholder="mis. Staf Tata Usaha">
        @error('jabatan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Aktif --}}
    <div class="md:col-span-2">
        <label class="flex items-center gap-3 rounded-lg border border-border-default bg-slate-50/60 px-4 py-3">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $pengguna?->aktif ?? true))
                class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent/40">
            <span>
                <span class="block text-sm font-medium text-slate-700">Akun Aktif</span>
                <span class="block text-xs text-slate-500">Pengguna nonaktif tidak dapat masuk ke sistem.</span>
            </span>
        </label>
    </div>
</div>
