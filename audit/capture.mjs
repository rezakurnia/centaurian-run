// Phase 1: audit one site at every viewport.
//   node capture.mjs reference|target [viewportName ...]
// Writes audit/<site>.json (merged per viewport) and audit/shots/<site>-<viewport>[-masked].png
import { chromium } from 'playwright';
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { ANCHORS, BOX_PROPS, PRE, PROBES, REFERENCE_URL, TARGET_URL, TEXT_PROPS, VIEWPORTS } from './config.mjs';

const dir = dirname(fileURLToPath(import.meta.url));
const site = process.argv[2];
if (!['reference', 'target'].includes(site)) throw new Error('usage: capture.mjs reference|target [viewport...]');
const url = site === 'reference' ? REFERENCE_URL : TARGET_URL;
const only = process.argv.slice(3);
const parity = process.env.PARITY === '1' && site === 'target';
const outFile = join(dir, `${PRE}${parity ? 'target-parity' : site}.json`);
const refData = parity ? JSON.parse(readFileSync(join(dir, `${PRE}reference.json`), 'utf8')) : null;
const result = existsSync(outFile) ? JSON.parse(readFileSync(outFile, 'utf8')) : { site, url, viewports: {} };

/** Runs in the page: measures every probe with transforms/animations neutralised. */
function measure({ probes, anchors, boxProps, textProps, side }) {
  const round = (n) => Math.round(n * 100) / 100;
  const visible = (el) => {
    const r = el.getBoundingClientRect();
    return r.width > 0 && r.height > 0 && getComputedStyle(el).visibility !== 'hidden';
  };
  const pick = (selectors, nth = 0) => {
    for (const s of selectors) {
      const list = [...document.querySelectorAll(s)].filter(visible);
      if (list.length > nth) return list[nth];
    }
    return null;
  };
  const rect = (el) => {
    const r = el.getBoundingClientRect();
    return { x: round(r.left + scrollX), y: round(r.top + scrollY), w: round(r.width), h: round(r.height) };
  };
  const styles = (el, props) => {
    const cs = getComputedStyle(el);
    return Object.fromEntries(props.map((p) => [p, cs[p]]));
  };
  const anchorRects = {};
  for (const [k, a] of Object.entries(anchors)) {
    const el = pick([a[side]]) || document.querySelector(a[side]);
    anchorRects[k] = el ? rect(el) : null;
  }
  const out = {};
  for (const p of probes) {
    const selectors = p[side];
    const el = pick(selectors, p.nth || 0);
    const existing = selectors.reduce((n, s) => n + document.querySelectorAll(s).length, 0);
    if (!el) { out[p.key] = { found: false, existing }; continue; }
    const r = rect(el);
    const a = p.anchor && anchorRects[p.anchor];
    const entry = { found: true, existing, tag: el.tagName.toLowerCase(), rect: r };
    if (a) entry.rel = { x: r.x, y: round(r.y - (p.edge === 'bottom' ? a.y + a.h : a.y)), w: r.w, h: r.h };
    const lab = el.matches('.c-button_label, .c-button-quote_label') ? el : el.querySelector('.c-button_label, .c-button-quote_label');
    if (lab) entry.label = lab.textContent.trim();
    entry.box = styles(el, boxProps);
    if (p.kind === 'text') {
      const t = p.text ? el.querySelector(p.text) || el : el;
      entry.text = styles(t, textProps);
      entry.text.content = (t.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 60);
      entry.text.html = t.innerHTML.trim();
      entry.text.loadedFont = document.fonts.check(`${entry.text.fontWeight} ${entry.text.fontSize} Inter`);
    }
    if (p.kind === 'img') {
      entry.img = {
        src: el.getAttribute('src'), naturalWidth: el.naturalWidth, naturalHeight: el.naturalHeight,
        widthAttr: el.getAttribute('width'), heightAttr: el.getAttribute('height'),
        objectFit: getComputedStyle(el).objectFit, loading: el.getAttribute('loading'), kind: 'img',
      };
    }
    if (p.kind === 'svg') {
      const paths = [...el.querySelectorAll('path')].map((pa) => {
        const cs = getComputedStyle(pa);
        return { id: pa.id || null, stroke: cs.stroke, strokeWidth: cs.strokeWidth, fill: cs.fill, opacity: cs.opacity, strokeLinecap: cs.strokeLinecap };
      });
      entry.svg = {
        kind: el.tagName.toLowerCase() === 'svg' ? 'inline' : 'img', viewBox: el.getAttribute('viewBox'),
        preserveAspectRatio: el.getAttribute('preserveAspectRatio'), widthAttr: el.getAttribute('width'),
        heightAttr: el.getAttribute('height'), pathCount: paths.length, paths,
      };
    }
    if (p.kind === 'rive') {
      const host = p.host && (el.closest('c-rive') || document.querySelector(p.host));
      const inst = host && host.rive;
      const rv = { dpr: devicePixelRatio, canvasAttr: { w: el.width, h: el.height }, canvasClient: { w: el.clientWidth, h: el.clientHeight } };
      if (host) rv.hostAttrs = Object.fromEntries([...host.attributes].filter((a2) => a2.name.startsWith('data-')).map((a2) => [a2.name, a2.value]));
      if (inst) {
        const safe = (fn) => { try { return fn(); } catch { return null; } };
        rv.layout = safe(() => ({ fit: inst.layout.fit, alignment: inst.layout.alignment, scaleFactor: inst.layout.layoutScaleFactor }));
        rv.source = safe(() => inst.source);
        rv.artboard = safe(() => inst.activeArtboard);
        rv.animations = safe(() => inst.animationNames);
        rv.stateMachines = safe(() => inst.stateMachineNames);
        rv.playing = safe(() => ({ animations: inst.playingAnimationNames, stateMachines: inst.playingStateMachineNames, isPlaying: inst.isPlaying }));
        rv.inputs = safe(() => Object.fromEntries((inst.stateMachineNames || []).map((n) => [n, (inst.stateMachineInputs(n) || []).map((i) => `${i.name}:${i.type}`)])));
      } else rv.noInstance = true;
      entry.rive = rv;
    }
    out[p.key] = entry;
  }
  return { out, anchorRects };
}

/** Runs in the page (parity mode): copies the reference's copy into the target so that
 *  geometry can be compared independent of text length. Layout, not content, is under test. */
function applyParity({ probes, copy }) {
  // Target-only content (data-driven lists/schedule) would otherwise change card heights; drop it.
  document.querySelectorAll('.c-tile-animated_inner > :is(ul, dl)').forEach((e) => e.remove());
  for (const p of probes) {
    const c = copy[p.key];
    if (!c) continue;
    const el = p.tgt.flatMap((s) => [...document.querySelectorAll(s)].filter(visible))[p.nth || 0];
    if (!el) continue;
    if (p.kind === 'text' && c.html) (p.text ? el.querySelector(p.text) || el : el).innerHTML = c.html;
    const lab = el.matches('.c-button_label') ? el : el.querySelector('.c-button_label');
    if (lab && c.label) lab.textContent = c.label;
  }
}

/** Runs in the page: page-level metrics (tokens, fonts, breakpoints, resets). */
function pageLevel() {
  const html = document.documentElement;
  const cs = (el) => getComputedStyle(el);
  const vars = {};
  const media = new Set();
  const fontFaces = [];
  const walk = (rules) => {
    for (const r of rules) {
      if (r.type === CSSRule.STYLE_RULE && /^(:root|html|:host|:root, ?:host)$/.test(r.selectorText)) {
        for (const n of r.style) if (n.startsWith('--')) vars[n] = r.style.getPropertyValue(n).trim();
      } else if (r.type === CSSRule.MEDIA_RULE) { media.add(r.conditionText || r.media.mediaText); walk(r.cssRules); }
      else if (r.type === CSSRule.FONT_FACE_RULE) {
        fontFaces.push({ family: r.style.getPropertyValue('font-family').replace(/["']/g, ''), weight: r.style.getPropertyValue('font-weight'),
          style: r.style.getPropertyValue('font-style'), display: r.style.getPropertyValue('font-display'),
          src: (r.style.getPropertyValue('src').match(/url\(["']?([^"')]+)/) || [])[1] || '' , format: (r.style.getPropertyValue('src').match(/format\(["']?([^"')]+)/) || [])[1] || '' });
      } else if (r.cssRules && r.type !== CSSRule.KEYFRAMES_RULE) walk(r.cssRules);
    }
  };
  for (const s of document.styleSheets) { try { walk(s.cssRules); } catch { /* cross-origin sheet */ } }
  const container = document.querySelector('.container');
  const px = (n) => Math.round(n * 100) / 100;
  return {
    title: document.title, scrollHeight: html.scrollHeight, scrollWidth: html.scrollWidth,
    htmlFontSize: cs(html).fontSize,
    body: { fontFamily: cs(document.body).fontFamily, fontSize: cs(document.body).fontSize, fontWeight: cs(document.body).fontWeight,
      lineHeight: cs(document.body).lineHeight, color: cs(document.body).color, backgroundColor: cs(document.body).backgroundColor },
    boxSizing: { div: cs(document.createElement('div')).boxSizing || null, body: cs(document.body).boxSizing },
    container: container ? { maxWidth: cs(container).maxWidth, paddingLeft: cs(container).paddingLeft, width: px(container.getBoundingClientRect().width) } : null,
    tokens: Object.fromEntries(['--radius-lg', '--radius-sm', '--radius-md', '--unit-md', '--grid-margin', '--grid-gutter', '--menu-bar-height', '--text-md', '--text-lg', '--text-sm', '--text-heading-2xl', '--text-heading-xl', '--text-heading-lg', '--text-heading-md', '--text-heading-sm', '--text-heading-xs', '--spacing-fluid-xs', '--spacing-fluid-sm', '--spacing-fluid-md', '--spacing-fluid-lg', '--spacing-fluid-xl', '--spacing-fluid-2xl', '--spacing-fluid-3xl', '--spacing-fluid-4xl', '--ease', '--ease-bounce', '--ease-smooth', '--font-sans-weight-normal'].map((n) => [n, cs(html).getPropertyValue(n).replace(/\s+/g, '')])),
    fonts: [...document.fonts].map((f) => `${f.family} ${f.weight} ${f.style} ${f.status}`),
    fontFaces, breakpoints: [...media].sort(), cssVars: vars,
  };
}

/** Runs in the page: sha1 of each referenced asset, to prove SVG/.riv files are byte-identical. */
async function hashAssets(urls) {
  const out = {};
  for (const u of urls) {
    try {
      const buf = await (await fetch(u)).arrayBuffer();
      const h = await crypto.subtle.digest('SHA-1', buf);
      out[u] = [...new Uint8Array(h)].map((b) => b.toString(16).padStart(2, '0')).join('') + ':' + buf.byteLength;
    } catch (e) { out[u] = 'ERR'; }
  }
  return out;
}

const browser = await chromium.launch();
for (const vp of VIEWPORTS.filter((v) => !only.length || only.includes(v.name))) {
  const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height }, deviceScaleFactor: 1 });
  const page = await ctx.newPage();
  const t0 = Date.now();
  await page.goto(url, { waitUntil: 'networkidle', timeout: 120000 });
  await page.evaluate(() => document.fonts.ready);
  await page.waitForTimeout(5000); // preloader / first-hit animations

  // Scroll through once so lazy images load, Rive instances start and reveals complete.
  const total = await page.evaluate(() => document.documentElement.scrollHeight);
  for (let y = 0; y < Math.min(total, 9000 * (vp.width < 768 ? 2 : 1)); y += Math.round(vp.height * 0.6)) {
    await page.mouse.wheel(0, Math.round(vp.height * 0.6));
    await page.waitForTimeout(220);
  }
  await page.waitForTimeout(2500);
  for (let i = 0; i < 40 && (await page.evaluate(() => scrollY)) > 2; i++) { await page.mouse.wheel(0, -2500); await page.waitForTimeout(120); }
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.waitForTimeout(2500);

  if (parity) {
    const copy = Object.fromEntries(Object.entries(refData.viewports[vp.name].elements).filter(([, e]) => e.found).map(([k, e]) => [k, { html: e.text?.html, label: e.label }]));
    await page.evaluate(applyParity, { probes: PROBES.map(({ key, kind, tgt, nth, text }) => ({ key, kind, tgt, nth, text })), copy });
    await page.waitForTimeout(500);
  }

  // Natural-state screenshot (for humans), then neutralise motion for deterministic geometry.
  const shotDir = join(dir, 'shots');
  await page.screenshot({ path: join(shotDir, `${PRE}${site}${parity ? '-parity' : ''}-${vp.name}.png`), fullPage: true, timeout: 120000 });
  await page.addStyleTag({ content: '*,*::before,*::after{transform:none!important;translate:none!important;scale:none!important;rotate:none!important;transition:none!important;animation:none!important}' });
  await page.waitForTimeout(600);

  const side = site === 'reference' ? 'ref' : 'tgt';
  const probes = PROBES.map(({ key, kind, ref, tgt, nth, anchor, text, host, at, edge }) => ({ key, kind, ref, tgt, nth, anchor, text, host, at, edge }));
  const anchors = ANCHORS;
  const { out: elements, anchorRects } = await page.evaluate(measure, { probes, anchors, boxProps: BOX_PROPS, textProps: TEXT_PROPS, side });
  const pageInfo = await page.evaluate(pageLevel);

  // Probes flagged `at: 'callout'` sit in sticky containers whose position depends on scroll, so
  // re-measure them with the callout's bottom edge aligned to the viewport bottom (same state on both sites).
  const cal = anchorRects.callout;
  if (cal) {
    await page.evaluate((y) => window.scrollTo(0, y), Math.max(0, cal.y + cal.h - vp.height));
    await page.waitForTimeout(1200);
    const again = await page.evaluate(measure, { probes: probes.filter((p) => p.at === 'callout'), anchors, boxProps: BOX_PROPS, textProps: TEXT_PROPS, side });
    Object.assign(elements, again.out);
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(800);
  }

  // Asset hashes (only the first viewport needs them; files don't vary per viewport).
  const srcs = new Set();
  for (const e of Object.values(elements)) { if (e.img?.src) srcs.add(e.img.src); if (e.rive?.hostAttrs?.['data-src']) srcs.add(e.rive.hostAttrs['data-src']); else if (e.rive?.source) srcs.add(e.rive.source); }
  const assets = await page.evaluate(hashAssets, [...srcs]);

  // Masked screenshot: Rive canvases hidden so animation frames can't cause false diffs.
  await page.addStyleTag({ content: 'canvas{visibility:hidden!important}' });
  await page.waitForTimeout(300);
  await page.screenshot({ path: join(shotDir, `${PRE}${site}${parity ? '-parity' : ''}-${vp.name}-masked.png`), fullPage: true, timeout: 120000 });

  result.viewports[vp.name] = { viewport: vp, page: pageInfo, anchors: anchorRects, elements, assets };
  console.log(`${site} ${vp.name}: ${Object.values(elements).filter((e) => e.found).length}/${probes.length} probes found, ${((Date.now() - t0) / 1000).toFixed(0)}s`);
  await ctx.close();
}
await browser.close();
writeFileSync(outFile, JSON.stringify(result, null, 1));
