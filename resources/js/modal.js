// ── Modal global (gaya Bootstrap) + konfirmasi aksi mutasi ──────────
// Pola pakai:
//   • Buka modal form  : <a href="/dokumen/create" data-modal data-modal-title="Tambah Dokumen">
//   • Form dalam modal  : <form data-modal-form data-confirm="…" data-success="…">
//   • Aksi mutasi biasa : <form data-confirm="Hapus data ini?" …> (submit normal setelah konfirmasi)
import Swal from 'sweetalert2';

/* ── Util SweetAlert2 konsisten dengan tema E-ARSIPKU ───────────── */
const ACCENT = '#009177';
const SLATE = '#64748b';

function konfirmasi(opts = {}) {
    return Swal.fire({
        icon: opts.icon || 'question',
        title: opts.title || 'Konfirmasi',
        text: opts.text || 'Lanjutkan tindakan ini?',
        showCancelButton: true,
        confirmButtonText: opts.confirmText || 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: opts.danger ? '#dc2626' : ACCENT,
        cancelButtonColor: SLATE,
        reverseButtons: true,
        focusCancel: true,
    }).then((r) => r.isConfirmed);
}

/* ── Modal shell ────────────────────────────────────────────────── */
const root = () => document.getElementById('ea-modal');

function bukaShell() {
    const m = root();
    if (!m) return;
    m.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => m.classList.add('is-open'));
}

function tutupModal() {
    const m = root();
    if (!m) return;
    m.classList.remove('is-open');
    document.body.classList.remove('overflow-hidden');
    setTimeout(() => {
        m.classList.add('hidden');
        const body = m.querySelector('[data-modal-body]');
        if (body) body.innerHTML = '';
    }, 200);
}
window.tutupModal = tutupModal;

async function muatModal(url, title) {
    const m = root();
    if (!m) return;
    m.querySelector('[data-modal-title]').textContent = title || 'Formulir';
    const body = m.querySelector('[data-modal-body]');
    body.innerHTML = '<div class="flex items-center justify-center py-20 text-sm text-slate-400">Memuat formulir…</div>';
    bukaShell();
    try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('Formulir gagal dimuat.');
        body.innerHTML = await res.text();
        window.enhanceUI?.(body);
        body.querySelector('input:not([type=hidden]):not([type=file]), select, textarea')?.focus();
    } catch (e) {
        body.innerHTML = `<div class="p-6 text-sm text-red-600">${e.message}</div>`;
    }
}

/* ── Tampilkan / bersihkan error validasi pada form ─────────────── */
const ERR_BORDER = ['!border-red-300', 'focus:!border-red-400', 'focus:!ring-red-200'];

function bersihkanError(form) {
    form.querySelectorAll('.js-error').forEach((el) => el.remove());
    form.querySelectorAll('[data-invalid]').forEach((el) => {
        el.classList.remove(...ERR_BORDER);
        el.removeAttribute('data-invalid');
    });
}

function tampilkanError(form, errors = {}) {
    bersihkanError(form);
    Object.entries(errors).forEach(([name, pesan]) => {
        const field = form.querySelector(`[name="${name}"]`);
        if (!field) return;
        field.classList.add(...ERR_BORDER);
        field.setAttribute('data-invalid', '');
        const p = document.createElement('p');
        p.className = 'js-error mt-1 text-xs text-red-600';
        p.textContent = Array.isArray(pesan) ? pesan[0] : pesan;
        (field.closest('div') || field.parentElement).appendChild(p);
    });
    const pertama = form.querySelector('[data-invalid]');
    pertama?.scrollIntoView({ block: 'center', behavior: 'smooth' });
}

/* ── Loading state tombol submit (umpan balik visual saat menyimpan) ─ */
const SPINNER = '<svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/></svg>';

function tombolMemuat(tombol, memuat) {
    if (!tombol) return;
    if (memuat) {
        tombol.dataset.htmlAsli = tombol.innerHTML;
        tombol.setAttribute('disabled', 'disabled');
        tombol.setAttribute('aria-busy', 'true');
        tombol.innerHTML = `${SPINNER}<span>Menyimpan…</span>`;
    } else {
        tombol.removeAttribute('disabled');
        tombol.removeAttribute('aria-busy');
        if (tombol.dataset.htmlAsli != null) {
            tombol.innerHTML = tombol.dataset.htmlAsli;
            delete tombol.dataset.htmlAsli;
        }
    }
}

/* ── Kirim form modal via fetch (validasi 422 + dukung file) ────── */
async function kirimFormModal(form) {
    const konfirm = await konfirmasi({
        text: form.dataset.confirm || 'Simpan data ini?',
        confirmText: 'Ya, Simpan',
    });
    if (!konfirm) return;

    const tombol = form.querySelector('[type=submit]');
    tombolMemuat(tombol, true);
    bersihkanError(form);
    window.NProgress?.start();

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        window.NProgress?.done();

        if (res.status === 422) {
            const data = await res.json();
            tampilkanError(form, data.errors || {});
            tombolMemuat(tombol, false);
            return;
        }
        if (!res.ok) throw new Error('Terjadi kesalahan saat menyimpan data.');

        // Sukses → toast setelah reload (lewat sessionStorage)
        sessionStorage.setItem('ea-flash', JSON.stringify({
            type: 'success',
            message: form.dataset.success || 'Data berhasil disimpan.',
        }));
        tutupModal();
        window.location.reload();
    } catch (err) {
        window.NProgress?.done();
        tombolMemuat(tombol, false);
        Swal.fire({ icon: 'error', title: 'Gagal', text: err.message, confirmButtonColor: ACCENT });
    }
}

/* ── Event delegation ───────────────────────────────────────────── */
document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-modal]');
    if (trigger) {
        e.preventDefault();
        muatModal(trigger.getAttribute('href') || trigger.dataset.modal, trigger.dataset.modalTitle);
        return;
    }
    if (e.target.closest('[data-modal-close]')) {
        e.preventDefault();
        tutupModal();
        return;
    }
    if (e.target.matches('[data-modal-backdrop]')) tutupModal();
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && root() && !root().classList.contains('hidden')) tutupModal();
});

document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    // Form di dalam modal → fetch
    if (form.matches('[data-modal-form]')) {
        e.preventDefault();
        kirimFormModal(form);
        return;
    }

    // Form mutasi biasa (hapus, kembalikan, dll.) → konfirmasi lalu submit normal
    if (form.hasAttribute('data-confirm')) {
        if (form.dataset.confirmed === '1') return; // submit asli setelah dikonfirmasi
        e.preventDefault();
        const ok = await konfirmasi({
            icon: form.dataset.confirmIcon || 'warning',
            text: form.dataset.confirm,
            confirmText: form.dataset.confirmBtn || 'Ya, Lanjutkan',
            danger: form.hasAttribute('data-confirm-danger'),
        });
        if (ok) {
            form.dataset.confirmed = '1';
            form.submit();
        }
    }
});
