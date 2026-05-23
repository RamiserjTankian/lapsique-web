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

const SHARED_BADGES: PaymentTrustBadge[] = [
    { id: 'protected', label: 'Compra protegida' },
    { id: 'cards', label: 'Acepta tarjetas' },
    { id: 'reserve', label: 'Paga ahora · reserva tu sesión' },
];

export function getPaymentTrustContent(variant: PaymentTrustVariant): PaymentTrustContent {
    if (variant === 'stripe') {
        return {
            badges: SHARED_BADGES,
            headline: 'Pago con tarjeta · 100% cubierto',
            body: 'Cobro seguro con Stripe y protección al comprador. Si no realizamos tu sesión según lo acordado, gestionamos la devolución del 100% de tu pago.',
            footnote: 'Paga con tarjeta, confirma tu fecha y aparta producción al instante.',
            protectedPaymentChip: 'Tarjeta con Stripe · reembolso 100% si no hay sesión',
        };
    }

    return {
        badges: SHARED_BADGES,
        headline: 'Checkout seguro al reservar',
        body: 'Puedes pagar con tarjeta (Stripe) o Mercado Pago. Al pagar con tarjeta, aplica reembolso del 100% si no realizamos tu sesión según lo acordado.',
        footnote: 'Elige fecha real en agenda y confirma tu reserva en minutos.',
        protectedPaymentChip: 'Compra protegida · pago seguro al confirmar',
    };
}
