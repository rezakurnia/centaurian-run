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

    const finishBtn = document.getElementById('finish-btn');
    const time = (v) => new Date(v).toLocaleTimeString('id-ID');

    // Admin → Pengaturan, "Konfigurasi Scan" (GET /api/settings); defaults match config/app_settings.php
    const cfg = { cooldown: 3, sound: true, autoClear: 5, startHour: '06:00', finishAfter: 60 };
    const settings = fetch('/api/settings', { headers: { Accept: 'application/json' } })
        .then((r) => r.json())
        .then(({ data: s }) => {
            const num = (v, d) => (Number.isFinite(Number(v)) && v !== '' ? Number(v) : d);
            Object.assign(cfg, {
                cooldown: num(s.scan_cooldown_seconds, cfg.cooldown),
                sound: s.scan_sound_enabled !== '0',
                autoClear: num(s.scan_auto_clear_seconds, cfg.autoClear),
                startHour: s.event_start_hour || cfg.startHour,
                finishAfter: num(s.finish_button_visible_minutes, cfg.finishAfter),
            });
        })
        .catch(() => {});

    let finishTimer;
    const showStart = () => {
        startInfo.textContent = event.finish_time
            ? `${event.name}: selesai ${time(event.finish_time)}. Scan ditutup.`
            : event.start_time
                ? `${event.name}: sudah dimulai ${time(event.start_time)}.`
                : `${event.name}: belum dimulai, jadwal start ${cfg.startHour}. Tekan tombol saat aba-aba start; waktu semua peserta tercatat serentak.`;
        startBtn.disabled = !!event.start_time;
        // The finish button appears finishAfter minutes after the start, so it can't be pressed by accident early on
        clearTimeout(finishTimer);
        const wait = event.start_time ? new Date(event.start_time).getTime() + cfg.finishAfter * 60000 - Date.now() : 0;
        finishBtn.hidden = !event.start_time || !!event.finish_time || wait > 0;
        if (event.start_time && !event.finish_time && wait > 0) finishTimer = setTimeout(showStart, Math.min(wait, 2 ** 31 - 1));
        // After finish the API rejects every scan, so close the form too
        form.querySelectorAll('input, button').forEach((el) => { el.disabled = !!event.finish_time; });
    };

    // Short beep: high for valid, low for duplicate/failed (Web Audio, no sound file)
    let audio;
    const beep = (ok) => {
        if (!cfg.sound) return;
        try {
            audio ??= new AudioContext();
            const osc = audio.createOscillator();
            const gain = audio.createGain();
            osc.frequency.value = ok ? 880 : 220;
            gain.gain.setValueAtTime(0.2, audio.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audio.currentTime + 0.25);
            osc.connect(gain).connect(audio.destination);
            osc.start();
            osc.stop(audio.currentTime + 0.25);
        } catch { /* no audio: scanning still works */ }
    };

    finishBtn.addEventListener('click', async () => {
        if (!confirm(`Akhiri "${event.name}" sekarang? Setelah ini scan tidak bisa dilakukan lagi.`)) return;
        setErr(startErr, '');
        finishBtn.disabled = true;
        const r = await api('/panitia/finish', { method: 'POST', body: { event_id: event.id } });
        finishBtn.disabled = false;
        if (!r.ok) return setErr(startErr, errText(r));
        event.finish_time = r.data.data.finish_time;
        showStart();
    });

    (async () => {
        const [r] = await Promise.all([api('/event/active'), settings]);
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

    // Same barcode again within the cooldown (double read by the camera or scanner) is ignored
    let last = { code: '', at: 0 };
    let clearTimer;
    const scan = async (barcode) => {
        if (barcode === last.code && Date.now() - last.at < cfg.cooldown * 1000) return;
        last = { code: barcode, at: Date.now() };
        const r = await api('/panitia/scan', { method: 'POST', body: { barcode } });
        const d = r.data.data;
        const [cls, label] = r.ok ? ['-ok', 'Valid'] : r.status === 409 ? ['-wait', 'Duplikat'] : ['-bad', 'Gagal'];
        beep(r.ok);
        clearTimeout(clearTimer);
        if (cfg.autoClear > 0) clearTimer = setTimeout(() => { result.className = 'k-result'; result.textContent = ''; }, cfg.autoClear * 1000);
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
                    if (found[0]) { await scan(found[0].rawValue); await new Promise((r) => setTimeout(r, cfg.cooldown * 1000)); }
                    busy = false;
                }
                setTimeout(tick, 300);
            };
            tick();
        });
    }
}
