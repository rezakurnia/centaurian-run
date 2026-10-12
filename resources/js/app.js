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

// Callout pop-up runner on hover (inspired by MindMarket pop_up_girl)
const calloutCta = document.querySelector('[data-callout-action]');
const popupRunner = document.querySelector('#callout-popup-runner');
if (calloutCta && popupRunner) {
    calloutCta.addEventListener('mouseenter', () => popupRunner.classList.add('is-active'));
    calloutCta.addEventListener('mouseleave', () => popupRunner.classList.remove('is-active'));
}

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

    // Character elastic entrance reveals (inspired by MindMarket Rive character reveals)
    gsap.utils.toArray('[data-character]').forEach((el) => {
        gsap.from(el, {
            scale: 0.72,
            y: 50,
            opacity: 0,
            duration: 1.4,
            ease: 'elastic.out(1, 0.4)',
            scrollTrigger: { trigger: el, start: 'top 88%' },
        });

        const img = el.querySelector('img') || el;
        gsap.to(img, {
            y: -7,
            rotation: gsap.utils.random(-2, 2),
            duration: gsap.utils.random(1.2, 1.8),
            delay: gsap.utils.random(0, 0.5),
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
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

    // Idle loop so the runners never stand still (stands in for the reference's Rive animation)
    gsap.utils.toArray('[data-track] img').forEach((img, i) => {
        gsap.to(img, {
            y: -10,
            rotation: i % 2 ? 2 : -2,
            duration: gsap.utils.random(0.8, 1.2),
            delay: gsap.utils.random(0, 0.5),
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
        });
    });

    gsap.matchMedia().add({
        isDesktop: '(min-width: 768px)',
        isMobile: '(max-width: 767px)',
    }, (context) => {
        const { isDesktop, isMobile } = context.conditions;

        if (isDesktop) {
            // Runners start centered in the hero, then steer smoothly towards the green route start while descending
            gsap.to('[data-track]', {
                x: () => innerWidth * 0.22,
                y: () => innerWidth * 0.297 + innerHeight * 0.38,
                ease: 'power2.inOut',
                scrollTrigger: { start: 0, end: () => innerHeight * 1.2, scrub: true, invalidateOnRefresh: true },
            });
        }

        if (isMobile) {
            // Runners start centered at bottom of hero, then fade out cleanly as timeline scrolls over
            gsap.to('[data-track]', {
                opacity: 0,
                y: -30,
                ease: 'power1.out',
                scrollTrigger: { start: 0, end: 320, scrub: true },
            });
        }
    });

    gsap.matchMedia().add('(min-width: 768px)', () => {

        // Ornaments drift slowly; [data-float] sits inside its [data-speed] group so the two transforms don't collide
        gsap.utils.toArray('[data-float]').forEach((el, i) => {
            gsap.to(el, {
                y: -8,
                rotation: i % 2 ? 3 : -3,
                transformOrigin: '50% 50%',
                duration: gsap.utils.random(1.6, 2.4),
                delay: gsap.utils.random(0, 1),
                ease: 'sine.inOut',
                yoyo: true,
                repeat: -1,
            });
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
