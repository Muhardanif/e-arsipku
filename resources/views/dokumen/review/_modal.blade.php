<form method="POST" action="{{ route('dokumen.review.store', $dokumen) }}"
      data-modal-form
      data-confirm="Catat review untuk dokumen ini?"
      data-success="Review berhasil dicatat.">
    @csrf

    <div class="space-y-5 px-6 py-5">
        {{-- Ringkasan dokumen --}}
        <div class="rounded-xl bg-slate-50 px-4 py-3">
            <p class="text-sm font-semibold text-foreground">{{ $dokumen->judul }}</p>
            <p class="mt-0.5 text-xs text-slate-500">{{ $dokumen->nomor_dokumen }}</p>
            @if ($jt = $dokumen->jatuhTempoReview())
                <p class="mt-2 text-xs text-slate-500">
                    Jatuh tempo saat ini: <span class="font-medium text-foreground">{{ $jt->translatedFormat('d F Y') }}</span>
                    @if ($dokumen->kategori?->periode_review_tahun)
                        · periode {{ $dokumen->kategori->periode_review_tahun }} tahun
                    @endif
                </p>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="tanggal_review" class="block text-sm font-medium text-slate-700">
                    Tanggal Review <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_review" name="tanggal_review"
                    value="{{ old('tanggal_review', now()->toDateString()) }}" class="input mt-1.5">
            </div>
            <div>
                <label for="hasil" class="block text-sm font-medium text-slate-700">
                    Hasil Review <span class="text-red-500">*</span>
                </label>
                <select id="hasil" name="hasil" class="input mt-1.5">
                    <option value="sesuai" @selected(old('hasil') === 'sesuai')>Masih sesuai (tidak ada perubahan)</option>
                    <option value="perlu_revisi" @selected(old('hasil') === 'perlu_revisi')>Perlu revisi</option>
                </select>
            </div>
        </div>

        <div>
            <label for="catatan" class="block text-sm font-medium text-slate-700">Catatan</label>
            <textarea id="catatan" name="catatan" rows="3" class="input mt-1.5"
                placeholder="Catatan hasil peninjauan (opsional).">{{ old('catatan') }}</textarea>
        </div>

        <p class="flex items-start gap-2 rounded-lg bg-accent-soft/60 px-3.5 py-2.5 text-xs text-slate-600">
            @svg('heroicon-o-information-circle', 'h-4 w-4 shrink-0 text-accent')
            Setelah dicatat, jatuh tempo review berikutnya dihitung dari tanggal review ini.
        </p>
    </div>

    <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-slate-100 bg-slate-50 px-6 py-4">
        <x-button variant="outline" size="sm" data-modal-close>Batal</x-button>
        <x-button variant="primary" size="sm" type="submit">
            <x-slot:icon>@svg('heroicon-o-check-circle', 'h-5 w-5')</x-slot>
            Simpan Review
        </x-button>
    </div>
</form>
