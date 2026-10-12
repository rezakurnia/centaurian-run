import { Rive, Layout, Fit, Alignment } from '@rive-app/canvas';
import { gsap } from 'gsap';

class CRive extends HTMLElement {
    constructor() {
        super();
        this.src = this.getAttribute('data-src') || this.getAttribute('src');
        this.autoplay = this.getAttribute('data-autoplay') !== 'false';
        this.reveal = this.getAttribute('data-reveal') === 'true';
        this.revealDelay = parseFloat(this.getAttribute('data-reveal-delay') || '0');
        this.revealTranslateX = this.getAttribute('data-reveal-translate-x') || '0%';
        this.$canvas = null;
        this.rive = null;
        this.timeline = null;
        this.intersectionObserver = null;
        this.hasRevealed = false;
        this.isReadyToReveal = false;
    }

    connectedCallback() {
        this.src = this.getAttribute('data-src') || this.getAttribute('src');
        if (!this.src) return;

        this.$canvas = this.querySelector('canvas');
        if (!this.$canvas) {
            const wrap = document.createElement('div');
            wrap.className = 'h-full w-full';
            this.$canvas = document.createElement('canvas');
            this.$canvas.className = 'h-full w-full';
            wrap.appendChild(this.$canvas);
            this.appendChild(wrap);
        }

        this.initRive();
        this.bindEvents();
    }

    disconnectedCallback() {
        this.unbindEvents();
        this.rive?.cleanup();
        this.timeline?.kill();
        this.timeline = null;
    }

    bindEvents() {
        this.setIntersectionObserver();
        window.addEventListener('resize', this.onResize);
    }

    unbindEvents() {
        this.intersectionObserver?.disconnect();
        window.removeEventListener('resize', this.onResize);
    }

    onResize = () => {
        this.rive?.resizeDrawingSurfaceToCanvas();
    };

    setIntersectionObserver() {
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
        }, { rootMargin: '120px' });

        if (this.$canvas) {
            this.intersectionObserver.observe(this.$canvas);
        }
    }

    initRive() {
        try {
            this.rive = new Rive({
                src: this.src,
                canvas: this.$canvas,
                autoplay: this.autoplay,
                layout: new Layout({
                    fit: Fit.Contain,
                    alignment: Alignment.Center,
                }),
                onLoad: () => {
                    this.rive?.resizeDrawingSurfaceToCanvas();
                    if (this.reveal) {
                        this.setTimeline();
                        if (this.isReadyToReveal) {
                            this.playReveal();
                        }
                    }
                },
                onLoadError: (err) => {
                    console.warn(`[c-rive] Could not load ${this.src}:`, err);
                },
            });
        } catch (e) {
            console.error('[c-rive] Init error:', e);
        }
    }

    setTimeline() {
        this.timeline = gsap.timeline({
            paused: true,
            delay: this.revealDelay,
            onStart: () => {
                this.hasRevealed = true;
            },
        });

        const target = this.$canvas?.parentElement || this;
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
