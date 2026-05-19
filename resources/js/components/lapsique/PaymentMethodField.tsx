import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { cn } from '@/lib/utils';
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
    const hasAny = stripeConfigured || mercadopagoConfigured;

    return (
        <div className="glass-panel space-y-3 rounded-xl p-4">
            <p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Método de pago
            </p>
            {!hasAny && (
                <p className="text-sm text-destructive">
                    No hay proveedores de pago configurados. Contacta al administrador.
                </p>
            )}
            <RadioGroup value={value} onValueChange={onChange} className="space-y-2">
                {mercadopagoConfigured && (
                    <div
                        className={cn(
                            'flex items-center gap-3 rounded-lg border border-border/60 px-3 py-2.5 transition',
                            value === 'mercadopago' && 'border-primary/40 bg-primary/5',
                        )}
                    >
                        <RadioGroupItem value="mercadopago" id="mp" />
                        <Label htmlFor="mp" className="cursor-pointer font-normal">
                            Mercado Pago
                            <span className="mt-0.5 block text-xs text-muted-foreground">
                                OXXO, SPEI y tarjetas en México
                            </span>
                        </Label>
                    </div>
                )}
                {stripeConfigured && (
                    <div
                        className={cn(
                            'flex items-center gap-3 rounded-lg border border-border/60 px-3 py-2.5 transition',
                            value === 'stripe' && 'border-primary/40 bg-primary/5',
                        )}
                    >
                        <RadioGroupItem value="stripe" id="stripe" />
                        <Label htmlFor="stripe" className="flex flex-1 cursor-pointer items-start gap-2 font-normal">
                            <CreditCard className="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <span>
                                Tarjeta internacional
                                <span className="mt-0.5 block text-xs text-muted-foreground">
                                    Pago seguro con Stripe Checkout
                                </span>
                            </span>
                        </Label>
                    </div>
                )}
            </RadioGroup>
        </div>
    );
}
