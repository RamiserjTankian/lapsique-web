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

export function getDroneSessionProduct(t: TranslateFn): BookingWidgetProduct {
    return {
        checkoutLabel: t('booking.drone.checkout_label'),
        headerTitle: t('booking.drone.header_title'),
        headerDescription: t('booking.drone.header_description'),
        summaryTitle: t('booking.drone.summary_title'),
        summaryDescription: t('booking.drone.summary_description'),
        summaryDescriptionLines: [
            t('booking.drone.checkout_line_coverage'),
            t('booking.drone.checkout_line_color'),
            t('booking.drone.checkout_line_usecases'),
        ],
        cartService: t('booking.drone.cart_service'),
        cartDuration: t('booking.drone.cart_duration'),
        summaryPerks: [
            t('booking.drone.perk_shots'),
            t('booking.drone.perk_photos'),
            t('booking.drone.perk_air3'),
            t('booking.drone.perk_color'),
            t('booking.drone.perk_range'),
            t('booking.drone.perk_construction'),
        ],
        terms: [
            t('booking.drone.terms.availability'),
            t('booking.drone.terms.inclusions'),
            t('booking.drone.terms.scope'),
            t('booking.drone.terms.reschedule'),
            t('booking.drone.terms.portfolio_use'),
        ],
        paymentCopy: t('booking.payment.stripe_copy'),
        unavailableWhatsApp: t('booking.whatsapp.unavailable_drone'),
    };
}

export function getConstructionProgressProduct(t: TranslateFn): BookingWidgetProduct {
    return {
        checkoutLabel: t('booking.construction.checkout_label'),
        headerTitle: t('booking.construction.header_title'),
        headerDescription: t('booking.construction.header_description'),
        summaryTitle: t('booking.construction.summary_title'),
        summaryDescription: t('booking.construction.summary_description'),
        summaryDescriptionLines: [
            t('booking.construction.checkout_line_progress'),
            t('booking.construction.checkout_line_color'),
            t('booking.construction.checkout_line_context'),
        ],
        cartService: t('booking.construction.cart_service'),
        cartDuration: t('booking.construction.cart_duration'),
        summaryPerks: [
            t('booking.construction.perk_flight'),
            t('booking.construction.perk_shots'),
            t('booking.construction.perk_photos'),
            t('booking.construction.perk_context'),
            t('booking.construction.perk_color'),
            t('booking.construction.perk_reports'),
        ],
        terms: [
            t('booking.construction.terms.availability'),
            t('booking.construction.terms.inclusions'),
            t('booking.construction.terms.scope'),
            t('booking.construction.terms.reschedule'),
            t('booking.construction.terms.portfolio_use'),
        ],
        paymentCopy: t('booking.payment.stripe_copy'),
        unavailableWhatsApp: t('booking.whatsapp.unavailable_construction'),
    };
}
