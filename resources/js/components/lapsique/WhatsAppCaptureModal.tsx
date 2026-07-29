import { useEffect, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import { CalendarDays, MessageCircle } from 'lucide-react';
import { PremiumSplitDialog } from '@/components/lapsique/PremiumSplitDialog';
import { Button } from '@/components/ui/button';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { markWhatsAppPopupSeen } from '@/lib/funnelModalEvents';
import { openBookingModal } from '@/lib/openBookingModal';
import {
    getPopupWhatsAppPrefillKey,
    getPopupVisualCopy,
    resolvePopupImage,
    type PopupVariant,
} from '@/lib/popupMedia';
import type { HeroProofVideoData, PageProps, PortfolioItemData, VideoItem } from '@/types';

interface WhatsAppCaptureModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    variant: PopupVariant;
    portfolioItems?: PortfolioItemData[];
    heroProofVideo?: HeroProofVideoData | null;
    originals?: VideoItem[];
    source?: string;
}

export function WhatsAppCaptureModal({
    open,
    onOpenChange,
    variant,
    portfolioItems = [],
    heroProofVideo = null,
    originals = [],
    source = 'auto',
}: WhatsAppCaptureModalProps) {
    const { site } = usePage<PageProps>().props;
    const { t, locale } = useTranslations();
    const visual = useMemo(() => getPopupVisualCopy(t, variant, 'whatsapp'), [t, variant]);
    const image = useMemo(
        () =>
            resolvePopupImage(t, {
                variant,
                portfolioItems,
                heroProofVideo,
                originals,
            }),
        [t, variant, portfolioItems, heroProofVideo, originals],
    );

    const whatsappHref = useMemo(() => {
        if (!site.whatsapp) {
            return '';
        }

        return `https://wa.me/${site.whatsapp}?text=${encodeURIComponent(t(getPopupWhatsAppPrefillKey(variant)))}`;
    }, [site.whatsapp, t, variant]);

    useEffect(() => {
        if (!open) {
            return;
        }

        trackBookingEvent('whatsapp_popup_shown', { variant, source });
    }, [open, source, variant]);

    const handleOpenChange = (next: boolean) => {
        if (!next) {
            markWhatsAppPopupSeen();
            trackBookingEvent('whatsapp_popup_dismissed', { variant, source });
        }

        onOpenChange(next);
    };

    const handleWhatsAppClick = () => {
        trackBookingEvent('whatsapp_popup_clicked', { variant, source });
        markWhatsAppPopupSeen();
        onOpenChange(false);
    };

    const handleBookClick = () => {
        onOpenChange(false);
        openBookingModal({
            source: 'whatsapp_popup',
            analyticsEvent: 'whatsapp_popup_booking_clicked',
            analyticsPayload: { variant },
        });
    };

    if (!site.whatsapp) {
        return null;
    }

    const intentMessage = locale === 'en'
        ? source === 'booking_dismissed'
            ? 'Could not find a date? We can help personally on WhatsApp.'
            : source === 'high_engagement'
              ? 'You have seen several projects. Tell us what you want to produce.'
              : source === 'scroll_depth'
                ? 'Like what you saw? We can shape a proposal with you.'
                : 'Tell us about your project and get guidance with no commitment.'
        : source === 'booking_dismissed'
          ? '¿No encontraste fecha? Te ayudamos personalmente por WhatsApp.'
          : source === 'high_engagement'
            ? 'Ya viste varios trabajos. Cuéntanos qué quieres producir.'
            : source === 'scroll_depth'
              ? '¿Te gustó lo que viste? Aterrizamos una propuesta contigo.'
              : 'Cuéntanos tu proyecto y te orientamos sin compromiso.';

    return (
        <PremiumSplitDialog
            open={open}
            onOpenChange={handleOpenChange}
            layout="promo"
            imageUrl={image.url}
            imageAlt={image.alt}
            title={visual.title}
            description={visual.description}
            contentClassName="px-4 py-4 sm:px-5 sm:py-5"
        >
            <div className="flex min-h-0 flex-col justify-center space-y-5">
                <div className="border-l-2 border-[#25D366] pl-3 text-sm font-semibold leading-relaxed text-foreground">
                    {intentMessage}
                </div>
                <p className="text-sm leading-relaxed text-muted-foreground">
                    {locale === 'en'
                        ? 'Send the location, tentative date, and type of content. We will reply with a concrete next step.'
                        : 'Envía la ubicación, fecha tentativa y tipo de contenido. Te respondemos con el siguiente paso concreto.'}
                </p>

                <div className="space-y-3">
                    <Button
                        asChild
                        className="w-full bg-[#25D366] text-[#04150a] hover:bg-[#20bd5a] hover:text-[#04150a]"
                    >
                        <a
                            href={whatsappHref}
                            target="_blank"
                            rel="noopener noreferrer"
                            onClick={handleWhatsAppClick}
                        >
                            <MessageCircle className="h-4 w-4" />
                            {t('funnel.whatsapp.cta_write')}
                        </a>
                    </Button>

                    <Button type="button" variant="outline" className="w-full rounded-none" onClick={handleBookClick}>
                        <CalendarDays className="h-4 w-4" />
                        {t('funnel.whatsapp.cta_book')}
                    </Button>
                </div>
            </div>
        </PremiumSplitDialog>
    );
}
