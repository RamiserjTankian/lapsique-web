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

export default function PortfolioIndex({ items, featuredItem, availableTags }: PortfolioIndexProps) {
    const [activeIndex, setActiveIndex] = useState<number | null>(null);
    const [tag, setTag] = useState<string | null>(null);

    const filteredItems = useMemo(
        () => (tag ? items.filter((i) => i.tags?.includes(tag)) : items),
        [items, tag],
    );

    const gridItems = useMemo(() => {
        if (!featuredItem) return filteredItems;
        return filteredItems.filter((i) => i.id !== featuredItem.id);
    }, [filteredItems, featuredItem]);

    const openItem = (item: PortfolioItemData) => {
        const idx = filteredItems.findIndex((i) => i.id === item.id);
        setActiveIndex(idx >= 0 ? idx : null);
    };

    const openFeaturedInGallery = () => {
        if (!featuredItem) return;
        const idx = filteredItems.findIndex((i) => i.id === featuredItem.id);
        setActiveIndex(idx >= 0 ? idx : 0);
    };

    return (
        <SiteLayout>
            <Head title="Portafolio" />
            {featuredItem && (
                <PortfolioHero item={featuredItem} onExplore={openFeaturedInGallery} />
            )}
            <GlassSection
                eyebrow="Portafolio"
                title="Trabajo reciente"
                description="Fotografía y video con look cinematográfico — capturado con Sony α7."
            >
                {availableTags.length > 0 && (
                    <div className="mb-8 flex flex-wrap gap-2">
                        <TagButton active={!tag} onClick={() => setTag(null)} label="Todos" />
                        {availableTags.map((t) => (
                            <TagButton
                                key={t}
                                active={tag === t}
                                onClick={() => setTag(t)}
                                label={t}
                            />
                        ))}
                    </div>
                )}

                <motion.div
                    variants={staggerContainer}
                    initial="hidden"
                    whileInView="visible"
                    viewport={{ once: true, margin: '-60px' }}
                    className="columns-2 gap-3 md:columns-3 lg:columns-4"
                >
                    {gridItems.map((item, index) => (
                        <PortfolioGridItem
                            key={item.id}
                            item={item}
                            index={index}
                            onSelect={openItem}
                            className="mb-3 break-inside-avoid"
                        />
                    ))}
                </motion.div>

                {gridItems.length === 0 && (
                    <p className="text-center text-muted-foreground">No hay proyectos con este filtro.</p>
                )}
            </GlassSection>

            <PortfolioLightbox
                items={filteredItems}
                activeIndex={activeIndex}
                onClose={() => setActiveIndex(null)}
                onNavigate={setActiveIndex}
            />
        </SiteLayout>
    );
}

function TagButton({
    active,
    onClick,
    label,
}: {
    active: boolean;
    onClick: () => void;
    label: string;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`rounded-full px-4 py-1.5 text-xs uppercase tracking-wider transition-all duration-200 ${
                active
                    ? 'bg-primary text-primary-foreground shadow-[0_0_20px_oklch(0.78_0.14_75/0.35)]'
                    : 'border border-border/60 bg-secondary/50 text-muted-foreground hover:border-primary/30 hover:text-foreground'
            }`}
        >
            {label}
        </button>
    );
}
