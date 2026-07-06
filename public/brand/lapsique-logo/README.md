# Lapsique Logo Export Pack

Source logo: `resources/js/components/lapsique/LapsiqueMediaLogo.tsx`.

The exported SVGs embed the exact fonts used by the logo: Syne ExtraBold and IBM Plex Mono SemiBold. The editable Anime.js file loads the same font files from `fonts/`.

## SVG

- `svg/lapsique-logo-black-transparent.svg`
- `svg/lapsique-logo-black-on-white.svg`
- `svg/lapsique-logo-white-transparent.svg`
- `svg/lapsique-logo-gold-beige.svg`
- `svg/lapsique-logo-white-gold-transparent.svg`

## Animation

- Editable Anime.js source: `animation/lapsique-logo-reveal.html`
- Export script: `scripts/export-lapsique-logo-assets.mjs`

The video exports are vertical 4K (`2160x3840`) at 30 fps. The MP4 export uses a pure green background for chroma key removal and is encoded at a high constant bitrate so it keeps enough data for reels. MP4 is included because it is convenient for reels, but normal MP4 does not preserve a true alpha channel. For real transparency, use the MOV ProRes 4444 or WebM VP9 alpha export.
