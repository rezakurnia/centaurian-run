import { api, errText, esc, setErr } from './api.js';

// Satu komponen tabel + form untuk semua resource CRUD admin.
// fields: n=nama, l=label, t=tipe (text|textarea|select|date|number|email|password), o=opsi select,
//   once=hanya saat buat (tidak dikirim/dapat diubah saat edit), omit=kosong = tidak dikirim.
// cols: [label, key, format?]
const day = (v) => (v ? String(v).slice(0, 10) : '');
const when = (v) => (v ? new Date(v).toLocaleString('id-ID') : '-');

const RES = {
    events: {
        path: '/admin/events', one: 'event', name: 'name',
        cols: [['Nama', 'name'], ['Tanggal', 'event_date', (v) => day(v)], ['Lokasi', 'location'], ['Status', 'is_active', (v) => (v ? 'Aktif' : 'Nonaktif')], ['Mulai', 'start_time', when], ['Selesai', 'finish_time', when]],
        fields: [
            { n: 'name', l: 'Nama' }, { n: 'event_date', l: 'Tanggal', t: 'date' },
            { n: 'location', l: 'Lokasi' }, { n: 'description', l: 'Deskripsi', t: 'textarea' },
        ],
        actions: [
            { label: (r) => (r.is_active ? 'Nonaktifkan' : 'Aktifkan'), run: (r, c) => c.call(`/admin/events/${r.id}/toggle-active`, 'PUT') },
            { label: () => 'Reset', run: (r, c) => c.reset(r) },
        ],
    },
    contents: {
        path: '/admin/contents', one: 'konten', name: 'title',
        cols: [['Bagian', 'section'], ['Judul', 'title'], ['Urutan', 'sort_order'], ['Diubah', 'updated_at', when]],
        fields: [
            { n: 'target_site', l: 'Situs', t: 'select', o: [['utama', 'Utama'], ['panitia', 'Panitia']], omit: true },
            { n: 'status', l: 'Status', t: 'select', o: [['draft', 'Draf'], ['published', 'Terbit']], omit: true },
            { n: 'section', l: 'Bagian (kunci)' }, { n: 'title', l: 'Judul' },
            { n: 'body', l: 'Isi', t: 'textarea' }, { n: 'sort_order', l: 'Urutan', t: 'number' },
        ],
    },
    users: {
        path: '/admin/users', one: 'pengguna', name: 'username',
        cols: [['Username', 'username'], ['Email', 'email'], ['Role', 'role'], ['Login terakhir', 'last_login', when]],
        fields: [
            { n: 'username', l: 'Username' }, { n: 'email', l: 'Email', t: 'email' },
            { n: 'role', l: 'Role', t: 'select', o: [['panitia', 'Panitia'], ['admin', 'Admin']] },
            { n: 'password', l: 'Password (min. 6)', t: 'password', omit: true },
        ],
    },
};

export function initCrud() {
    const root = document.querySelector('[data-crud]');
    if (!root) return;
    const cfg = RES[root.dataset.crud];
    const idKey = cfg.id ?? 'id';
    const $ = (id) => document.getElementById(id);
    const [head, body, msg, dlg, form, box, err, save] = ['crud-head', 'crud-body', 'crud-msg', 'crud-dialog', 'crud-form', 'crud-fields', 'crud-error', 'crud-save'].map($);
    let rows = [];
    let editing = null;

    const flash = (text, ok = true) => { msg.className = `k-result mt-3 ${ok ? '-ok' : '-bad'}`; msg.textContent = text; };
    const url = (r) => `${cfg.path}/${encodeURIComponent(r[idKey])}`;
    const cell = (r, [, k, f]) => esc(f ? f(r[k], r) : (r[k] ?? '-'));
    const span = cfg.cols.length + 1;

    const load = async () => {
        const r = await api(cfg.path);
        if (!r.ok) { body.innerHTML = `<tr><td colspan="${span}">${esc(errText(r))}</td></tr>`; return; }
        rows = r.data.data;
        body.innerHTML = rows.length ? rows.map((x, i) => `<tr>${cfg.cols.map((c) => `<td>${cell(x, c)}</td>`).join('')}<td class="k-actions"><button type="button" class="k-btn -ghost" data-a="edit" data-i="${i}">Ubah</button>${(cfg.actions ?? []).map((a, j) => `<button type="button" class="k-btn -ghost" data-a="${j}" data-i="${i}">${esc(a.label(x))}</button>`).join('')}<button type="button" class="k-btn -ghost" data-a="del" data-i="${i}">Hapus</button></td></tr>`).join('')
            : `<tr><td colspan="${span}">Belum ada data.</td></tr>`;
    };
    head.innerHTML = `<tr>${cfg.cols.map(([l]) => `<th>${l}</th>`).join('')}<th></th></tr>`;

    // Panggilan langsung (toggle dst.), lalu muat ulang
    const call = async (path, method, body) => {
        const r = await api(path, { method, body });
        flash(r.ok ? r.data.message : errText(r), r.ok);
        if (r.ok) load();
        return r;
    };

    // Reset acara: dialog konfirmasi + password
    const rd = $('reset-dialog');
    const rerr = $('reset-error');
    let target = null;
    const reset = (ev) => { target = ev; $('reset-confirm').value = ''; $('reset-password').value = ''; setErr(rerr, ''); rd.showModal(); };
    $('reset-cancel').addEventListener('click', () => rd.close());
    $('reset-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        $('reset-save').disabled = true;
        const r = await api(`/admin/events/${target.id}/reset`, { method: 'POST', body: { confirmation: $('reset-confirm').value.trim(), password: $('reset-password').value } });
        $('reset-save').disabled = false;
        if (!r.ok) return setErr(rerr, errText(r));
        rd.close();
        flash(r.data.message);
        load();
    });

    const input = (f, v) => {
        const id = `f-${f.n}`;
        const dis = editing && f.once ? ' disabled' : '';
        if (f.t === 'textarea') return `<textarea class="c-input" id="${id}" name="${f.n}" rows="4">${esc(v)}</textarea>`;
        if (f.t === 'select') {
            const opts = (editing && f.omit ? [['', '(tidak diubah)']] : []).concat(f.o);
            return `<select class="c-input" id="${id}" name="${f.n}">${opts.map(([k, l]) => `<option value="${k}"${k === v ? ' selected' : ''}>${l}</option>`).join('')}</select>`;
        }
        return `<input class="c-input" id="${id}" name="${f.n}" type="${f.t ?? 'text'}" value="${esc(v)}"${dis}${f.t === 'password' ? ' autocomplete="new-password"' : ''}>`;
    };

    const openForm = (row) => {
        editing = row;
        $('crud-title').textContent = `${row ? 'Ubah' : 'Tambah'} ${cfg.one}`;
        box.innerHTML = cfg.fields.map((f) => {
            const raw = row?.[f.n];
            const v = f.t === 'date' ? day(raw) : (raw ?? '');
            return `<div class="c-field"><label class="c-label" for="f-${f.n}">${f.l}</label>${input(f, v)}</div>`;
        }).join('');
        setErr(err, '');
        dlg.showModal();
    };

    $('crud-new').addEventListener('click', () => openForm(null));
    $('crud-cancel').addEventListener('click', () => dlg.close());
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {};
        for (const f of cfg.fields) {
            if (editing && f.once) continue;
            let v = form.elements[f.n].value.trim();
            if (v === '') { if (f.omit) continue; v = null; } else if (f.t === 'number') v = Number(v);
            payload[f.n] = v;
        }
        save.disabled = true;
        const r = await api(editing ? url(editing) : cfg.path, { method: editing ? 'PUT' : 'POST', body: payload });
        save.disabled = false;
        if (!r.ok) return setErr(err, errText(r));
        dlg.close();
        flash(r.data.message);
        load();
    });

    body.addEventListener('click', async (e) => {
        const b = e.target.closest('[data-a]');
        if (!b) return;
        const row = rows[b.dataset.i];
        const a = b.dataset.a;
        if (a === 'edit') {
            // Edit the latest version (GET {path}/{id}), not the row as it was when the list loaded
            b.disabled = true;
            const r = await api(url(row));
            b.disabled = false;
            if (!r.ok) return flash(errText(r), false);
            return openForm(r.data.data);
        }
        if (a === 'del') {
            if (!confirm(`Hapus ${cfg.one} "${row[cfg.name]}"?`)) return;
            return call(url(row), 'DELETE');
        }
        cfg.actions[a].run(row, { call, reset });
    });

    load();
}
