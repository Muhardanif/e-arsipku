@php $kategori = $kategori ?? null; @endphp

<div class="space-y-5">
    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-3">
        <div>
            <label for="kode" class="block text-sm font-medium text-slate-700">Kode <span class="text-red-500">*</span></label>
            <input type="text" id="kode" name="kode" value="{{ old('kode', $kategori?->kode) }}"
                class="input mt-1.5 uppercase {{ $errors->has('kode') ? '!border-red-300 focus:!ring-red-200' : '' }}"
                placeholder="mis. SOP" maxlength="20">
            <p class="mt-1 text-xs text-slate-400">Kode singkat, otomatis huruf besar.</p>
            @error('kode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="nama" class="block text-sm font-medium text-slate-700">Nama Kategori <span class="text-red-500">*</span></label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', $kategori?->nama) }}"
                class="input mt-1.5 {{ $errors->has('nama') ? '!border-red-300 focus:!ring-red-200' : '' }}"
                placeholder="mis. Standar Operasional Prosedur">
            @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label for="keterangan" class="block text-sm font-medium text-slate-700">Keterangan</label>
        <textarea id="keterangan" name="keterangan" rows="3"
            class="input mt-1.5 {{ $errors->has('keterangan') ? '!border-red-300 focus:!ring-red-200' : '' }}"
            placeholder="Penjelasan singkat kategori (opsional)">{{ old('keterangan', $kategori?->keterangan) }}</textarea>
        @error('keterangan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Review berkala --}}
    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
        <h3 class="text-sm font-semibold text-foreground">Review Berkala</h3>
        <p class="mt-0.5 text-xs text-slate-500">
            Setiap berapa tahun dokumen kategori ini wajib ditinjau ulang. Sistem menghitung
            jatuh tempo otomatis dari tanggal dokumen (atau tanggal revisi/review terakhir).
            Kosongkan bila kategori ini tidak perlu ditinjau berkala.
        </p>
        <div class="mt-3 max-w-xs">
            <label for="periode_review_tahun" class="block text-sm font-medium text-slate-700">Periode Review</label>
            <div class="mt-1.5 flex items-center gap-2">
                <input type="number" id="periode_review_tahun" name="periode_review_tahun" min="1" max="20"
                    value="{{ old('periode_review_tahun', $kategori?->periode_review_tahun) }}"
                    class="input {{ $errors->has('periode_review_tahun') ? '!border-red-300 focus:!ring-red-200' : '' }}"
                    placeholder="mis. 3">
                <span class="text-sm text-slate-500">tahun sekali</span>
            </div>
            @error('periode_review_tahun') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Format penomoran otomatis --}}
    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4"
         x-data="{
            f: {{ Illuminate\Support\Js::from(old('format_nomor', $kategori?->format_nomor ?? '')) }},
            d: {{ Illuminate\Support\Js::from((int) old('digit_nomor', $kategori?->digit_nomor ?? 3)) }},
            get contoh() {
                if (! this.f) return '— (penomoran manual)';
                const th = new Date().getFullYear();
                return this.f
                    .replaceAll('{NO}', String(1).padStart(Math.max(this.d || 1, 1), '0'))
                    .replaceAll('{TAHUN2}', String(th).slice(-2))
                    .replaceAll('{TAHUN}', String(th));
            }
         }">
        <h3 class="text-sm font-semibold text-foreground">Format Nomor Otomatis</h3>
        <p class="mt-0.5 text-xs text-slate-500">
            Token: <code class="rounded bg-white px-1 ring-1 ring-slate-200">{NO}</code> nomor urut,
            <code class="rounded bg-white px-1 ring-1 ring-slate-200">{TAHUN}</code> tahun 4 digit,
            <code class="rounded bg-white px-1 ring-1 ring-slate-200">{TAHUN2}</code> 2 digit.
            Nomor urut otomatis reset tiap tahun. Kosongkan bila kategori ini hanya memakai input manual.
        </p>

        <div class="mt-3 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label for="format_nomor" class="block text-sm font-medium text-slate-700">Template</label>
                <input type="text" id="format_nomor" name="format_nomor" x-model="f"
                    class="input mt-1.5 font-mono text-[13px] {{ $errors->has('format_nomor') ? '!border-red-300 focus:!ring-red-200' : '' }}"
                    placeholder="445/{NO}/SOP/K3/437.52.27/{TAHUN}">
                @error('format_nomor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="digit_nomor" class="block text-sm font-medium text-slate-700">Jumlah Digit</label>
                <input type="number" id="digit_nomor" name="digit_nomor" x-model.number="d" min="1" max="6"
                    class="input mt-1.5 {{ $errors->has('digit_nomor') ? '!border-red-300 focus:!ring-red-200' : '' }}">
                <p class="mt-1 text-xs text-slate-400">mis. 3 → 001</p>
                @error('digit_nomor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <p class="mt-3 text-xs text-slate-500">
            Contoh hasil: <span class="font-mono font-semibold text-accent" x-text="contoh"></span>
        </p>
    </div>
</div>
