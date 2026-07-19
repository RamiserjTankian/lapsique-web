import { useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { REEL_PLAYER_STATE_EVENT } from '@/hooks/useReelPlayerModal';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { FUNNEL_MODAL_STATE_EVENT, getActiveFunnelModal } from '@/lib/funnelModalEvents';
import { useIsMobileViewport } from '@/hooks/useMediaQuery';
import { useTranslations } from '@/hooks/useTranslations';
import type { PageProps } from '@/types';

export function WhatsAppFab() {
    const { site } = usePage<PageProps>().props;
    const { url } = usePage<PageProps>();
    const { t } = useTranslations();
    const isMobile = useIsMobileViewport();
    const [funnelModalOpen, setFunnelModalOpen] = useState(getActiveFunnelModal() !== null);
    const [reelPlayerOpen, setReelPlayerOpen] = useState(false);
    const number = site.whatsapp;
    const communityHref = site.whatsappCommunityUrl;
    const isTrascendental = Boolean(communityHref);

    const currentPath = useMemo(() => {
        if (typeof url !== 'string' || url === '') {
            return '';
        }

        try {
            return new URL(url, window.location.origin).pathname;
        } catch {
            return '';
        }
    }, [url]);

    const isHomePage = currentPath === '/';
    const isDjSetPage = currentPath === '/dj-set' || currentPath === '/djset';
    const isDronePage = currentPath === '/sesiones-de-dron'
        || currentPath === '/drone-session'
        || currentPath === '/vuelos-con-dron';
    const isConstructionProgressPage = currentPath === '/avances-de-obra';
    const isFoodReelsPage = currentPath === '/reels-de-comida' || currentPath === '/comida-y-reels';

    const serviceType = useMemo(() => {
        if (isConstructionProgressPage) return 'construction_progress';
        if (isDronePage) return 'drone_session';
        if (isDjSetPage) return 'dj_set';
        if (isFoodReelsPage) return 'food_reels';
        if (isHomePage) return 'content_session';

        return isTrascendental ? 'trascendental' : 'site';
    }, [isConstructionProgressPage, isDjSetPage, isDronePage, isFoodReelsPage, isHomePage, isTrascendental]);

    const href = useMemo(() => {
        if (communityHref) {
            return communityHref;
        }

        if (!number) {
            return '';
        }

        const message = isConstructionProgressPage
            ? t('funnel.whatsapp.prefill_construction')
            : isDronePage
              ? t('funnel.whatsapp.prefill_drone')
              : isDjSetPage
                ? t('funnel.whatsapp.prefill_djset')
                : isFoodReelsPage
                  ? t('funnel.whatsapp.prefill_food_reels')
                  : t('common.whatsapp.default_prefill');

        return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
    }, [communityHref, isConstructionProgressPage, isDjSetPage, isDronePage, isFoodReelsPage, number, t]);

    useEffect(() => {
        const onModalState = (event: Event) => {
            const type = (event as CustomEvent<{ type: string | null }>).detail?.type;
            setFunnelModalOpen(type !== null);
        };
        const onReelPlayerState = (event: Event) => {
            setReelPlayerOpen((event as CustomEvent<{ open?: boolean }>).detail?.open === true);
        };

        window.addEventListener(FUNNEL_MODAL_STATE_EVENT, onModalState);
        window.addEventListener(REEL_PLAYER_STATE_EVENT, onReelPlayerState);

        return () => {
            window.removeEventListener(FUNNEL_MODAL_STATE_EVENT, onModalState);
            window.removeEventListener(REEL_PLAYER_STATE_EVENT, onReelPlayerState);
        };
    }, []);

    if (!href || funnelModalOpen || reelPlayerOpen || (isMobile && isHomePage && !isTrascendental)) {
        return null;
    }

    const trackClick = () => {
        trackBookingEvent('whatsapp_popup_clicked', {
            content_name: isTrascendental ? 'Trascendental WhatsApp community' : 'Lapsique Media WhatsApp',
            content_category: serviceType === 'site' || serviceType === 'trascendental'
                ? 'site_contact'
                : `${serviceType}_booking`,
            service_type: serviceType,
            source: 'floating_button',
            target: 'whatsapp',
        });
    };

    return (
        <a
            href={href}
            target="_blank"
            rel="noopener noreferrer"
            onClick={trackClick}
            className="fixed bottom-[calc(1rem+env(safe-area-inset-bottom,0px))] right-4 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-[0_12px_34px_oklch(0.55_0.18_145/0.32)] transition hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2 focus-visible:ring-offset-background sm:bottom-6 sm:right-6"
            aria-label={isTrascendental ? t('trascendental.whatsapp.community_open') : t('common.whatsapp.open')}
        >
            <svg viewBox="0 0 24 24" className="h-7 w-7 fill-current" aria-hidden>
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
            </svg>
        </a>
    );
}
