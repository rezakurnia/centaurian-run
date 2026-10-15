// Registration form: live summary + POST /api/registrations with per-field errors.
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
        A: { graduation_year: 'Tahun lulus wajib diisi.' },
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

    // The package follows the category, so the summary and extra fields read from the checked category
    const summary = () => {
        const cat = checkedCategory();
        $('sum-category').textContent = cat?.dataset.name ?? 'Belum dipilih';
        $('sum-package').textContent = cat?.dataset.package || 'Ikut kategori';
        $('sum-price').textContent = cat?.dataset.price ? rupiah(Number(cat.dataset.price)) : '–';
        form.querySelectorAll('fieldset[data-for]').forEach((f) => {
            const on = f.dataset.for === cat?.dataset.code;
            f.hidden = !on;
            f.disabled = !on;
        });
    };
    form.addEventListener('change', summary);
    summary();

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
        .then(({ data }) => data?.forEach((c) => {
            const input = form.querySelector(`[name=category_id][value="${c.id}"]`);
            const el = input?.closest('.c-option').querySelector('[data-quota]');
            if (!el || !c.quota) return;
            el.hidden = false;
            if (c.is_full) {
                el.innerHTML = '<span class="c-badge -bad">Penuh</span>';
                input.checked = false;
                input.disabled = true;
                summary();
            } else {
                el.textContent = `Sisa ${c.remaining} dari ${c.quota} tempat`;
            }
        }))
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

        submit.disabled = true;
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
                    : 'Terjadi kendala di server. Datamu mungkin sudah tersimpan: cek status dengan emailmu sebelum mendaftar ulang.';
            }
        } catch {
            formError.textContent = 'Tidak bisa terhubung ke server. Periksa koneksimu lalu coba lagi.';
        } finally {
            submit.disabled = false;
        }
        formError.hidden = false;
    });

    const showSuccess = ({ message, data }) => {
        const reg = data.registration;
        const paid = reg.payment_status === 'unpaid';
        $('success-message').textContent = message;
        $('success-number').textContent = reg.registration_number;
        $('success-status').textContent = paid ? 'Menunggu pembayaran' : 'Terkonfirmasi';
        $('success-pay').hidden = !paid;
        const q = '?no=' + encodeURIComponent(reg.registration_number);
        $('success-pay-link').href = '/cek-status' + q;
        $('success-status-link').href = '/cek-status' + q;
        form.hidden = true;
        const box = $('register-success');
        box.hidden = false;
        box.scrollIntoView({ block: 'center' });
        box.focus({ preventScroll: true });
    };
}
