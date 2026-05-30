import { CalendarClock, ShieldCheck, CreditCard, CalendarDays } from 'lucide-react';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { BookingCtaSection } from '@/components/lapsique/BookingCtaSection';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import { solidCardVariants } from '@/lib/variants';
import type { LucideIcon } from 'lucide-react';

const GUARANTEE_ICONS: LucideIcon[] = [ShieldCheck, CreditCard, CalendarClock];

export function GuaranteeUrgencySection({ onBook }: { onBook?: () => void }) {
    const { t } = useTranslations();
    const ref = useSectionEvent('guarantee_section_viewed', { section: 'guarantee_urgency' });

    const guarantees = [
        {
            title: t('funnel.guarantee.refund_title'),
            copy: t('funnel.guarantee.refund_copy'),
        },
        {
            title: t('funnel.guarantee.secure_title'),
            copy: t('funnel.guarantee.secure_copy'),
        },
        {
            title: t('funnel.guarantee.reschedule_title'),
            copy: t('funnel.guarantee.reschedule_copy'),
        },
    ];

    return (
        <GlassSection
            eyebrow={t('funnel.guarantee.section_eyebrow')}
            title={t('funnel.guarantee.section_title')}
            description={t('funnel.guarantee.section_description')}
        >
            <div ref={ref} className="space-y-6">
                <div className="grid gap-3 sm:grid-cols-3">
                    {guarantees.map((item, index) => {
                        const Icon = GUARANTEE_ICONS[index] ?? ShieldCheck;

                        return (
                            <article
                                key={item.title}
                                className={cn(solidCardVariants(), 'flex flex-col gap-3 p-4 md:p-5')}
                            >
                                <span className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-accent/35 bg-accent/10 text-accent">
                                    <Icon className="h-4 w-4" aria-hidden />
                                </span>
                                <div className="min-w-0">
                                    <h3 className="text-base font-semibold text-foreground">{item.title}</h3>
                                    <p className="mt-1 text-sm leading-relaxed text-muted-foreground">
                                        {item.copy}
                                    </p>
                                </div>
                            </article>
                        );
                    })}
                </div>

                <div className="flex flex-col items-center gap-4 rounded-2xl border border-primary/30 bg-primary/5 px-5 py-6 text-center md:px-8">
                    <span className="inline-flex items-center gap-2 rounded-full border border-primary/35 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-primary">
                        <CalendarClock className="h-3.5 w-3.5" aria-hidden />
                        {t('funnel.urgency.badge')}
                    </span>
                    <p className="max-w-xl text-sm leading-relaxed text-muted-foreground md:text-base">
                        {t('funnel.urgency.copy')}
                    </p>
                    <BookingCtaSection className="py-0">
                        <BookingCtaButton
                            type="button"
                            opensBookingModal={!onBook}
                            bookingSource="guarantee_urgency"
                            onClick={onBook}
                        >
                            {t('funnel.urgency.cta')}
                            <CalendarDays className="h-5 w-5" />
                        </BookingCtaButton>
                    </BookingCtaSection>
                </div>
            </div>
        </GlassSection>
    );
}
