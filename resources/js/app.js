import './components/c-deluxe-callout.js';
import './components/c-register.js';
import './components/c-check-status.js';
import './components/c-payment-finish.js';
import './components/c-results.js';
import './components/c-result-detail.js';

const root = document.documentElement;
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
const has = (sel) => document.querySelector(sel);

// html.is-loaded (nav slide-in, hero first hit) is set by the inline script in partials/nav.blade.php

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

// GSAP, the route timeline, Rive and the scroll engine only load on pages that use them
if (has('c-rive')) import('./components/c-rive.js');
if (has('c-homepage-timeline')) import('./components/c-homepage-timeline.js');

// Smooth scroll + scroll-linked attributes, Locomotive default options:
// Locomotive Scroll v5 drives [data-scroll-speed] parallax, [data-scroll-css-progress] (--progress),
// [data-scroll-event-progress] (the route's `pathProgress` event) and the `is-inview` class.
if (!reduceMotion && has('[data-scroll], [data-scroll-speed], [data-scroll-event-progress], [data-scroll-css-progress]')) {
    root.classList.add('has-motion');
    Promise.all([
        import('locomotive-scroll'),
        import('locomotive-scroll/dist/locomotive-scroll.css'),
    ]).then(([{ default: LocomotiveScroll }]) => new LocomotiveScroll({ lenisOptions: { anchors: { offset: -100 } } }));
}
