import type { TranslateFn } from '@/hooks/useTranslations';

export type PaymentTrustVariant = 'stripe' | 'dual';

export interface PaymentTrustBadge {
    id: string;
    label: string;
}

export interface PaymentTrustContent {
    badges: PaymentTrustBadge[];
    headline?: string;
    body?: string;
    footnote?: string;
    protectedPaymentChip: string;
}

export function getPaymentTrustContent(
    t: TranslateFn,
    variant: PaymentTrustVariant,
): PaymentTrustContent {
    const badges: PaymentTrustBadge[] = [
        { id: 'protected', label: t('booking.trust.badge_protected') },
        { id: 'cards', label: t('booking.trust.badge_cards') },
        { id: 'reserve', label: t('booking.trust.badge_reserve') },
    ];

    if (variant === 'stripe') {
        return {
            badges,
            headline: t('booking.trust.stripe_headline'),
            body: t('booking.trust.stripe_body'),
            footnote: t('booking.trust.stripe_footnote'),
            protectedPaymentChip: t('booking.trust.stripe_chip'),
        };
    }

    return {
        badges,
        headline: t('booking.trust.dual_headline'),
        body: t('booking.trust.dual_body'),
        footnote: t('booking.trust.dual_footnote'),
        protectedPaymentChip: t('booking.trust.dual_chip'),
    };
}
