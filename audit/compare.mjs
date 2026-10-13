// Phase 2: diff audit/reference.json against audit/target.json.
//   node compare.mjs  ->  audit/comparison.md (tables per viewport, grouped by section) + audit/comparison.json
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { PRE, PROBES } from './config.mjs';

const dir = dirname(fileURLToPath(import.meta.url));
const ref = JSON.parse(readFileSync(join(dir, `${PRE}reference.json`), 'utf8'));
const parity = process.argv.includes('--parity');
const tgt = JSON.parse(readFileSync(join(dir, `${PRE}${parity ? 'target-parity' : 'target'}.json`), 'utf8'));
const suffix = parity ? '-parity' : '';
const TOL = 1; // px

// Computed-style props that are noise for a given element (e.g. border colour of a 0px border).
const SKIP_BOX = new Set(['opacity', 'minHeight', 'aspectRatio', 'boxSizing', 'overflow', 'borderTopStyle', 'borderTopColor', 'gridTemplateColumns', 'zIndex']);
const KEEP_BOX_FOR_MEDIA = new Set(['display']);
const num = (v) => (typeof v === 'number' ? v : typeof v === 'string' && /^-?[\d.]+(px)?$/.test(v) ? parseFloat(v) : null);

function rows(section, key, kind, r, t) {
  const out = [];
  const push = (prop, rv, tv, severity) => {
    const rn = num(rv), tn = num(tv);
    out.push({ section, element: key, property: prop, ref: rv, target: tv, delta: rn !== null && tn !== null ? Math.round((tn - rn) * 100) / 100 : '—', severity });
  };
  if (!r?.found && !t?.found) return out;
  if (!r?.found || !t?.found) {
    push('presence', r?.found ? 'present' : (r?.existing ? 'hidden' : 'absent'), t?.found ? 'present' : (t?.existing ? 'hidden' : 'absent'), 'high');
    return out;
  }
  const rect = r.rel && t.rel ? ['rel', 'rect'] : ['rect', 'rect'];
  for (const k of ['x', 'y', 'w', 'h']) {
    const d = t[rect[0]][k] - r[rect[0]][k];
    if (Math.abs(d) > TOL) push(`${rect[0] === 'rel' ? 'rel.' : ''}${k}`, r[rect[0]][k], t[rect[0]][k], Math.abs(d) > 20 ? 'high' : 'medium');
  }
  const cmp = (a, b, label, sev) => {
    if (typeof a === 'string') { a = a.replace(/url\("#[^"]*"\)/, 'url(#grad)'); b = String(b).replace(/url\("#[^"]*"\)/, 'url(#grad)'); }
    const an = num(a), bn = num(b);
    if (an !== null && bn !== null ? Math.abs(an - bn) > 0.5 : a !== b) push(label, a, b, sev);
  };
  if (kind === 'box' || kind === 'text') for (const p of Object.keys(r.box)) if (!SKIP_BOX.has(p)) cmp(r.box[p], t.box[p], p, 'low');
  if (kind === 'text') for (const p of Object.keys(r.text)) {
    if (p === 'content' || p === 'loadedFont' || p === 'html') continue; // copy is allowed to differ
    cmp(r.text[p], t.text[p], p, ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'letterSpacing'].includes(p) ? 'high' : 'medium');
  }
  if (kind === 'img') for (const p of ['naturalWidth', 'naturalHeight', 'objectFit']) cmp(r.img[p], t.img[p], `img.${p}`, 'medium');
  if (kind === 'svg') {
    for (const p of ['kind', 'viewBox', 'preserveAspectRatio', 'pathCount']) cmp(String(r.svg[p]), String(t.svg[p]), `svg.${p}`, 'medium');
    r.svg.paths.forEach((pa, i) => { const tp = t.svg.paths[i]; if (!tp) return; for (const k of ['stroke', 'strokeWidth', 'fill', 'opacity']) cmp(pa[k], tp[k], `svg.path[${i}].${k}`, 'low'); });
  }
  if (kind === 'rive') {
    const a = r.rive, b = t.rive;
    for (const k of ['w', 'h']) cmp(a.canvasAttr[k], b.canvasAttr[k], `canvas.attr.${k}`, 'medium');
    for (const k of ['fit', 'alignment']) cmp(a.layout?.[k], b.layout?.[k], `layout.${k}`, 'high');
    cmp(a.artboard, b.artboard, 'artboard', 'high');
    cmp(JSON.stringify(a.stateMachines), JSON.stringify(b.stateMachines), 'stateMachines', 'high');
    cmp(JSON.stringify(a.animations), JSON.stringify(b.animations), 'animations', 'high');
    cmp(JSON.stringify(a.playing), JSON.stringify(b.playing), 'playing', 'medium');
    cmp(JSON.stringify(a.inputs), JSON.stringify(b.inputs), 'inputs', 'medium');
    for (const k of Object.keys(a.hostAttrs || {})) cmp(a.hostAttrs[k], b.hostAttrs?.[k], k, 'low');
  }
  return out;
}

const all = {};
let md = '# Layout audit: reference (mindmarket.com) vs target (centaurian-run)\n\n';
md += 'Tolerance ±1px on x/y/w/h. `rel.*` = measured relative to its section anchor (hero / timeline / callout). Delta = target − reference.\n\n';
const summary = [];
for (const vpName of Object.keys(ref.viewports)) {
  const R = ref.viewports[vpName], T = tgt.viewports[vpName];
  if (!T) continue;
  const list = [];
  for (const p of PROBES) list.push(...rows(p.section, p.key, p.kind, R.elements[p.key], T.elements[p.key]));

  // Page-level
  const pg = (prop, rv, tv, sev = 'medium') => { if (String(rv) !== String(tv)) list.push({ section: 'Page', element: 'document', property: prop, ref: rv, target: tv, delta: num(String(rv)) !== null && num(String(tv)) !== null ? parseFloat(tv) - parseFloat(rv) : '—', severity: sev }); };
  pg('html font-size', R.page.htmlFontSize, T.page.htmlFontSize, 'high');
  for (const k of ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'color', 'backgroundColor']) pg(`body.${k}`, R.page.body[k], T.page.body[k], k === 'fontFamily' ? 'low' : 'medium');
  pg('container.maxWidth', R.page.container?.maxWidth, T.page.container?.maxWidth);
  pg('container.paddingLeft', R.page.container?.paddingLeft, T.page.container?.paddingLeft);
  const fam = (f) => [...new Set(f.map((x) => x.replace(/ (loaded|unloaded)$/, '')))].sort().join(' | ');
  pg('font-face set', fam(R.page.fonts), fam(T.page.fonts));
  pg('font-display', [...new Set(R.page.fontFaces.map((f) => f.display))].join(','), [...new Set(T.page.fontFaces.map((f) => f.display))].join(','), 'low');
  for (const k of Object.keys(R.page.tokens)) pg(`token ${k}`, R.page.tokens[k], T.page.tokens[k], 'low');
  const rbp = R.page.breakpoints.filter((b) => /width/.test(b)), tbp = T.page.breakpoints.filter((b) => /width/.test(b));
  const norm = (arr) => [...new Set(arr.map((b) => (b.match(/(\d+(\.\d+)?)(px|rem|em)/g) || []).join('~')))].filter(Boolean).sort();
  list.push({ section: 'Page', element: 'document', property: 'breakpoints (ref only)', ref: norm(rbp).filter((b) => !norm(tbp).includes(b)).join(' '), target: '', delta: '—', severity: 'info' });

  all[vpName] = list;
  const rank = { high: 0, medium: 1, low: 2, info: 3 };
  const mism = list.filter((r) => r.severity !== 'info');
  summary.push({ viewport: vpName, mismatches: mism.length, high: mism.filter((r) => r.severity === 'high').length, medium: mism.filter((r) => r.severity === 'medium').length, low: mism.filter((r) => r.severity === 'low').length });
  md += `\n## ${vpName}  (${mism.length} mismatches: ${summary.at(-1).high} high / ${summary.at(-1).medium} medium / ${summary.at(-1).low} low)\n`;
  for (const section of [...new Set(list.map((r) => r.section))]) {
    const rs = list.filter((r) => r.section === section).sort((a, b) => rank[a.severity] - rank[b.severity]);
    md += `\n### ${section}\n\n| Element | Property | Reference value | Target value | Delta | Severity |\n|---|---|---|---|---|---|\n`;
    for (const r of rs) md += `| ${r.element} | ${r.property} | ${String(r.ref).replace(/\|/g, '/')} | ${String(r.target).replace(/\|/g, '/')} | ${r.delta} | ${r.severity} |\n`;
  }
}

// Asset identity (SVG / .riv byte hashes), one table since files don't vary by viewport.
const vp0 = Object.keys(ref.viewports)[0];
const ra = Object.assign({}, ...Object.values(ref.viewports).map((v) => v.assets)), ta = Object.assign({}, ...Object.values(tgt.viewports).map((v) => v.assets));
md += '\n## Asset byte-identity (sha1:size)\n\n| Asset | Reference | Target | Same |\n|---|---|---|---|\n';
for (const k of Object.keys(ra).sort()) md += `| ${k} | ${ra[k]?.slice(0, 12)}:${ra[k]?.split(':')[1]} | ${ta[k] ? ta[k].slice(0, 12) + ':' + ta[k].split(':')[1] : 'missing'} | ${ra[k] === ta[k] ? 'yes' : '**NO**'} |\n`;

md = `${md}\n\n## Summary\n\n| Viewport | Mismatches | High | Medium | Low |\n|---|---|---|---|---|\n${summary.map((s) => `| ${s.viewport} | ${s.mismatches} | ${s.high} | ${s.medium} | ${s.low} |`).join('\n')}\n`;
writeFileSync(join(dir, `${PRE}comparison${suffix}.md`), md);
writeFileSync(join(dir, `${PRE}comparison${suffix}.json`), JSON.stringify({ summary, all }, null, 1));
console.table(summary);
