// BIB Checking page: POST /api/registrations/check-status, then open the result as a pop-up —
// the celebration BIB card for lunas/gratis registrations, an informational card for the rest.
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
        return card;
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
        field(card, 'note').textContent = note(r);
        return card;
    };

    const render = (r) => (r.bib_ready && bibTpl ? bibCard(r) : statusCard(r));

    // Native <dialog> gives the pop-up focus trapping and Escape for free; older browsers get an
    // inline panel instead so the result is never hidden.
    const openDialog = () => {
        if (!dialog) {
            return results.scrollIntoView({ block: 'nearest' });
        }
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
            dialogClose?.focus();
        } else {
            dialog.setAttribute('open', '');
        }
    };

    const closeDialog = () => {
        if (dialog?.open) {
            dialog.close();
        } else {
            dialog?.removeAttribute('open');
        }
    };

    const lookup = async (value) => {
        setErr(err, '');
        input.removeAttribute('aria-invalid');
        results.replaceChildren();
        if (!value) {
            input.setAttribute('aria-invalid', 'true');
            setErr(err, 'Masukkan nomor peserta atau email.');
            return input.focus();
        }
        // Registration numbers look like "SW-0001"; anything with "@" is an email
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

    // Arriving from the success screen: /cek-status?no=SW-0001
    const preset = new URLSearchParams(location.search).get('no');
    if (preset) { input.value = preset; lookup(preset); }
}
