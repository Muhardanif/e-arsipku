import './bootstrap';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);
window.Alpine = Alpine;

// Komponen penomoran dokumen (form tambah dokumen) — didaftarkan global
// agar tetap berfungsi saat form dimuat via AJAX ke dalam modal.
Alpine.data('dokumenNomor', (cfg = {}) => ({
    mode: cfg.modeAwal || 'otomatis',
    kategoriId: cfg.kategoriAwal || '',
    klasterId: cfg.klasterAwal || '',
    tanggal: cfg.tanggalAwal || '',
    nomorManual: cfg.nomorAwal || '',
    formats: cfg.formats || {},
    klasters: cfg.klasters || {}, // { id: kode }
    tahunIni: cfg.tahunIni,

    init() {
        // Bila kategori awal tak punya format, paksa mode manual.
        if (!this.adaFormat) this.mode = 'manual';

        // Saran metadata AI: bila nomor terdeteksi & belum diisi, pakai mode
        // manual dengan nomor tersebut (dokumen yang sudah punya nomor resmi).
        window.addEventListener('ai-metadata', (e) => {
            const d = e.detail || {};
            if (d.nomor_dokumen && !this.nomorManual) {
                this.mode = 'manual';
                this.nomorManual = d.nomor_dokumen;
            }
        });
    },
    get fmt() {
        return this.formats[this.kategoriId] || null;
    },
    get adaFormat() {
        return !!(this.fmt && this.fmt.format);
    },
    get pakaiKlaster() {
        return !!(this.fmt && this.fmt.useKlaster);
    },
    get tahun() {
        const t = String(this.tanggal || '').slice(0, 4);
        return /^\d{4}$/.test(t) ? parseInt(t, 10) : this.tahunIni;
    },
    get preview() {
        if (!this.adaFormat) return '';

        let seq;
        if (this.pakaiKlaster) {
            if (!this.klasterId) return ''; // klaster belum dipilih
            const next = (this.fmt.nextKlaster || {})[this.klasterId];
            seq = (this.tahun === this.tahunIni && next) ? next : 1;
        } else {
            seq = (this.tahun === this.tahunIni && this.fmt.next) ? this.fmt.next : 1;
        }

        const no = String(seq).padStart(Math.max(this.fmt.digit || 3, 1), '0');
        return this.fmt.format
            .replaceAll('{NO}', no)
            .replaceAll('{TAHUN2}', String(this.tahun).slice(-2))
            .replaceAll('{TAHUN}', String(this.tahun))
            .replaceAll('{KLASTER}', this.klasters[this.klasterId] || '');
    },
    onKategoriChange(e) {
        this.kategoriId = e.target.value;
        if (!this.adaFormat) this.mode = 'manual';
    },
    onKlasterChange(e) {
        this.klasterId = e.target.value;
    },
    onTanggalChange(e) {
        this.tanggal = e.target.value;
    },
}));

// Tombol "Isi otomatis (AI)" pada form tambah dokumen. Mengunggah berkas ke
// endpoint saran-metadata, lalu mengisi field yang MASIH KOSONG (tidak menimpa
// input petugas). Nomor & kategori diserahkan ke komponen penomoran via event.
Alpine.data('saranMetadata', (cfg = {}) => ({
    loading: false,
    pesan: '',
    ok: false,

    async jalankan() {
        const input = document.getElementById('file');
        const file = input?.files?.[0];

        if (!file) {
            this.ok = false;
            this.pesan = 'Pilih berkas terlebih dahulu.';
            return;
        }

        this.loading = true;
        this.pesan = '';

        try {
            const fd = new FormData();
            fd.append('file', file);

            const res = await fetch(cfg.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': cfg.token,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: fd,
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                this.ok = false;
                this.pesan = data.message || 'Gagal membaca dokumen.';
                return;
            }

            const jml = this.isiForm(data.saran || {});
            this.ok = true;
            this.pesan = jml > 0
                ? `Terisi ${jml} field dari dokumen. Periksa & sesuaikan sebelum menyimpan.`
                : 'Tidak ada data baru yang bisa diisi (field mungkin sudah terisi).';
        } catch (e) {
            this.ok = false;
            this.pesan = 'Terjadi kesalahan saat menghubungi layanan AI.';
        } finally {
            this.loading = false;
        }
    },

    // Isi hanya field kosong; kembalikan jumlah field yang terisi.
    isiForm(s) {
        let n = 0;

        const isiKosong = (id, val) => {
            if (!val) return;
            const el = document.getElementById(id);
            if (el && !el.value) {
                el.value = val;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
                n++;
            }
        };

        // Kategori memicu @change (onKategoriChange) di komponen penomoran.
        isiKosong('kategori_id', s.kategori_id);
        isiKosong('judul', s.judul);
        isiKosong('deskripsi', s.deskripsi);
        isiKosong('tanggal_dokumen', s.tanggal_dokumen);
        isiKosong('tanggal_berlaku', s.tanggal_berlaku);
        isiKosong('tanggal_berakhir', s.tanggal_berakhir);
        isiKosong('pengesah', s.pengesah);

        // Nomor ditangani komponen penomoran (agar tak melawan mode otomatis).
        const nomorEl = document.getElementById('nomor_dokumen');
        if (s.nomor_dokumen && (!nomorEl || !nomorEl.value)) {
            window.dispatchEvent(new CustomEvent('ai-metadata', { detail: s }));
            n++;
        }

        return n;
    },
}));

Alpine.start();

// Peningkatan komponen UI (datepicker, dropdown) → library profesional
import './enhance';
// Grafik dashboard (ApexCharts)
import './charts';
// Polesan: loading bar, tooltip, toast
import './ui';
// Modal global (gaya Bootstrap) + konfirmasi aksi mutasi
import './modal';
