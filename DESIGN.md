# Design — MindMarket Style Reference (Centaurian Run)

> Warm storybook on sand-cream paper — a friendly editorial canvas where oversized Inter headlines and paper-cut characters share a sunlit, sticker-soft surface.

**Theme:** light  
Public UI style, modelled 1:1 on MindMarket (layout, tokens, motion, components).

Tokens live in `resources/css/app.css` (`@theme` and `:root`). Values are measured against the reference by `audit/` (see `audit/README.md`); change them there and here together.

---

## Tokens — Colors

| Name | Value | Token | Role |
|------|-------|-------|------|
| Rose Pop | `#ea6592` | `--color-rose-pop` (`--color-green`) | Primary brand accent — navigation strokes, card borders, hero fill, callout, route path. The chromatic anchor. |
| Sand Cream | `#fff6e5` | `--color-sand-cream` (`--color-beige`) | Dominant page background and soft card surface. Warm canvas. |
| Plum Night | `#3a1f3f` | `--color-plum-night` (`--color-charcoal`) | Primary text, icons, nav borders, dominant hairline border color; dark surface behind the logo. |
| Pure White | `#ffffff` | `--color-pure-white` | Elevated card surfaces, floating nav background. The highest surface level in the stack. |
| Sand | `#f3d9a8` | `--color-sand` (`--color-beige-500`) | Secondary surface tone for inset or recessed card states, subtle dividers. |
| Mauve Gray | `#7a6580` | `--color-mauve-gray` | Muted body text and secondary link borders. |
| Hairline Mist | `#e8d9dd` | `--color-hairline-mist` | Subtle nav dividers and low-contrast borders. |
| Pure Ink | `#000000` | `--color-pure-ink` | Icon fills and high-contrast borders where maximum contrast is needed. |
| Orchid | `#b5568b` | `--color-orchid` (`--color-blue`) | Decorative illustration accent, card border accent; Jadwal card button. |
| Peach | `#f2a28e` | `--color-peach` (`--color-red`) | Tags, dividers, Kategori card CTA button. |
| Logo Yellow | `#fdea00` | `--color-logo-yellow` (`--color-yellow`) | Logo colour, Paket card button, hover fill of the nav CTA. Use on plum or the gradient, not on cream/white. |
| Marigold | `#eec15a` | `--color-marigold` (`--color-yellow-600`) | Badges, mobile contact icon, hero gradient end. |
| Grape | `#8b4a80` | `--color-grape` | Deep brand tone for dark sections and headings on cream. |
| Blush | `#f9c9da` | `--color-pink` | Decorative card accent; Hasil card button. |
| Rose Dark / Light | `#c94f7e` / `#f6a9c0` | `--color-green-600` / `--color-green-300` | Timeline end hill layers. |

**Hero gradient:** `--gradient-hero` = `linear-gradient(160deg, Rose Pop 0%, Peach 55%, Marigold 100%)`, used on `.c-hero-home_inner` (and the hero below 1000px). Palette is sampled from the Centaurian Run background (plum → magenta → peach → gold). Text on rose, peach, marigold and cream is Plum Night; white on rose is large text only.

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
- Logo mark 36 × 30px + wordmark 32 × spacing wide; links `--text-md` Inter 500 with a beige hover pill (50px): Tentang, Kategori, Paket, Jadwal, Cek Status, Hasil Lomba (no dropdown); quote button on the right.

### 2. Button (`.c-button`)
- **Role:** Every call to action. White (`button-secondary`) or colour-filled (`button-primary`), **10px radius**, 15 × spacing tall, 2.5 × spacing padding, `--text-md` Inter 500.
- 40px circular icon on the right; on hover the label slides right and the icon swaps sides (`--ease-bounce`).
- Colours: rose (`green`), yellow `#fdea00`, blush (`pink`) `#f9c9da`, peach (`red`) `#f2a28e`, orchid (`blue`) `#b5568b`.

### 3. Nav Quote Button (`<c-button-quote>`)
- White **10px** button whose 40px icon is a Rive face (`mindmarket_cursor_crop.riv`, state machine "State Machine 1") that follows the cursor and smiles on hover.

### 4. Content Card (`.c-homepage-timeline-cards` > `.c-tile-animated`)
- White (`#ffffff`) panel, **10px radius**, `heading-md`/`heading-sm` title, `body-lg` copy, button at the bottom. Flat (no shadow).

### 5. Form (`/daftar`)
- **Role:** Registration, modelled on the reference's cost estimator: numbered steps (`.c-step`: white panel, **10px radius**, 40px plum number badge) and a sticky white summary panel beside them.
- **Option card** (`.c-option_card`): a native radio drives it. Sand Cream fill, 2px Hairline Mist border, 10px radius, 20px ring indicator top-right. Checked = Blush (`--color-pink`) fill, Plum Night border, filled ring. Focus = 2px Rose Pop outline.
- **Input** (`.c-input`): 13 × spacing tall, white, 1px Hairline Mist border, 10px radius, label above in `--text-md`. Focus = 2px Rose Pop outline, Plum Night border. Placeholders are not used as labels.
- **Error** (`.c-error`): Grape (`#8b4a80`, 6.4:1 on white) text under the field with a "!" badge, input gets a 2px Grape border. Never colour alone.
- **Status badge** (`.c-badge`): 10px radius, always text, never colour alone. `-ok` Marigold (confirmed, paid, free), `-wait` Blush (pending, unpaid), `-bad` Grape with white text (cancelled). Used on `/cek-status`, which also hosts the payment-proof file input (`.c-input.c-file`).
- **Table** (`.c-table`, `/hasil`): inside a `.c-step`; 2px Plum Night header rule, 1px Hairline Mist row rules, `--text-lg` rows, no zebra or shadow, scrolls horizontally in its wrapper below 34rem. The category filter is a native `select.c-input`.
- Submit is a `.c-button` rendered as `<button>`; it dims and shows a progress cursor while sending.

- **Result detail** (`/hasil/{no}`): one `.c-step` card (name heading-xs, category line, duration heading-md tabular, start/finish line). Registration numbers in the `/hasil` table link to it with `.c-link`.

### 6. Footer Row
- **Role:** Closing line inside the green callout, under the CTA (no separate footer block).
- A "Login panitia" `.c-link` sits in the last column. Three centred columns (`body-md`, `md:grid-cols-3`, 60 × `--spacing` above on desktop; stacked below 1000px), text in `#3a1f3f`.

### 7. Console (panitia/admin, `/login`, `/panitia/*`)
- **Role:** Working screens for staff, not marketing. Same tokens as the public site, denser, no Locomotive/Rive (separate Vite entry `resources/js/console/main.js`).
- Shell (`.k-shell`): white sidebar 15rem from 1000px (top bar with horizontally scrolling nav below), Sand Cream canvas, white `.k-card` panels, 10px radius, flat.
- Nav link current page = Blush fill. Button `.k-btn` = Plum Night fill, `-ghost` = outlined. Scan result `.k-result` reuses badge colours: `-ok` Marigold, `-wait` Blush (duplicate), `-bad` Grape with white text; always text, never colour alone.
- Admin: stat tiles (`.k-stats` > `.k-card` + `.k-stat`, heading-sm bold), native `<dialog class="k-dialog">` for verification (white, 10px radius, plum 50% backdrop).
- CRUD pages (`/admin/event|konten|pengguna|pengaturan`) share one table + `<dialog>` form (`c-crud` config in `resources/js/console/crud.js`); row actions are `.k-btn.-ghost` in `.k-actions`; outcome line is `.k-result` (`-ok`/`-bad`).
- Log pages (`/admin/scan-log|log-aktivitas`): filter row of `.c-input` + `.c-table` + prev/next `.k-btn.-ghost` (50 rows per page); scan status uses `.c-badge`. `/admin/ekspor` downloads CSV through fetch + blob (Bearer header needed).
- Mobile rules: `.k-shell` column is `minmax(0, 1fr)` so the long nav scrolls inside `.k-nav` instead of widening the page; every tappable control in the console is at least 44px tall (`.k-nav a`, `.k-btn.-ghost`, links in `.k-dialog` / login card get block padding). Tables scroll inside `.k-scroll`.
- Forms and tables reuse `.c-input`, `.c-error`, `.c-table`, `.c-badge`.

---

## Surfaces & Elevation

| Level | Name | Value | Purpose |
|-------|------|-------|---------|
| 0 | Sand Cream | `#fff6e5` | Page canvas — the dominant warm background |
| 1 | Pure White | `#ffffff` | Elevated cards, floating navigation |
| 2 | Sand | `#f3d9a8` | Inset or secondary surface tone, hairline borders |

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
- Inner-page heroes (daftar, cek status, hasil, detail hasil) are centered text on `--gradient-hero`, no illustration.
- `prefers-reduced-motion`: transitions off and smooth scroll / scroll-linked motion disabled.
