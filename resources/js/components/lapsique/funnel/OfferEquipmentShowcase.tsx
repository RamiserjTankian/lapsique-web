import { useSectionEvent } from '@/hooks/useSectionEvent';
import type { PortfolioItemData } from '@/types';

export function OfferEquipmentShowcase({ images }: { images: PortfolioItemData[] }) {
    const ref = useSectionEvent('equipment_viewed', { section: 'equipment' });
    const galleryImages = images
        .filter((item) => item.media_type === 'image' && Boolean(item.asset_url || item.poster_url))
        .slice(0, 2);

    if (galleryImages.length === 0) {
        return null;
    }

    return (
        <div ref={ref} className="grid grid-cols-2 gap-3 sm:gap-4">
            {galleryImages.map((image) => (
                <figure
                    key={image.id}
                    className="group relative aspect-[4/5] overflow-hidden rounded-xl border border-border/70 bg-secondary sm:aspect-[5/4]"
                >
                    <img
                        src={image.asset_url ?? image.poster_url ?? ''}
                        alt={image.title ?? 'Portafolio Lapsique'}
                        className="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                        loading="lazy"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-transparent" />
                    {image.title && (
                        <figcaption className="absolute inset-x-0 bottom-0 p-3 text-xs font-semibold text-white">
                            {image.title}
                        </figcaption>
                    )}
                </figure>
            ))}
        </div>
    );
}
