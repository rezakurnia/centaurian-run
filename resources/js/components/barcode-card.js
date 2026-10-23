// QR pop-up for the participant's own barcode (the string POST /api/registrations returns).
// The barcode is the scan credential, so it is drawn in the browser and only kept in this tab's
// sessionStorage between registering and coming back from the payment page.
import QRCode from 'qrcode';

const dialog = document.getElementById('barcode-dialog');
const key = (number) => 'barcode:' + number;

export const stashBarcode = (number, barcode) => {
    try { sessionStorage.setItem(key(number), barcode); } catch { /* private mode: the email still has it */ }
};

export const stashedBarcode = (number) => {
    try { return sessionStorage.getItem(key(number)); } catch { return null; }
};

export const showBarcode = async (number, barcode) => {
    if (!dialog || !barcode) return;
    const canvas = document.getElementById('barcode-canvas');
    await QRCode.toCanvas(canvas, barcode, { width: 256, margin: 2 });
    document.getElementById('barcode-number').textContent = number;
    document.getElementById('barcode-download').href = canvas.toDataURL('image/png');

    if (typeof dialog.showModal === 'function') {
        if (!dialog.open) dialog.showModal();
        document.getElementById('barcode-close')?.focus();
    } else {
        dialog.setAttribute('open', '');
    }
};

if (dialog) {
    const close = () => (dialog.open ? dialog.close() : dialog.removeAttribute('open'));
    document.getElementById('barcode-close')?.addEventListener('click', close);
    dialog.addEventListener('click', (e) => { if (e.target === dialog) close(); });
}
