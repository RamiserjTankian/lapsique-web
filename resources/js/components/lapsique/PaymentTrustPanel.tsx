import { usePage } from '@inertiajs/react';
import { CreditCard, LockKeyhole, ShieldCheck } from 'lucide-react';
import {
    getPaymentTrustContent,
    type PaymentTrustVariant,
} from '@/lib/paymentTrustCopy';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';

const BADGE_ICONS = {
    protected: ShieldCheck,
    cards: CreditCard,
    reserve: LockKeyhole,
} as const;

interface PaymentTrustPanelProps {
    variant: PaymentTrustVariant;
    layout?: 'compact' | 'card' | 'inline';
    className?: string;
    /** Hide refund / Stripe promises (e.g. test mode without real payment). */
    hidePromises?: boolean;
    /** Light text for dark hero backgrounds. */
    onDark?: boolean;
}

export function PaymentTrustPanel({
    variant,
    layout = 'inline',
    className,
    hidePromises = false,
    onDark = false,
}: PaymentTrustPanelProps) {
    const content = getPaymentTrustContent(variant);
    const showExtended = !hidePromises && layout !== 'compact';

    return (
        <div className={cn('space-y-3', className)}>
            <div className="flex flex-wrap gap-2">
                {content.badges.map((badge) => {
                    const Icon = BADGE_ICONS[badge.id as keyof typeof BADGE_ICONS] ?? ShieldCheck;

                    return (
                        <span
                            key={badge.id}
                            className={cn(
                                'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] sm:text-[11px] sm:tracking-[0.16em]',
                                onDark
                                    ? 'border-white/20 bg-white/10 text-white/90'
                                    : 'border-primary/25 bg-primary/10 text-primary',
                            )}
                        >
                            <Icon className="h-3 w-3 shrink-0 opacity-90" aria-hidden />
                            {badge.label}
                        </span>
                    );
                })}
            </div>

            {showExtended && layout === 'card' && (
                <div
                    className={cn(
                        'rounded-2xl border p-4',
                        onDark
                            ? 'border-white/15 bg-black/40 text-white backdrop-blur-md'
                            : 'border-primary/25 bg-primary/5',
                    )}
                >
                    {content.headline && (
                        <p
                            className={cn(
                                'text-sm font-semibold',
                                onDark ? 'text-white' : 'text-foreground',
                            )}
                        >
                            {content.headline}
                        </p>
                    )}
                    {content.body && (
                        <p
                            className={cn(
                                'mt-2 text-sm leading-relaxed',
                                onDark ? 'text-white/75' : 'text-muted-foreground',
                            )}
                        >
                            {content.body}
                        </p>
                    )}
                    {content.footnote && (
                        <p
                            className={cn(
                                'mt-2 text-xs font-medium',
                                onDark ? 'text-primary' : 'text-primary',
                            )}
                        >
                            {content.footnote}
                        </p>
                    )}
                </div>
            )}

            {showExtended && layout === 'inline' && content.body && (
                <p
                    className={cn(
                        'text-sm leading-relaxed',
                        onDark ? 'text-white/75' : 'text-muted-foreground',
                    )}
                >
                    {content.body}
                </p>
            )}
        </div>
    );
}

export function PaymentTrustTestModeNote({
    className,
    onDark = false,
}: {
    className?: string;
    onDark?: boolean;
}) {
    return (
        <p
            className={cn(
                'text-xs',
                onDark ? 'text-white/65' : 'text-muted-foreground',
                className,
            )}
        >
            Modo prueba — sin cobro real en este entorno.
        </p>
    );
}

/** Renders trust marketing or a test-mode note when checkout skips payment. */
export function PaymentTrustOrTestMode({
    variant,
    layout = 'inline',
    className,
    onDark = false,
}: Omit<PaymentTrustPanelProps, 'hidePromises'>) {
    const { booking } = usePage<PageProps>().props;

    if (booking.skipPayment) {
        return <PaymentTrustTestModeNote className={className} onDark={onDark} />;
    }

    return (
        <PaymentTrustPanel
            variant={variant}
            layout={layout}
            className={className}
            onDark={onDark}
        />
    );
}
