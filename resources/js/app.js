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

Alpine.start();

// Peningkatan komponen UI (datepicker, dropdown) → library profesional
import './enhance';
// Grafik dashboard (ApexCharts)
import './charts';
// Polesan: loading bar, tooltip, toast
import './ui';
// Modal global (gaya Bootstrap) + konfirmasi aksi mutasi
import './modal';
