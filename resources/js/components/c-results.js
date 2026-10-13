// Results page: GET /api/results (optionally ?category_id=), rendered as a ranked table.
const root = document.getElementById('results');

if (root) {
    const filter = document.getElementById('category-filter');
    const status = document.getElementById('results-status');
    const error = document.getElementById('results-error');
    const wrap = document.getElementById('results-wrap');
    const body = document.getElementById('results-body');

    // Durations are whole seconds: 3725 -> "1:02:05", 725 -> "12:05"
    const time = (s) => {
        const h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), sec = s % 60;
        const pad = (n) => String(n).padStart(2, '0');
        return h ? `${h}:${pad(m)}:${pad(sec)}` : `${m}:${pad(sec)}`;
    };
    const cell = (text, cls = '') => {
        const c = document.createElement('td');
        c.textContent = text;
        if (cls) c.className = cls;
        return c;
    };

    let seq = 0;
    const load = async () => {
        const mine = ++seq; // ignore out-of-order responses when the filter changes quickly
        error.hidden = true;
        wrap.hidden = true;
        status.hidden = false;
        status.textContent = 'Memuat hasil…';
        try {
            const q = filter.value ? `?category_id=${encodeURIComponent(filter.value)}` : '';
            const res = await fetch(root.dataset.url + q, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(res.status);
            const { data } = await res.json();
            if (mine !== seq) return;

            body.replaceChildren(...data.map((r, i) => {
                const tr = document.createElement('tr');
                tr.append(cell(i + 1), cell(r.full_name), cell(r.category), cell(r.registration_number), cell(r.duration == null ? '–' : time(r.duration), 'text-right tabular-nums'));
                return tr;
            }));
            wrap.hidden = !data.length;
            status.hidden = !!data.length;
            status.textContent = filter.value
                ? 'Belum ada hasil untuk kategori ini.'
                : 'Belum ada hasil. Waktu muncul di sini begitu peserta melewati garis finish.';
        } catch {
            if (mine !== seq) return;
            status.hidden = true;
            error.textContent = 'Hasil belum bisa dimuat. Coba muat ulang halaman sebentar lagi.';
            error.hidden = false;
        }
    };

    filter.addEventListener('change', load);
    load();
}
