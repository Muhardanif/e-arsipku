@php
    $dokumen = $dokumen ?? null;
    $isEdit = (bool) $dokumen;
    $formatKategori = $formatKategori ?? [];
    $klaster = $klaster ?? collect();
    $val = fn ($field, $default = '') => old($field, $dokumen?->{$field} instanceof \Illuminate\Support\Carbon
        ? $dokumen->{$field}->format('Y-m-d')
        : ($dokumen?->{$field} ?? $default));
    $inputClass = 'input mt-1.5';
    $errBorder = '!border-red-300 focus:!border-red-400 focus:!ring-red-200';
    $labelClass = 'block text-sm font-medium text-slate-700';

    // Konfigurasi state Alpine untuk penomoran (khusus form tambah).
    $configNomor = [
        'formats' => $formatKategori,
        'klasters' => $klaster->pluck('kode', 'id'),
        'tahunIni' => (int) now()->year,
        'modeAwal' => old('mode_nomor', 'otomatis'),
        'kategoriAwal' => (string) $val('kategori_id'),
        'klasterAwal' => (string) old('klaster_id'),
        'tanggalAwal' => (string) $val('tanggal_dokumen'),
        'nomorAwal' => (string) old('nomor_dokumen'),
    ];
@endphp

<div class="space-y-7"
    @unless ($isEdit) x-data="dokumenNomor({{ Illuminate\Support\Js::from($configNomor) }})" @endunless>

    {{-- ══ Seksi 1: Klasifikasi & Penomoran ══════════════════════════ --}}
    <section class="space-y-4">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Klasifikasi &amp; Penomoran</p>

        <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
            {{-- Kategori --}}
            <div>
                <label for="kategori_id" class="{{ $labelClass }}">Kategori <span class="text-red-500">*</span></label>
                <select id="kategori_id" name="kategori_id"
                    @unless ($isEdit) @change="onKategoriChange($event)" @endunless
                    class="{{ $inputClass }} {{ $errors->has('kategori_id') ? $errBorder : '' }}">
                    <option value="">— Pilih kategori —</option>
                    @foreach ($kategori as $k)
                        <option value="{{ $k->id }}" @selected((string) $val('kategori_id') === (string) $k->id)>{{ $k->kode }} — {{ $k->nama }}</option>
                    @endforeach
                </select>
                @error('kategori_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="{{ $labelClass }}">Status <span class="text-red-500">*</span></label>
                @if ($isEdit && $dokumen->status === 'draf')
                    {{-- Status draf terkunci: hanya bisa diubah lewat aksi "Terbitkan". --}}
                    <div class="mt-1.5 flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                        <x-badge status="draf" />
                        <span class="text-xs text-slate-500">Terbitkan dokumen untuk mengubah status.</span>
                    </div>
                    <input type="hidden" name="status" value="draf">
                @else
                    <select id="status" name="status" class="{{ $inputClass }} {{ $errors->has('status') ? $errBorder : '' }}">
                        @foreach (['berlaku' => 'Berlaku', 'kadaluarsa' => 'Kadaluarsa', 'dicabut' => 'Dicabut'] as $value => $label)
                            <option value="{{ $value }}" @selected($val('status', 'berlaku') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @unless ($isEdit)
                        <p class="mt-1 text-xs text-slate-400">Berlaku saat berkas diunggah. Tanpa berkas, dokumen tersimpan sebagai <span class="font-medium text-amber-600">Draf</span>.</p>
                    @endunless
                @endif
                @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Panel Nomor Dokumen (full width) --}}
        @if ($isEdit)
            <div>
                <label for="nomor_dokumen" class="{{ $labelClass }}">Nomor Dokumen <span class="text-red-500">*</span></label>
                <input type="text" id="nomor_dokumen" name="nomor_dokumen" value="{{ $val('nomor_dokumen') }}"
                    class="{{ $inputClass }} font-mono {{ $errors->has('nomor_dokumen') ? $errBorder : '' }}"
                    placeholder="mis. SOP/UKP/001/2024">
                <p class="mt-1 text-xs text-slate-400">Ikuti konvensi puskesmas, contoh: SOP/UKP/001/2024</p>
                @error('nomor_dokumen') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @else
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="flex items-center gap-1.5 text-sm font-semibold text-foreground">
                            @svg('heroicon-o-hashtag', 'h-4 w-4 text-accent')
                            Nomor Dokumen <span class="text-red-500">*</span>
                        </p>
                        <p class="mt-0.5 text-xs text-slate-500">Dibuat otomatis dari format kategori, atau isi manual.</p>
                    </div>

                    {{-- Segmented toggle --}}
                    <div class="inline-flex shrink-0 rounded-lg border border-slate-200 bg-white p-0.5 text-xs font-semibold shadow-xs">
                        <button type="button" @click="adaFormat && (mode = 'otomatis')" :disabled="! adaFormat"
                            :class="mode === 'otomatis' ? 'bg-accent text-white shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                            class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 transition disabled:cursor-not-allowed disabled:opacity-40">
                            @svg('heroicon-o-bolt', 'h-4 w-4')
                            Otomatis
                        </button>
                        <button type="button" @click="mode = 'manual'"
                            :class="mode === 'manual' ? 'bg-accent text-white shadow-xs' : 'text-slate-500 hover:text-slate-700'"
                            class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 transition">
                            @svg('heroicon-o-pencil', 'h-4 w-4')
                            Manual
                        </button>
                    </div>
                </div>

                <input type="hidden" name="mode_nomor" :value="mode">

                {{-- Body: Otomatis --}}
                <div x-show="mode === 'otomatis'" x-cloak class="mt-4 space-y-3">
                    {{-- Klaster (kategori yang templatenya memuat {KLASTER}) --}}
                    <div x-show="pakaiKlaster" x-cloak>
                        <label for="klaster_id" class="block text-xs font-medium text-slate-600">Klaster <span class="text-red-500">*</span></label>
                        <select id="klaster_id" name="klaster_id" @change="onKlasterChange($event)"
                            :disabled="! (mode === 'otomatis' && pakaiKlaster)"
                            class="input mt-1 {{ $errors->has('klaster_id') ? $errBorder : '' }}">
                            <option value="">— Pilih klaster —</option>
                            @foreach ($klaster as $kl)
                                <option value="{{ $kl->id }}" @selected((string) old('klaster_id') === (string) $kl->id)>{{ $kl->kode }} — {{ $kl->nama }}</option>
                            @endforeach
                        </select>
                        @error('klaster_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Pratinjau nomor --}}
                    <template x-if="adaFormat">
                        <div class="rounded-lg border border-accent/20 bg-accent-soft/50 px-4 py-3">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-accent/80">Pratinjau Nomor</p>
                            <p class="mt-1 font-mono text-base font-semibold text-foreground" x-text="preview || 'Pilih klaster terlebih dahulu…'"></p>
                            <p class="mt-1.5 flex items-center gap-1 text-[11px] text-slate-500">
                                @svg('heroicon-o-information-circle', 'h-3.5 w-3.5 shrink-0')
                                Nomor urut final ditetapkan saat disimpan · tahun mengikuti Tanggal Dokumen.
                            </p>
                        </div>
                    </template>
                    <template x-if="! adaFormat">
                        <div class="flex items-start gap-2 rounded-lg bg-amber-50 px-4 py-3 text-xs text-amber-700 ring-1 ring-inset ring-amber-600/20">
                            @svg('heroicon-o-exclamation-triangle', 'h-4 w-4 shrink-0')
                            <span>Kategori ini belum punya format penomoran. Gunakan mode <span class="font-semibold">Manual</span>, atau atur format di Master ▸ Kategori.</span>
                        </div>
                    </template>
                </div>

                {{-- Body: Manual --}}
                <div x-show="mode === 'manual'" x-cloak class="mt-4">
                    <input type="text" id="nomor_dokumen" name="nomor_dokumen" x-model="nomorManual" :disabled="mode !== 'manual'"
                        aria-label="Nomor dokumen"
                        class="input font-mono {{ $errors->has('nomor_dokumen') ? $errBorder : '' }}"
                        placeholder="mis. 445/001/SOP/K3/437.52.27/2025">
                    <p class="mt-1.5 flex items-center gap-1 text-[11px] text-slate-500">
                        @svg('heroicon-o-information-circle', 'h-3.5 w-3.5 shrink-0')
                        Untuk dokumen lama / format berbeda — tidak memakai nomor urut berjalan.
                    </p>
                </div>

                @error('nomor_dokumen') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif
    </section>

    {{-- ══ Seksi 2: Identitas Dokumen ════════════════════════════════ --}}
    <section class="space-y-4 border-t border-slate-100 pt-6">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Identitas Dokumen</p>

        <div>
            <label for="judul" class="{{ $labelClass }}">Judul Dokumen <span class="text-red-500">*</span></label>
            <input type="text" id="judul" name="judul" value="{{ $val('judul') }}"
                class="{{ $inputClass }} {{ $errors->has('judul') ? $errBorder : '' }}"
                placeholder="mis. SOP Pelayanan Pendaftaran Pasien">
            @error('judul') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="deskripsi" class="{{ $labelClass }}">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="3"
                class="{{ $inputClass }} {{ $errors->has('deskripsi') ? $errBorder : '' }}"
                placeholder="Ringkasan isi atau tujuan dokumen (opsional)">{{ $val('deskripsi') }}</textarea>
            @error('deskripsi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </section>

    {{-- ══ Seksi 3: Tanggal & Pengesahan ═════════════════════════════ --}}
    <section class="space-y-4 border-t border-slate-100 pt-6">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Tanggal &amp; Pengesahan</p>

        <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
            {{-- Tanggal dokumen --}}
            <div>
                <label for="tanggal_dokumen" class="{{ $labelClass }}">Tanggal Dokumen <span class="text-red-500">*</span></label>
                <input type="date" id="tanggal_dokumen" name="tanggal_dokumen" value="{{ $val('tanggal_dokumen') }}"
                    @unless ($isEdit) @change="onTanggalChange($event)" @endunless
                    class="{{ $inputClass }} {{ $errors->has('tanggal_dokumen') ? $errBorder : '' }}">
                @error('tanggal_dokumen') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Pengesah --}}
            <div>
                <label for="pengesah" class="{{ $labelClass }}">Pengesah</label>
                <input type="text" id="pengesah" name="pengesah" value="{{ $val('pengesah') }}"
                    class="{{ $inputClass }} {{ $errors->has('pengesah') ? $errBorder : '' }}"
                    placeholder="mis. Kepala Puskesmas">
                @error('pengesah') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Tanggal berlaku --}}
            <div>
                <label for="tanggal_berlaku" class="{{ $labelClass }}">Tanggal Berlaku</label>
                <input type="date" id="tanggal_berlaku" name="tanggal_berlaku" value="{{ $val('tanggal_berlaku') }}"
                    class="{{ $inputClass }} {{ $errors->has('tanggal_berlaku') ? $errBorder : '' }}">
                @error('tanggal_berlaku') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Tanggal berakhir --}}
            <div>
                <label for="tanggal_berakhir" class="{{ $labelClass }}">Tanggal Berakhir</label>
                <input type="date" id="tanggal_berakhir" name="tanggal_berakhir" value="{{ $val('tanggal_berakhir') }}"
                    class="{{ $inputClass }} {{ $errors->has('tanggal_berakhir') ? $errBorder : '' }}">
                <p class="mt-1 text-xs text-slate-400">Kosongkan bila tidak ada masa berakhir.</p>
                @error('tanggal_berakhir') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>
</div>
