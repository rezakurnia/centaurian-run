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
- The 40px burger circle has an invisible 2px ring (`::after`) for a 44px tap area. Standalone `.c-link`s (not inside running text) are `inline-block py-3 -my-3`: 44px tap height, no layout change. FAQ `summary` rows carry the `py-4` themselves so the whole row is the tap target. The footer "Jelajahi" list is two columns below 700px.
- White (`#ffffff`) bar, **10px radius**, 16 × spacing tall, floating 4 × spacing from the top and inset to grid column 1 (2 from 1600px).
- Logo mark 36 × 30px + wordmark 32 × spacing wide; links `--text-md` Inter 500 with a beige hover pill (50px): Tentang, Kategori, Paket, Jadwal, Rute and Tata Tertib (landing only), BIB Checking, Hasil Lomba (no dropdown); the mobile list is the same set. A skip link ("Lewati ke konten") sits before the bars and the current page link carries `aria-current`; quote button on the right.

### 2. Button (`.c-button`)
- **Role:** Every call to action. White (`button-secondary`) or colour-filled (`button-primary`), **10px radius**, 15 × spacing tall, 2.5 × spacing padding, `--text-md` Inter 500.
- 40px circular icon on the right; on hover the label slides right and the icon swaps sides (`--ease-bounce`).
- Colours: rose (`green`), yellow `#fdea00`, blush (`pink`) `#f9c9da`, peach (`red`) `#f2a28e`, orchid (`blue`) `#b5568b`.

### 3. Nav Quote Button
- White **10px** text-only "Daftar sekarang" button (no icon); yellow hover fill rises from the bottom.

### 4. Content Card (`.c-homepage-timeline-cards` > `.c-tile-animated`)
- White (`#ffffff`) panel, **10px radius**, `heading-md`/`heading-sm` title, `body-lg` copy, button at the bottom. Flat (no shadow).

- **Rute and Tata Tertib** (landing `#rute`, `#tata-tertib`): Rose Pop sections after Jadwal. Rute is the 5K map (`public/images/rute-5k.webp`) in a keyboard-focusable scroll region that scrolls sideways below 1000px, plus a legend of `bg-beige` 10px chips. Tata Tertib lists the rules from `TataTertibService`.
- **FAQ** (`.c-faq`): native `<details>` rows inside a white panel, hairline dividers, a "+" / "–" marker drawn with CSS generated content and hidden from screen readers.

### 5. Form (`/daftar`)
- **Role:** Registration, modelled on the reference's cost estimator: numbered steps (`.c-step`: white panel, **10px radius**, 40px plum number badge) and a sticky white summary panel beside them.
- Two steps: category, then personal data. The package is not picked; it follows the category (the API assigns it), so each category card shows only the category name, and the summary reads the package and price from the checked card.
- Fields per category (NIS/kelas, NIP/jabatan/unit, anak, tahun lulus) sit in `fieldset[data-for=<code>]` blocks, shown and enabled only for the checked category so hidden ones are not submitted.
- **Guru Karyawan NIP gate**: picking that category focuses the NIP field first. The NIP is checked live against the `employees` master (`GET /api/employees/{nip}`); a match shows a `.c-note` line with a Marigold ✓, fills "Nama lengkap" from the master and locks it (`readonly`, Sand Cream fill, `.c-hint` note under it). An unknown NIP shows a `.c-error.-nip` line (Grape × instead of "!") plus the 2px Grape input border. One NIP may only register once per event; the API enforces both rules.
- Quota from `/api/categories/quota` feeds the "Kuota" line in the summary ("N dari M tempat"); a full category gets a "Penuh" `.c-badge -bad` and a disabled card: its content dims to 60% but the badge stays at full opacity for contrast (not-allowed cursor).
- When the `registration_open` / `registration_deadline` settings close registration, a `.c-step` notice sits above the form and submit is disabled. Errors not tied to a field (closed, deadline, quota) show in the form error line.
- **Compact option** (`.c-option_card.-compact`, gender): 12px padding, ring centred vertically, `--text-md` title so both fit two-up at 375px.
- **Option card** (`.c-option_card`): a native radio drives it. Sand Cream fill, 2px Hairline Mist border, 10px radius, 20px ring indicator top-right. Checked = Blush (`--color-pink`) fill, Plum Night border, filled ring. Focus = 2px Rose Pop outline. Disabled = content at 60% opacity (badge excluded).
- **Input** (`.c-input`): 13 × spacing tall, white, 1px Hairline Mist border, 10px radius, label above in `--text-md`. Focus = 2px Rose Pop outline, Plum Night border. Placeholders are not used as labels.
- **Error** (`.c-error`): Grape (`#8b4a80`, 6.4:1 on white) text under the field with a "!" badge, input gets a 2px Grape border. Never colour alone. The NIP lookup failure uses `.c-error.-nip`, which swaps the "!" for a "×".
- **Helper and success lines**: `.c-hint` is the muted `body-sm` hint under a field (hidden once the field has a value where it would repeat the status); `.c-note` is the verified state, same layout as `.c-error` but with a Marigold "✓" badge and no error border.
- **Status badge** (`.c-badge`): 10px radius, always text, never colour alone. `-ok` Marigold (confirmed, paid, free), `-wait` Blush (pending, unpaid), `-bad` Grape with white text (cancelled). Used on `/cek-status`.
- **Result pop-up** (`.c-dialog`): the BIB lookup result opens in a native `<dialog>` over Plum Night 55% instead of pushing the page down — either the celebration BIB card or the informational status card, riding the same white 10px-radius card. A 44px white close chip (`.c-dialog_close`) sits inside the gradient panel's top-right corner; Escape, a backdrop click and the chip all dismiss it. The dialog has no fill of its own, so the card is the surface; the body scrolls past the viewport height (minus 2rem) when an email matches several registrations. The BIB card ends in a Sand Cream QR panel: an email lookup draws the QR (256px canvas, "Unduh QR" `.c-link`) straight away; a number-only lookup shows a centred email field + "Tampilkan QR" button there instead, and the API only returns the barcode once number and email match.
- **Table** (`.c-table`, `/hasil`): inside a `.c-step`; 2px Plum Night header rule, 1px Hairline Mist row rules, `--text-lg` rows, no zebra or shadow, scrolls horizontally in its wrapper below 34rem. The category filter is a native `select.c-input`.
- Submit is a `.c-button` rendered as `<button>`; it dims and shows a progress cursor while sending.
- **Payment redirect** (`/daftar` success panel): a paid package replaces the form with a `.c-step` "Mengarahkan ke pembayaran…" panel (no participant number) and a `.c-link` fallback, then sends the browser to the Snap page after 0.4s. The number/status card (`#register-success`) is only for free packages, or when Snap is unavailable (a "Pembayaran belum tersedia" note tells the participant to contact the committee; there is no upload-proof flow).
- **Payment result page** (`/pembayaran/selesai/{no}`): same public shell as `/cek-status`. Waiting state = white `.c-step`, "Menunggu pembayaran", category/package and links (resume Snap, reload, BIB Checking), no number; `c-payment-finish.js` polls the status API every 3s and reloads once paid. Paid state = the celebration `.c-step` (gradient "Pendaftaran berhasil!" + Lunas/Gratis badge, big participant number, name/category/package, "Lihat kartu BIB →"). Unknown numbers render the same card with "Pendaftaran tidak ditemukan" and HTTP 404.

- **Result detail** (`/hasil/{no}`): one `.c-step` card (name heading-xs, category line, duration heading-md tabular, start/finish line). Registration numbers in the `/hasil` table link to it with `.c-link`.

### 6. Footer
- **Role:** Site footer inside the rose callout (landing) or rose panel (inner pages), text in Plum Night.
- Four columns from 1000px (`1.4fr 1fr 1fr 1fr`), two from 700px, stacked below: brand (logo mark, app name, one-line tagline), "Jelajahi" nav links, "Kontak" (email/phone) and "Ikuti kami" (WhatsApp/Instagram/Facebook). Contact and social columns only render when Admin → Pengaturan has values; only http(s) social links are shown.
- Links are `.c-link` `inline-block` with 12px block padding (44px tap height). A legal bar under a 1px plum rule holds the copyright and the "Login panitia" link.

### 7. Console (panitia/admin, `/login`, `/panitia/*`)
- **Role:** Working screens for staff, not marketing. Same tokens as the public site, denser, no Locomotive/Rive (separate Vite entry `resources/js/console/main.js`).
- Shell (`.k-shell`): white sidebar 15rem from 1000px (top bar with horizontally scrolling nav below), Sand Cream canvas, white `.k-card` panels, 10px radius, flat.
- Nav link current page = Blush fill. Button `.k-btn` = Plum Night fill, `-ghost` = outlined. Scan result `.k-result` reuses badge colours: `-ok` Marigold, `-wait` Blush (duplicate), `-bad` Grape with white text; always text, never colour alone.
- Admin: stat tiles (`.k-stats` > `.k-card` + `.k-stat`, heading-sm bold), native `<dialog class="k-dialog">` for verification (white, 10px radius, plum 50% backdrop).
- Detail lists (`.k-dl`): a two-column `dt`/`dd` grid, `dt` at 75% opacity, long values wrap. They're used for the registrant in the Pendaftaran verify dialog and in the log detail dialog (a "Detail" `.k-btn.-ghost` on each log row).
- Announcements (`.k-note`): published Konten with target "panitia" render as `.k-card` panels under the page title on every console page.
- Scan page follows "Konfigurasi Scan" settings: the same barcode is ignored within the cooldown, the result line clears after the auto-clear time, a Web Audio beep plays (high = valid, low = duplicate/failed) when sound is on, the scheduled start hour shows before the start, and "Akhiri acara" only appears N minutes after the start.
- CRUD pages (`/admin/event|konten|pengguna`) share one table + `<dialog>` form (`c-crud` config in `resources/js/console/crud.js`); row actions are `.k-btn.-ghost` in `.k-actions`; outcome line is `.k-result` (`-ok`/`-bad`).
- Pengaturan (`/admin/pengaturan`): one `.k-card` per settings group (`.k-h2` title, two-column grid of `.c-input`, textareas full width), one "Simpan pengaturan" `.k-btn` that saves everything in one bulk update. Toggles are `.k-check`: a native checkbox (20px, Plum Night accent, Rose Pop focus ring) inside a 44px-tall label.
- Scan (`/panitia/scan`): "Mulai serentak" `.k-btn` and "Akhiri acara" `.k-btn.-ghost` side by side; after finish the status line says "Scan ditutup" and the scan form is disabled.
- Peserta (`/admin/peserta`): "Impor peserta" `.k-card` under the table: template downloads as `.k-btn.-ghost`, type and package selects, `.c-input.c-file`. The outcome is a `.k-result` (`-ok`, `-wait` when some rows failed, `-bad`), with per-row lines in a `<details>`.
- Log pages (`/admin/scan-log|log-aktivitas`): filter row of `.c-input` + `.c-table` + prev/next `.k-btn.-ghost` (50 rows per page); scan status uses `.c-badge`. `/admin/ekspor` and the import templates download through `download()` in `api.js` (fetch + blob, Bearer header needed).
- Mobile rules: `.k-shell` column is `minmax(0, 1fr)` so the long nav scrolls inside `.k-nav` instead of widening the page; every tappable control in the console is at least 44px tall (`.k-nav a`, `.k-btn.-ghost`, links in `.k-dialog` / login card get block padding). Below 700px every console table becomes cards: `main.js` copies each `thead th` into `data-label` on its cells (one `MutationObserver` per `.k-scroll tbody`, so every renderer is covered), rows are blocks with a hairline, cells are label-left/value-right lines, and the actions cell (sr-only "Aksi" header, empty label) holds wrapping full-width `.k-btn.-ghost`s. Below 1000px the side bar is a compact header (brand + user/Keluar on one row, nav scrolling under it with a right-edge fade, the current link scrolled into view) and `.k-shell` rows are `auto 1fr` so it never stretches. Below 700px `.k-row > .k-btn` share the row width (scan, start, pager, dialog actions); the log pager puts the page count under prev/next. Tables scroll inside `.k-scroll` (`position: relative`, so the absolute `sr-only` "Aksi" header cannot widen the page); console tables drop the 34rem `.c-table` floor and size to their nowrap cells. Stat tiles are two per row from 300px (`minmax(9rem, 1fr)`) with the value pinned to the tile bottom.
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
- `prefers-reduced-motion`: transitions off, no smooth scroll or scroll-linked motion, the route is shown fully drawn, and Rive characters render without autoplay or the pop-in.
- The scroll engine, GSAP, the route timeline and Rive load only on pages that use them (landing); other public pages ship the small form/status scripts only. `html.is-loaded` is set by an inline script in the nav partial, so the nav never stays off-screen if the bundle fails.
