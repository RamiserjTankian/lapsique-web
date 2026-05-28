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

export const landingSectionInnerPaddingClass = 'p-5 sm:p-6 md:p-8';
