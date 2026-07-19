import { useMemo, useState } from 'react';
import { motion } from 'framer-motion';
import SiteLayout from '@/layouts/SiteLayout';
import { PortfolioGridItem } from '@/components/lapsique/PortfolioGridItem';
import { PortfolioLightbox } from '@/components/lapsique/PortfolioLightbox';
import { PaginationLinks } from '@/components/lapsique/PaginationLinks';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { useTranslations } from '@/hooks/useTranslations';
import { staggerContainer } from '@/lib/motion';
import type { Paginated, PortfolioItemData } from '@/types';

interface PortfolioIndexProps {
    items: Paginated<PortfolioItemData>;
    availableTags: string[];
}

export default function PortfolioIndex({ items }: PortfolioIndexProps) {
    const { t, locale } = useTranslations();
    const [activeIndex, setActiveIndex] = useState<number | null>(null);

    const pageItems = items?.data ?? [];
    const gridItems = useMemo(() => arrangePortfolioMosaic(pageItems), [pageItems]);

    const openItem = (item: PortfolioItemData) => {
        const idx = gridItems.findIndex((i) => i.id === item.id);
        setActiveIndex(idx >= 0 ? idx : null);
    };

    return (
        <SiteLayout>
            <SeoHead />
            <header className="border-b border-foreground/15 pb-10 pt-12 md:pb-16 md:pt-20">
                <h1 className="max-w-4xl text-6xl font-medium leading-[0.88] md:text-8xl">
                    {locale === 'en' ? 'Photography and video produced by Lapsique.' : 'Fotografía y video producidos por Lapsique.'}
                </h1>
                <p className="mt-6 max-w-2xl text-base leading-relaxed text-muted-foreground md:text-lg">
                    {locale === 'en'
                        ? 'Campaigns, restaurants, hospitality, music, and spaces across Riviera Maya.'
                        : 'Campañas, restaurantes, hospitalidad, música y espacios en Riviera Maya.'}
                </p>
            </header>
            <section aria-label={t('pages.portfolio.title')} className="py-12 md:py-16">
                <motion.div
                    variants={staggerContainer}
                    initial="hidden"
                    whileInView="visible"
                    viewport={{ once: true, margin: '-60px' }}
                    className="grid grid-flow-row-dense auto-rows-[8.5rem] grid-cols-2 gap-3 md:auto-rows-[10.5rem] md:grid-cols-4 md:gap-4 xl:grid-cols-6"
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
                    <p className="text-center text-muted-foreground">{t('pages.portfolio.empty')}</p>
                )}

                {gridItems.length > 0 && (
                    <PaginationLinks links={items?.links ?? []} />
                )}
            </section>

            <PortfolioLightbox
                items={gridItems}
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
        return 'col-span-1 row-span-2 md:col-span-1 md:row-span-2';
    }

    if (item.orientation === 'horizontal') {
        return 'col-span-2 row-span-1';
    }

    return index % 5 === 0
        ? 'col-span-1 row-span-2'
        : 'col-span-1 row-span-1';
}
