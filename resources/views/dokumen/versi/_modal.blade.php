<form method="POST" action="{{ route('dokumen.versi.store', $dokumen) }}" enctype="multipart/form-data"
      data-modal-form
      data-confirm="Unggah Revisi {{ $revisiBerikutnya }} untuk dokumen ini?"
      data-success="Revisi berhasil diunggah."
      x-data="{ fileName: '' }">
    @csrf

    <div class="space-y-5 px-6 py-5">
        {{-- Ringkasan revisi --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-foreground">{{ $dokumen->judul }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $dokumen->nomor_dokumen }}</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-700 ring-1 ring-inset ring-slate-500/15">Revisi {{ $dokumen->kodeRevisiTerkini() }}</span>
                @svg('heroicon-o-arrow-right', 'h-4 w-4 text-slate-300')
                <span class="inline-flex items-center rounded-full bg-gradient-to-br from-accent to-indigo px-2.5 py-1 font-semibold text-white shadow-accent">Revisi {{ $revisiBerikutnya }} (baru)</span>
            </div>
        </div>

        <div>
            <label for="tanggal_revisi" class="block text-sm font-medium text-slate-700">
                Tanggal Revisi <span class="text-red-500">*</span>
            </label>
            <input type="date" id="tanggal_revisi" name="tanggal_revisi" max="{{ date('Y-m-d') }}"
                value="{{ old('tanggal_revisi', date('Y-m-d')) }}" class="input mt-1.5">
        </div>

        <div>
            <label for="catatan_revisi" class="block text-sm font-medium text-slate-700">
                Catatan Revisi <span class="text-red-500">*</span>
            </label>
            <textarea id="catatan_revisi" name="catatan_revisi" rows="3" class="input mt-1.5"
                placeholder="Jelaskan perubahan pada versi ini, mis. pembaruan prosedur bagian 3.">{{ old('catatan_revisi') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">
                Berkas Revisi <span class="text-red-500">*</span>
            </label>
            <label for="file"
                class="mt-1.5 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 px-6 py-7 text-center transition hover:border-accent hover:bg-accent-soft/40">
                @svg('heroicon-o-arrow-up-tray', 'h-7 w-7 text-slate-400')
                <span class="text-sm text-slate-600"><span class="font-medium text-accent">Klik untuk memilih berkas</span> atau seret ke sini</span>
                <span class="text-xs text-slate-400" x-text="fileName || 'PDF, JPG, PNG · maks 10 MB'"></span>
                <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only"
                    @change="fileName = $event.target.files[0]?.name ?? ''">
            </label>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-slate-100 bg-slate-50 px-6 py-4">
        <x-button variant="outline" size="sm" data-modal-close>Batal</x-button>
        <x-button variant="primary" size="sm" type="submit">
            <x-slot:icon>@svg('heroicon-o-arrow-up-tray', 'h-5 w-5')</x-slot>
            Unggah Revisi
        </x-button>
    </div>
</form>
