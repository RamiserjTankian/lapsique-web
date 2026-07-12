# Design QA — Alpha editorial system

## Evidence

- Source visual: `/Users/redasoft/.codex/generated_images/019f52f1-6e89-7c93-aa9a-6ce054ecc35a/exec-f1797c23-6e55-48de-b9d3-0f643975c631.png`
- Desktop implementation: `storage/app/design-qa-alpha/home-desktop-passed.png`
- Mobile implementation: `storage/app/design-qa-alpha/home-mobile-final-v2.png`
- Side-by-side comparison: `storage/app/design-qa-alpha/home-comparison-final.png`
- Desktop viewport: 1440 × 1024
- Mobile viewport: 390 × 844
- State: default home hero, local runtime at `http://127.0.0.1:8000/`

## Comparison history

1. P1 — The original implementation pushed the price and primary CTA below the first viewport. Reduced headline scale and tightened the editorial column so the commercial action is visible without scrolling.
2. P2 — The media surface was excessively dark because the generic background overlay obscured the footage. Replaced it with the direct autoplay media surface and retained only the camera metadata overlay.
3. P2 — The desktop headline produced an editorially poor syllable break. Disabled hyphenation and adjusted desktop padding and type scale to preserve complete words.

## Full-view comparison

- Layout: matches the selected split editorial/viewfinder composition and technical equipment strip.
- Typography: condensed uppercase display face, neutral sans body copy, and mono camera metadata reproduce the selected hierarchy.
- Color: warm off-white, near-black, neutral gray, and restrained Alpha-orange accent are consistent across public pages and shared components.
- Content: the generated camera-rig placeholder is intentionally replaced by Lapsique's real portfolio footage so the landing sells the actual output.

## Focused component and interaction checks

- Primary booking CTA opens the booking dialog.
- Date selection advances to available time slots.
- Time-slot selection advances to the lead form and displays the booking summary.
- Form controls have explicit labels, names, autocomplete metadata, and required-state behavior.
- All seven public landing routes were checked at desktop and mobile widths: one H1, no horizontal overflow, meta description, structured data, and no browser console errors.
- Meta contact events are deduplicated when an explicitly managed click and delegated auto-tracking occur in the same interaction.

## Remaining findings

- P0: none.
- P1: none.
- P2: none.
- P3: the source mockup uses a generated camera product image; production uses real portfolio footage by design.

final result: passed

## Orientation audit — vertical and horizontal

- Routes checked: `/`, `/djs`, `/eventos`, `/trabajos-en-video`, `/reels-de-comida`, `/dj-set`, and `/avances-de-obra`.
- Portrait viewport: 390 × 844. All routes measured zero horizontal overflow, retained one visible H1, and reported no completed broken images.
- Landscape viewport: 844 × 390. All routes measured zero horizontal overflow; the desktop header, language control and grouped navigation fit inside the viewport.
- Wide landscape viewport: 1280 × 720. The home split composition retains its editorial hierarchy with no document overflow.
- Interaction state: desktop `Escena` and `Servicios` menus were opened in the 844 × 390 viewport and checked against all four viewport edges.
- Console state: no warning or error was emitted during the final landscape menu checks.

### Fixes made during this pass

1. P1 — The final DJ-set CTA group overflowed in landscape because the booking control retained a full-row width inside a horizontal flex group. Replaced the group with a bounded two-column grid; final measured bounds are 81–411 px and 423–753 px in landscape, and both controls stack at 20–359 px in portrait.
2. P2 — Navigation dropdowns exceeded the right and bottom edges on short landscape screens. Right-aligned each dropdown to its trigger and limited it to the available viewport height with internal scrolling. Final `Escena` bounds are 287–623 × 60–378 px and final `Servicios` bounds are 395–731 × 60–378 px.

### Remaining findings

- P0: none.
- P1: none.
- P2: none.
- P3: lazy images outside the current viewport intentionally remain unloaded until scrolling.

final result: passed

## Editorial reconstruction — DJs, events and productions

### Evidence

- Source visual truth: `/tmp/lapsique-sony-source-home-final.png`
- Desktop implementation: `/tmp/lapsique-editorial-djs-final.png`
- Mobile implementation: `/tmp/lapsique-editorial-djs-mobile-final.png`
- Side-by-side comparison: `/tmp/lapsique-design-comparison-final.png`
- Source and implementation viewport: 714 × 892 browser capture from the same local session and theme state.
- Responsive validation viewport: 390 × 844; additional overflow checks at 320 and 375 px.
- State: commercial Sony Alpha home system compared with the restored public DJ archive.

### Full-view comparison

- Fonts and typography: the editorial archive preserves the same condensed display face, uppercase hierarchy, mono technical labels, optical weights and tight headline leading used by the commercial source.
- Spacing and layout rhythm: both surfaces use the same flat header, full-bleed media bands, one-pixel rules, square geometry and restrained section rhythm. The archive deliberately gives photography more area than supporting copy.
- Colors and tokens: near-black, warm off-white, neutral gray and Alpha orange map to the existing public tokens without introducing a separate scene palette.
- Image quality and assets: the implementation uses the 18 real DJ records and their generated 500 px thumbnails. No placeholders, CSS drawings or generated substitutes remain.
- Copy and content: labels describe the real Lapsique archive, Psique Sessions and event production offer. Trascendental records remain excluded.

### Focused regions and interactions

- Compared the shared header, kicker, condensed headline, rules and photographic grid at matching visual scale; no additional crop was needed because these fidelity surfaces are readable in the combined capture.
- Desktop `Escena` and `Servicios` dropdowns open, expose the configured routes and close with Escape.
- Mobile navigation opens as a labelled dialog, groups both sections in accordions and logs no accessibility warning.
- Click-to-load YouTube playback was verified: zero iframe requests before interaction and one privacy-enhanced iframe after play.
- Local media URLs were normalized from the configured `.test` host to the active `127.0.0.1` host; all six sampled DJ thumbnails load at 500 px natural width.
- Desktop and mobile pages have no horizontal overflow in the checked routes.

### Comparison history

1. P1 — DJ thumbnails rendered black in the local browser because media URLs retained the configured `.test` host. Added request-aware normalization for `/storage` and `/images` resources across DJs, events, videos and portfolio items. Post-fix evidence shows all sampled thumbnails loaded from `127.0.0.1` with non-zero natural width.
2. P2 — The mobile Sheet initially emitted missing-title and missing-description accessibility warnings. Added a semantic Sheet title and description while preserving the visible Lapsique logo. Post-fix console contains no warning or error.
3. P2 — The desktop scene menu was narrower than its editorial descriptions. Set a stable 21 rem content width and verified keyboard dismissal.

### Findings

- P0: none.
- P1: none.
- P2: none.
- P3: external YouTube thumbnails remain dependent on YouTube availability; first-party DJ, event and portfolio imagery now resolves locally.

final result: passed

## Browser annotation revision — food conversion flow

- Replaced the food hero secondary CTA with direct WhatsApp contact and retained the proof link below.
- Increased the proof gallery offset to 64/80 px and removed the duplicated business-outcome section.
- Elevated the restaurant lead form with a two-pixel conversion border, intent copy, and response promise.
- Converted FAQs into distinguishable expandable cards.
- Added photo-viewer likes, photo-view analytics, and a contextual content-booking CTA.
- Rebuilt booking and lead popups with a flatter editorial shell and intent-specific copy.
- Activated coordinated behavioral triggers for booking, lead capture, exit intent, engagement, scroll depth, and booking abandonment.
- Preserved trigger source in analytics and fixed modal scroll restoration on automated reopen.

final result: passed

## Browser annotation revision — spacing, gradient, WhatsApp

- Added 24 px of deliberate separation and a top rule between the home hero and equipment strip.
- Reduced the construction hero horizontal overlay from near-opaque values to 72% / 46% / 12%, with a softer 55% bottom fade.
- Standardized every `wa.me` CTA across public Lapsique pages to WhatsApp green with a consistent hover state.
- Excluded the floating informational prompt from the CTA color rule so it remains a neutral readable card.

final result: passed

## Browser annotation revision — hero media and spacing

- Replaced the single home hero image with a six-item media sequence: two videos and four portfolio photographs.
- Added automatic rotation every 6.5 seconds, direct media selectors, previous/next controls, media type, and position counter.
- Verified the second item loads as video, reaches ready state 4, and plays automatically.
- Added the `Newest camera` / `Cámara más nueva` badge to the Sony α7 V card.
- Normalized the global page ending: 24 px from final main content to creator profile and 40 px from creator profile to footer.
- Browser measurement: zero horizontal overflow at the tested desktop viewport.

final result: passed

## Browser annotation revision — 2026-07-11

- Removed the camera/drone text overlay from the home hero.
- Replaced equipment placeholders with locally cached official Sony and DJI product photography.
- Added a global keyboard-accessible image viewer with previous/next navigation.
- Restyled DJ set WhatsApp and booking buttons using the approved flat Alpha system.
- Replaced the generic creator copy on `/dj-set` with DJ production-specific copy.
- Stabilized the drone hero with a native poster-backed video surface and removed the delayed media swap.
- Shortened the drone hero and removed the duplicated long-form lead sections.
- Grouped GOBA and OKOM construction clips into independent project carousels.
- Rebuilt the construction booking controls at a measured 56 px height without text overflow.
- Browser checks: no horizontal overflow; viewer opens and closes; GOBA carousel advances from 1/3 to 2/3; both project carousels render; construction CTA fits its container.

final result: passed
