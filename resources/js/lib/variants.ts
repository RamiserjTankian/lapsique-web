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
