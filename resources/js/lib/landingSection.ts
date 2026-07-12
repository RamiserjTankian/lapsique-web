import { cn } from '@/lib/utils';

/** Vertical rhythm between stacked landing blocks inside `main`. */
export const landingPageStackClass = 'flex flex-col';

/** Anchor offset + optional outer spacing (gap comes from the page stack). */
export const landingSectionShellClass = 'scroll-mt-20';

/**
 * Elevated “card” surface for each landing section — uses theme glass tokens
 * so light/dark shadows stay consistent.
 */
export const landingSectionSurfaceClass = cn(
    'editorial-rule bg-transparent',
);

/**
 * Solid (non-glass) surface for content-dense sections. Keeps the brand radius
 * but drops the glass blur/glow so the page reads less generic and the
 * conversion-focused glass blocks stand out.
 */
export const landingSectionSolidSurfaceClass = cn(
    'editorial-rule bg-transparent',
);

export const landingSectionInnerPaddingClass = 'px-1 py-14 sm:px-2 sm:py-18 md:px-4 md:py-24';
