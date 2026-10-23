import { gsap } from 'gsap';

/**
 * <c-rive data-src="/rive/x.riv" data-autoplay="true" data-reveal="true" ...>
 * Plays one .riv file in the <canvas> it wraps (runtime: @rive-app/canvas-lite, default Fit.Contain / Alignment.Center).
 * The runtime and the .riv file load only once the canvas is within 300px of the viewport.
 * Rendering only runs while the canvas is on screen; with data-reveal the canvas wrapper pops in with an elastic scale.
 */
class CRive extends HTMLElement {
    constructor() {
        super();
        this.src = this.getAttribute('data-src');
        const still = matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.autoplay = !still && this.getAttribute('data-autoplay') === 'true';
        this.reveal = !still && this.getAttribute('data-reveal') === 'true';
        this.revealDelay = parseFloat(this.getAttribute('data-reveal-delay') || '0');
        this.revealTranslateX = this.getAttribute('data-reveal-translate-x') || '0%';
        this.$canvas = this.querySelector('canvas');
        this.rive = null;
        this.timeline = null;
        this.intersectionObserver = null;
        this.hasRevealed = false;
        this.isReadyToReveal = false;
        this.resizeTimer = null;
    }

    connectedCallback() {
        if (!this.src || !this.$canvas) return;
        this.$canvas.setAttribute('aria-hidden', 'true'); // decorative illustration
        this.loadObserver = new IntersectionObserver((entries) => {
            if (!entries.some((entry) => entry.isIntersecting)) return;
            this.loadObserver.disconnect();
            this.initRive();
        }, { rootMargin: '300px' });
        this.loadObserver.observe(this.$canvas);
        this.intersectionObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    this.rive?.startRendering();
                    if (this.reveal && !this.hasRevealed) {
                        this.playReveal();
                        this.isReadyToReveal = true;
                    }
                } else {
                    this.rive?.stopRendering();
                }
            });
        });
        this.intersectionObserver.observe(this.$canvas);
        window.addEventListener('resize', this.onResize);
    }

    disconnectedCallback() {
        this.loadObserver?.disconnect();
        this.intersectionObserver?.disconnect();
        window.removeEventListener('resize', this.onResize);
        clearTimeout(this.resizeTimer);
        this.rive?.cleanup();
        this.timeline?.kill();
        this.timeline = null;
    }

    // Debounced (200ms) so the drawing surface follows the canvas size
    onResize = () => {
        clearTimeout(this.resizeTimer);
        this.resizeTimer = setTimeout(() => this.rive?.resizeDrawingSurfaceToCanvas(), 200);
    };

    async initRive() {
        const { Rive } = await import('../rive.js');
        if (!this.isConnected) return;
        this.rive = new Rive({
            src: this.src,
            canvas: this.$canvas,
            autoplay: this.autoplay,
            onLoad: () => {
                this.rive?.resizeDrawingSurfaceToCanvas();
                if (this.reveal) {
                    this.setTimeline();
                    if (this.isReadyToReveal) this.playReveal();
                }
            },
            onLoadError: (err) => console.warn(`[c-rive] Could not load ${this.src}:`, err),
        });
    }

    setTimeline() {
        this.timeline = gsap.timeline({
            paused: true,
            delay: this.revealDelay,
            onStart: () => { this.hasRevealed = true; },
        });

        const target = this.$canvas.parentElement;
        this.timeline
            .fromTo(target, { scale: 0.75, y: 100 }, { scale: 1, y: 0, duration: 1.75, ease: 'elastic.out(1, 0.3)' })
            .fromTo(target, { x: this.revealTranslateX }, { x: '0%', duration: 1.75, ease: 'power3.inOut' }, 0)
            .fromTo(target, { opacity: 0 }, { opacity: 1, duration: 0.15, ease: 'power1.out' }, 0);
    }

    playReveal() {
        this.timeline?.play();
    }
}

if (!customElements.get('c-rive')) {
    customElements.define('c-rive', CRive);
}

export default CRive;
