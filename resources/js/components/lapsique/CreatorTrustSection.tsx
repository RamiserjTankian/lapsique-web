import { ArrowUpRight } from 'lucide-react';
import { usePage } from '@inertiajs/react';
import { CREATOR_PROFILE } from '@/data/creatorProfile';
import { useTranslations } from '@/hooks/useTranslations';

const CREATOR_COPY = {
    es: {
        title: 'Dirección y cámara: Ramiro Díaz.',
        body: 'Fundador y realizador de Lapsique Media en Riviera Maya. Produce reels comerciales, fotografía y coberturas de evento.',
        cta: 'Conocer su trabajo',
    },
    en: {
        title: 'Direction and camera: Ramiro Díaz.',
        body: 'Founder and filmmaker at Lapsique Media in Riviera Maya. He produces commercial reels, photography, and event coverage.',
        cta: 'See his work',
    },
} as const;

const DJ_SET_COPY = {
    es: {
        title: 'DJ sets dirigidos y editados por Ramiro Díaz.',
        body: 'Dos cámaras, audio directo del mixer y edición al beat. El resultado incluye el set completo y clips verticales para promoción.',
        cta: 'Ver sets producidos',
    },
    en: {
        title: 'DJ sets directed and edited by Ramiro Díaz.',
        body: 'Two cameras, direct mixer audio, and beat-matched editing. Delivery includes the full set and vertical promotional clips.',
        cta: 'See produced sets',
    },
} as const;

export function CreatorTrustSection() {
    const { locale } = useTranslations();
    const { url } = usePage();
    const language = locale === 'en' ? 'en' : 'es';
    const path = url.split('?')[0];
    const copy = path === '/dj-set' || path === '/djset' ? DJ_SET_COPY[language] : CREATOR_COPY[language];

    return (
        <section data-creator-trust-section="true" className="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 md:py-18">
            <div className="grid gap-8 border-y border-foreground/15 py-8 lg:grid-cols-[1fr_auto] lg:items-end lg:gap-12">
                <div className="max-w-3xl">
                    <h2 className="font-display text-3xl font-bold leading-tight text-foreground md:text-4xl">
                        {copy.title}
                    </h2>
                    <p className="mt-4 max-w-2xl text-sm leading-relaxed text-muted-foreground md:text-base">
                        {copy.body}
                    </p>
                </div>

                <div className="min-w-[14rem]">
                    <p className="font-display text-2xl font-bold text-foreground">
                        {CREATOR_PROFILE.name}
                    </p>
                    <p className="mt-1 text-sm font-semibold text-primary">
                        {CREATOR_PROFILE.instagramHandle}
                    </p>
                    <a
                        href={CREATOR_PROFILE.instagramUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 bg-foreground px-5 text-sm font-bold text-background transition hover:bg-primary hover:text-white"
                    >
                        {copy.cta}
                        <ArrowUpRight className="size-4" aria-hidden />
                    </a>
                </div>
            </div>
        </section>
    );
}
