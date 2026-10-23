import { gsap } from 'gsap';
import { DrawSVGPlugin } from 'gsap/DrawSVGPlugin';
import { CustomEase } from 'gsap/CustomEase';

gsap.registerPlugin(DrawSVGPlugin, CustomEase);

/**
 * The green route. One paused GSAP timeline is scrubbed by the `pathProgress` event that
 * Locomotive Scroll fires for this element (data-scroll-event-progress="pathProgress").
 * Desktop and mobile have their own SVG + timeline; it rebuilds when the viewport crosses 1000px.
 */
class CHomepageTimeline extends HTMLElement {
    connectedCallback() {
        this.masterTimeline = null;
        this.lastWidth = innerWidth;
        window.addEventListener('pathProgress', this.onPathProgress);
        window.addEventListener('resize', this.onResize);
        this.build();
    }

    disconnectedCallback() {
        window.removeEventListener('pathProgress', this.onPathProgress);
        window.removeEventListener('resize', this.onResize);
        clearTimeout(this.resizeTimer);
        this.masterTimeline?.kill();
    }

    onPathProgress = (e) => this.masterTimeline?.progress(e.detail.progress);

    onResize = () => {
        clearTimeout(this.resizeTimer);
        this.resizeTimer = setTimeout(() => {
            if ((this.lastWidth < 1000) !== (innerWidth < 1000)) this.build();
            this.lastWidth = innerWidth;
        }, 200);
    };

    build() {
        this.masterTimeline?.kill();
        this.masterTimeline = gsap.timeline({ paused: true });
        const $ = (sel) => this.querySelector(sel);

        if (innerWidth < 1000) {
            this.masterTimeline
                .fromTo($('#mobile-main-path'), { drawSVG: '0.1%' }, { drawSVG: '100%', duration: 1, ease: 'power1.in' })
                .fromTo($('#mobile-overlap-end'), { drawSVG: '0%' }, { drawSVG: '100%', duration: 1, ease: 'none' })
                .fromTo($('#mobile-shadow-path'), { opacity: 0 }, { opacity: 1, duration: 0.15, ease: 'none' }, 1.05);
        } else {
            this.masterTimeline
                .fromTo($('#main-path'), { drawSVG: '5%' }, { drawSVG: '100%', duration: 0.9, ease: CustomEase.create('route', 'M0,0 C0.952,0.017 0.744,0.69 1,1') })
                .fromTo($('#secondary-path'), { drawSVG: '0%' }, { drawSVG: '100%', duration: 1, ease: 'none' })
                .fromTo($('#shadow-path'), { opacity: 0 }, { opacity: 1, duration: 0.15, ease: 'none' }, 0.95);
        }

        // No scroll engine under reduced motion, so nothing scrubs the route: show it fully drawn
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) this.masterTimeline.progress(1);
    }
}

if (!customElements.get('c-homepage-timeline')) {
    customElements.define('c-homepage-timeline', CHomepageTimeline);
}

export default CHomepageTimeline;
