import { usePage } from '@inertiajs/react';
import { NewsletterCaptureModal } from '@/components/lapsique/NewsletterCaptureModal';
import { useBookingAutoOpenListener, useBookingPopupTrigger } from '@/hooks/useBookingPopupTrigger';
import { useNewsletterPopupTrigger } from '@/hooks/useNewsletterPopupTrigger';
import { openBookingModal } from '@/lib/openBookingModal';
import type { HeroProofVideoData, PageProps, PortfolioItemData, VideoItem } from '@/types';
import type { PopupVariant } from '@/lib/popupMedia';

interface FunnelPopupsProps {
    variant: PopupVariant;
    slotsCount: number;
    portfolioItems: PortfolioItemData[];
    heroProofVideo?: HeroProofVideoData | null;
    originals?: VideoItem[];
}

export function FunnelPopups({
    variant,
    slotsCount,
    portfolioItems,
    heroProofVideo = null,
    originals = [],
}: FunnelPopupsProps) {
    const { customer } = usePage<PageProps>().props;
    const skipNewsletter = Boolean(customer?.email);

    const { open, setOpen } = useNewsletterPopupTrigger({
        enabled: true,
        skipIfLoggedIn: skipNewsletter,
    });

    useBookingPopupTrigger(slotsCount > 0);

    useBookingAutoOpenListener((source) => {
        openBookingModal({
            source,
            analyticsEvent: 'booking_popup_auto',
            analyticsPayload: { variant },
        });
    });

    return (
        <NewsletterCaptureModal
            open={open}
            onOpenChange={setOpen}
            variant={variant}
            portfolioItems={portfolioItems}
            heroProofVideo={heroProofVideo}
            originals={originals}
        />
    );
}
