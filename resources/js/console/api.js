// Token Sanctum + role disimpan di localStorage; semua panggilan console lewat api().
const KEY = 'cr_auth';
export const auth = {
    get() { try { return JSON.parse(localStorage.getItem(KEY)); } catch { return null; } },
    set(v) { localStorage.setItem(KEY, JSON.stringify(v)); },
    clear() { localStorage.removeItem(KEY); },
};

export async function api(path, { method = 'GET', body } = {}) {
    const a = auth.get();
    const headers = { Accept: 'application/json' };
    if (body) headers['Content-Type'] = 'application/json';
    if (a) headers.Authorization = `Bearer ${a.token}`;
    try {
        const res = await fetch(`/api${path}`, { method, headers, body: body && JSON.stringify(body) });
        const data = await res.json().catch(() => ({}));
        if (res.status === 401 && a) { auth.clear(); location.href = '/login'; }
        return { ok: res.ok, status: res.status, data };
    } catch {
        return { ok: false, status: 0, data: { message: 'Tidak bisa terhubung ke server. Periksa koneksimu.' } };
    }
}

export const errText = (r) => (r.data.errors && Object.values(r.data.errors)[0]?.[0]) || r.data.message || 'Terjadi kendala di server.';
export const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
export const setErr = (el, msg) => { el.textContent = msg; el.hidden = !msg; };
export const fmtDur = (s) => [Math.floor(s / 3600), Math.floor(s / 60) % 60, s % 60].map((n) => String(n).padStart(2, '0')).join(':');
