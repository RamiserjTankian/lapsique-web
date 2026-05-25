import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import type { LandingVideoEntry, PortfolioItemData } from '@/types';

export function OfferEquipmentShowcase({
    images,
    equipmentVideos = [],
}: {
    images: PortfolioItemData[];
    equipmentVideos?: LandingVideoEntry[];
}) {
    const ref = useSectionEvent('equipment_viewed', { section: 'equipment' });

    if (equipmentVideos.length >= 2) {
        return (
            <div ref={ref} className="grid grid-cols-2 gap-3 sm:gap-4">
                {equipmentVideos.slice(0, 2).map((video) => (
                    <figure
                        key={video.src}
                        className="group relative aspect-[9/16] overflow-hidden rounded-xl border border-border/70 bg-black"
                    >
                        <AutoplayVideo
                            src={video.src}
                            poster={video.poster}
                            className="absolute inset-0 h-full w-full"
                            videoClassName="object-cover object-center transition duration-700 group-hover:scale-[1.03]"
                            pauseWhenOffscreen
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent" />
                    </figure>
                ))}
            </div>
        );
    }

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
                    className="group relative aspect-[9/16] overflow-hidden rounded-xl border border-border/70 bg-secondary"
                >
                    <img
                        src={image.asset_url ?? image.poster_url ?? ''}
                        alt={image.title ?? 'Portafolio Lapsique'}
                        className="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]"
                        loading="lazy"
                    />
                </figure>
            ))}
        </div>
    );
}
