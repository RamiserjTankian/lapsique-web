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
                showCloseButton
                className={cn(
                    'flex max-h-[min(92dvh,920px)] w-full max-w-[min(96vw,1140px)] flex-col overflow-hidden border-primary/25 p-0',
                    'max-lg:fixed max-lg:inset-0 max-lg:top-0 max-lg:left-0 max-lg:h-[100dvh] max-lg:max-h-[100dvh] max-lg:max-w-none max-lg:translate-x-0 max-lg:translate-y-0 max-lg:rounded-none',
                    '[&_[data-slot=dialog-close]]:z-30 max-lg:[&_[data-slot=dialog-close]]:top-3 max-lg:[&_[data-slot=dialog-close]]:right-3',
                    'max-lg:[&_[data-slot=dialog-close]]:border-white/25 max-lg:[&_[data-slot=dialog-close]]:bg-black/55 max-lg:[&_[data-slot=dialog-close]]:text-white',
                )}
            >
                <div
                    className={cn(
                        'flex min-h-0 flex-1 flex-col',
                        'lg:grid lg:max-h-[min(92dvh,920px)] lg:grid-cols-[minmax(260px,0.42fr)_minmax(0,1fr)]',
                    )}
                >
                    <aside
                        className={cn(
                            'relative shrink-0 overflow-hidden border-b border-border/60',
                            'h-[6rem] max-lg:h-[6rem]',
                            'lg:h-auto lg:min-h-0 lg:border-b-0 lg:border-r',
                        )}
                        aria-hidden="true"
                    >
                        <img
                            src={imageUrl}
                            alt=""
                            className="premium-modal-image absolute inset-0 h-full w-full object-cover"
                        />
                        <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,oklch(0.08_0.02_280/0.35)_0%,oklch(0.08_0.02_280/0.92)_100%)] lg:bg-[linear-gradient(90deg,oklch(0.08_0.02_280/0.2)_0%,oklch(0.08_0.02_280/0.75)_55%,oklch(0.08_0.02_280/0.92)_100%)]" />
                        <div className="grain-overlay pointer-events-none absolute inset-0 opacity-40" />
                        <div className="relative hidden h-full flex-col justify-end gap-0 p-6 lg:flex">
                            <span className="inline-flex w-fit rounded-full border border-primary/30 bg-primary/15 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-primary backdrop-blur-sm">
                                {badge}
                            </span>
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
                    </aside>

                    <div
                        className={cn(
                            'theme-scrollbar flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-contain',
                            'pb-[max(1rem,env(safe-area-inset-bottom))]',
                            contentClassName,
                        )}
                    >
                        <DialogTitle className="sr-only">{title}</DialogTitle>
                        <DialogDescription className="sr-only">{description}</DialogDescription>
                        <div className="min-h-0 flex-1">{children}</div>
                        {footer}
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
