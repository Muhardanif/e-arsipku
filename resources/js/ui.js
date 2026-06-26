// Polesan UI: loading bar (NProgress), tooltip (Tippy), notifikasi (SweetAlert2)
import NProgress from 'nprogress';
import tippy from 'tippy.js';
import Swal from 'sweetalert2';

// CSS NProgress & Tippy diimpor di resources/css/app.css (lihat sana).

/* ── NProgress: bar tipis saat berpindah halaman ─────────────────── */
NProgress.configure({ showSpinner: false, trickleSpeed: 120, minimum: 0.12 });
window.NProgress = NProgress; // dipakai modal.js
window.addEventListener('load', () => NProgress.done());
window.addEventListener('pageshow', () => NProgress.done());

document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    const url = new URL(a.href, location.href);
    const lewati = a.target === '_blank' || a.hasAttribute('download')
        || url.origin !== location.origin || a.getAttribute('href')?.startsWith('#')
        || a.hasAttribute('@click') || a.hasAttribute('x-on:click');
    if (!lewati) NProgress.start();
});
document.addEventListener('submit', (e) => {
    if (e.target.matches('form') && e.target.method !== 'get') NProgress.start();
});

/* ── Tippy: ubah atribut title menjadi tooltip yang rapi ─────────── */
export function initTooltips(root = document) {
    root.querySelectorAll('[title]:not([data-no-tippy])').forEach((el) => {
        const teks = el.getAttribute('title');
        if (!teks) return;
        el.removeAttribute('title'); // cegah tooltip bawaan browser
        el.setAttribute('data-tippy-content', teks);
    });
    tippy('[data-tippy-content]', {
        theme: 'kemenkes',
        animation: 'shift-away',
        delay: [120, 0],
        arrow: true,
    });
}

/* ── Tooltip sidebar: hanya aktif saat sidebar di-collapse ───────── */
function initSidebarTooltips() {
    const links = document.querySelectorAll('[data-sidebar-link]');
    if (!links.length) return;

    // Pakai data-label (bukan title/data-tippy-content) agar tidak ikut tergrab
    // initTooltips global. Tampil di kanan ikon, nonaktif saat sidebar terbuka.
    const instances = [];
    links.forEach((el) => {
        const content = el.getAttribute('data-label');
        if (!content) return;
        instances.push(tippy(el, {
            content,
            theme: 'kemenkes',
            placement: 'right',
            animation: 'shift-away',
            delay: [60, 0],
            offset: [0, 14],
            arrow: true,
        }));
    });

    const sync = (collapsed) => instances.forEach((i) => (collapsed ? i.enable() : i.disable()));
    sync(localStorage.getItem('ea-sidebar-collapsed') === '1');
    window.addEventListener('sidebar-collapse', (e) => sync(e.detail));
}

/* ── SweetAlert2: toast untuk pesan flash ───────────────────────── */
export const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: (el) => {
        el.addEventListener('mouseenter', Swal.stopTimer);
        el.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

function tampilkanFlash() {
    const peta = { success: 'success', error: 'error', warning: 'warning', info: 'info' };

    // 1) Flash dari server (session Laravel)
    const el = document.getElementById('flash-data');
    if (el) Toast.fire({ icon: peta[el.dataset.type] || 'info', title: el.dataset.message });

    // 2) Flash yang disimpan sebelum reload (mis. sukses submit modal)
    const tersimpan = sessionStorage.getItem('ea-flash');
    if (tersimpan) {
        sessionStorage.removeItem('ea-flash');
        try {
            const { type, message } = JSON.parse(tersimpan);
            Toast.fire({ icon: peta[type] || 'info', title: message });
        } catch { /* abaikan */ }
    }
}

window.Swal = Swal;
window.Toast = Toast;

function initUI() {
    initTooltips();
    initSidebarTooltips();
    tampilkanFlash();
}

if (document.readyState !== 'loading') {
    initUI();
} else {
    document.addEventListener('DOMContentLoaded', initUI);
}

window.initTooltips = initTooltips;
