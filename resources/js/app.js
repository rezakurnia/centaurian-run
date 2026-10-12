import 'lenis/dist/lenis.css';
import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { DrawSVGPlugin } from 'gsap/DrawSVGPlugin';
import { CustomEase } from 'gsap/CustomEase';

const ease = 'brand'; // --ease, registered below via CustomEase
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

requestAnimationFrame(() => document.documentElement.classList.add('is-loaded'));

// Close the mobile menu after picking a link
document.querySelectorAll('details a').forEach((a) => a.addEventListener('click', () => a.closest('details').removeAttribute('open')));

if (!reduceMotion) {
    gsap.registerPlugin(ScrollTrigger, DrawSVGPlugin, CustomEase);
    CustomEase.create(ease, '.5,0,0,1');
    gsap.defaults({ ease: 'power3.out' });

    // Smooth scroll, driven by GSAP's ticker so ScrollTrigger stays in sync
    const lenis = new Lenis({ anchors: { offset: -100 } });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);

    // Hero darkens as the timeline covers it
    const hero = document.querySelector('.hero');
    ScrollTrigger.create({
        trigger: '.timeline',
        start: 'top bottom',
        end: 'top top',
        scrub: true,
        onUpdate: (self) => hero.style.setProperty('--progress', self.progress),
    });

    // Fade-up reveals
    gsap.utils.toArray('[data-reveal]').forEach((el) => {
        gsap.from(el, {
            opacity: 0,
            y: 40,
            duration: 0.8,
            ease,
            scrollTrigger: { trigger: el, start: 'top 90%' },
        });
    });

    // Callout heading: each line slides up out of its mask
    gsap.utils.toArray('[data-lines]').forEach((el) => {
        gsap.from(el.querySelectorAll(':scope > span > span'), {
            yPercent: 100,
            duration: 1,
            stagger: 0.1,
            ease,
            scrollTrigger: { trigger: el, start: 'top 85%' },
        });
    });

    gsap.matchMedia().add('(min-width: 768px)', () => {
        // Track trails the scroll from the hero bottom to the route start (≈59.7vw into the timeline)
        gsap.to('[data-track]', {
            y: () => innerWidth * 0.304 + innerHeight * 0.38,
            ease: 'none',
            scrollTrigger: { start: 0, end: () => innerHeight * 1.3, scrub: true, invalidateOnRefresh: true },
        });

        // Route draws itself, tip following ~70% of the viewport
        gsap.utils.toArray('[data-draw]').forEach((path) => {
            gsap.from(path, {
                drawSVG: 0,
                ease: 'none',
                scrollTrigger: { trigger: path, start: 'top 70%', end: 'bottom 70%', scrub: true },
            });
        });

        // Parallax: data-speed > 0 drifts down, < 0 drifts up
        gsap.utils.toArray('[data-speed]').forEach((el) => {
            const distance = parseFloat(el.dataset.speed) * 80;
            gsap.fromTo(el, { y: -distance }, {
                y: distance,
                ease: 'none',
                scrollTrigger: { trigger: el, start: 'top bottom', end: 'bottom top', scrub: true },
            });
        });
    });
}
