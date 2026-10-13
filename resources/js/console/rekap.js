import { api, esc } from './api.js';

const REG = { pending: 'Menunggu', confirmed: 'Terkonfirmasi', cancelled: 'Dibatalkan' };
const PAY = { unpaid: 'Belum bayar', paid: 'Lunas', free: 'Gratis' };

export async function initRekap() {
    const cat = document.getElementById('recap-category');
    if (!cat) return;
    const body = document.getElementById('recap-participants');
    const q = document.getElementById('recap-q');
    const empty = (n, t) => `<tr><td colspan="${n}">${t}</td></tr>`;

    const [c, p] = await Promise.all([api('/panitia/recap/category'), api('/panitia/recap/participants')]);
    cat.innerHTML = c.ok
        ? c.data.data.map((x) => `<tr><td>${esc(x.name)}</td><td>${esc(x.code)}</td><td>${x.total_participants}</td></tr>`).join('')
        : empty(3, 'Gagal memuat.');

    const list = p.ok ? p.data.data : [];
    const draw = () => {
        const k = q.value.trim().toLowerCase();
        const rows = list.filter((x) => !k || `${x.registration_number} ${x.full_name}`.toLowerCase().includes(k));
        body.innerHTML = rows.length
            ? rows.map((x) => `<tr><td>${esc(x.registration_number)}</td><td>${esc(x.full_name)}</td><td>${esc(x.category)}</td><td>${esc(x.package)}</td><td>${REG[x.registration_status] ?? esc(x.registration_status)}</td><td>${PAY[x.payment_status] ?? esc(x.payment_status)}</td></tr>`).join('')
            : empty(6, p.ok ? 'Tidak ada peserta.' : 'Gagal memuat.');
    };
    q.addEventListener('input', draw);
    draw();
}
