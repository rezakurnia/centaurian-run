import { api, auth, errText, esc } from './api.js';

const when = (v) => (v ? new Date(v).toLocaleString('id-ID') : '-');
const SCAN = { valid: ['-ok', 'Valid'], duplicate: ['-wait', 'Duplikat'], invalid: ['-bad', 'Invalid'] };
const input = (n, l, t = 'text') => `<input class="c-input" name="${n}" type="${t}" aria-label="${l}" placeholder="${t === 'date' ? '' : l}" autocomplete="off">`;
const dates = input('date_from', 'Dari tanggal', 'date') + input('date_to', 'Sampai tanggal', 'date');

// f = HTML filter; cols: [label, (row) => teks aman-HTML]
const LOGS = {
    scan: {
        path: '/admin/scan-logs',
        f: `<select class="c-input" name="scan_status" aria-label="Status scan"><option value="">Semua status</option>${Object.entries(SCAN).map(([k, [, l]]) => `<option value="${k}">${l}</option>`).join('')}</select>${input('barcode', 'Barcode')}${dates}`,
        cols: [
            ['Waktu', (x) => when(x.scanned_at)], ['Barcode', (x) => esc(x.barcode)],
            ['Peserta', (x) => esc(x.participant?.full_name ?? '-')],
            ['Status', (x) => `<span class="c-badge ${SCAN[x.scan_status]?.[0] ?? '-wait'}">${SCAN[x.scan_status]?.[1] ?? esc(x.scan_status)}</span>`],
            ['Petugas', (x) => esc(x.scanned_by?.username ?? '-')], ['IP', (x) => esc(x.ip_address ?? '-')],
        ],
    },
    activity: {
        path: '/admin/activity-logs',
        f: `${input('action', 'Aksi')}${input('target_table', 'Tabel')}${dates}`,
        cols: [
            ['Waktu', (x) => when(x.created_at)], ['Pengguna', (x) => esc(x.user?.username ?? '-')],
            ['Aksi', (x) => esc(x.action)], ['Target', (x) => esc(`${x.target_table ?? ''}${x.target_id ? ` #${x.target_id}` : ''}` || '-')],
            ['Detail', (x) => esc(x.detail ?? '-')], ['IP', (x) => esc(x.ip_address ?? '-')],
        ],
    },
};

export function initLogs() {
    const root = document.querySelector('[data-log]');
    if (!root) return;
    const cfg = LOGS[root.dataset.log];
    const $ = (id) => document.getElementById(id);
    const form = $('log-filter');
    const body = $('log-body');
    const span = cfg.cols.length;
    let page = 1;
    let last = 1;

    form.innerHTML = cfg.f;
    $('log-head').innerHTML = `<tr>${cfg.cols.map(([l]) => `<th>${l}</th>`).join('')}</tr>`;

    const load = async () => {
        const q = new URLSearchParams([...new FormData(form)].filter(([, v]) => v));
        q.set('page', page);
        const r = await api(`${cfg.path}?${q}`);
        if (!r.ok) { body.innerHTML = `<tr><td colspan="${span}">${esc(errText(r))}</td></tr>`; return; }
        const { data, meta } = r.data;
        last = meta.last_page;
        body.innerHTML = data.length ? data.map((x) => `<tr>${cfg.cols.map(([, f]) => `<td>${f(x)}</td>`).join('')}</tr>`).join('')
            : `<tr><td colspan="${span}">Tidak ada data.</td></tr>`;
        $('log-page').textContent = `Halaman ${meta.current_page} dari ${last} (${meta.total} data)`;
        $('log-prev').disabled = page <= 1;
        $('log-next').disabled = page >= last;
    };

    let t;
    const reload = () => { clearTimeout(t); t = setTimeout(() => { page = 1; load(); }, 300); };
    form.addEventListener('input', reload);
    form.addEventListener('submit', (e) => e.preventDefault());
    $('log-prev').addEventListener('click', () => { page -= 1; load(); });
    $('log-next').addEventListener('click', () => { page += 1; load(); });
    load();
}

// Ekspor CSV: butuh header Bearer, jadi fetch -> blob -> unduh
export function initExport() {
    const root = document.querySelector('[data-export]');
    if (!root) return;
    const form = document.getElementById('ex-form');
    const msg = document.getElementById('ex-msg');
    fetch('/api/categories', { headers: { Accept: 'application/json' } }).then((x) => x.json()).then((r) => {
        form.category_id.insertAdjacentHTML('beforeend', (r.data ?? []).map((c) => `<option value="${c.id}">${esc(c.name)}</option>`).join(''));
    }).catch(() => {});

    root.addEventListener('click', async (e) => {
        const b = e.target.closest('[data-ex]');
        if (!b) return;
        const kind = b.dataset.ex;
        const q = new URLSearchParams([...new FormData(form)].filter(([k, v]) => v && (kind === 'registrations' || k === 'category_id')));
        b.disabled = true;
        msg.className = 'k-result mt-3';
        msg.textContent = '';
        try {
            const res = await fetch(`/api/admin/export/${kind}?${q}`, { headers: { Authorization: `Bearer ${auth.get().token}` } });
            if (!res.ok) throw new Error(res.status === 401 ? 'Sesi habis, login ulang.' : 'Gagal mengunduh.');
            const a = Object.assign(document.createElement('a'), {
                href: URL.createObjectURL(await res.blob()),
                download: /filename="?([^";]+)/.exec(res.headers.get('Content-Disposition') ?? '')?.[1] ?? `${kind}.csv`,
            });
            a.click();
            URL.revokeObjectURL(a.href);
            msg.className = 'k-result mt-3 -ok';
            msg.textContent = 'Unduhan dimulai.';
        } catch (err) {
            msg.className = 'k-result mt-3 -bad';
            msg.textContent = err.message;
        }
        b.disabled = false;
    });
}
