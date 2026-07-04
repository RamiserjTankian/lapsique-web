import type { TranslateFn } from '@/hooks/useTranslations';
import type { HeroProofVideoData, PortfolioItemData, VideoItem } from '@/types';

export type PopupVariant = 'home' | 'djset' | 'drone' | 'construction';

export type PopupMediaPurpose = 'booking' | 'newsletter' | 'whatsapp';

export interface ResolvePopupImageInput {
    variant: PopupVariant;
    purpose?: PopupMediaPurpose;
    portfolioItems?: PortfolioItemData[];
    originals?: VideoItem[];
    heroProofVideo?: HeroProofVideoData | null;
}

export interface ResolvedPopupImage {
    url: string;
    alt: string;
}

const FALLBACK_IMAGES: Record<PopupVariant, string> = {
    home: '/images/equipment/sony-a7iv.svg',
    djset: '/images/portfolio/photos/082-proper-collective-cab1bed3f4.webp',
    drone: '/images/drone-sessions/hero.jpg',
    construction: '/images/drone-sessions/goba-construction.jpg',
};

function firstPortfolioImage(
    items: PortfolioItemData[] | undefined,
): PortfolioItemData | undefined {
    if (!items?.length) {
        return undefined;
    }

    return (
        items.find((item) => item.media_type === 'image' && item.is_featured && item.asset_url)
        ?? items.find((item) => item.media_type === 'image' && item.asset_url)
    );
}

function firstVideoPoster(items: PortfolioItemData[] | undefined): string | null {
    if (!items?.length) {
        return null;
    }

    const video = items.find(
        (item) =>
            (item.media_type === 'video' || item.media_type === 'youtube')
            && (item.poster_url || item.asset_url),
    );

    return video?.poster_url ?? video?.asset_url ?? null;
}

function firstOriginalThumbnail(originals: VideoItem[] | undefined): string | null {
    if (!originals?.length) {
        return null;
    }

    const featured = originals.find((item) => item.is_featured && item.thumbnail_url);

    return featured?.thumbnail_url ?? originals.find((item) => item.thumbnail_url)?.thumbnail_url ?? null;
}

export function resolvePopupImage(
    t: TranslateFn,
    {
        variant,
        portfolioItems = [],
        originals = [],
        heroProofVideo = null,
    }: ResolvePopupImageInput,
): ResolvedPopupImage {
    const featuredImage = firstPortfolioImage(portfolioItems);
    const posterFromPortfolio = firstVideoPoster(portfolioItems);
    const posterFromProof = heroProofVideo?.poster_url ?? null;
    const djThumbnail = variant === 'djset' ? firstOriginalThumbnail(originals) : null;

    const url = variant === 'djset'
        ? (
            djThumbnail
            ?? posterFromProof
            ?? posterFromPortfolio
            ?? featuredImage?.asset_url
            ?? FALLBACK_IMAGES[variant]
        )
        : (
            featuredImage?.asset_url
            ?? posterFromProof
            ?? posterFromPortfolio
            ?? FALLBACK_IMAGES[variant]
        );

    const alt =
        (variant === 'djset' ? null : featuredImage?.title)
        ?? heroProofVideo?.title
        ?? (variant === 'djset'
            ? t('funnel.popup.fallback_alt_djset')
            : variant === 'drone'
              ? t('funnel.popup.fallback_alt_drone')
              : variant === 'construction'
                ? t('funnel.popup.fallback_alt_construction')
              : t('funnel.popup.fallback_alt_home'));

    return { url, alt };
}

export function getPopupVisualCopy(
    t: TranslateFn,
    variant: PopupVariant,
    purpose: PopupMediaPurpose,
): { badge: string; title: string; description: string; caption?: string } {
    if (purpose === 'booking') {
        if (variant === 'djset') {
            return {
                badge: t('funnel.popup.booking_djset_badge'),
                title: t('funnel.popup.booking_djset_title'),
                description: t('funnel.popup.booking_djset_description'),
                caption: t('funnel.popup.booking_djset_caption'),
            };
        }

        if (variant === 'drone') {
            return {
                badge: t('funnel.popup.booking_drone_badge'),
                title: t('funnel.popup.booking_drone_title'),
                description: t('funnel.popup.booking_drone_description'),
                caption: t('funnel.popup.booking_drone_caption'),
            };
        }

        if (variant === 'construction') {
            return {
                badge: t('funnel.popup.booking_construction_badge'),
                title: t('funnel.popup.booking_construction_title'),
                description: t('funnel.popup.booking_construction_description'),
                caption: t('funnel.popup.booking_construction_caption'),
            };
        }

        return {
            badge: t('funnel.popup.booking_home_badge'),
            title: t('funnel.popup.booking_home_title'),
            description: t('funnel.popup.booking_home_description'),
            caption: t('funnel.popup.booking_home_caption'),
        };
    }

    if (purpose === 'newsletter') {
        return variant === 'djset'
            ? {
                  badge: t('funnel.popup.newsletter_djset_badge'),
                  title: t('funnel.popup.newsletter_djset_title'),
                  description: t('funnel.popup.newsletter_djset_description'),
                  caption: t('funnel.popup.newsletter_djset_caption'),
              }
            : {
                  badge: t('funnel.popup.newsletter_home_badge'),
                  title: t('funnel.popup.newsletter_home_title'),
                  description: t('funnel.popup.newsletter_home_description'),
                  caption: t('funnel.popup.newsletter_home_caption'),
              };
    }

    return variant === 'djset'
        ? {
              badge: t('funnel.popup.whatsapp_badge'),
              title: t('funnel.popup.whatsapp_djset_title'),
              description: t('funnel.popup.whatsapp_djset_description'),
              caption: t('funnel.popup.whatsapp_djset_caption'),
          }
        : {
              badge: t('funnel.popup.whatsapp_badge'),
              title: t('funnel.popup.whatsapp_home_title'),
              description: t('funnel.popup.whatsapp_home_description'),
              caption: t('funnel.popup.whatsapp_home_caption'),
          };
}
