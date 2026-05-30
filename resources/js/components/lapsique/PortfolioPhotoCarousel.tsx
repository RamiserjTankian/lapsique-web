import { useMemo } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import { cn } from '@/lib/utils';
import { glassCardVariants } from '@/lib/variants';
import type { PageProps, PortfolioItemData } from '@/types';

function getPortfolioImageUrl(item: PortfolioItemData): string | null {
    if (item.media_type !== 'image') {
        return null;
    }

    return item.asset_url ?? item.poster_url ?? null;
}

function pickHorizontalPortfolioPhotos(items: PortfolioItemData[]): PortfolioItemData[] {
    const photos = items.filter((item) => Boolean(getPortfolioImageUrl(item)));
    const horizontal = photos.filter((item) => item.orientation === 'horizontal');
    const pool = horizontal.length > 0 ? horizontal : photos;

    return pool.slice(0, 12);
}

export function PortfolioPhotoCarousel({ items }: { items: PortfolioItemData[] }) {
    const { t } = useTranslations();
    const { ziggy } = usePage<PageProps>().props;

    const slides = useMemo(() => pickHorizontalPortfolioPhotos(items), [items]);

    if (slides.length === 0) {
        return null;
    }

    return (
        <Carousel
            opts={{ align: 'start', loop: slides.length > 2 }}
            className="relative w-full"
            aria-label={t('pages.home.about_carousel_aria')}
        >
            <CarouselContent className="-ml-3">
                {slides.map((item) => {
                    const imageUrl = getPortfolioImageUrl(item);

                    if (!imageUrl) {
                        return null;
                    }

                    return (
                        <CarouselItem
                            key={item.id}
                            className="basis-[88%] pl-3 sm:basis-[62%] md:basis-[46%] lg:basis-[36%]"
                        >
                            <Link
                                href={route('portfolio.index', undefined, false, ziggy)}
                                className={cn(
                                    glassCardVariants(),
                                    'group relative block aspect-[16/10] w-full overflow-hidden',
                                )}
                            >
                                <img
                                    src={imageUrl}
                                    alt={item.title ?? t('pages.portfolio.view_gallery')}
                                    className="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.04]"
                                    loading="lazy"
                                />
                                <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-black/5 to-transparent opacity-70 transition group-hover:opacity-90" />
                            </Link>
                        </CarouselItem>
                    );
                })}
            </CarouselContent>
            {slides.length > 1 && (
                <>
                    <CarouselPrevious
                        className="left-2 size-9 border-border/70 bg-background/85 shadow-md backdrop-blur-sm sm:left-3"
                        variant="outline"
                    />
                    <CarouselNext
                        className="right-2 size-9 border-border/70 bg-background/85 shadow-md backdrop-blur-sm sm:right-3"
                        variant="outline"
                    />
                </>
            )}
        </Carousel>
    );
}
