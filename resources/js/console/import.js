import { api, auth, download, errText, esc, expired, setErr } from './api.js';

// Impor peserta (Admin → Peserta): xlsx templates + POST /admin/participants/import (multipart, so no api()).
export function initImport() {
    const form = document.getElementById('imp-form');
    if (!form) return;
    const $ = (id) => document.getElementById(id);
    const [err, msg, save, detail, lines] = ['imp-error', 'imp-msg', 'imp-save', 'imp-detail', 'imp-lines'].map($);
    const flash = (text, cls) => { msg.className = `k-result mt-3 ${cls}`; msg.textContent = text; };
    let eventId = null;

    api('/event/active').then((r) => { eventId = r.ok ? r.data.data.id : null; });
    api('/admin/packages').then((r) => {
        $('imp-package').innerHTML = r.ok
            ? r.data.data.map((p) => `<option value="${p.id}"${p.is_active ? '' : ' disabled'}>${esc(p.name)}${p.is_active ? '' : ' (nonaktif)'}</option>`).join('')
            : '<option value="">Paket gagal dimuat</option>';
    });

    document.querySelectorAll('[data-template]').forEach((b) => b.addEventListener('click', async () => {
        b.disabled = true;
        try { await download(`/admin/participants/import/template/${b.dataset.template}`, `template_import_${b.dataset.template}.xlsx`); }
        catch (e) { flash(e.message, '-bad'); }
        b.disabled = false;
    }));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        setErr(err, '');
        if (!eventId) return setErr(err, 'Belum ada acara aktif. Aktifkan acara dulu di menu Event.');
        if (!form.file.files.length) return setErr(err, 'Pilih file Excel dulu.');
        const body = new FormData(form);
        body.append('event_id', eventId);

        save.disabled = true;
        flash('Mengimpor…', '-wait');
        detail.hidden = true;
        try {
            const res = await fetch('/api/admin/participants/import', {
                method: 'POST',
                headers: { Accept: 'application/json', Authorization: `Bearer ${auth.get()?.token}` },
                body,
            });
            if (res.status === 401) return expired();
            const data = await res.json().catch(() => ({}));
            if (!res.ok) return flash(errText({ data }), '-bad');
            const d = data.data;
            flash(data.message, d.error_count ? '-wait' : '-ok');
            const rows = [...d.errors, ...d.skipped];
            lines.innerHTML = rows.map((t) => `<li>${esc(t)}</li>`).join('');
            detail.hidden = !rows.length;
            form.file.value = '';
            document.getElementById('p-filter')?.search.dispatchEvent(new Event('input')); // reload the table above
        } catch {
            flash('Tidak bisa terhubung ke server. Periksa koneksimu.', '-bad');
        } finally {
            save.disabled = false;
        }
    });
}
