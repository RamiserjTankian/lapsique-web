import { useEffect, useRef, type ReactNode } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    modalCloseDefault,
    modalCloseOnDarkImage,
    modalShellForm,
    modalShellPromo,
    type PremiumDialogLayout,
} from '@/lib/modalLayout';
import { cn } from '@/lib/utils';

export interface PremiumSplitDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    imageUrl: string;
    imageAlt: string;
    badge?: string;
    title: string;
    description: string;
    caption?: string;
    imageOverlay?: ReactNode;
    children: ReactNode;
    footer?: ReactNode;
    contentClassName?: string;
    /** `form` = full-screen mobile booking wizard. `promo` = compact centered popup. */
    layout?: PremiumDialogLayout;
}

export function PremiumSplitDialog({
    open,
    onOpenChange,
    imageUrl,
    imageAlt,
    badge,
    title,
    description,
    caption,
    imageOverlay,
    children,
    footer,
    contentClassName,
    layout = 'form',
}: PremiumSplitDialogProps) {
    const isPromo = layout === 'promo';
    const scrollContainerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!open) return;

        const frame = window.requestAnimationFrame(() => {
            if (scrollContainerRef.current) scrollContainerRef.current.scrollTop = 0;
        });

        return () => window.cancelAnimationFrame(frame);
    }, [open]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                showCloseButton
                className={cn(
                    isPromo ? modalShellPromo : modalShellForm,
                    isPromo ? modalCloseDefault : modalCloseOnDarkImage,
                )}
            >
                <div
                    className={cn(
                        'flex min-h-0 flex-1 flex-col',
                        isPromo
                            ? 'lg:grid lg:max-h-[min(90dvh,44rem)] lg:grid-cols-[minmax(15rem,0.42fr)_minmax(0,0.58fr)]'
                            : 'lg:grid lg:max-h-[min(92dvh,900px)] lg:grid-cols-[minmax(15rem,0.32fr)_minmax(0,0.68fr)]',
                    )}
                >
                    <aside
                        className={cn(
                            'relative shrink-0 overflow-hidden border-border/60',
                            isPromo
                                ? 'h-[8rem] border-b sm:h-[9rem] lg:h-auto lg:min-h-0 lg:border-b-0 lg:border-r'
                                : 'h-[7rem] border-b max-lg:h-[7rem] lg:h-auto lg:min-h-0 lg:border-b-0 lg:border-r',
                        )}
                        aria-hidden={isPromo ? undefined : true}
                    >
                        <img
                            src={imageUrl}
                            alt={isPromo ? imageAlt : ''}
                            className="premium-modal-image absolute inset-0 h-full w-full object-cover"
                        />
                        <div
                            className={cn(
                                'pointer-events-none absolute inset-0',
                                isPromo
                                    ? 'bg-[linear-gradient(180deg,oklch(0.08_0.02_280/0.15)_0%,oklch(0.08_0.02_280/0.72)_100%)] lg:bg-[linear-gradient(90deg,oklch(0.08_0.02_280/0.2)_0%,oklch(0.08_0.02_280/0.82)_100%)]'
                                    : 'bg-[linear-gradient(180deg,oklch(0.08_0.02_280/0.35)_0%,oklch(0.08_0.02_280/0.92)_100%)] lg:bg-[linear-gradient(90deg,oklch(0.08_0.02_280/0.2)_0%,oklch(0.08_0.02_280/0.75)_55%,oklch(0.08_0.02_280/0.92)_100%)]',
                            )}
                        />
                        <div className="grain-overlay pointer-events-none absolute inset-0 opacity-40" />

                        {isPromo ? (
                            <div className="relative flex h-full items-end p-3 sm:p-4 lg:flex-col lg:justify-end lg:p-5">
                                {badge ? (
                                    <span className="inline-flex w-fit rounded-full border border-primary/30 bg-primary/15 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-[0.22em] text-primary backdrop-blur-sm">
                                        {badge}
                                    </span>
                                ) : null}
                                <p className="mt-3 hidden font-display text-2xl font-bold leading-tight text-white drop-shadow-md lg:block">
                                    {title}
                                </p>
                                <p className="mt-2 hidden max-w-xs text-sm leading-relaxed text-white/80 lg:block">
                                    {description}
                                </p>
                                {caption ? (
                                    <p className="mt-3 hidden text-xs font-medium uppercase tracking-[0.18em] text-white/55 lg:block">
                                        {caption}
                                    </p>
                                ) : null}
                            </div>
                        ) : (
                            <div className="relative hidden h-full flex-col justify-end gap-0 p-6 lg:flex">
                                {badge ? (
                                    <span className="inline-flex w-fit rounded-full border border-primary/30 bg-primary/15 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-primary backdrop-blur-sm">
                                        {badge}
                                    </span>
                                ) : null}
                                <p className="mt-4 font-display text-3xl font-bold leading-tight text-white drop-shadow-md">
                                    {title}
                                </p>
                                <p className="mt-2 hidden max-w-sm text-sm leading-relaxed text-white/80 lg:block">
                                    {description}
                                </p>
                                {caption && (
                                    <p className="mt-3 hidden text-xs font-medium uppercase tracking-[0.18em] text-white/55 lg:block">
                                        {caption}
                                    </p>
                                )}
                                {imageOverlay ? (
                                    <div className="mt-5 hidden lg:block">{imageOverlay}</div>
                                ) : null}
                            </div>
                        )}
                    </aside>

                    <div
                        ref={scrollContainerRef}
                        className={cn(
                            'theme-scrollbar flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain',
                            'pb-[max(0.75rem,env(safe-area-inset-bottom))]',
                            contentClassName,
                        )}
                    >
                        <DialogTitle className="sr-only">{title}</DialogTitle>
                        <DialogDescription className="sr-only">{description}</DialogDescription>

                        {isPromo && (
                            <div className="border-b border-border/60 px-4 py-3 sm:px-5 lg:hidden">
                                <p className="font-display text-lg font-bold leading-tight">{title}</p>
                                <p className="mt-1 text-sm leading-relaxed text-muted-foreground">
                                    {description}
                                </p>
                                {caption ? (
                                    <p className="mt-2 text-[11px] font-medium uppercase tracking-[0.16em] text-muted-foreground/80">
                                        {caption}
                                    </p>
                                ) : null}
                            </div>
                        )}

                        <div className="min-h-0 flex-1">{children}</div>
                        {footer}
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
