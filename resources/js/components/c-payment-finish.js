// /pembayaran/selesai/{no}: Midtrans sends the browser back here right after paying. The webhook
// may land later (and might not be configured at all), so poll the endpoint that asks Midtrans for
// the transaction status, then reload into the success state once it is settled.
const waiting = document.getElementById('payment-waiting');

if (waiting) {
    const number = waiting.dataset.number;
    const slow = document.getElementById('payment-slow');
    const startedAt = Date.now();
    const maxWait = 120000;

    const check = async () => {
        try {
            const res = await fetch('/api/registrations/' + encodeURIComponent(number) + '/payment-status', {
                method: 'POST',
                headers: { Accept: 'application/json' },
            });
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

        window.setTimeout(check, 3000);
    };

    window.setTimeout(check, 2000);
}
