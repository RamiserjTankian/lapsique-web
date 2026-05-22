import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';

export function FunnelFAQ() {
    const { site } = usePage<PageProps>().props;

    const items = [
        {
            value: 'duracion',
            question: '¿Cuánto dura la sesión?',
            answer: 'Normalmente entre 2 y 3 horas, dependiendo del ritmo de grabación, cambios de vestuario, locación y complejidad del concepto.',
        },
        {
            value: 'que-llevar',
            question: '¿Qué necesito llevar o preparar?',
            answer: 'Idealmente referencias visuales, producto, props de marca y claridad sobre la oferta, promoción o mensaje que quieres comunicar.',
        },
        {
            value: 'anuncios',
            question: '¿Sirve para anuncios de negocio?',
            answer: 'Sí. La sesión se plantea para entregar reels y fotos que puedan alimentar orgánico, campañas y pruebas de mensajes con una oferta clara.',
        },
        {
            value: 'aftermovie',
            question: '¿También producen aftermovies?',
            answer: 'Sí. Aftermovies de eventos, venues o lanzamientos se cotizan por alcance cuando el proyecto necesita cobertura más amplia que una sesión de reels.',
        },
        {
            value: 'reagendar',
            question: '¿Puedo reagendar?',
            answer: 'Sí, sujeto a disponibilidad y con aviso previo. La meta es proteger agenda y producción sin fricción innecesaria.',
        },
        {
            value: 'pago',
            question: '¿Cómo funciona el pago?',
            answer: 'En producción real se confirma mediante checkout seguro con Stripe o Mercado Pago. En entornos de prueba no se hace cobro real.',
        },
        {
            value: 'ubicacion',
            question: '¿Dónde se realiza la sesión?',
            answer: site.studioLocation || 'Se coordina según tipo de proyecto, locación, objetivo visual y disponibilidad.',
        },
    ];

    return (
        <GlassSection
            eyebrow="FAQ"
            title="Objeciones comunes antes de agendar"
            description="Resolvemos lo esencial para que puedas decidir rápido y con contexto."
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
