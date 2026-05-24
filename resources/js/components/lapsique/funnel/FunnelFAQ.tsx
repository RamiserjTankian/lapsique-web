import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { CONTENT_REEL_DURATION_SECONDS } from '@/data/contentOffer';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';

export function FunnelFAQ({ variant = 'default' }: { variant?: 'home' | 'default' }) {
    const { site } = usePage<PageProps>().props;

    const paymentAnswer =
        variant === 'home'
            ? 'Compra protegida al confirmar: puedes pagar con tarjeta (Stripe) o Mercado Pago. Al pagar con tarjeta, aplica reembolso del 100% si no realizamos tu sesión según lo acordado. En entornos de prueba no hay cobro real.'
            : 'En producción real se confirma mediante checkout seguro con Stripe o Mercado Pago. En entornos de prueba no se hace cobro real.';

    const items = [
        {
            value: 'reel-duracion',
            question: '¿Cuánto dura el reel entregado?',
            answer: `El reel editado dura ${CONTENT_REEL_DURATION_SECONDS} segundos — formato ideal para Meta Ads, hooks y piezas de retargeting.`,
        },
        {
            value: 'dron',
            question: '¿El reel incluye tomas de dron?',
            answer: 'Sí. Incluye 3 tomas aéreas con dron DJI integradas al reel, cuando locación, permisos y condiciones de vuelo lo permiten.',
        },
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
            answer: paymentAnswer,
        },
        ...(variant === 'home'
            ? [
                  {
                      value: 'reembolso-tarjeta',
                      question: '¿Qué pasa si pago con tarjeta y no se realiza la sesión?',
                      answer: 'Si pagas con tarjeta vía Stripe y no realizamos tu sesión según lo acordado, gestionamos la devolución del 100% de tu pago. El cobro cuenta con protección al comprador de Stripe; Lapsique coordina contigo el seguimiento.',
                  },
              ]
            : []),
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
