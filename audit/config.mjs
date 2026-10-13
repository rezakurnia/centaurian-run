// Shared audit configuration: viewports + the element map used to line the two sites up.
// Each probe pairs a reference selector with a target selector. A selector list is tried in
// order and the first *visible* match wins (so mobile/desktop variants can share one probe).

export const REFERENCE_URL = 'https://mindmarket.com';
export const TARGET_URL = process.env.TARGET_URL || 'http://127.0.0.1:8000';

// SET=extended audits the in-between widths that exercise every breakpoint branch of the reference CSS
export const SET = process.env.SET || '';
export const PRE = SET ? `${SET}-` : '';
const BASE_VIEWPORTS = [
  { name: '375x812', width: 375, height: 812 },
  { name: '768x1024', width: 768, height: 1024 },
  { name: '1280x800', width: 1280, height: 800 },
  { name: '1440x900', width: 1440, height: 900 },
  { name: '1920x1080', width: 1920, height: 1080 },
];
const EXTENDED_VIEWPORTS = [
  { name: '500x900', width: 500, height: 900 },
  { name: '700x900', width: 700, height: 900 },
  { name: '999x900', width: 999, height: 900 },
  { name: '1000x800', width: 1000, height: 800 },
  { name: '1199x800', width: 1199, height: 800 },
  { name: '1399x900', width: 1399, height: 900 },
  { name: '1600x900', width: 1600, height: 900 },
  { name: '1800x1000', width: 1800, height: 1000 },
  { name: '2200x1200', width: 2200, height: 1200 },
  { name: '2560x1300', width: 2560, height: 1300 },
];
export const VIEWPORTS = SET === 'extended' ? EXTENDED_VIEWPORTS : BASE_VIEWPORTS;

// kind: box | text | img | svg | rive
// anchor: key of the section the element is measured relative to ("rel" rect), so that
//         content-driven offsets above a section (e.g. the target-only stats band) don't
//         cascade into every element below it.
export const PROBES = [];
const add = (section, key, kind, ref, tgt, opts = {}) =>
  PROBES.push({ section, key, kind, ref: [].concat(ref), tgt: [].concat(tgt), ...opts });

// ---- Header -------------------------------------------------------------------------
add('Header', 'nav.bar', 'box', ['.c-menu-desktop_bar', '.c-menu-mobile_bar'], ['.c-menu-desktop_bar', '.c-menu-mobile_bar']);
add('Header', 'nav.logo', 'box', ['.c-menu-desktop_logo', '.c-menu-mobile_logo'], ['.c-menu-desktop_logo', '.c-menu-mobile_logo']);
add('Header', 'nav.link[0]', 'text', ['.c-menu-desktop_link'], ['.c-menu-desktop_link'], { text: '.c-menu-desktop_link_label' });
add('Header', 'nav.cta', 'box', ['a.c-menu-desktop_contact'], ['a.c-menu-desktop_contact']);
add('Header', 'nav.quote.rive', 'rive', ['.c-button-quote_icon_canvas'], ['.c-button-quote_icon_canvas'], { host: 'c-button-quote' });
add('Header', 'nav.cta.label', 'text', ['.c-button-quote_label'], ['a.c-menu-desktop_contact .c-button-quote_label']);

// ---- Hero ---------------------------------------------------------------------------
add('Hero', 'hero', 'box', ['.c-hero-home'], ['.c-hero-home'], { anchor: 'hero' });
add('Hero', 'hero.title', 'text', ['.c-hero-home_title'], ['.c-hero-home_title'], { anchor: 'hero' });
add('Hero', 'hero.tagline', 'text', ['.c-hero-home_tagline'], ['.c-hero-home_tagline'], { anchor: 'hero' });

// ---- Timeline intro -----------------------------------------------------------------
add('Timeline intro', 'timeline', 'box', ['.c-homepage-timeline'], ['section.c-homepage-timeline'], { anchor: 'timeline' });
add('Timeline intro', 'intro', 'box', ['.c-homepage-timeline_intro'], ['.c-homepage-timeline_intro'], { anchor: 'timeline' });
add('Timeline intro', 'intro.illustration', 'box', ['.c-homepage-timeline_intro_illustration'], ['.c-homepage-timeline_intro_illustration'], { anchor: 'timeline' });
add('Timeline intro', 'intro.rive', 'rive', ['c-rive[data-src*="hero_animation"] canvas'], ['c-rive[data-src*="hero_animation"] canvas'], { anchor: 'timeline', host: 'c-rive[data-src*="hero_animation"]' });
add('Timeline intro', 'intro.background', 'img', ['img.c-homepage-timeline_intro_illustration_background'], ['img.c-homepage-timeline_intro_illustration_background'], { anchor: 'timeline' });
add('Timeline intro', 'intro.path', 'svg', ['svg.c-homepage-timeline_intro_path'], ['svg.c-homepage-timeline_intro_path'], { anchor: 'timeline' });
add('Timeline intro', 'intro.text', 'text', ['.c-homepage-timeline_intro .container p'], ['.c-homepage-timeline_intro .container p'], { anchor: 'timeline' });
add('Timeline intro', 'intro.button', 'box', ['.c-homepage-timeline_intro .container a.c-button'], ['.c-homepage-timeline_intro .container a.c-button'], { anchor: 'timeline' });
add('Timeline intro', 'intro.ghost', 'box', ['.c-homepage-timeline_intro_ghost'], ['.c-homepage-timeline_intro_ghost'], { anchor: 'timeline' });

// ---- Cards --------------------------------------------------------------------------
for (let i = 0; i < 4; i++) {
  const n = `card${i + 1}`;
  add('Cards', n, 'box', [`.c-homepage-timeline-cards`], [`.c-homepage-timeline-cards`], { nth: i, anchor: 'wrap' });
  add('Cards', `${n}.title`, 'text', ['.c-homepage-timeline-cards .c-tile-animated_title'], ['.c-homepage-timeline-cards .c-tile-animated_title'], { nth: i, anchor: 'wrap' });
  add('Cards', `${n}.body`, 'text', ['.c-homepage-timeline-cards .c-tile-animated_description'], ['.c-homepage-timeline-cards .c-tile-animated_description'], { nth: i, anchor: 'wrap' });
  add('Cards', `${n}.button`, 'box', ['.c-homepage-timeline-cards a.c-button'], ['.c-homepage-timeline-cards a.c-button'], { nth: i, anchor: 'wrap' });
}

// ---- Rive characters ----------------------------------------------------------------
export const RIVES = ['climber', 'shrug', 'composer', 'trumpeter_1', 'trumpeter_2', 'gamer', 'scientist', 'soccer', 'binoculars', 'unicycle', 'basketball_player'];
for (const r of RIVES) {
  add('Rive', `rive.${r}`, 'rive', [`c-rive[data-src*="${r}.riv"] canvas`], [`c-rive[data-src*="${r}.riv"] canvas`], { anchor: 'wrap', host: `c-rive[data-src*="${r}.riv"]` });
}

// ---- Floating SVG ornaments (images) ------------------------------------------------
export const IMGS = ['clock', 'clock-shape', 'clock-shape-mobile', 'folder', 'folder-shape', 'plane-1', 'plane-2', 'plane-trace', 'sheet', 'composer-shape', 'composer-shape-2',
  'music-note-1', 'music-note-2', 'music-note-3', 'trumper-shape', 'gamer-shape', 'joystick', 'joystick-shape', 'soccer-circle', 'soccer-shape-1', 'soccer-shape-2',
  'soccer-shape-3', 'soccer-shape-4', 'soccer-trace', 'soccer-circle-2'];
for (const s of IMGS) add('SVG ornaments', `svg.${s}`, 'img', [`.c-homepage-timeline img[src$="/timeline/${s}.svg"]`], [`.c-homepage-timeline img[src$="/timeline/${s}.svg"]`], { anchor: 'wrap' });

// ---- Route path (inline SVG) --------------------------------------------------------
add('Route path', 'path.wrapper', 'box', ['.c-homepage-timeline_path'], ['.c-homepage-timeline_path'], { anchor: 'wrap' });
add('Route path', 'path.svg', 'svg', ['.c-homepage-timeline_path svg'], ['.c-homepage-timeline_path svg'], { anchor: 'wrap' });

// ---- Timeline end -------------------------------------------------------------------
add('Timeline end', 'end', 'box', ['.c-homepage-timeline_end'], ['.c-homepage-timeline_end'], { anchor: 'wrap' });
for (let i = 1; i <= 5; i++) add('Timeline end', `end.cloud${i}`, 'img', [`img.c-homepage-timeline_end_cloud_${i}`], [`img.c-homepage-timeline_end_cloud_${i}`], { anchor: 'wrap' });
for (const g of ['back', 'middle', 'front']) add('Timeline end', `end.green_${g}`, 'img', [`img.c-homepage-timeline_end_green_${g}`], [`img.c-homepage-timeline_end_green_${g}`], { anchor: 'wrap' });

// ---- Callout ------------------------------------------------------------------------
add('Callout', 'callout', 'box', ['c-deluxe-callout'], ['c-deluxe-callout'], { anchor: 'callout' });
add('Callout', 'callout.title', 'text', ['c-deluxe-callout p.heading-2xl'], ['c-deluxe-callout h2.heading-2xl'], { anchor: 'callout' });
add('Callout', 'callout.body', 'text', ['c-deluxe-callout p.heading-xs'], ['c-deluxe-callout p.heading-xs'], { anchor: 'callout' });
add('Callout', 'callout.button', 'box', ['c-deluxe-callout a.c-button'], ['c-deluxe-callout a.c-button'], { anchor: 'callout' });
add('Callout', 'callout.rive', 'rive', ['c-deluxe-callout c-rive canvas'], ['c-deluxe-callout c-rive canvas'], { anchor: 'callout', host: 'c-deluxe-callout c-rive', at: 'callout', edge: 'bottom' });

// Anchors: elements whose top edge defines the origin for `rel` rects.
export const ANCHORS = {
  hero: { ref: '.c-hero-home', tgt: '.c-hero-home' },
  timeline: { ref: '.c-homepage-timeline', tgt: 'section.c-homepage-timeline' },
  wrap: { ref: '.c-homepage-timeline > div.relative', tgt: '.c-homepage-timeline > div.relative' },
  callout: { ref: 'c-deluxe-callout', tgt: 'c-deluxe-callout' },
};

// Properties pulled for each element kind.
export const BOX_PROPS = ['display', 'position', 'zIndex', 'opacity', 'overflow', 'boxSizing', 'maxWidth', 'minHeight',
  'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 'marginTop', 'marginRight', 'marginBottom', 'marginLeft',
  'borderTopWidth', 'borderTopStyle', 'borderTopColor', 'borderTopLeftRadius', 'borderTopRightRadius', 'borderBottomLeftRadius',
  'boxShadow', 'backgroundColor', 'flexDirection', 'justifyContent', 'alignItems', 'gap', 'gridTemplateColumns', 'aspectRatio'];
export const TEXT_PROPS = ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'letterSpacing', 'textTransform', 'color', 'textAlign', 'fontStyle'];
