// Peningkatan komponen native → library profesional (offline, di-bundle Vite)
import Choices from 'choices.js';
import { Calendar } from 'vanilla-calendar-pro';

// CSS library diimpor di resources/css/app.css agar override tema menang urutan cascade.

/** Ubah semua <input type="date"> menjadi datepicker Vanilla Calendar Pro.
 *  Nilai yang dikirim ke server tetap baku Y-m-d. */
function enhanceDates(root = document) {
    root.querySelectorAll('input[type="date"]:not([data-no-enhance])').forEach((el) => {
        if (el._vcal) return;

        // Jadikan type=text agar picker bawaan browser tidak muncul ganda.
        // Nilai (Y-m-d) tetap dipertahankan saat tipe berubah.
        el.type = 'text';
        el.autocomplete = 'off';
        el.classList.add('vcal-input');

        const cal = new Calendar(el, {
            inputMode: true,
            type: 'default',
            locale: 'id',
            positionToInput: 'auto',
            firstWeekday: 1,            // Senin
            selectedTheme: 'light',
            selectedDates: el.value ? [el.value] : [],
            onClickDate(self) {
                el.value = self.context.selectedDates[0] || '';
                el.dispatchEvent(new Event('change', { bubbles: true }));
                self.hide();
            },
        });
        cal.init();
        el._vcal = cal;
    });
}

/** Ubah semua <select class="input"> menjadi Choices.js yang bisa dicari. */
function enhanceSelects(root = document) {
    root.querySelectorAll('select.input:not([data-no-enhance])').forEach((el) => {
        if (el._choices) return;

        const choices = new Choices(el, {
            searchEnabled: true,
            searchResultLimit: 50,
            searchPlaceholderValue: 'Cari…',
            itemSelectText: '',
            shouldSort: false,        // pertahankan urutan <option> dari server
            allowHTML: false,
            position: 'bottom',       // selalu buka ke bawah → konsisten di semua halaman & modal
        });
        el._choices = choices;

        // Pindahkan kelas margin Tailwind (mis. `mt-1.5`) dari <select> ke
        // wrapper `.choices`. Choices membungkus & menyembunyikan <select> asli,
        // sehingga margin yang menempel di <select> tidak lagi memosisikan
        // kontrol → kontrol jadi tidak sejajar dengan input lain di barisnya.
        const wrapper = el.closest('.choices');
        if (wrapper) {
            el.className.split(/\s+/).forEach((cls) => {
                if (/^-?m[trblxy]?-/.test(cls)) {
                    wrapper.classList.add(cls);
                    el.classList.remove(cls);
                }
            });
        }

        // Sinkronkan status disabled bila dikontrol Alpine (mis. :disabled).
        // Variabel `applied` mencegah loop: Choices.disable()/enable() ikut menulis
        // ulang atribut `disabled` pada select asli yang sedang diobservasi.
        if (el.hasAttribute(':disabled') || el.hasAttribute('x-bind:disabled')) {
            let applied = el.disabled;
            new MutationObserver(() => {
                if (el.disabled !== applied) {
                    applied = el.disabled;
                    applied ? choices.disable() : choices.enable();
                }
            }).observe(el, { attributes: true, attributeFilter: ['disabled'] });
        }
    });
}

export function enhanceUI(root = document) {
    enhanceDates(root);
    enhanceSelects(root);
}

if (document.readyState !== 'loading') {
    enhanceUI();
} else {
    document.addEventListener('DOMContentLoaded', () => enhanceUI());
}

// Diekspos agar bisa dipanggil ulang bila ada konten dinamis (mis. isi modal).
window.enhanceUI = enhanceUI;
