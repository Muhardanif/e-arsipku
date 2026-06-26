@php $isEdit = (bool) ($dokumen ?? null); @endphp

<form method="POST"
      action="{{ $isEdit ? route('dokumen.update', $dokumen) : route('dokumen.store') }}"
      enctype="multipart/form-data"
      data-modal-form
      data-confirm="{{ $isEdit ? 'Simpan perubahan dokumen ini?' : 'Simpan dokumen baru ini?' }}"
      data-success="{{ $isEdit ? 'Dokumen berhasil diperbarui.' : 'Dokumen berhasil ditambahkan.' }}"
      x-data="{ fileName: '' }">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="space-y-6 px-6 py-5">
        @include('dokumen._form', ['dokumen' => $dokumen ?? null])

        @unless ($isEdit)
            <div class="space-y-4 border-t border-slate-100 pt-6">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Berkas Versi Awal</p>

                <div class="flex items-start gap-2 rounded-lg bg-amber-50 px-3.5 py-2.5 text-xs text-amber-700 ring-1 ring-inset ring-amber-600/20">
                    @svg('heroicon-o-information-circle', 'h-4 w-4 shrink-0')
                    <span>Kosongkan berkas untuk menyimpan sebagai <span class="font-semibold">Draf</span> — nomor tetap dipesan, unggah berkas final nanti lewat <span class="font-semibold">Terbitkan</span>.</span>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Berkas Dokumen <span class="text-slate-400">(opsional)</span></label>
                        <label for="file"
                            class="group mt-1.5 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 px-6 py-7 text-center transition hover:border-accent hover:bg-accent-soft/40"
                            :class="fileName && '!border-accent bg-accent-soft/40'">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition group-hover:bg-accent-soft group-hover:text-accent"
                                  :class="fileName && '!bg-accent-soft !text-accent'">
                                <span x-show="!fileName">@svg('heroicon-o-arrow-up-tray', 'h-6 w-6')</span>
                                <span x-show="fileName" x-cloak>@svg('heroicon-o-document-check', 'h-6 w-6')</span>
                            </span>
                            <span class="text-sm text-slate-600" x-show="!fileName">
                                <span class="font-semibold text-accent">Klik untuk memilih berkas</span> atau seret ke sini
                            </span>
                            <span class="text-sm font-medium text-foreground" x-show="fileName" x-cloak x-text="fileName"></span>
                            <span class="text-xs text-slate-400">PDF, JPG, atau PNG · maksimal 10 MB</span>
                            <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only"
                                @change="fileName = $event.target.files[0]?.name ?? ''">
                        </label>
                    </div>

                    <div>
                        <label for="catatan_revisi" class="block text-sm font-medium text-slate-700">Catatan Versi</label>
                        <input type="text" id="catatan_revisi" name="catatan_revisi" value="{{ old('catatan_revisi') }}"
                            class="input mt-1.5" placeholder="mis. Versi awal dokumen">
                    </div>
                </div>
            </div>
        @else
            <p class="rounded-lg bg-slate-50 px-3.5 py-2.5 text-xs text-slate-500">
                Berkas tidak diubah di sini — gunakan menu <span class="font-medium text-slate-700">Upload Revisi</span> pada halaman detail untuk mengunggah versi baru.
            </p>
        @endunless
    </div>

    <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-slate-100 bg-slate-50 px-6 py-4">
        <x-button variant="outline" size="sm" data-modal-close>Batal</x-button>
        <x-button variant="primary" size="sm" type="submit">
            <x-slot:icon>@svg('heroicon-o-check', 'h-5 w-5')</x-slot>
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Dokumen' }}
        </x-button>
    </div>
</form>
