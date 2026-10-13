import 'locomotive-scroll/dist/locomotive-scroll.css';
import LocomotiveScroll from 'locomotive-scroll';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import './components/c-rive.js';
import './components/c-button-quote.js';
import './components/c-deluxe-callout.js';
import './components/c-homepage-timeline.js';
import './components/c-register.js';
import './components/c-check-status.js';
import './components/c-results.js';
import './components/c-result-detail.js';

const root = document.documentElement;
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
gsap.registerPlugin(ScrollTrigger);

// Slides the nav in and starts the hero's first-hit animation
requestAnimationFrame(() => root.classList.add('is-loaded'));

// Mobile menu: the burger toggles html.has-menu-mobile-open, picking a link closes it
const burger = document.querySelector('.c-menu-mobile_burger');
const setMenu = (open) => {
    root.classList.toggle('has-menu-mobile-open', open);
    burger?.setAttribute('aria-expanded', String(open));
};
burger?.addEventListener('click', () => setMenu(!root.classList.contains('has-menu-mobile-open')));
document.querySelectorAll('.c-menu-mobile_nav a').forEach((a) => a.addEventListener('click', () => setMenu(false)));
document.addEventListener('keydown', (e) => e.key === 'Escape' && setMenu(false));
matchMedia('(min-width: 1000px)').addEventListener('change', (e) => e.matches && setMenu(false));

// Smooth scroll + scroll-linked attributes. Same engine and default options as the reference:
// Locomotive Scroll v5 drives [data-scroll-speed] parallax, [data-scroll-css-progress] (--progress),
// [data-scroll-event-progress] (the route's `pathProgress` event) and the `is-inview` class.
if (!reduceMotion) {
    root.classList.add('has-motion');
    new LocomotiveScroll({
        lenisOptions: { anchors: { offset: -100 } },
        scrollCallback: () => ScrollTrigger.update(),
    });
}
