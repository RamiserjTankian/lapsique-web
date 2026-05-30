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

type PortfolioPhotoSlide = {
    id: string;
    title: string | null;
    url: string;
};

const FALLBACK_PORTFOLIO_PHOTOS: PortfolioPhotoSlide[] = [
    {
        id: 'fallback-traumer-shonky',
        title: 'Traumer b2b Shonky',
        url: '/images/portfolio/photos/044-traumer-shonky-18f03299b9.webp',
    },
    {
        id: 'fallback-umi',
        title: 'Umi nightlife',
        url: '/images/portfolio/photos/105-umi-001-20ed3ad5cc.webp',
    },
    {
        id: 'fallback-drone-malfa',
        title: 'Drone cinematic',
        url: '/images/portfolio/photos/064-dron-malfa-15c67a8bf8.webp',
    },
    {
        id: 'fallback-rebolledo',
        title: 'Rebolledo session',
        url: '/images/portfolio/photos/084-rebolledo-aca3815016.webp',
    },
];

function getPortfolioImageUrl(item: PortfolioItemData): string | null {
    if (item.media_type !== 'image') {
        return null;
    }

    return item.asset_url ?? item.poster_url ?? null;
}

function isOptimizedPortfolioPhoto(item: PortfolioItemData): boolean {
    const imageUrl = getPortfolioImageUrl(item);

    return Boolean(
        imageUrl
        && (
            item.source === 'public'
            || imageUrl.includes('/images/portfolio/photos/')
        ),
    );
}

function toSlide(item: PortfolioItemData): PortfolioPhotoSlide | null {
    const imageUrl = getPortfolioImageUrl(item);

    if (!imageUrl) {
        return null;
    }

    return {
        id: String(item.id),
        title: item.title,
        url: imageUrl,
    };
}

function pickHorizontalPortfolioPhotos(items: PortfolioItemData[]): PortfolioPhotoSlide[] {
    const publicPhotos = items.filter(isOptimizedPortfolioPhoto);
    const horizontal = publicPhotos.filter((item) => item.orientation === 'horizontal');
    const sourcePool = horizontal.length > 0 ? horizontal : publicPhotos;
    const slides = sourcePool
        .map(toSlide)
        .filter((slide): slide is PortfolioPhotoSlide => slide !== null);

    for (const fallback of FALLBACK_PORTFOLIO_PHOTOS) {
        if (slides.length >= 12) {
            break;
        }

        if (!slides.some((slide) => slide.url === fallback.url)) {
            slides.push(fallback);
        }
    }

    return slides.slice(0, 12);
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
                {slides.map((item) => (
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
                                src={item.url}
                                alt={item.title ?? t('pages.portfolio.view_gallery')}
                                className="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.04]"
                                loading="lazy"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-black/5 to-transparent opacity-70 transition group-hover:opacity-90" />
                        </Link>
                    </CarouselItem>
                ))}
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
