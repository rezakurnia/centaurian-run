// Registration form: live summary + POST /api/registrations with per-field errors.
const form = document.getElementById('register-form');

if (form) {
    const $ = (id) => document.getElementById(id);
    const rupiah = (p) => (p === 0 ? 'Gratis' : 'Rp' + p.toLocaleString('id-ID'));
    const required = {
        category_id: 'Pilih salah satu kategori.',
        package_id: 'Pilih salah satu paket.',
        full_name: 'Nama lengkap wajib diisi.',
        email: 'Email wajib diisi.',
        phone: 'Nomor telepon wajib diisi.',
        birth_place: 'Tempat lahir wajib diisi.',
        birth_date: 'Tanggal lahir wajib diisi.',
        gender: 'Pilih jenis kelamin.',
    };
    const submit = $('register-submit');
    const formError = $('form-error');

    const setError = (field, msg) => {
        const el = form.querySelector(`[data-error="${field}"]`);
        if (el) { el.textContent = msg; el.hidden = !msg; }
        form.querySelectorAll(`[name="${field}"]`).forEach((i) => i.type !== 'radio' && (msg ? i.setAttribute('aria-invalid', 'true') : i.removeAttribute('aria-invalid')));
    };
    const clearErrors = () => {
        Object.keys(required).forEach((f) => setError(f, ''));
        setError('motivation', '');
        formError.hidden = true;
    };
    const showFirst = (fields) => {
        const first = form.querySelector(`[name="${fields[0]}"]`);
        first?.closest('fieldset')?.scrollIntoView({ block: 'center' });
        first?.focus({ preventScroll: true });
    };

    const summary = () => {
        const cat = form.querySelector('[name=category_id]:checked');
        const pkg = form.querySelector('[name=package_id]:checked');
        $('sum-category').textContent = cat?.dataset.name ?? 'Belum dipilih';
        $('sum-package').textContent = pkg?.dataset.name ?? 'Belum dipilih';
        $('sum-price').textContent = pkg ? rupiah(Number(pkg.dataset.price)) : '–';
    };
    form.addEventListener('change', summary);
    summary();

    // Clear a field's error as soon as it's edited
    form.addEventListener('input', (e) => e.target.name && setError(e.target.name, ''));
    form.addEventListener('change', (e) => e.target.name && setError(e.target.name, ''));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();

        const data = Object.fromEntries(new FormData(form));
        const missing = Object.keys(required).filter((f) => !String(data[f] ?? '').trim());
        missing.forEach((f) => setError(f, required[f]));
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

            if (res.status === 422 && json.errors) {
                const fields = Object.keys(json.errors);
                fields.forEach((f) => setError(f, json.errors[f][0]));
                formError.textContent = 'Periksa kembali isian yang ditandai.';
                formError.hidden = false;
                return showFirst(fields);
            }

            formError.textContent = res.status === 429
                ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.'
                : 'Terjadi kendala di server. Datamu mungkin sudah tersimpan: cek status dengan emailmu sebelum mendaftar ulang.';
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
