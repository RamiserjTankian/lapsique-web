import type { TranslateFn } from '@/hooks/useTranslations';
import type { HeroProofVideoData, PortfolioItemData, VideoItem } from '@/types';

export const POPUP_VARIANTS = [
    'home',
    'contentCreation',
    'businessReels',
    'foodReels',
    'djset',
    'drone',
    'construction',
    'eventCoverage',
    'multiCamera',
] as const;

export type PopupVariant = (typeof POPUP_VARIANTS)[number];

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
    home: '/images/og-default.jpg',
    contentCreation: '/images/booking-og.jpg',
    businessReels: '/images/og-default.jpg',
    foodReels: '/images/food-reels/food-santino-brunch-table.webp',
    djset: '/images/portfolio/photos/082-proper-collective-cab1bed3f4.webp',
    drone: '/images/drone-sessions/hero.jpg',
    construction: '/images/drone-sessions/construction-goba-aerial.jpg',
    eventCoverage: '/images/portfolio/video-posters/2026-07-11-mtrx-dumas-a0794b89f7.jpg',
    multiCamera: '/images/og/multicamara.jpg',
};

const COPY_SUFFIXES: Record<PopupVariant, string> = {
    home: 'home',
    contentCreation: 'content_creation',
    businessReels: 'business_reels',
    foodReels: 'food_reels',
    djset: 'djset',
    drone: 'drone',
    construction: 'construction',
    eventCoverage: 'event_coverage',
    multiCamera: 'multi_camera',
};

const NEWSLETTER_SUFFIXES: Partial<Record<PopupVariant, string>> = {
    home: 'home',
    djset: 'djset',
    eventCoverage: 'event_coverage',
    multiCamera: 'multi_camera',
};

export function getPopupWhatsAppPrefillKey(variant: PopupVariant): string {
    return `funnel.whatsapp.prefill_${COPY_SUFFIXES[variant]}`;
}

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

    const url = variant === 'eventCoverage'
        ? (
            posterFromProof
            ?? FALLBACK_IMAGES[variant]
        )
        : variant === 'djset'
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
        ?? t(`funnel.popup.fallback_alt_${COPY_SUFFIXES[variant]}`);

    return { url, alt };
}

export function getPopupVisualCopy(
    t: TranslateFn,
    variant: PopupVariant,
    purpose: PopupMediaPurpose,
): { badge: string; title: string; description: string; caption?: string } {
    if (purpose === 'booking') {
        const suffix = COPY_SUFFIXES[variant];

        return {
            badge: t(`funnel.popup.booking_${suffix}_badge`),
            title: t(`funnel.popup.booking_${suffix}_title`),
            description: t(`funnel.popup.booking_${suffix}_description`),
            caption: t(`funnel.popup.booking_${suffix}_caption`),
        };
    }

    if (purpose === 'newsletter') {
        const suffix = NEWSLETTER_SUFFIXES[variant] ?? 'home';

        return {
            badge: t(`funnel.popup.newsletter_${suffix}_badge`),
            title: t(`funnel.popup.newsletter_${suffix}_title`),
            description: t(`funnel.popup.newsletter_${suffix}_description`),
            caption: t(`funnel.popup.newsletter_${suffix}_caption`),
        };
    }

    const suffix = COPY_SUFFIXES[variant];

    return {
        badge: t('funnel.popup.whatsapp_badge'),
        title: t(`funnel.popup.whatsapp_${suffix}_title`),
        description: t(`funnel.popup.whatsapp_${suffix}_description`),
        caption: t(`funnel.popup.whatsapp_${suffix}_caption`),
    };
}
