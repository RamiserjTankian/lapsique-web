import { cn } from '@/lib/utils';

export type PremiumDialogLayout = 'form' | 'promo';

/**
 * Promo popups (newsletter, WhatsApp): centered card, never edge-to-edge on mobile.
 */
export const modalShellPromo = cn(
    'flex w-full max-w-[min(calc(100vw-1.5rem),42rem)] flex-col overflow-hidden rounded-none p-0',
    'max-h-[min(90dvh,44rem)]',
    'border-foreground/20 bg-background shadow-[0_28px_100px_oklch(0_0_0/0.52)]',
);

/**
 * Booking wizard: full viewport on mobile for multi-step forms; split panel on desktop.
 */
export const modalShellForm = cn(
    'flex max-h-[min(92dvh,900px)] w-full max-w-[min(96vw,67.5rem)] flex-col overflow-hidden rounded-none border-foreground/20 bg-background p-0 sm:rounded-xl',
    'max-lg:fixed max-lg:inset-0 max-lg:top-0 max-lg:left-0 max-lg:h-[100dvh] max-lg:max-h-[100dvh] max-lg:max-w-none max-lg:translate-x-0 max-lg:translate-y-0 max-lg:rounded-none',
);

/** Terms, legal copy, readable documents */
export const modalShellDocument = cn(
    'max-h-[min(90vh,47.5rem)] max-w-[min(calc(100vw-1.5rem),42.5rem)] overflow-y-auto border-primary/25 p-0',
    'shadow-[0_24px_80px_oklch(0_0_0/0.45)]',
);

/** Vertical reel player */
export const modalShellMediaReel = cn(
    'aspect-[9/16] max-h-[min(92dvh,920px)] w-[min(calc(100vw-1.5rem),26.25rem)] max-w-none gap-0 overflow-hidden',
    'border-white/10 bg-black p-0 shadow-2xl',
);

/** Portfolio / DJ gallery lightbox */
export const modalShellGallery = cn(
    'theme-scrollbar glass-panel-elevated max-h-[min(92vh,920px)] w-[min(calc(100vw-1.5rem),68.75rem)] max-w-none',
    'gap-0 overflow-y-auto border-border/80 p-0',
);

export const modalCloseOnDarkImage = cn(
    '[&_[data-slot=dialog-close]]:top-3 [&_[data-slot=dialog-close]]:right-3 [&_[data-slot=dialog-close]]:z-30',
    '[&_[data-slot=dialog-close]]:border-white/25 [&_[data-slot=dialog-close]]:bg-black/55 [&_[data-slot=dialog-close]]:text-white',
);

export const modalCloseDefault = cn(
    '[&_[data-slot=dialog-close]]:top-3 [&_[data-slot=dialog-close]]:right-3 [&_[data-slot=dialog-close]]:z-30',
);

export const modalOverlayMedia = '!bg-black/92 backdrop-blur-sm';
