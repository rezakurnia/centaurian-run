// Check-status page: POST /api/registrations/check-status, render cards, upload payment proof.
const form = document.getElementById('status-form');

if (form) {
    const results = document.getElementById('status-results');
    const tpl = document.getElementById('status-card');
    const input = document.getElementById('identifier');
    const err = document.getElementById('err-identifier');
    const submit = document.getElementById('status-submit');
    const REG = { pending: 'Menunggu verifikasi', confirmed: 'Terkonfirmasi', cancelled: 'Dibatalkan' };
    const PAY = { unpaid: 'Belum dibayar', paid: 'Lunas', free: 'Gratis' };
    const BADGE = { pending: '-wait', unpaid: '-wait', confirmed: '-ok', paid: '-ok', free: '-ok', cancelled: '-bad' };
    const json = (url, opts) => fetch(url, { ...opts, headers: { Accept: 'application/json', ...opts.headers } });
    const setErr = (el, msg) => { el.textContent = msg; el.hidden = !msg; };

    const note = (r) => {
        if (r.registration_status === 'cancelled') return 'Pendaftaran ini dibatalkan. Hubungi panitia jika menurutmu ini keliru.';
        if (r.payment_status === 'unpaid') return r.payment_proof_url
            ? 'Bukti pembayaran sudah kami terima dan sedang menunggu verifikasi panitia. Kamu bisa mengunggah ulang jika berkasnya salah.'
            : 'Selesaikan pembayaran, lalu unggah buktinya di bawah ini agar panitia bisa memverifikasi.';
        return 'Pendaftaranmu sudah terkonfirmasi. Tunjukkan barcode dari emailmu (atau kode di atas) saat check-in.';
    };

    const render = (r) => {
        const card = tpl.content.firstElementChild.cloneNode(true);
        const f = (n) => card.querySelector(`[data-f="${n}"]`);
        f('event').textContent = r.event;
        f('number').textContent = r.registration_number;
        f('name').textContent = r.participant?.full_name ?? '-';
        f('category').textContent = r.category;
        f('package').textContent = r.package;
        f('created').textContent = new Date(r.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        for (const [k, map, val] of [['reg-badge', REG, r.registration_status], ['pay-badge', PAY, r.payment_status]]) {
            f(k).textContent = map[val] ?? val;
            f(k).classList.add(BADGE[val] ?? '-wait');
        }
        f('note').textContent = note(r);
        if (r.registration_status === 'confirmed' && r.barcode) {
            f('barcode').textContent = r.barcode;
            f('barcode').hidden = f('barcode-label').hidden = false;
        }

        const payable = r.payment_status === 'unpaid' && r.registration_status !== 'cancelled';
        const upload = f('upload');
        upload.hidden = !payable;
        if (r.payment_proof_url) {
            f('proof').hidden = false;
            f('proof-link').href = r.payment_proof_url;
        }

        const label = f('file-label');
        const file = f('file');
        file.id = `file-${r.registration_number}`;
        label.setAttribute('for', file.id);
        const fileErr = f('upload-error');
        fileErr.id = `err-${file.id}`;
        file.setAttribute('aria-describedby', fileErr.id);

        upload.addEventListener('submit', async (e) => {
            e.preventDefault();
            setErr(fileErr, '');
            file.removeAttribute('aria-invalid');
            const bad = (m) => { setErr(fileErr, m); file.setAttribute('aria-invalid', 'true'); file.focus(); };
            if (!file.files[0]) return bad('Pilih berkas bukti pembayaran dulu.');
            if (file.files[0].size > 2048 * 1024) return bad('Ukuran berkas maksimal 2 MB.');

            const btn = f('upload-submit');
            btn.disabled = true;
            try {
                const body = new FormData();
                body.append('payment_proof', file.files[0]);
                const res = await json(`/api/registrations/${encodeURIComponent(r.registration_number)}/payment-proof`, { method: 'POST', body });
                const out = await res.json().catch(() => ({}));
                if (res.ok) {
                    const fresh = render(out.data);
                    card.replaceWith(fresh);
                    fresh.querySelector('[data-f="note"]').textContent = 'Bukti pembayaran terkirim. Panitia akan memverifikasinya, cek lagi nanti.';
                    fresh.scrollIntoView({ block: 'center' });
                    return;
                }
                bad(out.errors?.payment_proof?.[0] ?? out.message ?? 'Gagal mengunggah. Coba lagi.');
            } catch {
                bad('Tidak bisa terhubung ke server. Periksa koneksimu lalu coba lagi.');
            } finally {
                btn.disabled = false;
            }
        });
        return card;
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
                return results.firstElementChild.scrollIntoView({ block: 'nearest' });
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

    // Arriving from the success screen: /cek-status?no=S-0001
    const preset = new URLSearchParams(location.search).get('no');
    if (preset) { input.value = preset; lookup(preset); }
}
