declare global {
    interface Window {
        LapsiqueTracker?: {
            track: (name: string, options?: Record<string, unknown>) => void;
            getContext?: () => Record<string, unknown>;
            pageview?: (overrides?: Record<string, unknown>) => void;
        };
        trackMetaPixel?: (
            event: string,
            data?: Record<string, unknown>,
            options?: { eventID?: string },
        ) => void;
        trackMetaPixelCustom?: (event: string, data?: Record<string, unknown>) => void;
    }
}

export function trackNewsletterEvent(
    name: string,
    payload: Record<string, unknown> = {},
): void {
    const {
        client_email: _clientEmail,
        client_phone: _clientPhone,
        client_name: _clientName,
        customer_email: _customerEmail,
        customer_phone: _customerPhone,
        customer_name: _customerName,
        ...internalPayload
    } = payload;

    window.LapsiqueTracker?.track(name, internalPayload);

    if (name === 'newsletter_form_submitted') {
        const eventID = typeof payload.event_id === 'string'
            ? payload.event_id
            : (typeof payload.eventID === 'string' ? payload.eventID : undefined);
        const { event_id: _eventId, eventID: _eventID, ...metaPayload } = payload;

        window.trackMetaPixel?.('Lead', {
            content_name: 'Newsletter signup',
            content_category: 'newsletter_popup',
            ...metaPayload,
        }, eventID ? { eventID } : undefined);
    } else if (name === 'newsletter_popup_shown') {
        window.trackMetaPixelCustom?.('newsletter_popup_shown', payload);
    }
}
