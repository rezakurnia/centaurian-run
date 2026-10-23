{{-- QR pop-up for the participant's own barcode; filled by components/barcode-card.js, never rendered server-side --}}
<dialog id="barcode-dialog" class="c-dialog" aria-label="QR kartu masuk">
    <button type="button" class="c-dialog_close" id="barcode-close" aria-label="Tutup QR">
        <svg viewBox="0 0 20 20" width="20" height="20" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
    <div class="c-dialog_body">
        <article class="c-step text-center">
            <p class="body-md uppercase">Peserta Resmi</p>
            <h2 class="heading-sm mt-2">Ini kartu masukmu.</h2>
            <canvas id="barcode-canvas" class="mx-auto mt-6 max-w-full rounded-sm" role="img" aria-label="QR code kartu masuk"></canvas>
            <p class="heading-xs mt-4" id="barcode-number"></p>
            <p class="body-md mx-auto mt-3 max-w-100 text-balance">Tunjukkan QR ini saat pengambilan racepack dan check-in di hari acara.</p>
            <p class="mt-6"><a class="c-link inline-block py-3 -my-3" id="barcode-download" href="#" download="qr-peserta.png">Unduh QR</a></p>
        </article>
    </div>
</dialog>
