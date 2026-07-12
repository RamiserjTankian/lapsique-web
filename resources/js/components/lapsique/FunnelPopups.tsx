import { usePage } from '@inertiajs/react';
import { NewsletterCaptureModal } from '@/components/lapsique/NewsletterCaptureModal';
import { WhatsAppCaptureModal } from '@/components/lapsique/WhatsAppCaptureModal';
import { useBookingAutoOpenListener, useBookingPopupTrigger } from '@/hooks/useBookingPopupTrigger';
import { useNewsletterPopupTrigger } from '@/hooks/useNewsletterPopupTrigger';
import { useWhatsAppPopupTrigger } from '@/hooks/useWhatsAppPopupTrigger';
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
    const { customer, site } = usePage<PageProps>().props;
    const skipNewsletter = Boolean(customer?.email);

    const { open, setOpen, source: newsletterSource } = useNewsletterPopupTrigger({
        enabled: true,
        skipIfLoggedIn: skipNewsletter,
    });

    const {
        open: whatsappOpen,
        setOpen: setWhatsappOpen,
        source: whatsappSource,
    } = useWhatsAppPopupTrigger({
        enabled: Boolean(site.whatsapp),
        whatsapp: site.whatsapp,
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
        <>
            <NewsletterCaptureModal
                open={open}
                onOpenChange={setOpen}
                variant={variant}
                portfolioItems={portfolioItems}
                heroProofVideo={heroProofVideo}
                originals={originals}
                source={newsletterSource}
            />
            <WhatsAppCaptureModal
                open={whatsappOpen}
                onOpenChange={setWhatsappOpen}
                variant={variant}
                portfolioItems={portfolioItems}
                heroProofVideo={heroProofVideo}
                originals={originals}
                source={whatsappSource}
            />
        </>
    );
}
