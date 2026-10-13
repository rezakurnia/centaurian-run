# Design — MindMarket Style Reference (Centaurian Run)

> Warm storybook on cream paper — a friendly editorial canvas where oversized Inter headlines and paper-cut characters share a sunlit, sticker-soft surface.

**Theme:** light  
Public UI style, modelled 1:1 on MindMarket (layout, tokens, motion, components).

Tokens live in `resources/css/app.css` (`@theme` and `:root`). Values are measured against the reference by `audit/` (see `audit/README.md`); change them there and here together.

---

## Tokens — Colors

| Name | Value | Token | Role |
|------|-------|-------|------|
| Fresh Grass | `#8ed462` | `--color-fresh-grass` (`--color-green`) | Primary brand accent — navigation strokes, card borders, hero fills, callout, route path. The chromatic anchor. |
| Cream Paper | `#f5f1e4` | `--color-cream-paper` (`--color-beige`) | Dominant page background and soft card surface. Warm off-white canvas replacing stark white. |
| Ink Black | `#2c2e2a` | `--color-ink-black` (`--color-charcoal`) | Primary text, icons, nav borders, dominant hairline border color. |
| Pure White | `#ffffff` | `--color-pure-white` | Elevated card surfaces, floating nav background. The highest surface level in the stack. |
| Sandstone | `#e0dbce` | `--color-sandstone` (`--color-beige-500`) | Secondary surface tone for inset or recessed card states, subtle dividers. |
| Stone Gray | `#80827f` | `--color-stone-gray` | Muted body text and secondary link borders. The mid-gray for de-emphasized content. |
| Hairline Mist | `#d5d5d4` | `--color-hairline-mist` | Subtle nav dividers and low-contrast borders. Barely visible structural lines. |
| Pure Ink | `#000000` | `--color-pure-ink` | Icon fills, body text on light surfaces, high-contrast borders where maximum contrast is needed. |
| Sky Pop | `#2ba0ff` | `--color-sky-pop` (`--color-blue`) | Decorative illustration accent and card border accent; Jadwal card button. |
| Coral Pop | `#ff705d` | `--color-coral-pop` (`--color-red`) | Red outline accent for tags, dividers, and Kategori card CTA button. |
| Sunshine Pop | `#f5e211` | `--color-sunshine-pop` (`--color-yellow`) | Paket card button, hover fill of the nav CTA. |
| Blossom Pop | `#ebc1ff` | `--color-pink` | Decorative card accent; Hasil card button. |
| Forest Back / Middle | `#6fb846` / `#b5e590` | `--color-green-600` / `--color-green-300` | Timeline end hill layers. |

---

## Tokens — Typography

### Inter — Single-family system
Inter carries everything from 9px micro-labels to 144px display headlines. The system trusts Inter's geometric clarity at extreme sizes, relying on tight letter-spacing (`-0.06em` at 81px+) and aggressive line-height compression (`0.95–1.20`) to create visual weight without switching families.
- **Weights:** 400 (regular), 500 (medium), 700
- **Scale (fluid, rem = root size):**
  - `body-sm`: `--text-sm` clamp(.75rem → .875rem), lh 1.25
  - `body-md` / buttons / nav links: `--text-md` clamp(1rem → 1.0625rem), lh 1.25, tracking -0.04em
  - `body-lg` / card copy: `--text-lg` clamp(1.1563rem → 1.1875rem), lh 1.25, tracking -0.04em
  - `heading-xs`: `--text-heading-xs` clamp(1.5rem → 1.75rem), lh 1.2, tracking -0.06em
  - `heading-sm` (card title ≥1000px <1600px): `--text-heading-sm` clamp(1.625rem → 2.375rem), lh 1.2
  - `heading-md` (card title below 1000px and from 1600px): `--text-heading-md` clamp(2.125rem → 3.125rem), lh 1.15
  - `heading-2xl` (hero, callout): `--text-heading-2xl` clamp(3.625rem → 10.875rem); from 1000px `9.7vw`, lh .95, tracking -0.06em

Fluid root font-size: `--text-md` (16 → 17px) by default, 15px from 1000 to 1199px, 19px from 2000px, 21.5px from 2400px. Everything sized in `rem` or `--spacing` (.25rem) scales with it.

---

## Tokens — Spacing & Shapes

**Base unit:** 4px  
**Density:** comfortable  
**Spacing scale:** `--spacing` (.25rem) multiples, `--unit-md` 20px (grid gutter and margin), fluid steps `--spacing-fluid-xs` … `--spacing-fluid-4xl` (8px → 160px).

### Breakpoints (reference scale, not Tailwind's defaults)
`2xs` 400px · `xs` 500px · `sm` 700px · **`md` 1000px (desktop layout starts here)** · `lg` 1200px · `xl` 1400px · `2xl` 1600px · `3xl` 1800px · `4xl` 2000px · `5xl` 2400px.

### Border Radius
| Element | Value | Token |
|---------|-------|-------|
| Nav bar, buttons, card panels, chips | 10px | `--radius-sm` |
| Large panels (timeline top, callout bottom) | 35px below 1000px, 50px from 1000px | `--radius-lg` |
| Nav link hover pill | 50px | (literal) |
| Mobile "Daftar" chip | 5px | (literal) |

### Layout
- **Grid:** 16 columns, 20px gutter and margin (`--unit-md`); `--gs-{columns}-{gutters}` tokens hold the reference's `grid-space()` maths.
- **Menu bar height:** 15 × `--spacing` below 1000px, 16 × `--spacing` from 1000px; fixed 4 × `--spacing` from the top.
- **Card width:** 6 of 16 columns (max 800px), 7 columns on 1000–1399px; padding 6.25 × 5 `--spacing` below 1400px, 12.5 × `--spacing` from 1400px.
- **Section gap:** `--spacing-fluid-*` (xs 8px → 4xl 160px).

---

## Components

### 1. Floating Navigation Bar
- **Role:** Primary site navigation. Desktop bar (`.c-menu-desktop`, ≥1000px) and mobile bar + sliding list (`.c-menu-mobile`).
- White (`#ffffff`) bar, **10px radius**, 16 × spacing tall, floating 4 × spacing from the top and inset to grid column 1 (2 from 1600px).
- Logo mark 36 × 30px + wordmark 32 × spacing wide; links `--text-md` Inter 500 with a beige hover pill (50px); green 40px burger; quote button on the right.

### 2. Button (`.c-button`)
- **Role:** Every call to action. White (`button-secondary`) or colour-filled (`button-primary`), **10px radius**, 15 × spacing tall, 2.5 × spacing padding, `--text-md` Inter 500.
- 40px circular icon on the right; on hover the label slides right and the icon swaps sides (`--ease-bounce`).
- Colours: green, yellow `#f5e211`, pink `#ebc1ff`, red `#ff705d`, blue `#2ba0ff`.

### 3. Nav Quote Button (`<c-button-quote>`)
- White **10px** button whose 40px icon is a Rive face (`mindmarket_cursor_crop.riv`, state machine "State Machine 1") that follows the cursor and smiles on hover.

### 4. Content Card (`.c-homepage-timeline-cards` > `.c-tile-animated`)
- White (`#ffffff`) panel, **10px radius**, `heading-md`/`heading-sm` title, `body-lg` copy, button at the bottom. Flat (no shadow).

### 5. Footer Row
- **Role:** Closing line inside the green callout, under the CTA (no separate footer block).
- Three centred columns (`body-md`, `md:grid-cols-3`, 60 × `--spacing` above on desktop; stacked below 1000px), text in `#2c2e2a`.

---

## Surfaces & Elevation

| Level | Name | Value | Purpose |
|-------|------|-------|---------|
| 0 | Cream Paper | `#f5f1e4` | Page canvas — the dominant warm background |
| 1 | Pure White | `#ffffff` | Elevated cards, floating navigation |
| 2 | Sandstone | `#e0dbce` | Inset or secondary surface tone, hairline borders |

**Elevation Rule:**  
MindMarket uses a cream-to-white surface stack and generous border-radii instead of box-shadows for elevation. Drop shadows are avoided to maintain the flat, editorial, printed storybook feel.

---

## Motion & Behaviors

| Token | Value |
|---|---|
| `--ease` | `cubic-bezier(.5,0,0,1)`: entrances, reveals |
| `--ease-bounce` | `cubic-bezier(.17,.67,.3,1.33)`: hover scale |
| `--ease-smooth` | `cubic-bezier(.38,.005,.215,1)` |
| Durations | .2s fast, .4s default, .6s slow |

- Locomotive Scroll v5 (Lenis inside) drives smooth scroll and every `[data-scroll-*]` attribute: `data-scroll-speed` parallax, `data-scroll-css-progress` (`--progress`), `data-scroll-event-progress` (the route's `pathProgress`) and the `is-inview` class that reveals cards. Anchor links offset 100px for the fixed nav.
- Nav slides down on load (`.4s var(--ease) .2s`); hero text rises (`.anim-first-hit`, 80px, `.8s`).
- Hero is pinned (sticky, 200svh inner) while the timeline slides over it; the hero darkens (`--progress * .4`) and its content scales to .8.
- The green route is one paused GSAP timeline scrubbed by `pathProgress` (desktop and mobile SVGs, rebuilt at 1000px).
- Rive runtime: `@rive-app/canvas-lite` 2.31.5, default `Fit.Contain` / `Alignment.Center`, rendering only while on screen; `.riv` characters pop in with an elastic scale. The `.wasm` is served from our build.
- `prefers-reduced-motion`: transitions off and smooth scroll / scroll-linked motion disabled.
