import { cva } from 'class-variance-authority';

const tagBadgeMap: Record<string, string> = {
    'psique-originals': 'border-primary/30 bg-primary/10 text-primary',
};

export function tagBadgeClass(tag: string): string {
    return tagBadgeMap[tag] ?? 'border-border/60 bg-secondary text-muted-foreground';
}

export const glassCardVariants = cva('rounded-xl glass-panel', {
    variants: {
        elevated: {
            true: 'glass-panel-elevated',
            false: '',
        },
    },
    defaultVariants: {
        elevated: false,
    },
});

/**
 * Solid card surface for content-dense funnel sections. Avoids the glass blur
 * stack so the conversion blocks (hero, offer, booking) keep the spotlight.
 */
export const solidCardVariants = cva(
    'rounded-xl border bg-card transition-colors',
    {
        variants: {
            interactive: {
                true: 'hover:border-border',
                false: '',
            },
        },
        defaultVariants: {
            interactive: false,
        },
    },
);
