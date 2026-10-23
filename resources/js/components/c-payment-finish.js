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
