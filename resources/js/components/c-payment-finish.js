// /pembayaran/selesai/{no}: Midtrans sends the browser back here right after paying. The webhook
// may land later (and might not be configured at all), so poll the endpoint that asks Midtrans for
// the transaction status, then reload into the success state once it is settled.
import { showBarcode, stashedBarcode } from './barcode-card.js';

const waiting = document.getElementById('payment-waiting');

// Settled state: offer the QR if this browser registered the participant
const qrOpen = document.getElementById('barcode-open');
if (qrOpen) {
    const number = qrOpen.dataset.number;
    const barcode = stashedBarcode(number);
    if (barcode) {
        qrOpen.hidden = false;
        qrOpen.addEventListener('click', () => showBarcode(number, barcode));
        showBarcode(number, barcode);
    }
}

if (waiting) {
    const number = waiting.dataset.number;
    const slow = document.getElementById('payment-slow');
    const startedAt = Date.now();
    const maxWait = 120000;

    let delay = 3000;

    const check = async () => {
        try {
            const res = await fetch('/api/registrations/' + encodeURIComponent(number) + '/payment-status', {
                method: 'POST',
                headers: { Accept: 'application/json' },
            });
            // Rate limited: back off instead of hammering the endpoint
            if (res.status === 429) delay = Math.min(delay * 2, 30000);
            const json = await res.json().catch(() => ({}));
            const status = json.data?.payment_status;

            if (status && ['paid', 'free'].includes(status)) {
                window.location.reload();
                return;
            }
        } catch {
            // Offline or server hiccup: the next tick tries again
        }

        if (Date.now() - startedAt >= maxWait) {
            if (slow) slow.hidden = false;
            return;
        }

        window.setTimeout(check, delay);
    };

    window.setTimeout(check, 2000);
}

// Payment lapsed: ask for a fresh Snap page (takes the quota seat again) and go there
const retry = document.getElementById('payment-retry');
if (retry) {
    const number = document.getElementById('payment-waiting').dataset.number;
    const error = document.getElementById('payment-retry-error');
    retry.addEventListener('click', async () => {
        retry.disabled = true;
        error.hidden = true;
        try {
            const res = await fetch('/api/registrations/' + encodeURIComponent(number) + '/payment-retry', {
                method: 'POST',
                headers: { Accept: 'application/json' },
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.payment?.redirect_url) return window.location.assign(json.payment.redirect_url);
            if (res.ok && json.data?.paid) return window.location.reload();
            error.textContent = res.status === 429 ? 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.' : json.message || 'Tidak bisa membuka halaman pembayaran.';
        } catch {
            error.textContent = 'Tidak bisa terhubung ke server. Periksa koneksimu lalu coba lagi.';
        }
        error.hidden = false;
        retry.disabled = false;
    });
}
