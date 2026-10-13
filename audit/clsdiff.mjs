import { chromium } from 'playwright';
const grab = async (url) => { const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1440, height: 900 } }); await p.goto(url, { waitUntil: 'networkidle', timeout: 90000 });
  const r = await p.evaluate(() => Object.fromEntries([...document.querySelectorAll('.c-homepage-timeline img, .c-homepage-timeline c-rive > div')].map(e => { const k = e.tagName === 'IMG' ? e.getAttribute('src') : e.closest('c-rive').getAttribute('data-src'); return [k, [e.getAttribute('class'), e.getAttribute('style') || '', e.getAttribute('width'), e.getAttribute('height'), e.getAttribute('loading') || '']]; })));
  await b.close(); return r; };
const [R, T] = [await grab('https://mindmarket.com'), await grab('http://127.0.0.1:8000')];
const norm = (c) => c.split(/\s+/).filter(Boolean).sort();
for (const k of Object.keys(R)) {
  if (!T[k]) { console.log('MISSING in target:', k); continue; }
  const a = norm(R[k][0]), b = norm(T[k][0]);
  const onlyR = a.filter((x) => !b.includes(x)), onlyT = b.filter((x) => !a.includes(x));
  if (onlyR.length || onlyT.length || R[k][2] !== T[k][2] || R[k][3] !== T[k][3] || R[k][4] !== T[k][4]) console.log(k, '\n   ref-only:', onlyR.join(' '), '\n   tgt-only:', onlyT.join(' '), (R[k][2] !== T[k][2] || R[k][3] !== T[k][3]) ? `\n   size ${R[k][2]}x${R[k][3]} vs ${T[k][2]}x${T[k][3]}` : '', R[k][4] !== T[k][4] ? `\n   loading ${R[k][4]} vs ${T[k][4]}` : '');
}
for (const k of Object.keys(T)) if (!R[k]) console.log('EXTRA in target:', k);
