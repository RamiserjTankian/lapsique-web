import {
    isFunnelContentEngagementEvent,
    recordFunnelContentEngagement,
} from '@/lib/funnelEngagement';

declare global {
    interface Window {
        SiteTracker?: {
            track: (name: string, options?: Record<string, unknown>) => void;
            getContext?: () => Record<string, unknown>;
            pageview?: (overrides?: Record<string, unknown>) => void;
        };
        trackMetaPixel?: (
            event: string,
            data?: Record<string, unknown>,
            options?: { eventID?: string },
        ) => void;
        trackMetaPixelCustom?: (
            event: string,
            data?: Record<string, unknown>,
            options?: { eventID?: string },
        ) => void;
    }
}

const BOOKING_CONTENT = {
    content_category: 'content_booking',
    content_name: 'Sesion de contenido Lapsique Media',
} as const;

/** Eventos internos que también disparan un evento estándar de Meta (para optimización de campañas). */
const STANDARD_EVENTS: Record<string, string> = {
    booking_page_viewed: 'ViewContent',
    deliverables_viewed: 'ViewContent',
    process_viewed: 'ViewContent',
    proof_section_viewed: 'ViewContent',
    equipment_viewed: 'ViewContent',
    booking_widget_viewed: 'ViewContent',
    gear_section_viewed: 'ViewContent',
    workflow_section_viewed: 'ViewContent',
    package_includes_viewed: 'ViewContent',
    reel_card_clicked: 'ViewContent',
    hero_cta_clicked: 'Lead',
    header_cta_clicked: 'Lead',
    sticky_cta_clicked: 'Lead',
    booking_cta_clicked: 'Lead',
    reel_overlay_cta_clicked: 'Lead',
    reel_player_agendar_clicked: 'Lead',
    booking_date_selected: 'Schedule',
    booking_slot_selected: 'AddToCart',
    booking_form_started: 'Lead',
    booking_payment_info_added: 'AddPaymentInfo',
    booking_checkout_started: 'InitiateCheckout',
    booking_confirmed: 'Purchase',
    djset_booking_cta_clicked: 'Lead',
    djset_whatsapp_cta_clicked: 'Contact',
    drone_session_page_viewed: 'ViewContent',
    drone_session_booking_cta_clicked: 'Lead',
    drone_session_whatsapp_cta_clicked: 'Contact',
    construction_progress_page_viewed: 'ViewContent',
    construction_progress_booking_cta_clicked: 'Lead',
    construction_progress_whatsapp_cta_clicked: 'Contact',
    whatsapp_popup_clicked: 'Contact',
};

/** Sin evento estándar: no contaminar optimización (modo prueba o eventos puramente internos). */
const CUSTOM_ONLY_EVENTS = new Set([
    'booking_test_confirmed',
    'booking_form_submitted',
    'booking_abandoned',
    'booking_slot_cleared',
    'booking_popup_shown',
    'booking_payment_pending',
    'booking_payment_failed',
    'faq_opened',
    'whatsapp_popup_shown',
    'whatsapp_popup_dismissed',
]);

/** IDs alineados con Meta CAPI (MetaConversionsApiService). */
export function bookingMetaEventId(kind: 'checkout' | 'purchase', publicId?: string | null): string | undefined {
    if (!publicId) {
        return undefined;
    }

    if (kind === 'purchase') {
        return `booking_${publicId}`;
    }

    return `booking_checkout_${publicId}`;
}

export function trackBookingEvent(
    event: string,
    data?: Record<string, unknown>,
): void {
    const basePayload = {
        ...BOOKING_CONTENT,
        ...data,
    };

    trackBookingMetaEvent(event, basePayload);
    trackBookingInternalEvent(event, basePayload);
}

function trackBookingInternalEvent(event: string, data: Record<string, unknown>): void {
    if (isFunnelContentEngagementEvent(event)) {
        const section = typeof data.section === 'string' ? data.section : event;
        recordFunnelContentEngagement(section);
    }

    if (window.SiteTracker) {
        window.SiteTracker.track(event, {
            category: 'booking_funnel',
            metadata: data,
        });
    }
}

function trackBookingMetaEvent(event: string, payload: Record<string, unknown>): void {
    const metaEvent = STANDARD_EVENTS[event];
    const eventId = resolveMetaEventId(event, payload);

    if (
        metaEvent &&
        !CUSTOM_ONLY_EVENTS.has(event) &&
        typeof window.trackMetaPixel === 'function' &&
        shouldFireStandardMetaEvent(event, payload)
    ) {
        const { event_id: _eid, eventID: _eID, ...pixelPayload } = payload;
        window.trackMetaPixel(
            metaEvent,
            normalizeMetaPayload(metaEvent, pixelPayload),
            eventId ? { eventID: eventId } : undefined,
        );
    }

    if (typeof window.trackMetaPixelCustom === 'function') {
        const customPayload = eventId ? { ...payload, event_id: eventId } : payload;
        window.trackMetaPixelCustom(event, customPayload);
    }
}

function shouldFireStandardMetaEvent(event: string, payload: Record<string, unknown>): boolean {
    // En modo prueba (sin pago real) no disparamos InitiateCheckout estándar
    // para no contaminar la optimización de campañas; el CAPI tampoco lo envía.
    if (event === 'booking_checkout_started' || event === 'booking_payment_info_added') {
        return !payload.skip_payment;
    }

    if (event !== 'booking_confirmed' && event !== 'booking_test_confirmed') {
        return true;
    }

    const publicId = payload.booking_id ?? payload.public_id;

    return typeof publicId === 'string' && publicId !== '';
}

function resolveMetaEventId(event: string, payload: Record<string, unknown>): string | undefined {
    const explicit = payload.event_id ?? payload.eventID;
    if (typeof explicit === 'string' && explicit !== '') {
        return explicit;
    }

    const publicId = payload.booking_id ?? payload.public_id;
    if (typeof publicId !== 'string' || publicId === '') {
        return undefined;
    }

    switch (event) {
        case 'booking_checkout_started':
        case 'booking_form_submitted':
            return bookingMetaEventId('checkout', publicId);
        case 'booking_confirmed':
        case 'booking_test_confirmed':
            return bookingMetaEventId('purchase', publicId);
        default:
            return undefined;
    }
}

function normalizeMetaPayload(event: string, payload: Record<string, unknown>): Record<string, unknown> {
    if (
        event === 'Purchase'
        || event === 'InitiateCheckout'
        || event === 'AddToCart'
        || event === 'AddPaymentInfo'
    ) {
        return {
            currency: payload.currency || 'MXN',
            value: payload.value ?? payload.amount,
            content_ids: payload.booking_id ? [payload.booking_id] : undefined,
            content_type: 'product',
            content_name: payload.content_name ?? BOOKING_CONTENT.content_name,
            content_category: payload.content_category ?? BOOKING_CONTENT.content_category,
            customer_id: payload.customer_id,
            payment_provider: payload.payment_provider,
        };
    }

    if (event === 'ViewContent') {
        const contentId =
            typeof payload.src === 'string' ? payload.src.split('/').pop()?.split('?')[0] : undefined;

        return {
            content_name:
                payload.section ??
                payload.title ??
                payload.content_name ??
                BOOKING_CONTENT.content_name,
            content_category: payload.content_category ?? BOOKING_CONTENT.content_category,
            content_ids: contentId ? [contentId] : undefined,
            content_type: contentId ? 'video' : undefined,
        };
    }

    return payload;
}
