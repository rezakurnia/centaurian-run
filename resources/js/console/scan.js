import { api, errText, esc, fmtDur, setErr } from './api.js';

export function initScan() {
    const form = document.getElementById('scan-form');
    if (!form) return;
    const input = document.getElementById('barcode');
    const result = document.getElementById('scan-result');
    const history = document.getElementById('scan-history');
    const startInfo = document.getElementById('start-info');
    const startBtn = document.getElementById('start-btn');
    const startErr = document.getElementById('start-error');
    let event = null;
    let rows = [];

    const showStart = () => {
        startInfo.textContent = event.start_time
            ? `${event.name}: sudah dimulai ${new Date(event.start_time).toLocaleTimeString('id-ID')}.`
            : `${event.name}: belum dimulai. Tekan tombol saat aba-aba start; waktu semua peserta tercatat serentak.`;
        startBtn.disabled = !!event.start_time;
    };

    (async () => {
        const r = await api('/event/active');
        if (!r.ok) { startInfo.textContent = 'Belum ada acara aktif.'; return; }
        event = r.data.data;
        showStart();
    })();

    startBtn.addEventListener('click', async () => {
        if (!confirm(`Mulai "${event.name}" sekarang? Ini tidak bisa diulang.`)) return;
        setErr(startErr, '');
        startBtn.disabled = true;
        const r = await api('/panitia/start', { method: 'POST', body: { event_id: event.id } });
        if (!r.ok) { startBtn.disabled = false; return setErr(startErr, errText(r)); }
        event.start_time = r.data.data.start_time;
        showStart();
        input.focus();
    });

    const draw = () => {
        history.innerHTML = rows.length
            ? rows.map((x) => `<tr><td>${x.at}</td><td>${esc(x.no)} ${esc(x.name)}</td><td>${esc(x.cat)}</td><td>${x.dur}</td><td><span class="c-badge ${x.cls}">${x.label}</span></td></tr>`).join('')
            : '<tr><td colspan="5">Belum ada scan.</td></tr>';
    };

    const scan = async (barcode) => {
        const r = await api('/panitia/scan', { method: 'POST', body: { barcode } });
        const d = r.data.data;
        const [cls, label] = r.ok ? ['-ok', 'Valid'] : r.status === 409 ? ['-wait', 'Duplikat'] : ['-bad', 'Gagal'];
        result.className = `k-result ${cls}`;
        result.textContent = r.ok
            ? `${d.registration_number} ${d.full_name}: ${fmtDur(d.duration)}`
            : r.status === 409 ? `${r.data.message} ${d.registration_number} ${d.full_name}` : errText(r);
        rows.unshift({ at: new Date().toLocaleTimeString('id-ID'), no: d?.registration_number ?? '', name: d?.full_name ?? barcode, cat: d?.category ?? '-', dur: d?.duration != null ? fmtDur(d.duration) : '-', cls, label });
        rows = rows.slice(0, 20);
        draw();
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const v = input.value.trim();
        if (!v) return input.focus();
        input.value = '';
        await scan(v);
        input.focus();
    });
    input.focus();

    // Kamera: hanya jika browser punya BarcodeDetector
    const camBtn = document.getElementById('cam-btn');
    if ('BarcodeDetector' in window) {
        camBtn.hidden = false;
        const video = document.getElementById('cam');
        let stream = null;
        const stop = () => { stream?.getTracks().forEach((t) => t.stop()); stream = null; video.hidden = true; camBtn.textContent = 'Kamera'; };
        camBtn.addEventListener('click', async () => {
            if (stream) return stop();
            try { stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }); } catch { return setErr(startErr, 'Kamera tidak bisa dibuka.'); }
            video.srcObject = stream;
            video.hidden = false;
            await video.play();
            camBtn.textContent = 'Tutup kamera';
            const det = new BarcodeDetector();
            let busy = false;
            const tick = async () => {
                if (!stream) return;
                if (!busy) {
                    busy = true;
                    const found = await det.detect(video).catch(() => []);
                    if (found[0]) { await scan(found[0].rawValue); await new Promise((r) => setTimeout(r, 1500)); }
                    busy = false;
                }
                setTimeout(tick, 300);
            };
            tick();
        });
    }
}
