import type { ReactNode } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

export interface PremiumSplitDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    imageUrl: string;
    imageAlt: string;
    badge: string;
    title: string;
    description: string;
    caption?: string;
    imageOverlay?: ReactNode;
    children: ReactNode;
    footer?: ReactNode;
    contentClassName?: string;
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
}: PremiumSplitDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                className={cn(
                    'theme-scrollbar max-h-[min(92dvh,920px)] max-w-[min(96vw,1140px)] overflow-hidden border-primary/25 p-0',
                    'max-sm:top-0 max-sm:left-0 max-sm:h-[100dvh] max-sm:max-h-[100dvh] max-sm:max-w-full max-sm:translate-x-0 max-sm:translate-y-0 max-sm:rounded-none',
                )}
            >
                <div className="grid max-h-[min(92dvh,920px)] overflow-hidden lg:grid-cols-[minmax(260px,0.42fr)_minmax(0,1fr)]">
                    <aside className="relative min-h-[200px] overflow-hidden border-b border-border/60 lg:min-h-0 lg:border-b-0 lg:border-r">
                        <img
                            src={imageUrl}
                            alt={imageAlt}
                            className="premium-modal-image absolute inset-0 h-full w-full object-cover"
                        />
                        <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,oklch(0.08_0.02_280/0.15)_0%,oklch(0.08_0.02_280/0.88)_72%,oklch(0.08_0.02_280/0.96)_100%)] lg:bg-[linear-gradient(90deg,oklch(0.08_0.02_280/0.2)_0%,oklch(0.08_0.02_280/0.75)_55%,oklch(0.08_0.02_280/0.92)_100%)]" />
                        <div className="grain-overlay pointer-events-none absolute inset-0 opacity-40" />
                        <div className="relative flex h-full flex-col justify-end p-5 md:p-6">
                            <span className="inline-flex w-fit rounded-full border border-primary/30 bg-primary/15 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-primary backdrop-blur-sm">
                                {badge}
                            </span>
                            <DialogTitle className="mt-4 font-display text-2xl font-bold text-white drop-shadow-md md:text-3xl">
                                {title}
                            </DialogTitle>
                            <DialogDescription className="mt-2 max-w-sm text-sm leading-relaxed text-white/80">
                                {description}
                            </DialogDescription>
                            {caption && (
                                <p className="mt-3 text-xs font-medium uppercase tracking-[0.18em] text-white/55">
                                    {caption}
                                </p>
                            )}
                            {imageOverlay}
                        </div>
                    </aside>

                    <div
                        className={cn(
                            'theme-scrollbar flex min-h-0 flex-col overflow-y-auto',
                            contentClassName,
                        )}
                    >
                        <div className="flex-1">{children}</div>
                        {footer}
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
