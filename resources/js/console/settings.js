import { api, errText, esc, setErr } from './api.js';

// GET /admin/settings -> { "<grup>": [{ key, label, type, value }] }; simpan lewat PUT /admin/settings/bulk-update.
// Semua nilai dikirim sebagai string (toggle = "1"/"0"), sesuai validasi backend.
export function initSettings() {
    const form = document.querySelector('[data-settings]');
    if (!form) return;
    const $ = (id) => document.getElementById(id);
    const [groups, err, msg, save] = ['set-groups', 'set-error', 'set-msg', 'set-save'].map($);

    const input = ({ key, type, value }) => {
        const v = esc(value ?? '');
        if (type === 'toggle') return `<input type="checkbox" id="s-${key}" name="${key}"${value === '1' ? ' checked' : ''}>`;
        if (type === 'textarea') return `<textarea class="c-input" id="s-${key}" name="${key}" rows="3">${v}</textarea>`;
        return `<input class="c-input" id="s-${key}" name="${key}" type="${type}" value="${v}">`;
    };
    const render = (data) => {
        groups.innerHTML = Object.entries(data).map(([group, items]) => `<fieldset class="flex flex-col gap-3"><legend class="k-h2">${esc(group)}</legend>${items.map((s) =>
            `<div class="c-field"><label class="c-label" for="s-${s.key}">${esc(s.label)}</label>${input(s)}</div>`).join('')}</fieldset>`).join('');
    };

    api('/admin/settings').then((r) => (r.ok ? render(r.data.data) : setErr(err, errText(r))));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        setErr(err, '');
        msg.textContent = '';
        const settings = {};
        for (const el of form.querySelectorAll('[name]')) settings[el.name] = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value.trim();
        save.disabled = true;
        const r = await api('/admin/settings/bulk-update', { method: 'PUT', body: { settings } });
        save.disabled = false;
        if (!r.ok) return setErr(err, errText(r));
        render(r.data.data);
        msg.className = 'k-result mt-3 -ok';
        msg.textContent = r.data.message ?? 'Pengaturan disimpan.';
    });
}
