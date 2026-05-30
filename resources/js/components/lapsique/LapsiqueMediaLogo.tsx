import { cn } from '@/lib/utils';

export function LapsiqueMediaLogo({ className }: { className?: string }) {
    return (
        <span
            className={cn(
                'inline-flex items-center gap-2 text-foreground',
                className,
            )}
            aria-label="lapsique media"
        >
            <span
                className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border/70 bg-background/80 text-primary shadow-sm"
                aria-hidden="true"
            >
                <span className="font-display text-lg font-extrabold leading-none tracking-normal">l</span>
            </span>
            <span className="grid leading-none">
                <span className="font-display text-lg font-extrabold tracking-normal text-foreground">
                    lapsique
                </span>
                <span className="font-mono text-[0.68rem] font-semibold uppercase tracking-[0.36em] text-primary">
                    media
                </span>
            </span>
        </span>
    );
}
