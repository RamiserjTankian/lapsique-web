/** Shared sales copy for the content-session landing funnel (Home). */

import type { TranslateFn } from '@/hooks/useTranslations';

export const CONTENT_REEL_DURATION_SECONDS = 30;
export const CONTENT_DRONE_SHOTS = 3;
export const CONTENT_PHOTOS_COUNT = 10;

/** Preview loops on landing (shorter clips for performance). */
export const LANDING_VIDEO_LOOP_SECONDS = 6;

/** Delay before showing Agendar CTA in full-screen reel player modal. */
export const REEL_PLAYER_CTA_DELAY_SECONDS = 3;

export const CONTENT_DELIVERY_BUSINESS_DAYS = 3;

export function getContentSessionDuration(t: TranslateFn): string {
    return t('funnel.offer.session_duration');
}

export function getContentOfferOneLiner(t: TranslateFn): string {
    return t('funnel.offer.one_liner', {
        seconds: CONTENT_REEL_DURATION_SECONDS,
        drone_shots: CONTENT_DRONE_SHOTS,
        photos_count: CONTENT_PHOTOS_COUNT,
    });
}

export function getContentOfferShort(t: TranslateFn): string {
    return t('funnel.offer.short', {
        seconds: CONTENT_REEL_DURATION_SECONDS,
        photos_count: CONTENT_PHOTOS_COUNT,
    });
}

export function getContentReelDescription(t: TranslateFn): string {
    return t('funnel.offer.reel_description');
}

export function getContentDroneDescription(t: TranslateFn): string {
    return t('funnel.offer.drone_description');
}

export function getContentOfferCheckoutSummaryLines(t: TranslateFn): string[] {
    return [
        getContentOfferOneLiner(t),
        t('funnel.offer.checkout_line_videomaker'),
        t('funnel.offer.checkout_line_meeting'),
        t('funnel.offer.checkout_line_delivery', { days: CONTENT_DELIVERY_BUSINESS_DAYS }),
    ];
}
