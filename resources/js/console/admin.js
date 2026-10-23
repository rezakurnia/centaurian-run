import { api, errText, esc, setErr } from './api.js';

const REG = { pending: 'Menunggu', confirmed: 'Terkonfirmasi', cancelled: 'Dibatalkan' };
const PAY = { unpaid: 'Belum bayar', paid: 'Lunas', free: 'Gratis' };
const BADGE = { pending: '-wait', unpaid: '-wait', confirmed: '-ok', paid: '-ok', free: '-ok', cancelled: '-bad' };
const badge = (map, v) => `<span class="c-badge ${BADGE[v] ?? '-wait'}">${map[v] ?? esc(v)}</span>`;
const row = (n, t) => `<tr><td colspan="${n}">${t}</td></tr>`;
const qs = (form) => new URLSearchParams([...new FormData(form)].filter(([, v]) => v)).toString();

// Isi <select name=category_id> dari API publik
async function fillCategories(form) {
    const r = await fetch('/api/categories', { headers: { Accept: 'application/json' } }).then((x) => x.json()).catch(() => ({}));
    form.category_id.insertAdjacentHTML('beforeend', (r.data ?? []).map((c) => `<option value="${c.id}">${esc(c.name)}</option>`).join(''));
}

// Debounce ringan agar ketik di kolom cari tidak menembak API tiap huruf
const debounce = (fn, ms = 300) => { let t; return () => { clearTimeout(t); t = setTimeout(fn, ms); }; };

async function dashboard() {
    const stats = document.getElementById('dash-stats');
    const r = await api('/admin/dashboard');
    if (!r.ok) { stats.textContent = errText(r); document.getElementById('dash-category').innerHTML = row(3, esc(errText(r))); return; }
    const d = r.data.data;
    const card = (label, v) => `<div class="k-card k-stat-card"><p class="body-md">${label}</p><p class="k-stat">${v}</p></div>`;
    const sum = (arr, key, v) => arr.find((x) => x[key] === v)?.total ?? 0;
    stats.innerHTML = card('Peserta', d.total_participants) + card('Pendaftaran', d.total_registrations)
        + card('Lunas / gratis', sum(d.by_payment_status, 'payment_status', 'paid') + sum(d.by_payment_status, 'payment_status', 'free'))
        + card('Menunggu verifikasi', sum(d.by_registration_status, 'registration_status', 'pending'));
    document.getElementById('dash-category').innerHTML = d.by_category.map((c) => `<tr><td>${esc(c.name)}</td><td>${esc(c.code)}</td><td>${c.total}</td></tr>`).join('');
}

function pendaftaran() {
    const form = document.getElementById('reg-filter');
    const body = document.getElementById('reg-body');
    const dlg = document.getElementById('reg-dialog');
    const err = document.getElementById('reg-dialog-error');
    const save = document.getElementById('reg-save');
    let list = [];
    let current = null;

    let seq = 0;
    const load = async () => {
        const mine = ++seq;
        body.innerHTML = row(8, 'Memuat…');
        const r = await api(`/admin/registrations?${qs(form)}`);
        if (mine !== seq) return; // a newer filter is already loading
        if (!r.ok) { body.innerHTML = row(8, esc(errText(r))); return; }
        list = r.data.data;
        body.innerHTML = list.length ? list.map((x, i) => `<tr><td>${esc(x.registration_number)}</td><td>${esc(x.participant?.full_name ?? '-')}</td><td>${esc(x.category)}</td><td>${esc(x.package)}</td><td>${badge(REG, x.registration_status)}</td><td>${badge(PAY, x.payment_status)}</td><td>${esc(x.payment_method ?? '-')}</td><td><button type="button" class="k-btn -ghost" data-i="${i}" aria-label="Verifikasi ${esc(x.registration_number)}">Verifikasi</button></td></tr>`).join('')
            : row(8, 'Tidak ada pendaftaran.');
    };

    // Detail peserta untuk dialog verifikasi; list sudah cukup untuk dibuka, detail menyusul
    const person = document.getElementById('reg-dialog-person');
    const showPerson = (p) => {
        const items = p ? [
            ['Nama', p.full_name], ['Email', p.email], ['Telepon', p.phone],
            ['L/P', p.gender], ['Lahir', [p.birth_place, p.birth_date && String(p.birth_date).slice(0, 10)].filter(Boolean).join(', ')],
            ['Identitas', identity(p)], ['Motivasi', p.motivation],
        ].filter(([, v]) => v && v !== '-') : [];
        person.innerHTML = items.map(([k, v]) => `<dt>${k}</dt><dd>${esc(v)}</dd>`).join('');
    };

    body.addEventListener('click', async (e) => {
        const b = e.target.closest('[data-i]');
        if (!b) return;
        current = list[b.dataset.i];
        document.getElementById('reg-dialog-title').textContent = `Verifikasi ${current.registration_number}`;
        document.getElementById('reg-dialog-info').textContent = [`${current.category}, ${current.package}`, current.payment_method && `Metode ${current.payment_method}`, current.paid_at && `Dibayar ${new Date(current.paid_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })}`].filter(Boolean).join(' · ');
        document.getElementById('reg-dialog-proof').innerHTML = current.payment_proof_url
            ? `<a class="c-link" href="${esc(current.payment_proof_url)}" target="_blank" rel="noopener">Buka bukti pembayaran</a>` : '';
        document.getElementById('v-reg').value = current.registration_status;
        document.getElementById('v-pay').value = current.payment_status;
        setErr(err, '');
        showPerson(current.participant);
        dlg.showModal();
        const id = current.id;
        const r = await api(`/admin/registrations/${id}`);
        if (r.ok && current?.id === id) showPerson(r.data.data.participant);
    });
    document.getElementById('reg-cancel').addEventListener('click', () => dlg.close());
    document.getElementById('reg-verify').addEventListener('submit', async (e) => {
        e.preventDefault();
        save.disabled = true;
        const r = await api(`/admin/registrations/${current.id}/verify`, { method: 'PUT', body: { registration_status: document.getElementById('v-reg').value, payment_status: document.getElementById('v-pay').value } });
        save.disabled = false;
        if (!r.ok) return setErr(err, errText(r));
        dlg.close();
        load();
    });

    form.addEventListener('change', load);
    fillCategories(form);
    load();
}

// Whichever identity fields the participant's category filled (siswa, guru, orangtua, alumni)
const identity = (x) => [
    x.nis && `NIS ${x.nis}`, x.class && `Kelas ${x.class}`,
    x.nip && `NIP ${x.nip}`, x.position, x.unit,
    x.child_name && `Anak: ${x.child_name}${x.child_class ? ` (${x.child_class})` : ''}`,
    x.graduation_year && `Lulus ${x.graduation_year}`,
].filter(Boolean).join(' · ') || '-';

function peserta() {
    const form = document.getElementById('p-filter');
    const body = document.getElementById('p-body');
    let seq = 0;
    const load = async () => {
        const mine = ++seq;
        const r = await api(`/admin/participants?${qs(form)}`);
        if (mine !== seq) return;
        if (!r.ok) { body.innerHTML = row(6, esc(errText(r))); return; }
        const list = r.data.data;
        body.innerHTML = list.length ? list.map((x) => `<tr><td>${esc(x.full_name)}</td><td>${esc(identity(x))}</td><td>${esc(x.gender)}</td><td>${esc(x.email)}</td><td>${esc(x.phone)}</td><td>${new Date(x.created_at).toLocaleDateString('id-ID')}</td></tr>`).join('')
            : row(6, 'Tidak ada peserta.');
    };
    form.addEventListener('submit', (e) => e.preventDefault());
    form.search.addEventListener('input', debounce(load));
    form.category_id.addEventListener('change', load);
    fillCategories(form);
    load();
}

export function initAdmin() {
    if (document.getElementById('dash-stats')) dashboard();
    if (document.getElementById('reg-filter')) pendaftaran();
    if (document.getElementById('p-filter')) peserta();
}
