import { ArrowUpRight, AtSign, Camera, Sparkles, Video } from 'lucide-react';
import { CREATOR_PROFILE } from '@/data/creatorProfile';
import { useTranslations } from '@/hooks/useTranslations';

const CREATOR_COPY = {
    es: {
        eyebrow: 'Quién está detrás',
        title: 'Ramiro convierte tu negocio en contenido que se siente real.',
        body: 'Video maker detrás de Lapsique Media. Dirige la toma, la luz, el ritmo y la edición para que cada pieza tenga uso comercial en anuncios, redes y páginas de venta.',
        cta: 'Ver Instagram',
        chips: ['Video maker', 'Foto de producto', 'Reels para ads'],
    },
    en: {
        eyebrow: 'Who is behind it',
        title: 'Ramiro turns your business into content that feels real.',
        body: 'Video maker behind Lapsique Media. He directs the shot, light, rhythm, and edit so every piece has commercial use in ads, social, and sales pages.',
        cta: 'View Instagram',
        chips: ['Video maker', 'Product photo', 'Ad reels'],
    },
} as const;

const CHIP_ICONS = [Video, Camera, Sparkles] as const;

export function CreatorTrustSection() {
    const { locale } = useTranslations();
    const copy = CREATOR_COPY[locale === 'en' ? 'en' : 'es'];

    return (
        <section data-creator-trust-section="true" className="mx-auto mt-16 w-full max-w-6xl px-4 sm:px-6">
            <div className="relative overflow-hidden rounded-2xl border border-border/70 bg-[linear-gradient(135deg,rgb(255_255_255/0.88),rgb(244_239_232/0.76))] p-5 shadow-[0_24px_80px_rgb(42_23_12/0.10)] backdrop-blur-md dark:bg-[linear-gradient(135deg,rgb(15_12_10/0.9),rgb(24_18_14/0.86))] dark:shadow-black/30 sm:p-6 md:p-8">
                <div className="absolute inset-y-0 right-0 hidden w-1/3 bg-[radial-gradient(circle_at_center,rgb(206_58_42/0.22),transparent_62%)] lg:block" />
                <div className="relative grid gap-8 lg:grid-cols-[1fr_0.46fr] lg:items-center">
                    <div className="max-w-3xl">
                        <p className="text-xs font-semibold uppercase tracking-[0.22em] text-primary">
                            {copy.eyebrow}
                        </p>
                        <h2 className="mt-3 font-display text-3xl font-bold leading-tight text-foreground md:text-4xl">
                            {copy.title}
                        </h2>
                        <p className="mt-4 max-w-2xl text-sm leading-relaxed text-muted-foreground md:text-base">
                            {copy.body}
                        </p>
                    </div>

                    <div className="rounded-xl border border-border/70 bg-background/80 p-4 shadow-sm">
                        <div className="flex items-center justify-between gap-4">
                            <div>
                                <p className="font-display text-2xl font-bold text-foreground">
                                    {CREATOR_PROFILE.name}
                                </p>
                                <p className="mt-1 text-sm font-semibold text-primary">
                                    {CREATOR_PROFILE.instagramHandle}
                                </p>
                            </div>
                            <span className="flex size-12 shrink-0 items-center justify-center rounded-xl border border-primary/25 bg-primary/10 text-primary">
                                <AtSign className="size-5" aria-hidden />
                            </span>
                        </div>

                        <div className="mt-5 grid gap-2">
                            {copy.chips.map((chip, index) => {
                                const Icon = CHIP_ICONS[index];

                                return (
                                    <div
                                        key={chip}
                                        className="flex min-h-10 items-center gap-2 rounded-lg border border-border/70 bg-secondary/50 px-3 text-sm font-semibold text-foreground"
                                    >
                                        <Icon className="size-4 shrink-0 text-primary" aria-hidden />
                                        {chip}
                                    </div>
                                );
                            })}
                        </div>

                        <a
                            href={CREATOR_PROFILE.instagramUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-bold text-primary-foreground shadow-[0_16px_44px_oklch(0.78_0.14_75/0.28)] transition hover:bg-primary/90"
                        >
                            {copy.cta}
                            <ArrowUpRight className="size-4" aria-hidden />
                        </a>
                    </div>
                </div>
            </div>
        </section>
    );
}
