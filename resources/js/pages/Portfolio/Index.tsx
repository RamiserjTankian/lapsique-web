import { Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { motion } from 'framer-motion';
import SiteLayout from '@/layouts/SiteLayout';
import { PortfolioGridItem } from '@/components/lapsique/PortfolioGridItem';
import { PortfolioLightbox } from '@/components/lapsique/PortfolioLightbox';
import { PortfolioHero } from '@/components/lapsique/PortfolioHero';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { staggerContainer } from '@/lib/motion';
import type { PortfolioItemData } from '@/types';

interface PortfolioIndexProps {
    items: PortfolioItemData[];
    featuredItem: PortfolioItemData | null;
    availableTags: string[];
}

export default function PortfolioIndex({ items, featuredItem }: PortfolioIndexProps) {
    const [activeIndex, setActiveIndex] = useState<number | null>(null);

    const gridItems = useMemo(() => {
        const visibleItems = featuredItem
            ? items.filter((i) => i.id !== featuredItem.id)
            : items;

        return arrangePortfolioMosaic(visibleItems);
    }, [items, featuredItem]);

    const galleryItems = useMemo(
        () => (featuredItem ? [featuredItem, ...gridItems] : gridItems),
        [featuredItem, gridItems],
    );

    const openItem = (item: PortfolioItemData) => {
        const idx = galleryItems.findIndex((i) => i.id === item.id);
        setActiveIndex(idx >= 0 ? idx : null);
    };

    const openFeaturedInGallery = () => {
        if (!featuredItem) return;
        setActiveIndex(0);
    };

    return (
        <SiteLayout>
            <Head title="Portafolio" />
            {featuredItem && (
                <PortfolioHero item={featuredItem} onExplore={openFeaturedInGallery} />
            )}
            <GlassSection
                title="Portafolio"
                description="Fotografía y video con look cinematográfico."
            >
                <motion.div
                    variants={staggerContainer}
                    initial="hidden"
                    whileInView="visible"
                    viewport={{ once: true, margin: '-60px' }}
                    className="grid grid-flow-row-dense auto-rows-[9rem] grid-cols-2 gap-3 md:auto-rows-[12rem] md:grid-cols-4 md:gap-4 xl:auto-rows-[13rem] xl:grid-cols-6"
                >
                    {gridItems.map((item, index) => (
                        <PortfolioGridItem
                            key={item.id}
                            item={item}
                            index={index}
                            onSelect={openItem}
                            className={getMosaicClassName(item, index)}
                        />
                    ))}
                </motion.div>

                {gridItems.length === 0 && (
                    <p className="text-center text-muted-foreground">No hay proyectos disponibles.</p>
                )}
            </GlassSection>

            <PortfolioLightbox
                items={galleryItems}
                activeIndex={activeIndex}
                onClose={() => setActiveIndex(null)}
                onNavigate={setActiveIndex}
            />
        </SiteLayout>
    );
}

function arrangePortfolioMosaic(items: PortfolioItemData[]): PortfolioItemData[] {
    const photos = items.filter((item) => item.media_type === 'image');
    const videos = items.filter((item) => item.media_type !== 'image');
    const arranged: PortfolioItemData[] = [];
    let photoIndex = 0;
    let videoIndex = 0;

    while (photoIndex < photos.length || videoIndex < videos.length) {
        for (let i = 0; i < 5 && photoIndex < photos.length; i++) {
            arranged.push(photos[photoIndex]);
            photoIndex++;
        }

        if (videoIndex < videos.length) {
            arranged.push(videos[videoIndex]);
            videoIndex++;
        }
    }

    return arranged;
}

function getMosaicClassName(item: PortfolioItemData, index: number): string {
    if (item.media_type === 'video' || item.media_type === 'youtube') {
        return index % 8 === 2
            ? 'col-span-2 row-span-2 md:col-span-2 md:row-span-2 xl:col-span-3'
            : 'col-span-2 row-span-2 md:col-span-2 md:row-span-2';
    }

    if (index % 17 === 0) {
        return 'col-span-2 row-span-2 md:col-span-2 md:row-span-2 xl:col-span-3';
    }

    if (item.orientation === 'vertical' && index % 6 === 1) {
        return 'col-span-1 row-span-2 md:col-span-1 md:row-span-2';
    }

    if (item.orientation === 'horizontal' && index % 9 === 3) {
        return 'col-span-2 row-span-1 md:col-span-2 md:row-span-1';
    }

    if (index % 14 === 6) {
        return 'col-span-2 row-span-2 md:col-span-2 md:row-span-2';
    }

    return 'col-span-1 row-span-1';
}
