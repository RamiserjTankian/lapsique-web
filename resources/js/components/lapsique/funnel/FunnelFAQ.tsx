import { useMemo } from 'react';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { GlassSection } from '@/components/lapsique/GlassSection';
import {
    CONTENT_DRONE_SHOTS,
    CONTENT_REEL_DURATION_SECONDS,
    getContentSessionDuration,
} from '@/data/contentOffer';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';

export function FunnelFAQ({ variant = 'default' }: { variant?: 'home' | 'default' }) {
    const { site } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const sessionDuration = getContentSessionDuration(t);

    const items = useMemo(() => {
        const paymentAnswer =
            variant === 'home'
                ? t('funnel.faq.payment_home_a')
                : t('funnel.faq.payment_test_a');

        return [
            {
                value: 'reel-duracion',
                question: t('funnel.faq.reel_duration_q'),
                answer: t('funnel.faq.reel_duration_a', { seconds: CONTENT_REEL_DURATION_SECONDS }),
            },
            {
                value: 'dron',
                question: t('funnel.faq.drone_q'),
                answer: t('funnel.faq.drone_a', { drone_shots: CONTENT_DRONE_SHOTS }),
            },
            {
                value: 'duracion',
                question: t('funnel.faq.session_duration_q'),
                answer: t('funnel.faq.session_duration_a', { duration: sessionDuration }),
            },
            {
                value: 'que-llevar',
                question: t('funnel.faq.prepare_q'),
                answer: t('funnel.faq.prepare_a'),
            },
            {
                value: 'anuncios',
                question: t('funnel.faq.ads_q'),
                answer: t('funnel.faq.ads_a'),
            },
            {
                value: 'aftermovie',
                question: t('funnel.faq.aftermovie_q'),
                answer: t('funnel.faq.aftermovie_a'),
            },
            {
                value: 'reagendar',
                question: t('funnel.faq.reschedule_q'),
                answer: t('funnel.faq.reschedule_a'),
            },
            {
                value: 'pago',
                question: t('funnel.faq.payment_q'),
                answer: paymentAnswer,
            },
            ...(variant === 'home'
                ? [
                      {
                          value: 'reembolso-tarjeta',
                          question: t('funnel.faq.refund_q'),
                          answer: t('funnel.faq.refund_card_a'),
                      },
                  ]
                : []),
            {
                value: 'ubicacion',
                question: t('funnel.faq.location_q'),
                answer: site.studioLocation || t('funnel.faq.location_fallback'),
            },
        ];
    }, [sessionDuration, site.studioLocation, t, variant]);

    return (
        <GlassSection
            eyebrow={t('funnel.faq.section_eyebrow')}
            title={t('funnel.faq.section_title')}
            description={t('funnel.faq.section_description')}
        >
            <div className="rounded-[2rem] border border-border/70 bg-secondary px-5 py-3 md:px-8 md:py-4">
                <Accordion
                    type="single"
                    collapsible
                    onValueChange={(value) => {
                        if (value) {
                            trackBookingEvent('faq_opened', { item: value });
                        }
                    }}
                >
                    {items.map((item) => (
                        <AccordionItem key={item.value} value={item.value}>
                            <AccordionTrigger className="text-base font-semibold text-foreground hover:no-underline">
                                {item.question}
                            </AccordionTrigger>
                            <AccordionContent className="text-sm leading-7 text-muted-foreground">
                                {item.answer}
                            </AccordionContent>
                        </AccordionItem>
                    ))}
                </Accordion>
            </div>
        </GlassSection>
    );
}
