import type { BookingWidgetProduct } from '@/components/lapsique/BookingWidget';
import {
    CONTENT_DELIVERY_BUSINESS_DAYS,
    CONTENT_DRONE_SHOTS,
    CONTENT_PHOTOS_COUNT,
    CONTENT_REEL_DURATION_SECONDS,
    getContentOfferCheckoutSummaryLines,
    getContentOfferOneLiner,
    getContentSessionDuration,
} from '@/data/contentOffer';
import type { TranslateFn } from '@/hooks/useTranslations';

function contentReplacements(t: TranslateFn) {
    const duration = getContentSessionDuration(t);

    return {
        seconds: CONTENT_REEL_DURATION_SECONDS,
        drone_shots: CONTENT_DRONE_SHOTS,
        photos_count: CONTENT_PHOTOS_COUNT,
        duration,
    };
}

export function getContentSessionProduct(t: TranslateFn): BookingWidgetProduct {
    const replacements = contentReplacements(t);
    const sessionDuration = replacements.duration;

    return {
        checkoutLabel: t('booking.product.checkout_label'),
        headerTitle: t('booking.product.header_title'),
        headerDescription: t('booking.product.header_description', replacements),
        summaryTitle: t('booking.product.summary_title'),
        summaryDescription: getContentOfferOneLiner(t),
        summaryDescriptionLines: getContentOfferCheckoutSummaryLines(t),
        cartService: getContentOfferOneLiner(t),
        cartDuration: sessionDuration,
        summaryPerks: [
            t('booking.product.perk_reel', replacements),
            t('booking.product.perk_drone', replacements),
            t('booking.product.perk_photos', replacements),
            t('booking.product.perk_session', replacements),
            t('booking.product.perk_sony'),
            t('booking.product.perk_delivery', { business_days: CONTENT_DELIVERY_BUSINESS_DAYS }),
        ],
        terms: [
            t('booking.terms.availability'),
            t('booking.terms.duration', { duration: sessionDuration }),
            t('booking.terms.inclusions', replacements),
            t('booking.terms.reschedule'),
            t('booking.terms.portfolio_use'),
        ],
        paymentCopy: t('booking.payment.stripe_copy'),
        unavailableWhatsApp: t('booking.whatsapp.unavailable_content'),
    };
}

export function getDjSetProduct(t: TranslateFn): BookingWidgetProduct {
    return {
        checkoutLabel: t('booking.djset.checkout_label'),
        headerTitle: t('booking.djset.header_title'),
        headerDescription: t('booking.djset.header_description'),
        summaryTitle: t('booking.djset.summary_title'),
        summaryDescription: t('booking.djset.summary_description'),
        cartService: t('booking.djset.cart_service'),
        cartDuration: t('booking.djset.cart_duration'),
        summaryPerks: [
            t('booking.djset.perk_cameras'),
            t('booking.djset.perk_drone'),
            t('booking.djset.perk_final_video'),
            t('booking.djset.perk_dj_focus'),
            t('booking.djset.perk_stripe'),
        ],
        terms: [
            t('booking.djset.terms.availability'),
            t('booking.djset.terms.inclusions'),
            t('booking.djset.terms.scope'),
            t('booking.djset.terms.reschedule'),
            t('booking.djset.terms.portfolio_use'),
        ],
        paymentCopy: t('booking.payment.stripe_copy'),
        unavailableWhatsApp: t('booking.whatsapp.unavailable_djset'),
    };
}
