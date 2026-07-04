import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

interface SpecBadgeProps {
    children: React.ReactNode;
    highlight?: boolean;
    className?: string;
}

export function SpecBadge({ children, highlight, className }: SpecBadgeProps) {
    return (
        <Badge
            variant="secondary"
            className={cn(
                'inline-flex items-center gap-1.5 px-3 py-1.5 font-mono text-xs uppercase tracking-wider',
                highlight && 'border-primary/40 bg-primary/10 text-primary',
                className,
            )}
        >
            {children}
        </Badge>
    );
}
