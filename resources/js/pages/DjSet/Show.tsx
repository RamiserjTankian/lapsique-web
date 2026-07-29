import { useEffect, useMemo, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, CalendarDays } from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { EditorialVideoPlayer } from '@/components/lapsique/EditorialVideoPlayer';
import { PortfolioLightbox } from '@/components/lapsique/PortfolioLightbox';
import { SeoHead } from '@/components/lapsique/SeoHead';
import {
    ServiceFunnelDeliverables,
    ServiceFunnelFaq,
    ServiceFunnelFinalCta,
    ServiceFunnelHeading,
    ServiceFunnelHero,
    ServiceFunnelProcess,
    ServiceFunnelSection,
    ServicePortfolioShowcase,
    ServiceProofBand,
    ServiceWhatsAppButton,
    serviceFunnelPrimaryActionClass,
} from '@/components/lapsique/ServiceFunnel';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { openBookingModal } from '@/lib/openBookingModal';
import { getDjSetProduct } from '@/lib/bookingProducts';
import { route } from '@/lib/route';
import { cn } from '@/lib/utils';
import type {
    BookingSlot,
    DjItem,
    PageProps,
    PortfolioItemData,
    ServicePortfolioBundle,
    ServicePortfolioMedia,
    VideoItem,
} from '@/types';

interface DjSetShowProps {
    price: number;
    slots: BookingSlot[];
    originals: VideoItem[];
    portfolioItems: PortfolioItemData[];
    djs: DjItem[];
    servicePortfolio: ServicePortfolioBundle;
    errors?: Record<string, string>;
}

const DJ_SET_COPY = {
    es: {
        proofTitle: 'Sets, drops y fotografía agrupados por sesión real.',
        proofDescription: 'La evidencia se presenta por fecha, venue o artista para mostrar que una sola grabación puede seguir trabajando después de la noche.',
        sessionEyebrow: 'Sets documentados',
        sessionTitle: 'Mira cómo se sostiene la energía de una sesión real.',
        sessionDescription: 'Revisa encuadre, movimiento y sonido con un reproductor propio, sin controles nativos ni videos de otra marca.',
        dropsEyebrow: 'Drops editados',
        dropsTitle: 'Drops listos para mantener el set en circulación.',
        dropsDescription: 'Piezas breves de Danzahaus, MTRX y otras sesiones reales para reels, anuncios y recap de la fecha.',
        galleryEyebrow: 'Fotografía nightlife',
        galleryTitle: 'Cabina, público y venue cuentan la misma noche.',
        galleryDescription: 'Una selección contenida de fotografías para prensa, flyers, redes y archivo del artista.',
        artistsEyebrow: 'Artistas documentados',
        artistsTitle: 'DJs que ya pasaron frente a las cámaras de Lapsique.',
        artistsDescription: 'Explora perfiles y sesiones relacionadas dentro del archivo editorial de Lapsique.',
        productionEyebrow: 'La entrega',
        productionTitle: 'Un set completo. Varias piezas para moverlo.',
        productionDescription: 'La producción se diseña alrededor del estreno, la promoción y las siguientes fechas del artista.',
        deliverables: [
            { title: 'Set continuo', description: 'Una pieza horizontal editada para YouTube, promotores, venues y archivo del artista.' },
            { title: 'Drops para redes', description: 'Cortes breves listos para reels, anuncios y publicaciones posteriores al evento.' },
            { title: 'Audio sincronizado', description: 'Señal de mixer y ambiente alineados para conservar presencia, público y dinámica.' },
            { title: 'Fotografía editorial', description: 'Cabina, artista, público y espacio en una selección lista para comunicar la fecha.' },
        ],
        processTitle: 'Del venue al estreno, sin pasos innecesarios.',
        processDescription: 'Confirmamos tres decisiones antes de grabar; el resto es producción.',
        process: [
            { title: 'Alineamos la sesión', description: 'Confirmamos venue, horario, accesos, duración y referencias visuales.' },
            { title: 'Grabamos el set', description: 'Documentamos la presentación y capturamos el audio durante la misma sesión.' },
            { title: 'Editamos para publicar', description: 'Entregamos la pieza principal, los cortes y las fotografías acordadas.' },
        ],
        bookingEyebrow: 'Elige una fecha',
        bookingTitle: 'Reserva la producción de tu DJ set.',
        bookingDescription: 'Comparte venue, horario y duración. Confirmamos disponibilidad y construimos el plan de grabación.',
        faqEyebrow: 'Antes de reservar',
        faqTitle: 'Respuestas directas para producir el set.',
        faqDescription: 'El alcance, los accesos y los formatos finales quedan definidos antes de la fecha.',
        finalTitle: 'Tu set merece seguir circulando después de la noche.',
        finalDescription: 'Comparte la fecha y el venue. Te respondemos con disponibilidad y un alcance claro.',
        heroMediaCaption: 'Sesión real documentada por Lapsique Media.',
    },
    en: {
        proofTitle: 'Sets, cutdowns, and photography grouped by real session.',
        proofDescription: 'Evidence is presented by date, venue, or artist to show how one recording keeps working after the night ends.',
        sessionEyebrow: 'Documented sets',
        sessionTitle: 'See how the energy holds across a real session.',
        sessionDescription: 'Review framing, movement, and sound with a custom player—no native controls and no media from another brand.',
        dropsEyebrow: 'Edited drops',
        dropsTitle: 'Cutdowns built to keep the set in circulation.',
        dropsDescription: 'Short pieces from Danzahaus, MTRX, and other real sessions for reels, ads, and post-event recaps.',
        galleryEyebrow: 'Nightlife photography',
        galleryTitle: 'Booth, crowd, and venue tell the same night.',
        galleryDescription: 'A contained photography selection for press, flyers, social media, and the artist archive.',
        artistsEyebrow: 'Documented artists',
        artistsTitle: 'DJs who have already performed in front of Lapsique cameras.',
        artistsDescription: 'Explore related profiles and sessions inside the Lapsique editorial archive.',
        productionEyebrow: 'The delivery',
        productionTitle: 'One complete set. Several assets to move it.',
        productionDescription: 'Production is designed around release, promotion, and the artist’s next dates.',
        deliverables: [
            { title: 'Continuous set', description: 'A horizontal edit for YouTube, promoters, venues, and the artist archive.' },
            { title: 'Social cutdowns', description: 'Short pieces ready for reels, ads, and posts after the event.' },
            { title: 'Synchronized audio', description: 'Mixer signal and room ambience aligned to preserve presence, crowd, and dynamics.' },
            { title: 'Editorial photography', description: 'Booth, artist, crowd, and venue in a selection ready to communicate the date.' },
        ],
        processTitle: 'From venue to release, without unnecessary steps.',
        processDescription: 'We confirm three decisions before recording; the rest is production.',
        process: [
            { title: 'Align the session', description: 'We confirm venue, schedule, access, duration, and visual references.' },
            { title: 'Record the set', description: 'We document the performance and capture audio during the same session.' },
            { title: 'Edit for release', description: 'We deliver the main piece, cutdowns, and agreed photographs.' },
        ],
        bookingEyebrow: 'Choose a date',
        bookingTitle: 'Book your DJ set production.',
        bookingDescription: 'Share the venue, schedule, and duration. We confirm availability and build the recording plan.',
        faqEyebrow: 'Before booking',
        faqTitle: 'Direct answers for producing the set.',
        faqDescription: 'Scope, access, and final formats are defined before the date.',
        finalTitle: 'Your set should keep circulating after the night.',
        finalDescription: 'Share the date and venue. We will reply with availability and a clear scope.',
        heroMediaCaption: 'A real session documented by Lapsique Media.',
    },
} as const;

export default function DjSetShow({
    price,
    slots,
    originals,
    portfolioItems,
    djs,
    servicePortfolio,
    errors,
}: DjSetShowProps) {
    const { site, locale, ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const en = locale === 'en';
    const copy = DJ_SET_COPY[en ? 'en' : 'es'];
    const product = useMemo(() => getDjSetProduct(t), [t]);
    const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);
    const whatsappHref = useMemo(
        () => buildWhatsAppHref(site.whatsapp, t('funnel.whatsapp.prefill_djset')),
        [site.whatsapp, t],
    );
    const analyticsPayload = useMemo(
        () => ({
            content_name: t('pages.djset.hero_title'),
            content_category: 'dj_set_booking',
            service_type: 'dj_set',
            currency: 'MXN',
            value: price,
        }),
        [price, t],
    );
    const partitionedPortfolio = useMemo(() => {
        const bundleMedia = uniqueMedia(servicePortfolio.projects.flatMap((project) => project.media));
        const availableVideos = bundleMedia.filter(
            (media) => media.kind === 'video' && media.id !== servicePortfolio.hero.id,
        );
        const bundledDrops = availableVideos.filter(isDropMedia).slice(0, 6);
        const dropIds = new Set(bundledDrops.map((media) => media.id));
        const sessionMedia = availableVideos
            .filter((media) => !dropIds.has(media.id))
            .slice(0, 3);

        return {
            sessions: buildPortfolio(servicePortfolio, sessionMedia),
            drops: buildPortfolio(servicePortfolio, bundledDrops.slice(0, 10)),
        };
    }, [servicePortfolio]);
    const popupProofMedia = useMemo(
        () => pickDistinctPopupMedia(servicePortfolio),
        [servicePortfolio],
    );
    const galleryImages = useMemo(
        () => uniqueMedia(servicePortfolio.projects.flatMap((project) => project.media))
            .filter((media) => media.kind === 'image' && media.id !== servicePortfolio.hero.id)
            .slice(0, 10),
        [servicePortfolio],
    );
    const galleryItems = useMemo(
        () => galleryImages.map(toPortfolioItem),
        [galleryImages],
    );
    const relatedArtists = useMemo(
        () => djs
            .filter(isLapsiqueArtist)
            .filter((dj) => Boolean(dj.avatar_url || dj.cover_url))
            .slice(0, 6),
        [djs],
    );
    const faq = [
        { question: t('pages.djset.faq_video_duration_q'), answer: t('pages.djset.faq_video_duration_a') },
        { question: t('pages.djset.faq_drone_q'), answer: t('pages.djset.faq_drone_a') },
        { question: t('pages.djset.faq_calendar_q'), answer: t('pages.djset.faq_calendar_a') },
        { question: t('pages.djset.faq_location_q'), answer: t('pages.djset.faq_location_a') },
    ];

    useEffect(() => {
        trackBookingEvent('djset_page_viewed', { ...analyticsPayload, section: 'dj_set' });
    }, [analyticsPayload]);

    const openBooking = (source = 'djset') => {
        openBookingModal({
            source,
            analyticsEvent: 'djset_booking_cta_clicked',
            analyticsPayload: { ...analyticsPayload, source },
        });
    };
    const trackWhatsApp = (source: string) => {
        trackBookingEvent('djset_whatsapp_cta_clicked', {
            ...analyticsPayload,
            source,
            target: 'whatsapp',
        });
    };
    const primaryAction = (source: string, compact = false) => (
        <BookingCtaButton
            type="button"
            className={cn(serviceFunnelPrimaryActionClass, compact && 'sm:w-auto')}
            onClick={() => openBooking(source)}
        >
            <CalendarDays className="size-5" aria-hidden="true" />
            {t('booking.djset.cta_book_production')}
            <ArrowRight className="size-4" aria-hidden="true" />
        </BookingCtaButton>
    );
    const whatsappAction = (source: string, compact = false) => (
        <ServiceWhatsAppButton
            href={whatsappHref}
            label={t('pages.djset.cta_whatsapp')}
            onClick={() => trackWhatsApp(source)}
            className={compact ? 'sm:w-auto' : undefined}
        />
    );

    return (
        <SiteLayout>
            <SeoHead />

            <ServiceFunnelHero
                eyebrow={t('pages.djset.hero_eyebrow')}
                title={t('pages.djset.hero_title')}
                description={t('pages.djset.hero_subtitle')}
                locations={en ? 'Riviera Maya · Mérida · destination sets' : 'Riviera Maya · Mérida · sets en destino'}
                price={price}
                priceLabel={t('pages.djset.hero_price_label')}
                priceNote={t('booking.djset.price_note')}
                media={<HeroMedia media={servicePortfolio.hero} />}
                mediaLabel={servicePortfolio.hero.projectLabel}
                mediaCaption={servicePortfolio.hero.sessionLabel ?? copy.heroMediaCaption}
                primaryAction={primaryAction('hero')}
                secondaryAction={whatsappAction('hero')}
            />

            <ServiceFunnelSection innerClassName="py-0 sm:py-0 lg:py-0">
                <ServiceProofBand
                    portfolio={servicePortfolio}
                    eyebrow="Lapsique Originals"
                    title={copy.proofTitle}
                    description={copy.proofDescription}
                />
            </ServiceFunnelSection>

            {partitionedPortfolio.sessions.stats.mediaCount > 0 ? (
                <ServicePortfolioShowcase
                    portfolio={partitionedPortfolio.sessions}
                    eyebrow={copy.sessionEyebrow}
                    title={copy.sessionTitle}
                    description={copy.sessionDescription}
                    action={(
                        <>
                            {primaryAction('session_proof', true)}
                            {whatsappAction('session_proof', true)}
                        </>
                    )}
                />
            ) : null}

            {partitionedPortfolio.drops.stats.mediaCount > 0 ? (
                <ServicePortfolioShowcase
                    portfolio={partitionedPortfolio.drops}
                    eyebrow={copy.dropsEyebrow}
                    title={copy.dropsTitle}
                    description={copy.dropsDescription}
                    className="border-t border-white/12"
                />
            ) : null}

            {galleryImages.length > 0 ? (
                <ServiceFunnelSection id="fotografia-nightlife">
                    <ServiceFunnelHeading
                        eyebrow={copy.galleryEyebrow}
                        title={copy.galleryTitle}
                        description={copy.galleryDescription}
                    />
                    <NightlifeGallery
                        items={galleryImages}
                        onOpen={setLightboxIndex}
                    />
                </ServiceFunnelSection>
            ) : null}

            {relatedArtists.length > 0 ? (
                <ServiceFunnelSection tone="soft" id="artistas-relacionados">
                    <ServiceFunnelHeading
                        eyebrow={copy.artistsEyebrow}
                        title={copy.artistsTitle}
                        description={copy.artistsDescription}
                    />
                    <div className="mt-10 grid grid-cols-2 gap-px bg-border md:grid-cols-3 lg:grid-cols-6">
                        {relatedArtists.map((dj) => (
                            <Link
                                key={dj.id}
                                href={route('djs.show', { dj: dj.slug }, false, ziggy)}
                                className="group min-w-0 bg-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary"
                            >
                                <div className="aspect-[3/4] overflow-hidden bg-black">
                                    <img
                                        src={dj.cover_url || dj.avatar_url || ''}
                                        alt={dj.name}
                                        loading="lazy"
                                        decoding="async"
                                        className="h-full w-full object-cover grayscale transition-[filter,transform] duration-300 group-hover:scale-[1.02] group-hover:grayscale-0 motion-reduce:transition-none"
                                    />
                                </div>
                                <div className="border-t border-border px-3 py-4">
                                    <h3 className="text-balance font-display text-lg font-bold leading-[1.05] sm:text-xl">
                                        {dj.name}
                                    </h3>
                                    <p className="mt-2 font-mono text-[0.6rem] uppercase tracking-[0.12em] text-primary">
                                        {en ? 'Open artist profile' : 'Abrir perfil del artista'}
                                    </p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </ServiceFunnelSection>
            ) : null}

            <ServiceFunnelSection>
                <ServiceFunnelHeading
                    eyebrow={copy.productionEyebrow}
                    title={copy.productionTitle}
                    description={copy.productionDescription}
                />
                <div className="mt-10">
                    <ServiceFunnelDeliverables items={[...copy.deliverables]} />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="soft">
                <ServiceFunnelHeading
                    eyebrow={en ? 'Production flow' : 'Proceso de producción'}
                    title={copy.processTitle}
                    description={copy.processDescription}
                />
                <div className="mt-10">
                    <ServiceFunnelProcess items={[...copy.process]} />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelSection id="reservar">
                <ServiceFunnelHeading
                    eyebrow={copy.bookingEyebrow}
                    title={copy.bookingTitle}
                    description={copy.bookingDescription}
                />
                <BookingWidget
                    slots={slots}
                    price={price}
                    whatsapp={site.whatsapp}
                    errors={errors}
                    className="mt-10"
                    checkoutRoute="djset.checkout"
                    paymentProvider="stripe"
                    product={product}
                    popupVariant="djset"
                    popupPortfolioItems={portfolioItems}
                    popupHeroProofVideo={popupProofMedia ? toPopupProofVideo(popupProofMedia) : null}
                    popupOriginals={originals}
                    highlight
                    analyticsPayload={analyticsPayload}
                />
            </ServiceFunnelSection>

            <ServiceFunnelSection tone="soft">
                <ServiceFunnelHeading
                    eyebrow={copy.faqEyebrow}
                    title={copy.faqTitle}
                    description={copy.faqDescription}
                />
                <div className="mt-10">
                    <ServiceFunnelFaq items={faq} />
                </div>
            </ServiceFunnelSection>

            <ServiceFunnelFinalCta
                eyebrow="Lapsique Originals"
                title={copy.finalTitle}
                description={copy.finalDescription}
                primaryAction={primaryAction('final')}
                secondaryAction={whatsappAction('final')}
            />

            <PortfolioLightbox
                items={galleryItems}
                activeIndex={lightboxIndex}
                onClose={() => setLightboxIndex(null)}
                onNavigate={setLightboxIndex}
            />
        </SiteLayout>
    );
}

function HeroMedia({ media }: { media: ServicePortfolioMedia }) {
    if (media.kind === 'video') {
        return (
            <EditorialVideoPlayer
                src={media.src}
                poster={media.poster}
                title={media.alt}
                preload="metadata"
                autoPlay={false}
                muted={false}
                hasAudio={media.hasAudio ?? false}
                className="h-full w-full"
                videoClassName="h-full w-full object-cover"
            />
        );
    }

    return (
        <img
            src={media.src}
            alt={media.alt}
            className="h-full w-full object-cover"
            loading="eager"
            decoding="async"
            fetchPriority="high"
        />
    );
}

function NightlifeGallery({
    items,
    onOpen,
}: {
    items: ServicePortfolioMedia[];
    onOpen: (index: number) => void;
}) {
    return (
        <div className="mt-10 grid auto-rows-[11rem] grid-cols-2 gap-3 sm:auto-rows-[14rem] md:grid-cols-5">
            {items.map((item, index) => (
                <button
                    key={item.id}
                    type="button"
                    onClick={() => onOpen(index)}
                    className={cn(
                        'group relative min-h-0 overflow-hidden bg-black text-start outline outline-1 -outline-offset-1 outline-black/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary',
                        index === 0 && 'col-span-2 row-span-2',
                        index === 3 && 'md:col-span-2',
                        index === 6 && 'md:row-span-2',
                    )}
                    aria-label={item.alt}
                >
                    <img
                        src={item.src}
                        alt={item.alt}
                        loading="lazy"
                        decoding="async"
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02] motion-reduce:transition-none"
                    />
                    <span className="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/78 to-transparent px-3 pb-3 pt-10 font-mono text-[0.62rem] uppercase tracking-[0.12em] text-white/82">
                        {item.projectLabel}
                    </span>
                </button>
            ))}
        </div>
    );
}

function buildPortfolio(
    base: ServicePortfolioBundle,
    media: ServicePortfolioMedia[],
): ServicePortfolioBundle {
    const grouped = new Map<string, ServicePortfolioMedia[]>();

    uniqueMedia(media).forEach((item) => {
        grouped.set(item.projectKey, [...(grouped.get(item.projectKey) ?? []), item]);
    });

    const projects = [...grouped.entries()].map(([key, projectMedia]) => ({
        key,
        label: projectMedia[0]?.projectLabel ?? key,
        media: projectMedia,
    }));
    const allMedia = projects.flatMap((project) => project.media);

    return {
        ...base,
        projects,
        stats: {
            mediaCount: allMedia.length,
            projectCount: projects.length,
            imageCount: allMedia.filter((item) => item.kind === 'image').length,
            videoCount: allMedia.filter((item) => item.kind === 'video').length,
        },
    };
}

function uniqueMedia(items: ServicePortfolioMedia[]): ServicePortfolioMedia[] {
    const seen = new Set<string>();

    return items.filter((item) => {
        const key = `${item.id}:${item.src}`;
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
    });
}

function isDropMedia(media: ServicePortfolioMedia): boolean {
    const haystack = [
        media.id,
        media.projectKey,
        media.projectLabel,
        media.sessionLabel ?? '',
        media.src,
    ].join(' ').toLowerCase();

    return media.projectKey === 'danzahaus' || haystack.includes('drop');
}

function isLapsiqueArtist(dj: DjItem): boolean {
    const haystack = `${dj.slug} ${(dj.tags ?? []).join(' ')}`.toLowerCase();

    return !haystack.includes('trascendental');
}

function toPortfolioItem(media: ServicePortfolioMedia, index: number): PortfolioItemData {
    return {
        id: index + 1,
        title: media.alt,
        slug: null,
        type: 'dj_set',
        source: 'service-curation',
        caption: media.sessionLabel ?? media.location ?? null,
        tags: [media.projectKey],
        asset_url: media.src,
        poster_url: null,
        playback_url: null,
        embed_url: null,
        youtube_id: null,
        youtube_url: null,
        media_type: 'image',
        is_featured: index === 0,
        orientation: media.orientation,
    };
}

function pickDistinctPopupMedia(
    portfolio: ServicePortfolioBundle,
): ServicePortfolioMedia | null {
    const media = uniqueMedia(
        portfolio.projects.flatMap((project) => project.media),
    ).filter(
        (item) =>
            item.id !== portfolio.hero.id
            && item.src !== portfolio.hero.src,
    );

    return media.find(
        (item) => item.kind === 'video' && Boolean(item.poster),
    )
        ?? media.find((item) => item.kind === 'image')
        ?? media[0]
        ?? null;
}

function toPopupProofVideo(media: ServicePortfolioMedia) {
    return {
        title: media.alt,
        media_type: media.kind,
        embed_url: null,
        playback_url: media.kind === 'video' ? media.src : null,
        poster_url: media.poster ?? (media.kind === 'image' ? media.src : null),
    } as const;
}

function buildWhatsAppHref(number: string | undefined, message: string): string {
    if (!number) {
        return '#';
    }

    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}
