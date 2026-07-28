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
        SitePixel?: {
            enabled?: boolean;
            id?: string | null;
            trackPageView?: boolean;
        };
        gtag?: (...args: unknown[]) => void;
    }
}

const BOOKING_CONTENT = {
    content_category: 'content_booking',
    content_name: 'Sesion de contenido Lapsique Media',
} as const;

/**
 * Eventos estándar reservados para hitos reales. La navegación, aperturas, selección
 * de fecha y clicks de CTA se conservan como eventos personalizados.
 */
const STANDARD_EVENTS: Record<string, string> = {
    booking_page_viewed: 'ViewContent',
    booking_payment_info_added: 'AddPaymentInfo',
    booking_checkout_started: 'InitiateCheckout',
    booking_confirmed: 'Purchase',
    djset_page_viewed: 'ViewContent',
    djset_whatsapp_cta_clicked: 'Contact',
    food_reels_page_viewed: 'ViewContent',
    food_reels_whatsapp_cta_clicked: 'Contact',
    drone_session_page_viewed: 'ViewContent',
    drone_session_whatsapp_cta_clicked: 'Contact',
    construction_progress_page_viewed: 'ViewContent',
    construction_progress_whatsapp_cta_clicked: 'Contact',
    electronic_event_coverage_page_viewed: 'ViewContent',
    multi_camera_page_viewed: 'ViewContent',
    electronic_event_coverage_whatsapp_cta_clicked: 'Contact',
    multi_camera_whatsapp_cta_clicked: 'Contact',
    content_creation_whatsapp_cta_clicked: 'Contact',
    service_landing_whatsapp_clicked: 'Contact',
    service_landing_lead_form_submitted: 'Lead',
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

    trackGaEvent(event, data);
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

        const alias = CUSTOM_META_EVENT_ALIASES[event];
        if (alias) {
            window.trackMetaPixelCustom(alias, customPayload);
        }
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
            booking_id: payload.booking_id,
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

function trackGaEvent(event: string, payload: Record<string, unknown>): void {
    if (typeof window.gtag !== 'function') {
        return;
    }

    const gaName = GA_EVENT_NAMES[event] ?? event;
    window.gtag('event', gaName, {
        service_name: payload.service_name ?? payload.service ?? payload.service_type,
        service_area: payload.service_area ?? 'riviera_maya',
        landing_path: payload.landing ?? window.location.pathname,
        form_name: payload.form_name,
        source: payload.source,
        lead_type: payload.lead_type,
        contact_channel: payload.contact_channel ?? (event.includes('whatsapp') ? 'whatsapp' : undefined),
        transaction_id: payload.booking_id ?? payload.public_id,
        value: payload.value ?? payload.amount,
        currency: payload.currency,
        payment_provider: payload.payment_provider,
        coverage_id: payload.coverage_id,
        content_id: payload.asset_id,
        content_format: payload.format,
        video_percent: payload.percent,
        video_duration: payload.duration_seconds,
        item_position: payload.position,
    });
}

const GA_EVENT_NAMES: Record<string, string> = {
    djset_page_viewed: 'view_service_landing',
    food_reels_page_viewed: 'view_service_landing',
    drone_session_page_viewed: 'view_service_landing',
    construction_progress_page_viewed: 'view_service_landing',
    electronic_event_coverage_page_viewed: 'view_service_landing',
    multi_camera_page_viewed: 'view_service_landing',
    content_creation_booking_cta_clicked: 'booking_cta_click',
    electronic_event_coverage_booking_cta_clicked: 'booking_cta_click',
    multi_camera_booking_cta_clicked: 'booking_cta_click',
    electronic_event_coverage_booking_opened: 'booking_open',
    multi_camera_booking_opened: 'booking_open',
    electronic_event_coverage_portfolio_engaged: 'portfolio_engagement',
    multi_camera_portfolio_engaged: 'portfolio_engagement',
    multi_camera_workflow_viewed: 'view_item_list',
    multi_camera_gear_viewed: 'view_item_list',
    multi_camera_package_viewed: 'view_item',
    multi_camera_coverage_selected: 'select_content',
    multi_camera_format_selected: 'select_content_format',
    multi_camera_video_started: 'video_start',
    multi_camera_video_progress: 'video_progress',
    multi_camera_video_completed: 'video_complete',
    multi_camera_video_navigated: 'video_navigate',
    multi_camera_audio_toggled: 'video_audio_toggle',
    multi_camera_video_fullscreen_opened: 'video_fullscreen',
    djset_whatsapp_cta_clicked: 'whatsapp_click',
    food_reels_whatsapp_cta_clicked: 'whatsapp_click',
    drone_session_whatsapp_cta_clicked: 'whatsapp_click',
    construction_progress_whatsapp_cta_clicked: 'whatsapp_click',
    electronic_event_coverage_whatsapp_cta_clicked: 'whatsapp_click',
    multi_camera_whatsapp_cta_clicked: 'whatsapp_click',
    content_creation_whatsapp_cta_clicked: 'whatsapp_click',
    service_landing_whatsapp_clicked: 'whatsapp_click',
    whatsapp_popup_clicked: 'whatsapp_click',
    service_landing_lead_form_submitted: 'generate_lead',
    booking_checkout_started: 'begin_checkout',
    booking_payment_info_added: 'add_payment_info',
    booking_confirmed: 'purchase',
};

const CUSTOM_META_EVENT_ALIASES: Record<string, string> = {
    hero_cta_clicked: 'BookingCtaClick',
    header_cta_clicked: 'BookingCtaClick',
    sticky_cta_clicked: 'BookingCtaClick',
    booking_cta_clicked: 'BookingCtaClick',
    reel_overlay_cta_clicked: 'BookingCtaClick',
    reel_player_agendar_clicked: 'BookingCtaClick',
    djset_booking_cta_clicked: 'BookingCtaClick',
    food_reels_booking_cta_clicked: 'BookingCtaClick',
    drone_session_booking_cta_clicked: 'BookingCtaClick',
    construction_progress_booking_cta_clicked: 'BookingCtaClick',
    electronic_event_coverage_booking_cta_clicked: 'BookingCtaClick',
    multi_camera_booking_cta_clicked: 'BookingCtaClick',
    electronic_event_coverage_booking_opened: 'BookingOpened',
    multi_camera_booking_opened: 'BookingOpened',
    electronic_event_coverage_portfolio_engaged: 'PortfolioEngaged',
    multi_camera_portfolio_engaged: 'PortfolioEngaged',
    multi_camera_workflow_viewed: 'WorkflowViewed',
    multi_camera_gear_viewed: 'EquipmentViewed',
    multi_camera_package_viewed: 'PackageViewed',
    multi_camera_coverage_selected: 'CoverageSelected',
    multi_camera_format_selected: 'VideoFormatSelected',
    multi_camera_video_started: 'VideoStarted',
    multi_camera_video_progress: 'VideoProgress',
    multi_camera_video_completed: 'VideoCompleted',
    multi_camera_video_navigated: 'VideoNavigated',
    multi_camera_audio_toggled: 'VideoAudioToggled',
    multi_camera_video_fullscreen_opened: 'VideoFullscreenOpened',
    content_creation_booking_cta_clicked: 'BookingCtaClick',
    booking_date_selected: 'BookingDateSelected',
    booking_slot_selected: 'BookingSlotSelected',
    booking_form_started: 'BookingFormStarted',
    booking_form_submitted: 'BookingFormSubmitted',
    booking_checkout_started: 'BookingCheckoutStarted',
    booking_confirmed: 'BookingConfirmed',
    service_landing_lead_form_submitted: 'LeadSubmitted',
    djset_whatsapp_cta_clicked: 'WhatsAppClick',
    food_reels_whatsapp_cta_clicked: 'WhatsAppClick',
    drone_session_whatsapp_cta_clicked: 'WhatsAppClick',
    construction_progress_whatsapp_cta_clicked: 'WhatsAppClick',
    electronic_event_coverage_whatsapp_cta_clicked: 'WhatsAppClick',
    multi_camera_whatsapp_cta_clicked: 'WhatsAppClick',
    content_creation_whatsapp_cta_clicked: 'WhatsAppClick',
    service_landing_whatsapp_clicked: 'WhatsAppClick',
    whatsapp_popup_clicked: 'WhatsAppClick',
};
