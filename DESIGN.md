# Design

Public UI style, modelled on mindmarket.com (layout, tokens, motion). Illustrations are our own SVGs, not copied.
Tokens live in `resources/css/app.css` (`@theme`). Change them there and here together.

## Color
| Token | Value | Use |
|---|---|---|
| `green` | `#8ed462` | Hero, callout, route, primary accents |
| `green-600` / `green-300` | `#6fb846` / `#b5e590` | Back / middle hill layers |
| `beige` | `#f5f1e4` | Page + timeline background, chips |
| `beige-500` | `#e0dbce` | Dividers, dotted route plan |
| `charcoal` | `#2c2e2a` | Text, outlines |
| `yellow` `red` `blue` `pink` | `#f5e211` `#ff705d` `#2ba0ff` `#ebc1ff` | One per timeline card button + decoration |

White is used for cards, nav bar and secondary buttons.

## Type
Inter (400/500/700, via Bunny). Headings are weight 500 with `-0.06em` tracking.

| Utility | Size |
|---|---|
| `heading-2xl` | `clamp(3.625rem, 1.1033rem + 12.6087vw, 10.875rem)`, lh .95 |
| `heading-sm` | `clamp(1.625rem, 1.3641rem + 1.3043vw, 2.375rem)`, lh 1.2 |
| `heading-xs` | `clamp(1.5rem, 1.413rem + .4348vw, 1.75rem)`, lh 1.2 |
| `text-md` | `clamp(1rem, .9783rem + .1087vw, 1.0625rem)` (nav, buttons) |

## Shape
- `radius-sm` 10px: nav bar, buttons, cards.
- `radius-lg` 50px: section edges (timeline top, callout bottom).
- Page gutter 16px (nav) / 20px (sections).

## Motion
| Token | Value |
|---|---|
| `--ease` | `cubic-bezier(.5,0,0,1)`: entrances, reveals |
| `--ease-bounce` | `cubic-bezier(.17,.67,.3,1.33)`: hover scale |
| `--ease-smooth` | `cubic-bezier(.38,.005,.215,1)` |
| Durations | .2s fast, .4s default, .6s slow |

Behaviours (`resources/js/app.js`, Lenis + GSAP ScrollTrigger + DrawSVG):
- Lenis smooth scroll; anchor links offset 100px for the fixed nav.
- Nav slides down on load (`.4s var(--ease) .2s`).
- Hero is sticky; timeline slides over it with a 50px rounded top while the hero darkens (`--progress * .4`).
- Green route draws with scroll, its tip at ~70% of the viewport.
- `[data-speed]` parallax on decorations and hill layers (desktop only).
- `[data-reveal]` fade-up 40px; `[data-lines]` masked line slide-up.
- `prefers-reduced-motion`: no Lenis, no GSAP, transitions off.

## Components
- **Button** (`partials/btn.blade.php`): white (or `--btn-bg`) 10px box with a 43px circle arrow on the right. On hover the arrow moves to the left and the background scales to 1.04. `primary` makes the circle white.
- **Nav link**: a beige pill scales in on hover.
- **Nav CTA**: yellow fill grows from the centre on hover.
- **Card**: white 10px box, `heading-sm` title, body, data list, coloured primary button.

## Layout
- Desktop timeline height is `365vw`. Cards are absolutely positioned with `--top` (%) and `--left` (vw). Decorations share one SVG (`viewBox 0 0 1440 5256`, `xMidYMax slice`).
- Below 768px, cards stack, the illustration layer is hidden, a static hills SVG closes the section, and the nav collapses into a `<details>` menu.
