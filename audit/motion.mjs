// Behaviour audit: samples scroll-driven state (parallax transforms, --progress, route draw) at fixed scroll positions.
//   node motion.mjs  ->  audit/motion.json  (+ prints a diff table)
import { chromium } from 'playwright';
import { writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { REFERENCE_URL, TARGET_URL } from './config.mjs';

const dir = dirname(fileURLToPath(import.meta.url));
const VW = +process.env.VW || 1440, VH = +process.env.VH || 900;
const SCROLLS = (process.env.SCROLLS || '0,700,1600,2600,3600,4600,5600').split(',').map(Number);
const sample = () => {
  const q = (s) => document.querySelector(s);
  const tf = (s) => { const e = q(s); return e ? getComputedStyle(e).transform : null; };
  const dash = (s) => { const e = q(s); if (!e) return null; const cs = getComputedStyle(e); return `${cs.strokeDasharray}/${cs.strokeDashoffset}/${cs.opacity}`; };
  const prog = (s) => { const e = q(s); return e ? (e.style.getPropertyValue('--progress') || getComputedStyle(e).getPropertyValue('--progress')).trim() : null; };
  return {
    scrollY: Math.round(scrollY),
    heroProgress: prog('.c-hero-home'), introProgress: prog('.c-homepage-timeline_intro'), endProgress: prog('.c-homepage-timeline_end'),
    introIllustration: tf('.c-homepage-timeline_intro_illustration'), card1: tf('.c-homepage-timeline-cards'),
    musicNote3: tf('img[src$="music-note-3.svg"]'), plane1: tf('img[src$="plane-1.svg"]'), cloud1: tf('.c-homepage-timeline_end_cloud_1'),
    greenBack: tf('.c-homepage-timeline_end_green_back'), heroContent: tf('.c-hero-home_content'),
    mainPath: dash('#main-path'), secondaryPath: dash('#secondary-path'), shadowPath: dash('#shadow-path'),
    tileInview: [...document.querySelectorAll('.c-tile-animated')].map((e) => e.classList.contains('is-inview') ? 1 : 0).join(''),
  };
};
const out = {};
const b = await chromium.launch();
for (const [site, url] of [['reference', REFERENCE_URL], ['target', TARGET_URL]]) {
  const p = await (await b.newContext({ viewport: { width: VW, height: VH } })).newPage();
  await p.goto(url, { waitUntil: 'networkidle', timeout: 120000 });
  await p.waitForTimeout(5000);
  out[site] = [];
  for (const y of SCROLLS) {
    // wheel-scroll in steps so the smooth-scroll engine and scroll listeners behave like a real user
    let cur = await p.evaluate(() => scrollY);
    for (let guard = 0; Math.abs(y - cur) > 4 && guard < 60; guard++) {
      await p.mouse.wheel(0, Math.sign(y - cur) * Math.min(Math.abs(y - cur), 400));
      await p.waitForTimeout(120);
      cur = await p.evaluate(() => scrollY);
    }
    await p.waitForTimeout(1800);
    out[site].push(await p.evaluate(sample));
  }
  await p.close();
}
await b.close();
writeFileSync(join(dir, `motion-${VW}.json`), JSON.stringify(out, null, 1));
const keys = Object.keys(out.reference[0]).filter((k) => k !== 'scrollY');
for (let i = 0; i < SCROLLS.length; i++) {
  console.log(`\n== scrollY≈${SCROLLS[i]} (ref ${out.reference[i].scrollY} / tgt ${out.target[i].scrollY})`);
  for (const k of keys) { const r = out.reference[i][k], t = out.target[i][k]; if (String(r) !== String(t)) console.log(`  ${k}: ref=${r}  tgt=${t}`); }
}
