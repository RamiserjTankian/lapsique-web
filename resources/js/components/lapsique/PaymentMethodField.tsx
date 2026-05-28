import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import { bookingPaymentSelectedClasses } from '@/lib/bookingSelectionStyles';
import { CreditCard } from 'lucide-react';

interface PaymentMethodFieldProps {
    value: string;
    onChange: (value: string) => void;
    stripeConfigured?: boolean;
    mercadopagoConfigured?: boolean;
}

export function PaymentMethodField({
    value,
    onChange,
    stripeConfigured = true,
    mercadopagoConfigured = true,
}: PaymentMethodFieldProps) {
    const { t } = useTranslations();
    const hasAny = stripeConfigured || mercadopagoConfigured;

    return (
        <div className="glass-panel space-y-3 rounded-xl p-4">
            <p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                {t('booking.payment.method_label')}
            </p>
            {!hasAny && (
                <p className="text-sm text-destructive">
                    {t('booking.payment.no_providers')}
                </p>
            )}
            <RadioGroup value={value} onValueChange={onChange} className="space-y-2">
                {mercadopagoConfigured && (
                    <div
                        className={cn(
                            'flex items-center gap-3 rounded-lg border border-border/60 px-3 py-2.5 transition',
                            value === 'mercadopago' && bookingPaymentSelectedClasses,
                        )}
                    >
                        <RadioGroupItem value="mercadopago" id="mp" />
                        <Label htmlFor="mp" className="cursor-pointer font-normal">
                            Mercado Pago
                            <span className="mt-0.5 block text-xs text-muted-foreground">
                                {t('booking.payment.mercadopago_hint')}
                            </span>
                        </Label>
                    </div>
                )}
                {stripeConfigured && (
                    <div
                        className={cn(
                            'flex items-center gap-3 rounded-lg border border-border/60 px-3 py-2.5 transition',
                            value === 'stripe' && bookingPaymentSelectedClasses,
                        )}
                    >
                        <RadioGroupItem value="stripe" id="stripe" />
                        <Label htmlFor="stripe" className="flex flex-1 cursor-pointer items-start gap-2 font-normal">
                            <CreditCard className="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <span>
                                {t('booking.payment.stripe_hint')}
                                <span className="mt-0.5 block text-xs text-muted-foreground">
                                    {t('booking.payment.stripe_subhint')}
                                </span>
                            </span>
                        </Label>
                    </div>
                )}
            </RadioGroup>
        </div>
    );
}
