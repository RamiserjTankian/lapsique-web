import { cn } from '@/lib/utils';

/** Vertical rhythm between stacked landing blocks inside `main`. */
export const landingPageStackClass = 'flex flex-col gap-6 md:gap-8';

/** Anchor offset + optional outer spacing (gap comes from the page stack). */
export const landingSectionShellClass = 'scroll-mt-20';

/**
 * Elevated “card” surface for each landing section — uses theme glass tokens
 * so light/dark shadows stay consistent.
 */
export const landingSectionSurfaceClass = cn(
    'glass-panel rounded-2xl',
    'shadow-[0_20px_50px_var(--glass-panel-shadow)]',
);

/**
 * Solid (non-glass) surface for content-dense sections. Keeps the brand radius
 * but drops the glass blur/glow so the page reads less generic and the
 * conversion-focused glass blocks stand out.
 */
export const landingSectionSolidSurfaceClass = cn(
    'rounded-2xl border border-border/60 bg-card',
    'shadow-[0_8px_24px_oklch(0.2_0.02_260/0.06)]',
);

export const landingSectionInnerPaddingClass = 'p-5 sm:p-6 md:p-8';
