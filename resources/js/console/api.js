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
    const form = body instanceof FormData; // upload: biarkan browser set multipart boundary
    if (body && !form) headers['Content-Type'] = 'application/json';
    if (a) headers.Authorization = `Bearer ${a.token}`;
    try {
        const res = await fetch(`/api${path}`, { method, headers, body: body && (form ? body : JSON.stringify(body)) });
        const data = await res.json().catch(() => ({}));
        if (res.status === 401 && a) { auth.clear(); location.href = '/login'; }
        return { ok: res.ok, status: res.status, data };
    } catch {
        return { ok: false, status: 0, data: { message: 'Tidak bisa terhubung ke server. Periksa koneksimu.' } };
    }
}

// Unduh berkas yang butuh header Bearer: fetch -> blob -> <a download>
export async function download(path, fallback) {
    const res = await fetch(`/api${path}`, { headers: { Authorization: `Bearer ${auth.get().token}` } });
    if (!res.ok) throw new Error(res.status === 401 ? 'Sesi habis, login ulang.' : 'Gagal mengunduh.');
    const a = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(await res.blob()),
        download: /filename="?([^";]+)/.exec(res.headers.get('Content-Disposition') ?? '')?.[1] ?? fallback,
    });
    a.click();
    URL.revokeObjectURL(a.href);
}

export const errText =(r) => (r.data.errors && Object.values(r.data.errors)[0]?.[0]) || r.data.message || 'Terjadi kendala di server.';
export const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
export const setErr = (el, msg) => { el.textContent = msg; el.hidden = !msg; };
export const fmtDur = (s) => [Math.floor(s / 3600), Math.floor(s / 60) % 60, s % 60].map((n) => String(n).padStart(2, '0')).join(':');
