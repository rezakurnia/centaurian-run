// Registration form: live summary + POST /api/registrations with per-field errors.
import { showBarcode, stashBarcode } from './barcode-card.js';

const form = document.getElementById('register-form');

if (form) {
    const $ = (id) => document.getElementById(id);
    const rupiah = (p) => (p === 0 ? 'Gratis' : 'Rp' + p.toLocaleString('id-ID'));
    const base = {
        category_id: 'Pilih salah satu kategori.',
        full_name: 'Nama lengkap wajib diisi.',
        email: 'Email wajib diisi.',
        phone: 'Nomor telepon wajib diisi.',
        birth_place: 'Tempat lahir wajib diisi.',
        birth_date: 'Tanggal lahir wajib diisi.',
        gender: 'Pilih jenis kelamin.',
    };
    // Mirrors the per-category rules in StoreRegistrationRequest
    const byCode = {
        S: { nis: 'NIS wajib diisi.', class: 'Kelas wajib diisi.' },
        E: { nip: 'NIP wajib diisi.', position: 'Jabatan wajib diisi.', unit: 'Unit wajib diisi.' },
        P: { child_name: 'Nama anak wajib diisi.', child_class: 'Kelas anak wajib diisi.' },
    };
    const allFields = Object.assign({ motivation: '' }, base, ...Object.values(byCode));
    const checkedCategory = () => form.querySelector('[name=category_id]:checked');
    const required = () => ({ ...base, ...byCode[checkedCategory()?.dataset.code] });
    const submit = $('register-submit');
    const formError = $('form-error');

    const setError = (field, msg) => {
        const el = form.querySelector(`[data-error="${field}"]`);
        if (el) { el.textContent = msg; el.hidden = !msg; }
        form.querySelectorAll(`[name="${field}"]`).forEach((i) => i.type !== 'radio' && (msg ? i.setAttribute('aria-invalid', 'true') : i.removeAttribute('aria-invalid')));
    };
    const clearErrors = () => {
        Object.keys(allFields).forEach((f) => setError(f, ''));
        formError.hidden = true;
    };
    const showFirst = (fields) => {
        const first = form.querySelector(`[name="${fields[0]}"]`);
        first?.closest('fieldset')?.scrollIntoView({ block: 'center' });
        first?.focus({ preventScroll: true });
    };

    // Remaining spots per category, filled by the quota fetch below and reported in the summary
    const quotas = new Map();
    let quotasLoaded = false;

    // The package follows the category, so the summary and extra fields read from the checked category
    const summary = () => {
        const cat = checkedCategory();
        $('sum-category').textContent = cat?.dataset.name ?? 'Belum dipilih';
        $('sum-package').textContent = cat?.dataset.package || 'Ikut kategori';
        $('sum-price').textContent = cat?.dataset.price ? rupiah(Number(cat.dataset.price)) : '–';
        submit.querySelector('.c-button_label').textContent = Number(cat?.dataset.price) > 0 ? 'Lanjut ke pembayaran' : 'Kirim pendaftaran';
        const quota = quotas.get(cat?.value);
        $('sum-quota').textContent = !quotasLoaded || !cat ? '–' : (quota ? `${quota.remaining} dari ${quota.quota} tempat` : 'Tidak terbatas');
        form.querySelectorAll('fieldset[data-for]').forEach((f) => {
            const on = f.dataset.for === cat?.dataset.code;
            f.hidden = !on;
            f.disabled = !on;
        });
    };
    form.addEventListener('change', summary);
    summary();

    // ---- Guru Karyawan: NIP is checked against the employee master before anything else ----
    const nipInput = form.querySelector('[name="nip"]');
    const nipNote = $('nip-note');
    const nipHint = $('nip-hint');
    const nameNote = $('name-note');
    const nameInput = $('full_name');
    let verifiedNip = null;
    let nipRequest = null;
    let nipTimer = 0;

    const setNipNote = (msg) => {
        if (!nipNote) return;
        nipNote.textContent = msg;
        nipNote.hidden = !msg;
    };

    // The standing hint is only useful while the field is still empty
    const syncNipHint = () => {
        if (nipHint) nipHint.hidden = Boolean(nipInput?.value.trim());
    };

    // The name comes from the employee master, so the field is locked while that NIP is verified
    const unlockName = ({ clear = false } = {}) => {
        if (clear && nameInput?.readOnly) nameInput.value = '';
        if (nameInput) nameInput.readOnly = false;
        if (nameNote) nameNote.hidden = true;
    };

    const resetNip = ({ clearName = false } = {}) => {
        verifiedNip = null;
        setNipNote('');
        syncNipHint();
        unlockName({ clear: clearName });
    };

    const acceptNip = (employee) => {
        verifiedNip = employee.nip;
        setError('nip', '');
        setError('full_name', '');
        setNipNote(`NIP terdaftar sebagai ${employee.name}.`);
        syncNipHint();
        if (nameInput) {
            nameInput.value = employee.name;
            nameInput.readOnly = true;
        }
        if (nameNote) nameNote.hidden = false;
    };

    const rejectNip = (message) => {
        verifiedNip = null;
        setNipNote('');
        syncNipHint();
        setError('nip', message);
        unlockName({ clear: true });
    };

    const checkNip = async () => {
        const value = nipInput?.value.trim() ?? '';
        if (!value) {
            resetNip({ clearName: true });
            return;
        }
        if (value === verifiedNip) return;

        // Abort the previous lookup so an older answer can never overwrite a newer NIP
        nipRequest?.abort();
        const request = new AbortController();
        nipRequest = request;

        try {
            const res = await fetch('/api/employees/' + encodeURIComponent(value), {
                headers: { Accept: 'application/json' },
                signal: request.signal,
            });
            const json = await res.json().catch(() => ({}));
            if (nipRequest !== request) return;

            if (res.ok && json.data?.name) acceptNip(json.data);
            else if (res.status === 404) rejectNip('NIP tidak terdaftar sebagai guru/karyawan Alcent. Periksa kembali NIP-mu.');
            else rejectNip('Tidak bisa memeriksa NIP sekarang. Coba lagi sebentar lagi.');
        } catch (error) {
            if (error.name !== 'AbortError' && nipRequest === request) {
                rejectNip('Tidak bisa terhubung ke server. Periksa koneksimu lalu coba lagi.');
            }
        } finally {
            if (nipRequest === request) nipRequest = null;
        }
    };

    if (nipInput) {
        nipInput.addEventListener('input', () => {
            clearTimeout(nipTimer);
            if (nipInput.value.trim() !== verifiedNip) resetNip({ clearName: true });
            nipTimer = setTimeout(checkNip, 400);
        });
        nipInput.addEventListener('blur', () => {
            clearTimeout(nipTimer);
            checkNip();
        });
    }

    // Picking Guru Karyawan jumps straight to the NIP field; leaving the category releases the name
    form.addEventListener('change', (event) => {
        if (event.target.name !== 'category_id') return;
        clearTimeout(nipTimer);

        if (event.target.dataset.code === 'E') {
            if (nipInput && !nipInput.value.trim()) {
                nipInput.closest('.c-field')?.scrollIntoView({ block: 'center' });
                nipInput.focus({ preventScroll: true });
            }
            return;
        }

        resetNip();
    });

    const close = (text) => {
        $('register-closed').hidden = false;
        if (text) $('register-closed-text').textContent = text;
        submit.disabled = true;
    };
    fetch('/api/settings', { headers: { Accept: 'application/json' } })
        .then((r) => r.json())
        .then(({ data: s }) => {
            if (s?.registration_open === '0') close();
            else if (s?.registration_deadline && new Date().toLocaleDateString('sv') > s.registration_deadline) {
                close('Batas waktu pendaftaran sudah lewat.');
            }
        })
        .catch(() => {}); // the API still enforces both rules on submit

    // Remaining spots per category; quota 0/empty means unlimited (RegistrationController@store)
    fetch('/api/categories/quota', { headers: { Accept: 'application/json' } })
        .then((r) => r.json())
        .then(({ data }) => {
            if (!Array.isArray(data)) return;

            data.forEach((c) => {
                const input = form.querySelector(`[name=category_id][value="${c.id}"]`);
                if (!input || !c.quota) return;

                quotas.set(String(c.id), c);
                const option = input.closest('.c-option');
                if (!c.is_full) return;

                // A full category is disabled here so it cannot be picked; the API rejects it too
                input.checked = false;
                input.disabled = true;
                option?.querySelector('[data-full]')?.removeAttribute('hidden');
            });

            quotasLoaded = true;
            summary();
        })
        .catch(() => {});

    // Clear a field's error as soon as it's edited
    form.addEventListener('input', (e) => e.target.name && setError(e.target.name, ''));
    form.addEventListener('change', (e) => e.target.name && setError(e.target.name, ''));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();

        const data = Object.fromEntries(new FormData(form));
        const req = required();
        const missing = Object.keys(req).filter((f) => !String(data[f] ?? '').trim());
        missing.forEach((f) => setError(f, req[f]));
        if (missing.length) {
            formError.textContent = 'Periksa kembali isian yang ditandai.';
            formError.hidden = false;
            return showFirst(missing);
        }

        // Disabled before the NIP gate so a double click cannot start two submissions
        submit.disabled = true;

        // Guru Karyawan: never send a NIP that has not matched the employee master
        if (checkedCategory()?.dataset.code === 'E' && nipInput?.value.trim() && nipInput.value.trim() !== verifiedNip) {
            await checkNip();
            if (nipInput.value.trim() !== verifiedNip) {
                submit.disabled = false;
                formError.textContent = 'Periksa kembali NIP-mu sebelum mengirim.';
                formError.hidden = false;
                return showFirst(['nip']);
            }
        }

        try {
            const res = await fetch('/api/registrations', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(data),
            });
            const json = await res.json().catch(() => ({}));

            if (res.status === 201) return showSuccess(json);

            // Closed, past deadline or quota full: not tied to a field
            if (res.status === 422 && json.errors?.registration) {
                formError.textContent = json.errors.registration[0];
            } else if (res.status === 422 && json.errors) {
                const fields = Object.keys(json.errors);
                fields.forEach((f) => setError(f, json.errors[f][0]));
                formError.textContent = 'Periksa kembali isian yang ditandai.';
                formError.hidden = false;
                return showFirst(fields);
            } else {
                formError.textContent = res.status === 429
                    ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.'
                    : res.status === 503
                    ? (json.message || 'Halaman pembayaran belum bisa dibuat. Coba lagi sebentar lagi.')
                    : 'Terjadi kendala di server. Datamu mungkin sudah tersimpan: cek BIB dengan emailmu sebelum mendaftar ulang.';
            }
        } catch {
            formError.textContent = 'Tidak bisa terhubung ke server. Periksa koneksimu lalu coba lagi.';
        } finally {
            // Registration closed mid-session (close()) keeps the button off
            submit.disabled = !$('register-closed').hidden;
        }
        formError.hidden = false;
    });

    // A paid package comes back with payment.redirect_url. The participant is sent straight to
    // Snap: the success panel (and the participant number) only appear after the payment is
    // settled, on /pembayaran/selesai/{no}.
    const showSuccess = ({ message, data, payment }) => {
        const reg = data.registration;
        const payUrl = payment?.redirect_url ?? '';
        const unpaid = reg.payment_status === 'unpaid';

        if (payUrl) {
            // The QR appears on /pembayaran/selesai once the payment is settled
            stashBarcode(reg.registration_number, reg.barcode);
            form.hidden = true;
            const redirect = $('register-redirect');
            $('register-redirect-link').hidden = false;
            $('register-redirect-href').href = payUrl;
            redirect.hidden = false;
            redirect.scrollIntoView({ block: 'center' });
            // Straight to the payment page; the link above is the fallback if navigation is blocked
            window.setTimeout(() => window.location.assign(payUrl), 400);
            return;
        }

        // The API message promises an email, which an unpaid registration does not get yet
        $('success-message').textContent = unpaid ? 'Pendaftaranmu sudah tersimpan.' : message;
        $('success-number').textContent = reg.registration_number;
        $('success-status').textContent = unpaid ? 'Menunggu pembayaran' : 'Terkonfirmasi';

        $('success-pay').hidden = !unpaid;
        $('success-status-link').href = '/cek-status?no=' + encodeURIComponent(reg.registration_number);

        form.hidden = true;
        const box = $('register-success');
        box.hidden = false;
        box.scrollIntoView({ block: 'center' });
        box.focus({ preventScroll: true });

        // Free packages are confirmed already: open the QR right away
        const qrButton = $('success-qr');
        qrButton.hidden = unpaid;
        qrButton.onclick = () => showBarcode(reg.registration_number, reg.barcode);
        if (!unpaid) showBarcode(reg.registration_number, reg.barcode);
    };
}
