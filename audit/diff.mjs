// Phase 2/4: pixel diff of full-page screenshots (Rive canvases hidden, motion neutralised, copy-parity target).
//   node diff.mjs  ->  audit/diff/<viewport>-<region>.png + audit/diff/summary.md
import { PNG } from 'pngjs';
import pixelmatch from 'pixelmatch';
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { PRE, VIEWPORTS } from './config.mjs';

const dir = dirname(fileURLToPath(import.meta.url));
const ref = JSON.parse(readFileSync(join(dir, `${PRE}reference.json`), 'utf8'));
const tgt = JSON.parse(readFileSync(join(dir, `${PRE}target-parity.json`), 'utf8'));
const read = (f) => PNG.sync.read(readFileSync(join(dir, 'shots', f)));
const crop = (img, y, h) => { const H = Math.max(1, Math.min(h, img.height - y)); const out = new PNG({ width: img.width, height: H }); PNG.bitblt(img, out, 0, y, img.width, H, 0, 0); return out; };

let md = '# Visual diff (reference vs target, copy-parity, Rive canvases hidden)\n\nPixelmatch threshold 0.1, anti-aliasing ignored.\n\n| Viewport | Region | Size | Different pixels | % |\n|---|---|---|---|---|\n';
for (const vp of VIEWPORTS) {
  const R = ref.viewports[vp.name], T = tgt.viewports[vp.name];
  if (!R || !T) continue;
  const a = read(`${PRE}reference-${vp.name}-masked.png`), b = read(`${PRE}target-parity-${vp.name}-masked.png`);
  const rows = (E) => ({
    top: [0, Math.round(E.timeline.rect.y + E.timeline.rect.h)],
    callout: [Math.round(E.callout.rect.y), Math.round(E.callout.rect.h)],
  });
  const rr = rows(R.elements), tr = rows(T.elements);
  for (const region of ['top', 'callout']) {
    const h = Math.min(rr[region][1], tr[region][1]);
    const ca = crop(a, rr[region][0], h), cb = crop(b, tr[region][0], h);
    const w = Math.min(ca.width, cb.width), hh = Math.min(ca.height, cb.height);
    const A = crop(ca, 0, hh), B = crop(cb, 0, hh);
    const diff = new PNG({ width: w, height: hh });
    const n = pixelmatch(A.data, B.data, diff.data, w, hh, { threshold: 0.1, includeAA: false });
    writeFileSync(join(dir, 'diff', `${PRE}${vp.name}-${region}.png`), PNG.sync.write(diff));
    md += `| ${vp.name} | ${region} | ${w}×${hh} | ${n} | ${(100 * n / (w * hh)).toFixed(2)}% |\n`;
  }
}
writeFileSync(join(dir, 'diff', `${PRE}summary.md`), md);
console.log(md);
