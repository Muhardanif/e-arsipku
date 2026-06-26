@php $inputClass = 'input mt-1.5'; @endphp

<form method="POST" action="{{ route('peminjaman.store') }}"
      data-modal-form
      data-confirm="Catat peminjaman dokumen ini?"
      data-success="Peminjaman berhasil dicatat."
      x-data="{ mode: '{{ old('peminjam_nama') ? 'manual' : 'pengguna' }}' }">
    @csrf

    <div class="space-y-5 px-6 py-5">
        <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
            {{-- Dokumen --}}
            <div class="md:col-span-2">
                <label for="dokumen_id" class="block text-sm font-medium text-slate-700">Dokumen <span class="text-red-500">*</span></label>
                <select id="dokumen_id" name="dokumen_id" class="{{ $inputClass }}">
                    <option value="">— Pilih dokumen —</option>
                    @foreach ($dokumen as $d)
                        <option value="{{ $d->id }}" @selected((string) old('dokumen_id', $dokumenTerpilih) === (string) $d->id)>
                            {{ $d->nomor_dokumen }} — {{ $d->judul }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Peminjam: toggle --}}
            <div class="md:col-span-2">
                <span class="block text-sm font-medium text-slate-700">Peminjam <span class="text-red-500">*</span></span>
                <div class="mt-2 inline-flex rounded-lg border border-border-default bg-slate-100 p-1 text-sm">
                    <button type="button" @click="mode = 'pengguna'"
                        :class="mode === 'pengguna' ? 'bg-white text-foreground shadow-xs ring-1 ring-border-default' : 'text-slate-500'"
                        class="rounded-md px-3 py-1.5 font-semibold transition">Dari Pengguna</button>
                    <button type="button" @click="mode = 'manual'"
                        :class="mode === 'manual' ? 'bg-white text-foreground shadow-xs ring-1 ring-border-default' : 'text-slate-500'"
                        class="rounded-md px-3 py-1.5 font-semibold transition">Input Manual</button>
                </div>

                <div x-show="mode === 'pengguna'" class="mt-2">
                    <select name="peminjam_id" :disabled="mode !== 'pengguna'" class="{{ $inputClass }}">
                        <option value="">— Pilih pengguna —</option>
                        @foreach ($pengguna as $u)
                            <option value="{{ $u->id }}" @selected((string) old('peminjam_id') === (string) $u->id)>{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="mode === 'manual'" x-cloak class="mt-2">
                    <input type="text" name="peminjam_nama" value="{{ old('peminjam_nama') }}" :disabled="mode !== 'manual'"
                        placeholder="Nama peminjam" class="{{ $inputClass }}">
                </div>
            </div>

            {{-- Tujuan --}}
            <div class="md:col-span-2">
                <label for="tujuan" class="block text-sm font-medium text-slate-700">Tujuan Peminjaman <span class="text-red-500">*</span></label>
                <input type="text" id="tujuan" name="tujuan" value="{{ old('tujuan') }}" class="{{ $inputClass }}"
                    placeholder="mis. Keperluan audit internal">
            </div>

            {{-- Tanggal pinjam --}}
            <div>
                <label for="tanggal_pinjam" class="block text-sm font-medium text-slate-700">Tanggal Pinjam <span class="text-red-500">*</span></label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" class="{{ $inputClass }}">
            </div>

            {{-- Rencana kembali --}}
            <div>
                <label for="tanggal_kembali_rencana" class="block text-sm font-medium text-slate-700">Rencana Kembali <span class="text-red-500">*</span></label>
                <input type="date" id="tanggal_kembali_rencana" name="tanggal_kembali_rencana" value="{{ old('tanggal_kembali_rencana') }}" class="{{ $inputClass }}">
            </div>

            {{-- Keterangan --}}
            <div class="md:col-span-2">
                <label for="keterangan" class="block text-sm font-medium text-slate-700">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="2" class="{{ $inputClass }}"
                    placeholder="Catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-slate-100 bg-slate-50 px-6 py-4">
        <x-button variant="outline" size="sm" data-modal-close>Batal</x-button>
        <x-button variant="primary" size="sm" type="submit">
            <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
            Simpan Peminjaman
        </x-button>
    </div>
</form>
