// Single result page: GET /api/results/{registration_number}.
const root = document.getElementById('result-detail');

if (root) {
    const status = document.getElementById('result-status');
    const card = document.getElementById('result-card');
    const f = (n) => card.querySelector(`[data-f="${n}"]`);
    const clock = (v) => new Date(v).toLocaleTimeString('id-ID');
    const time = (s) => {
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(Math.floor(s / 3600))}:${pad(Math.floor((s % 3600) / 60))}:${pad(s % 60)}`;
    };

    (async () => {
        try {
            const res = await fetch(`/api/results/${encodeURIComponent(root.dataset.no)}`, { headers: { Accept: 'application/json' } });
            if (res.status === 404) { status.textContent = 'Belum ada hasil untuk nomor ini. Waktu muncul setelah peserta melewati garis finish.'; return; }
            if (!res.ok) throw new Error(res.status);
            const { data: r } = await res.json();
            f('name').textContent = r.full_name;
            f('number').textContent = r.registration_number;
            f('category').textContent = r.category;
            f('start').textContent = clock(r.start_time);
            f('finish').textContent = clock(r.finish_time);
            f('duration').textContent = r.duration == null ? '–' : time(r.duration);
            status.hidden = true;
            card.hidden = false;
        } catch {
            status.textContent = 'Hasil belum bisa dimuat. Coba muat ulang halaman sebentar lagi.';
        }
    })();
}
