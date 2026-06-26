<form method="POST" action="{{ route('dokumen.terbitkan', $dokumen) }}" enctype="multipart/form-data"
      data-modal-form
      data-confirm="Terbitkan dokumen {{ $dokumen->nomor_dokumen }} dengan berkas ini?"
      data-success="Dokumen berhasil diterbitkan."
      x-data="{ fileName: '' }">
    @csrf

    <div class="space-y-5 px-6 py-5">
        {{-- Ringkasan dokumen --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-foreground">{{ $dokumen->judul }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $dokumen->nomor_dokumen }}</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <x-badge status="draf" />
                @svg('heroicon-o-arrow-right', 'h-4 w-4 text-slate-300')
                <x-badge status="berlaku" />
            </div>
        </div>

        <p class="flex items-start gap-2 rounded-lg bg-accent-soft/50 px-3.5 py-2.5 text-xs text-slate-600 ring-1 ring-inset ring-accent/20">
            @svg('heroicon-o-information-circle', 'h-4 w-4 shrink-0 text-accent')
            <span>Unggah berkas final yang sudah ditandatangani. Berkas ini menjadi <span class="font-semibold">versi 1</span> dan status dokumen berubah menjadi <span class="font-semibold">Berlaku</span>.</span>
        </p>

        <div>
            <label class="block text-sm font-medium text-slate-700">
                Berkas Final <span class="text-red-500">*</span>
            </label>
            <label for="file"
                class="mt-1.5 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 px-6 py-7 text-center transition hover:border-accent hover:bg-accent-soft/40">
                @svg('heroicon-o-arrow-up-tray', 'h-7 w-7 text-slate-400')
                <span class="text-sm text-slate-600"><span class="font-medium text-accent">Klik untuk memilih berkas</span> atau seret ke sini</span>
                <span class="text-xs text-slate-400" x-text="fileName || 'PDF, JPG, PNG · maks 10 MB'"></span>
                <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only"
                    @change="fileName = $event.target.files[0]?.name ?? ''">
            </label>
            @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="catatan_revisi" class="block text-sm font-medium text-slate-700">Catatan <span class="text-slate-400">(opsional)</span></label>
            <input type="text" id="catatan_revisi" name="catatan_revisi" value="{{ old('catatan_revisi') }}"
                class="input mt-1.5" placeholder="mis. Berkas final disahkan Kepala Puskesmas">
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-slate-100 bg-slate-50 px-6 py-4">
        <x-button variant="outline" size="sm" data-modal-close>Batal</x-button>
        <x-button variant="primary" size="sm" type="submit">
            <x-slot:icon>@svg('heroicon-o-paper-airplane', 'h-5 w-5')</x-slot>
            Terbitkan Dokumen
        </x-button>
    </div>
</form>
