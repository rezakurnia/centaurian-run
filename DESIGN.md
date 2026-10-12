# Design — MindMarket Style Reference (Centaurian Run)

> Warm storybook on cream paper — a friendly editorial canvas where oversized Inter headlines and paper-cut characters share a sunlit, sticker-soft surface.

**Theme:** light  
Public UI style, modelled 1:1 on MindMarket (layout, tokens, motion, components).

Tokens live in `resources/css/app.css` (`@theme`). Change them there and here together.

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
| Sunshine Pop | `#f5e211` | `--color-sunshine-pop` (`--color-yellow`) | Footer accent block highlight, Paket card button, warm closing band. |
| Blossom Pop | `#ebc1ff` | `--color-pink` | Decorative card accent; Hasil card button. |
| Forest Back / Middle | `#6fb846` / `#b5e590` | `--color-green-600` / `--color-green-300` | Timeline end hill layers. |

---

## Tokens — Typography

### Inter — Single-family system
Inter carries everything from 9px micro-labels to 144px display headlines. The system trusts Inter's geometric clarity at extreme sizes, relying on tight letter-spacing (`-0.06em` at 81px+) and aggressive line-height compression (`0.95–1.20`) to create visual weight without switching families.
- **Weights:** 400 (regular), 500 (medium), 700
- **Scale:**
  - `body-sm`: 15px, lh 1.5 (`--text-body-sm`)
  - `body-lg`: 18px, lh 1.5 (`--text-body-lg`)
  - `subheading`: 20px, lh 1.25 (`--text-subheading`)
  - `heading-sm`: 30px, lh 1.2 (`--text-heading-sm`)
  - `heading`: 53px, lh 1.15, tracking -2.12px (`--text-heading`)
  - `heading-lg`: 81px, lh 1.2, tracking -4.86px (`--text-heading-lg`)
  - `display`: 140px, lh 0.95, tracking -8.4px (`--text-display`)
  - `display-lg`: 144px, lh 0.95, tracking -8.64px (`--text-display-lg`)

Fluid root font-size on ≥768px: `clamp(15px, .48vw + 10.1px, 17px)` (15px at 1024, 17px from 1440); 16px below.

---

## Tokens — Spacing & Shapes

**Base unit:** 4px  
**Density:** comfortable  
**Spacing scale:** `4px` (`--spacing-4`), `20px` (`--spacing-20`), `60px` (`--spacing-60`), `136px` (`--spacing-136`), `216px` (`--spacing-216`).

### Border Radius
| Element | Value | Token |
|---------|-------|-------|
| Navigation Bar | 50px | `--radius-nav` |
| Content Cards | 50px (50–64px) | `--radius-cards` |
| Buttons | 50px | `--radius-buttons` |
| Small / Chips / Tags | 10px | `--radius-small` |
| Illustration Containers | 63.75px | `--radius-illustration-containers` |

### Layout
- **Page max-width:** 1200px (desktop bounds)
- **Section gap:** 80–120px
- **Card padding:** 20–21px (comfortable editorial padding)
- **Element gap:** 17–21px

---

## Components

### 1. Floating Pill Navigation Bar
- **Role:** Primary floating site navigation container.
- White (`#ffffff`) pill-shaped bar with **50px border-radius**, floating over the cream canvas with generous margins.
- Contains brand logo container (rounded square, ~40px), nav links at 15px Inter 500 in `#2c2e2a` with hover indicator, a circular green menu toggle button (`#8ed462`, 40px), and a pill CTA.
- The entire bar reads as a single soft pill, not a rectangular header.

### 2. Primary CTA Button
- **Role:** Main action trigger.
- Light/white pill button with **50px radius**, ~11px vertical and 20px horizontal padding, 15px Inter 500 `#2c2e2a` text.
- Features a circular icon accent (green or blue) embedded at the edge as a visual action indicator.

### 3. Service Card Action Button
- **Role:** Navigable service/package card entry.
- Pill shape (**50px radius**), colored primary button (Coral `#ff705d`, Sunshine `#f5e211`, Sky `#2ba0ff`, Pink `#ebc1ff`).

### 4. Content Card
- **Role:** Feature and timeline card container.
- White (`#ffffff`) surface with **50px border-radius**, generous internal padding, 30px Inter 500 heading, body text at 17–18px.
- Flat surface stack (no drop shadow).

### 5. Footer Accent Block
- **Role:** Footer highlight surface.
- Full-width **#f5e211 Sunshine Pop** yellow fill section at the page bottom.
- 50px+ vertical padding (`py-16 md:py-20`).
- Serves as the warm closing band contrasting sharply with the cream canvas.

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

- Lenis smooth scroll; anchor links offset 100px for the fixed nav.
- Nav slides down on load (`.4s var(--ease) .2s`).
- Hero is sticky; timeline slides over it with a 50px rounded top while the hero darkens (`--progress * .4`).
- Rive animations and Web Components (`c-rive`, `c-deluxe-callout`, `c-homepage-timeline`) render 1:1 MindMarket character and track interactions.
- `prefers-reduced-motion`: transitions and continuous animations off.
