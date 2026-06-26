@php $klaster = $klaster ?? null; @endphp

<div class="space-y-5">
    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-4">
        <div>
            <label for="kode" class="block text-sm font-medium text-slate-700">Kode <span class="text-red-500">*</span></label>
            <input type="text" id="kode" name="kode" value="{{ old('kode', $klaster?->kode) }}"
                class="input mt-1.5 uppercase {{ $errors->has('kode') ? '!border-red-300 focus:!ring-red-200' : '' }}"
                placeholder="mis. K5" maxlength="20">
            <p class="mt-1 text-xs text-slate-400">Dipakai token <code class="rounded bg-slate-100 px-1">{KLASTER}</code>.</p>
            @error('kode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="nama" class="block text-sm font-medium text-slate-700">Nama Klaster <span class="text-red-500">*</span></label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', $klaster?->nama) }}"
                class="input mt-1.5 {{ $errors->has('nama') ? '!border-red-300 focus:!ring-red-200' : '' }}"
                placeholder="mis. Lintas Klaster">
            @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="urutan" class="block text-sm font-medium text-slate-700">Urutan</label>
            <input type="number" id="urutan" name="urutan" value="{{ old('urutan', $klaster?->urutan ?? 0) }}" min="0" max="255"
                class="input mt-1.5 {{ $errors->has('urutan') ? '!border-red-300 focus:!ring-red-200' : '' }}">
            <p class="mt-1 text-xs text-slate-400">Urutan tampil.</p>
            @error('urutan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
