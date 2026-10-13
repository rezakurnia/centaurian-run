# Layout audit

Measures this site against the reference (https://mindmarket.com) and diffs the two. Dev tooling only: it is not part of the app build and has its own `package.json`.

```sh
cd audit && npm install        # once: playwright 1.63 (use `npx playwright install chromium` if the browser is missing), pixelmatch, pngjs
bash run-all.sh                # capture both sites at 5 viewports, then compare
node diff.mjs                  # pixel diff -> diff/*.png + diff/summary.md
node motion.mjs                # scroll-driven behaviour (parallax, --progress, route draw) at fixed scroll positions
node smoke.mjs                 # console errors, third-party requests, menus, Rive instances
```

The app must be reachable at `http://127.0.0.1:8000` (override with `TARGET_URL`) with a fresh `npm run build`.

| File | What it is |
|---|---|
| `config.mjs` | Viewports and the reference/target selector pairs (add a probe here to audit a new element) |
| `reference.json`, `target.json` | Phase 1 audit: rects, computed styles, fonts, SVG and Rive details per viewport |
| `target-parity.json` | Same target audit after the reference's copy is injected (`PARITY=1`), so geometry is compared independent of text length |
| `comparison.md/.json`, `comparison-parity.md/.json` | Phase 2 tables (Element, Property, Reference, Target, Delta, Severity), grouped by section |
| `diff/` | Visual diff images and `summary.md` |
| `shots/` | Full-page screenshots (git-ignored) |

Tolerance is ±1px. Copy differences (Indonesian vs English text, nav item count, target-only blocks) move boxes by themselves; the parity run removes that noise.
