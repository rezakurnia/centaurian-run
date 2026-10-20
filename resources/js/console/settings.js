import { api, errText, esc } from './api.js';

// Pengaturan: grouped form from config/app_settings.php, saved in one PUT /admin/settings/bulk-update.
// Values are strings on the API side; toggles are '1'/'0'.
export function initSettings() {
    const form = document.getElementById('settings-form');
    if (!form) return;
    const groups = document.getElementById('settings-groups');
    const msg = document.getElementById('settings-msg');
    const save = document.getElementById('settings-save');
    const flash = (text, ok = true) => { msg.className = `k-result mt-3 ${ok ? '-ok' : '-bad'}`; msg.textContent = text; };

    const field = ({ key, label, type, value }) => {
        const id = `s-${key}`;
        if (type === 'toggle') {
            return `<label class="k-check" for="${id}"><input type="checkbox" id="${id}" name="${key}"${value === '1' ? ' checked' : ''}> ${esc(label)}</label>`;
        }
        const control = type === 'textarea'
            ? `<textarea class="c-input" id="${id}" name="${key}" rows="${Math.min(Math.max(String(value ?? '').split('\n').length, 3), 20)}">${esc(value)}</textarea>`
            : `<input class="c-input" id="${id}" name="${key}" type="${['email', 'url', 'number', 'date'].includes(type) ? type : 'text'}" value="${esc(value)}">`;
        return `<div class="c-field${type === 'textarea' ? ' sm:col-span-2' : ''}"><label class="c-label" for="${id}">${esc(label)}</label>${control}</div>`;
    };

    const render = (data) => {
        groups.innerHTML = Object.entries(data).map(([group, items]) => `
            <section class="k-card" aria-labelledby="g-${esc(items[0]?.key)}">
                <h2 class="k-h2" id="g-${esc(items[0]?.key)}">${esc(group)}</h2>
                <div class="grid gap-4 sm:grid-cols-2">${items.map(field).join('')}</div>
            </section>`).join('');
    };

    api('/admin/settings').then((r) => (r.ok ? render(r.data.data) : (groups.innerHTML = `<section class="k-card">${esc(errText(r))}</section>`)));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const settings = {};
        for (const el of form.querySelectorAll('[name]')) {
            settings[el.name] = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value.trim();
        }
        save.disabled = true;
        const r = await api('/admin/settings/bulk-update', { method: 'PUT', body: { settings } });
        save.disabled = false;
        flash(r.ok ? r.data.message : errText(r), r.ok);
        if (r.ok) render(r.data.data);
    });
}
