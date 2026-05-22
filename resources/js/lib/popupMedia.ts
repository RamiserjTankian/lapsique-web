import type { HeroProofVideoData, PortfolioItemData, VideoItem } from '@/types';

export type PopupVariant = 'home' | 'djset';

export type PopupMediaPurpose = 'booking' | 'newsletter';

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
    djset: '/images/equipment/sony-a6700.svg',
};

const FALLBACK_ALTS: Record<PopupVariant, string> = {
    home: 'Producción de contenido con cámara Sony full frame',
    djset: 'Grabación de DJ set con producción multicámara',
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

function firstVideoPoster(
    items: PortfolioItemData[] | undefined,
): string | null {
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

export function resolvePopupImage({
    variant,
    portfolioItems = [],
    originals = [],
    heroProofVideo = null,
}: ResolvePopupImageInput): ResolvedPopupImage {
    const featuredImage = firstPortfolioImage(portfolioItems);
    const posterFromPortfolio = firstVideoPoster(portfolioItems);
    const posterFromProof = heroProofVideo?.poster_url ?? null;
    const djThumbnail = variant === 'djset' ? firstOriginalThumbnail(originals) : null;

    const url =
        featuredImage?.asset_url
        ?? posterFromProof
        ?? posterFromPortfolio
        ?? djThumbnail
        ?? FALLBACK_IMAGES[variant];

    const alt =
        featuredImage?.title
        ?? heroProofVideo?.title
        ?? FALLBACK_ALTS[variant];

    return { url, alt };
}

export function getPopupVisualCopy(
    variant: PopupVariant,
    purpose: PopupMediaPurpose,
): { badge: string; title: string; description: string; caption?: string } {
    if (purpose === 'booking') {
        return variant === 'djset'
            ? {
                  badge: 'Reserva DJ set',
                  title: 'Tu set, capturado como contenido principal',
                  description: '3 cámaras fijas, dron y video final de una hora listo para mostrar tu sonido.',
                  caption: 'Apartado con Stripe · fecha real del equipo',
              }
            : {
                  badge: 'Agenda tu sesión',
                  title: 'Contenido premium que vende tu negocio',
                  description: 'Reel editado, fotos y dirección en set con captura Sony full frame.',
                  caption: 'Checkout seguro · entrega en días hábiles',
              };
    }

    return variant === 'djset'
        ? {
              badge: 'Comunidad Lapsique',
              title: 'Sets, fechas y la escena en tu inbox',
              description: 'Entérate antes que nadie de grabaciones, DJs y oportunidades para mostrar tu cabina.',
              caption: 'Sin spam · solo lo relevante para artistas',
          }
        : {
              badge: 'Newsletter Lapsique',
              title: 'Eventos, reels y fechas antes que se llenen',
              description: 'Recibe lanzamientos, behind the scenes y acceso anticipado a producción y eventos.',
              caption: 'Un email cuando vale la pena abrirlo',
          };
}
