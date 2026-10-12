import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

class CHomepageTimeline extends HTMLElement {
    connectedCallback() {
        this.$desktopSvg = this.querySelector('#desktop-svg');
        this.$mainPath = this.$desktopSvg?.querySelector('#main-path');
        this.$secondaryPath = this.$desktopSvg?.querySelector('#secondary-path');
        this.$shadowPath = this.$desktopSvg?.querySelector('#shadow-path');
        this.$mobileSvg = this.querySelector('#mobile-svg');
        this.$mobileMainPath = this.$mobileSvg?.querySelector('#mobile-main-path');
        this.$mobileOverlapEndPath = this.$mobileSvg?.querySelector('#mobile-overlap-end');

        this.initTimeline();
    }

    initTimeline() {
        if (typeof window === 'undefined') return;

        // Animate Desktop SVG paths
        if (this.$mainPath) {
            gsap.fromTo(this.$mainPath, { drawSVG: '5%' }, {
                drawSVG: '100%',
                ease: 'none',
                scrollTrigger: {
                    trigger: this,
                    start: 'top 80%',
                    end: 'center 50%',
                    scrub: true,
                },
            });
        }

        if (this.$secondaryPath) {
            gsap.fromTo(this.$secondaryPath, { drawSVG: '0%' }, {
                drawSVG: '100%',
                ease: 'none',
                scrollTrigger: {
                    trigger: this,
                    start: 'center 60%',
                    end: 'bottom 90%',
                    scrub: true,
                },
            });
        }

        // Animate Mobile SVG paths
        if (this.$mobileMainPath) {
            gsap.fromTo(this.$mobileMainPath, { drawSVG: '0.1%' }, {
                drawSVG: '100%',
                ease: 'none',
                scrollTrigger: {
                    trigger: this,
                    start: 'top 85%',
                    end: 'center 50%',
                    scrub: true,
                },
            });
        }

        if (this.$mobileOverlapEndPath) {
            gsap.fromTo(this.$mobileOverlapEndPath, { drawSVG: '0%' }, {
                drawSVG: '100%',
                ease: 'none',
                scrollTrigger: {
                    trigger: this,
                    start: 'center 60%',
                    end: 'bottom 90%',
                    scrub: true,
                },
            });
        }
    }
}

if (!customElements.get('c-homepage-timeline')) {
    customElements.define('c-homepage-timeline', CHomepageTimeline);
}

export default CHomepageTimeline;
