import { Link, usePage } from '@inertiajs/react';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { useCallback, useEffect, useState, type RefObject } from 'react';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import SiteLayout from '@/layouts/SiteLayout';
import { CinematicHero } from '@/components/lapsique/CinematicHero';
import { BookingWidget } from '@/components/lapsique/BookingWidget';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { VideoStrip } from '@/components/lapsique/VideoStrip';
import { PortfolioTile } from '@/components/lapsique/PortfolioTile';
import { PortfolioLightbox } from '@/components/lapsique/PortfolioLightbox';
import { DjCard } from '@/components/lapsique/DjCard';
import { FunnelDeliverables } from '@/components/lapsique/funnel/FunnelDeliverables';
import { FunnelProcess } from '@/components/lapsique/funnel/FunnelProcess';
import { FunnelTeam } from '@/components/lapsique/funnel/FunnelTeam';
import { FunnelEquipment } from '@/components/lapsique/funnel/FunnelEquipment';
import { FunnelFAQ } from '@/components/lapsique/funnel/FunnelFAQ';
import { FunnelStickyBar } from '@/components/lapsique/funnel/FunnelStickyBar';
import { Button } from '@/components/ui/button';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { route } from '@/lib/route';
import type {
    BookingSlot,
    DjItem,
    PageProps,
    PortfolioItemData,
    VideoItem,
} from '@/types';

interface HomeProps {
    title: string;
    subtitle: string;
    price: number;
    slots: BookingSlot[];
    originals: VideoItem[];
    portfolioItems: PortfolioItemData[];
    djs: DjItem[];
    errors?: Record<string, string>;
}

export default function Home({
    title,
    subtitle,
    price,
    slots,
    originals,
    portfolioItems,
    djs,
    errors,
}: HomeProps) {
    const { ziggy, site } = usePage<PageProps>().props;
    const portfolioProofRef = useSectionEvent<HTMLDivElement>('proof_section_viewed', { section: 'portfolio' });
    const [portfolioActiveIndex, setPortfolioActiveIndex] = useState<number | null>(null);
    const highlightedDj = djs.find((d) => d.is_highlighted) ?? djs[0];
    const lineupDjs = djs.filter((d) => d.id !== highlightedDj?.id).slice(0, 5);

    const galleryItems = portfolioItems.slice(0, 8);

    useEffect(() => {
        trackBookingEvent('booking_page_viewed', { section: 'home' });
    }, []);

    const openPortfolioItem = useCallback(
        (item: PortfolioItemData) => {
            const idx = galleryItems.findIndex((i) => i.id === item.id);
            setPortfolioActiveIndex(idx >= 0 ? idx : 0);
        },
        [galleryItems],
    );

    const openPortfolioGallery = useCallback(() => {
        setPortfolioActiveIndex(galleryItems.length > 0 ? 0 : null);
    }, [galleryItems.length]);

    return (
        <SiteLayout>
            <SeoHead />

            <CinematicHero
                title={title}
                subtitle={subtitle}
                price={price}
                portfolioItems={portfolioItems}
            />
            <BookingWidget
                slots={slots}
                price={price}
                whatsapp={site.whatsapp}
                errors={errors}
            />
            <FunnelDeliverables />
            <FunnelProcess />
            <FunnelTeam />
            <FunnelEquipment />

            {originals.length > 0 && (
                <GlassSection
                    className="relative"
                    eyebrow="Prueba"
                    title="Piezas recientes que elevan percepción y presencia"
                    description="Explora cómo se ve una producción cuando sí está pensada para comunicar valor."
                    action={
                        <Button variant="link" asChild className="text-primary">
                            <Link href={route('videos.index', undefined, false, ziggy)}>
                                Ver biblioteca →
                            </Link>
                        </Button>
                    }
                >
                    <VideoStrip videos={originals} />
                </GlassSection>
            )}

            {galleryItems.length > 0 && (
                <GlassSection
                    eyebrow="Portafolio"
                    title="Galería breve para validar el estándar"
                    description="Marcas, eventos y creadores con un lenguaje visual listo para pauta, social y posicionamiento."
                    action={
                        <Button
                            type="button"
                            variant="link"
                            className="text-primary"
                            onClick={openPortfolioGallery}
                        >
                            Ver galería →
                        </Button>
                    }
                >
                    <MotionPortfolioGrid
                        items={galleryItems}
                        sectionRef={portfolioProofRef}
                        onSelect={openPortfolioItem}
                    />
                </GlassSection>
            )}

            <PortfolioLightbox
                items={galleryItems}
                activeIndex={portfolioActiveIndex}
                onClose={() => setPortfolioActiveIndex(null)}
                onNavigate={setPortfolioActiveIndex}
            />

            <FunnelFAQ />

            {djs.length > 0 && (
                <GlassSection
                    eyebrow="Artistas"
                    title="Lineup y talento de nuestra órbita"
                    description="DJs y artistas con los que hemos colaborado en sets, aftermovies y producción."
                >
                    <MotionLineupGrid djs={djs} highlightedDj={highlightedDj} lineupDjs={lineupDjs} />
                </GlassSection>
            )}

            <FunnelStickyBar price={price} />
        </SiteLayout>
    );
}

function MotionPortfolioGrid({
    items,
    sectionRef,
    onSelect,
}: {
    items: PortfolioItemData[];
    sectionRef: RefObject<HTMLDivElement | null>;
    onSelect: (item: PortfolioItemData) => void;
}) {
    return (
        <div ref={sectionRef} className="grid grid-cols-2 gap-3 md:grid-cols-4">
            {items.map((item, i) => (
                <PortfolioTile key={item.id} item={item} index={i} onSelect={onSelect} />
            ))}
        </div>
    );
}

function MotionLineupGrid({
    highlightedDj,
    lineupDjs,
}: {
    djs: DjItem[];
    highlightedDj?: DjItem;
    lineupDjs: DjItem[];
}) {
    return (
        <div>
            {highlightedDj && <DjCard dj={highlightedDj} variant="featured" />}
            {lineupDjs.length > 0 && (
                <div className="mt-4 grid grid-cols-3 gap-1 sm:grid-cols-3">
                    {lineupDjs.map((dj, i) => (
                        <DjCard key={dj.id} dj={dj} index={i + 1} />
                    ))}
                </div>
            )}
        </div>
    );
}

