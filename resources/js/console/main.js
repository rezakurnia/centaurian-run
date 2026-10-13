import { api, auth, errText, setErr } from './api.js';
import { initScan } from './scan.js';
import { initRekap } from './rekap.js';
import { initAdmin } from './admin.js';
import { initCrud } from './crud.js';

// Menu per role; tambahkan item admin di sini saat halamannya jadi.
const NAV = [
    ['Scan finish', '/panitia/scan', 'panitia'],
    ['Rekap peserta', '/panitia/rekap', 'panitia'],
    ['Dashboard', '/admin', 'admin'],
    ['Pendaftaran', '/admin/pendaftaran', 'admin'],
    ['Peserta', '/admin/peserta', 'admin'],
    ['Event', '/admin/event', 'admin'],
    ['Konten', '/admin/konten', 'admin'],
    ['Pengguna', '/admin/pengguna', 'admin'],
    ['Pengaturan', '/admin/pengaturan', 'admin'],
];
const home = (u) => (u.role === 'admin' ? '/admin' : '/panitia/scan');

const login = document.getElementById('login-form');
if (login) {
    if (auth.get()) location.replace(home(auth.get().user));
    const err = document.getElementById('login-error');
    const btn = document.getElementById('login-submit');
    login.addEventListener('submit', async (e) => {
        e.preventDefault();
        setErr(err, '');
        const username = login.username.value.trim();
        const password = login.password.value;
        if (!username || !password) return setErr(err, 'Isi username dan password.');
        btn.disabled = true;
        const r = await api('/login', { method: 'POST', body: { username, password } });
        btn.disabled = false;
        if (!r.ok) return setErr(err, errText(r));
        auth.set({ token: r.data.token, user: r.data.user });
        location.href = home(r.data.user);
    });
}

const main = document.getElementById('k-main');
if (main) {
    const a = auth.get();
    const need = document.body.dataset.role;
    if (!a) location.replace('/login');
    else if (need === 'admin' && a.user.role !== 'admin') location.replace(home(a.user));
    else {
        document.getElementById('k-user').textContent = `${a.user.username} (${a.user.role})`;
        document.getElementById('k-nav').innerHTML = NAV
            .filter(([, , r]) => r === 'panitia' || a.user.role === 'admin')
            .map(([label, href]) => `<a href="${href}"${location.pathname === href ? ' aria-current="page"' : ''}>${label}</a>`)
            .join('');
        document.getElementById('k-logout').addEventListener('click', async () => {
            await api('/logout', { method: 'POST' });
            auth.clear();
            location.href = '/login';
        });
        main.hidden = false;
        initScan();
        initRekap();
        initAdmin();
        initCrud();
    }
}
