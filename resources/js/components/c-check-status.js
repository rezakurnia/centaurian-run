// BIB Checking page: POST /api/registrations/check-status, then open the result as a pop-up —
// the celebration BIB card (with the QR) for lunas/gratis registrations, an informational card for the rest.
import { drawBarcode, stashedBarcode } from './barcode-card.js';

const form = document.getElementById('status-form');

if (form) {
    const results = document.getElementById('status-results');
    const dialog = document.getElementById('status-dialog');
    const dialogClose = document.getElementById('status-close');
    const bibTpl = document.getElementById('bib-card');
    const statusTpl = document.getElementById('status-card');
    const input = document.getElementById('identifier');
    const err = document.getElementById('err-identifier');
    const submit = document.getElementById('status-submit');
    const REG = { pending: 'Menunggu verifikasi', confirmed: 'Terkonfirmasi', cancelled: 'Dibatalkan' };
    const PAY = { unpaid: 'Belum dibayar', paid: 'Lunas', free: 'Gratis' };
    const BADGE = { pending: '-wait', unpaid: '-wait', confirmed: '-ok', paid: '-ok', free: '-ok', cancelled: '-bad' };
    const json = (url, opts) => fetch(url, { ...opts, headers: { Accept: 'application/json', ...opts.headers } });
    const setErr = (el, msg) => { el.textContent = msg; el.hidden = !msg; };
    const field = (card, name) => card.querySelector(`[data-f="${name}"]`);

    // "Centaurian Run" plus the event year, unless the event name already carries it
    const eventLabel = (r) => {
        const year = bibTpl?.dataset.year;
        return year && !String(r.event).includes(year) ? `${r.event} ${year}` : r.event;
    };

    const badge = (el, map, value) => {
        el.textContent = map[value] ?? value;
        el.classList.add(BADGE[value] ?? '-wait');
    };

    const note = (r) => {
        if (r.can_retry_payment) return 'Waktu pembayaran habis, jadi tempatmu dilepas. Bayar lagi selama kuota kategori masih ada.';
        if (r.registration_status === 'cancelled') return 'Pendaftaran ini dibatalkan. Hubungi panitia jika menurutmu ini keliru.';
        if (r.payment_status === 'unpaid') return 'Pembayaran belum selesai, jadi nomor BIB belum bisa dibuka. Kartu BIB terbuka otomatis begitu pembayaranmu lunas.';
        return 'Pendaftaranmu sudah terkonfirmasi.';
    };

    const bibCard = (r) => {
        const card = bibTpl.content.firstElementChild.cloneNode(true);
        field(card, 'event').textContent = eventLabel(r);
        field(card, 'number').textContent = r.registration_number;
        field(card, 'name').textContent = r.participant?.full_name ?? '—';
        field(card, 'category').textContent = r.category;
        field(card, 'package').textContent = r.package;
        qrBlock(card, r.registration_number, r.barcode ?? stashedBarcode(r.registration_number));
        return card;
    };

    // The API sends the barcode only for a lookup that included the email; a number-only lookup
    // gets a small email form that repeats the lookup with both and then draws the QR.
    const qrBlock = (card, number, barcode) => {
        const wrap = field(card, 'qr-wrap');
        const qrForm = field(card, 'qr-form');
        const draw = async (code) => {
            const canvas = field(card, 'qr');
            await drawBarcode(canvas, code);
            field(card, 'qr-download').href = canvas.toDataURL('image/png');
            wrap.hidden = false;
            qrForm.hidden = true;
        };
        if (barcode) return draw(barcode);

        const email = field(card, 'qr-email');
        const qrErr = field(card, 'qr-error');
        email.id = `qr-email-${number}`;
        field(card, 'qr-label').htmlFor = email.id;
        qrErr.id = `${email.id}-error`;
        email.setAttribute('aria-describedby', qrErr.id);
        qrForm.hidden = false;
        qrForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const value = email.value.trim();
            if (!value) return setErr(qrErr, 'Masukkan email yang dipakai saat mendaftar.'), email.focus();
            const btn = qrForm.querySelector('button');
            btn.disabled = true;
            setErr(qrErr, '');
            try {
                const res = await json('/api/registrations/check-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ registration_number: number, email: value }),
                });
                const out = await res.json().catch(() => ({}));
                const code = out.data?.[0]?.barcode;
                if (res.ok && code) return await draw(code);
                setErr(qrErr, res.status === 429 ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.'
                    : res.status === 422 ? 'Format email tidak valid.'
                    : 'Email tidak cocok dengan nomor peserta ini.');
                email.focus();
            } catch {
                setErr(qrErr, 'Tidak bisa terhubung ke server. Periksa koneksimu lalu coba lagi.');
            } finally {
                btn.disabled = false;
            }
        });
    };

    const statusCard = (r) => {
        const card = statusTpl.content.firstElementChild.cloneNode(true);
        field(card, 'event').textContent = eventLabel(r);
        field(card, 'number').textContent = r.registration_number;
        field(card, 'category').textContent = r.category;
        field(card, 'package').textContent = r.package;
        field(card, 'created').textContent = new Date(r.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        badge(field(card, 'reg-badge'), REG, r.registration_status);
        badge(field(card, 'pay-badge'), PAY, r.payment_status);
        // Snap page stored at registration lets an unpaid participant pick the payment back up
        const payLink = field(card, 'pay-link');
        const payRow = field(card, 'pay-row');
        if (payLink && payRow && r.payment_url) {
            payLink.href = r.payment_url;
            payRow.hidden = false;
        }
        if (r.can_retry_payment) {
            const retryBtn = field(card, 'retry-btn');
            const retryErr = field(card, 'retry-error');
            field(card, 'retry-row').hidden = false;
            retryBtn.addEventListener('click', async () => {
                retryBtn.disabled = true;
                setErr(retryErr, '');
                try {
                    const res = await json(`/api/registrations/${encodeURIComponent(r.registration_number)}/payment-retry`, { method: 'POST' });
                    const out = await res.json().catch(() => ({}));
                    if (res.ok && out.payment?.redirect_url) return window.location.assign(out.payment.redirect_url);
                    if (res.ok && out.data?.paid) return window.location.assign('/pembayaran/selesai/' + encodeURIComponent(r.registration_number));
                    setErr(retryErr, res.status === 429 ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.' : out.message || 'Tidak bisa membuka halaman pembayaran.');
                } catch {
                    setErr(retryErr, 'Tidak bisa terhubung ke server. Periksa koneksimu lalu coba lagi.');
                }
                retryBtn.disabled = false;
            });
        }
        field(card, 'note').textContent = note(r);
        return card;
    };

    const render = (r) => (r.bib_ready && bibTpl ? bibCard(r) : statusCard(r));

    let lastQuery = '';
    let pollTimer = 0;
    let pollGen = 0;

    // Bumping the generation also cancels a tick that is already awaiting a response
    const stopPolling = () => {
        pollGen++;
        window.clearTimeout(pollTimer);
        pollTimer = 0;
    };

    // Midtrans confirms the payment asynchronously. While a card is still unpaid, ask the status
    // endpoint (which syncs with Midtrans) and re-render as soon as it flips to lunas, so nobody
    // has to press "Cek BIB" again.
    const startPolling = (rows) => {
        stopPolling();
        const unpaid = rows.filter((r) => r.payment_status === 'unpaid').map((r) => r.registration_number);
        if (!unpaid.length) return;

        const startedAt = Date.now();
        const gen = pollGen;
        let delay = 5000;
        const tick = async () => {
            for (const number of unpaid) {
                try {
                    const res = await json(`/api/registrations/${encodeURIComponent(number)}/payment-status`, { method: 'POST' });
                    if (gen !== pollGen) return;
                    // Rate limited: back off instead of hammering the endpoint
                    if (res.status === 429) delay = Math.min(delay * 2, 30000);
                    const out = await res.json().catch(() => ({}));
                    if (out.data?.payment_status && out.data.payment_status !== 'unpaid') {
                        return lookup(lastQuery);
                    }
                } catch {
                    // Offline or a hiccup: the next tick tries again
                }
            }
            if (gen === pollGen && Date.now() - startedAt < 120000) {
                pollTimer = window.setTimeout(tick, delay);
            }
        };
        pollTimer = window.setTimeout(tick, delay);
    };

    // Native <dialog> gives the pop-up focus trapping and Escape for free; older browsers get an
    // inline panel instead so the result is never hidden.
    const openDialog = () => {
        if (!dialog) {
            return results.scrollIntoView({ block: 'nearest' });
        }
        if (typeof dialog.showModal === 'function') {
            if (!dialog.open) {
                dialog.showModal();
                dialogClose?.focus();
            }
        } else {
            dialog.setAttribute('open', '');
        }
    };

    const closeDialog = () => {
        stopPolling();
        if (dialog?.open) {
            dialog.close();
        } else {
            dialog?.removeAttribute('open');
        }
    };

    const lookup = async (value) => {
        stopPolling();
        setErr(err, '');
        input.removeAttribute('aria-invalid');
        results.replaceChildren();
        if (!value) {
            input.setAttribute('aria-invalid', 'true');
            setErr(err, 'Masukkan nomor peserta atau email.');
            return input.focus();
        }
        lastQuery = value;
        // Registration numbers look like "S-0001"; anything with "@" is an email
        const body = value.includes('@') ? { email: value } : { registration_number: value.toUpperCase() };
        submit.disabled = true;
        try {
            const res = await json('/api/registrations/check-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            const out = await res.json().catch(() => ({}));
            if (res.ok && out.data?.length) {
                results.append(...out.data.map(render));
                startPolling(out.data);
                return openDialog();
            }
            input.setAttribute('aria-invalid', 'true');
            setErr(err, res.status === 404 ? 'Pendaftaran tidak ditemukan. Periksa nomor atau emailmu, lalu coba lagi.'
                : res.status === 429 ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.'
                : (out.errors && Object.values(out.errors)[0]?.[0]) || 'Terjadi kendala di server. Coba lagi sebentar lagi.');
        } catch {
            setErr(err, 'Tidak bisa terhubung ke server. Periksa koneksimu lalu coba lagi.');
        } finally {
            submit.disabled = false;
        }
    };

    form.addEventListener('submit', (e) => { e.preventDefault(); lookup(input.value.trim()); });
    input.addEventListener('input', () => { input.removeAttribute('aria-invalid'); setErr(err, ''); });
    dialogClose?.addEventListener('click', closeDialog);
    // A click on the backdrop lands on the <dialog> element itself
    dialog?.addEventListener('click', (e) => { if (e.target === dialog) closeDialog(); });
    // Escape closes the native dialog; stop the background status check with it
    dialog?.addEventListener('close', () => { stopPolling(); input.focus(); });

    // Arriving from the success screen: /cek-status?no=S-0001
    const preset = new URLSearchParams(location.search).get('no');
    if (preset) { input.value = preset; lookup(preset); }
}
