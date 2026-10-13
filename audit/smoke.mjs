// Smoke test: console errors, failed/third-party requests, mobile menu toggle, burger dropdown, Rive instances alive.
import { chromium } from 'playwright';
import { TARGET_URL } from './config.mjs';
const b = await chromium.launch(); let bad = 0;
const check = (name, ok, extra = '') => { console.log(`${ok ? 'PASS' : 'FAIL'}  ${name} ${extra}`); if (!ok) bad++; };
for (const [w, h] of [[1440, 900], [375, 812]]) {
  const p = await b.newPage({ viewport: { width: w, height: h } });
  const errors = [], external = [], failed = [];
  p.on('console', (m) => m.type() === 'error' && errors.push(m.text()));
  p.on('pageerror', (e) => errors.push(String(e)));
  p.on('requestfailed', (r) => failed.push(r.url()));
  p.on('request', (r) => { const u = new URL(r.url()); if (!['127.0.0.1', 'localhost'].includes(u.hostname) && !u.protocol.startsWith('data')) external.push(r.url()); });
  await p.goto(TARGET_URL, { waitUntil: 'networkidle' }); await p.waitForTimeout(3000);
  check(`${w}: no console errors`, errors.length === 0, errors.join(' | ').slice(0, 300));
  check(`${w}: no failed requests`, failed.length === 0, failed.join(' ').slice(0, 300));
  check(`${w}: no third-party requests`, external.length === 0, external.join(' ').slice(0, 300));
  const rives = await p.evaluate(() => [...document.querySelectorAll('c-rive, c-button-quote')].filter((e) => !e.rive).length);
  check(`${w}: every Rive host has an instance`, rives === 0, `(${rives} without)`);
  if (w < 1000) {
    await p.click('.c-menu-mobile_burger'); await p.waitForTimeout(700);
    check(`${w}: burger opens the mobile menu`, await p.evaluate(() => document.documentElement.classList.contains('has-menu-mobile-open') && getComputedStyle(document.querySelector('.c-menu-mobile_nav')).visibility === 'visible'));
    await p.click('.c-menu-mobile_nav a[href="#jadwal"]'); await p.waitForTimeout(1500);
    check(`${w}: picking a link closes it and scrolls`, await p.evaluate(() => !document.documentElement.classList.contains('has-menu-mobile-open') && scrollY > 500), `scrollY=${await p.evaluate(() => Math.round(scrollY))}`);
  } else {
    await p.click('.c-menu-desktop_button'); await p.waitForTimeout(300);
    check(`${w}: burger dropdown opens`, await p.evaluate(() => document.querySelector('.c-menu-desktop details').open));
    await p.click('.c-menu-desktop_dropdown a[href="#hasil"]'); await p.waitForTimeout(1800);
    check(`${w}: dropdown link scrolls and closes`, await p.evaluate(() => !document.querySelector('.c-menu-desktop details').open && scrollY > 1000), `scrollY=${await p.evaluate(() => Math.round(scrollY))}`);
  }
  await p.close();
}
await b.close(); process.exit(bad ? 1 : 0);
