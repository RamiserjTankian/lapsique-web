const pixelConfig = window.SitePixel || window.LapsiquePixel || {};
const pixelQueue = [
    ...(Array.isArray(window.__sitePixelQueue) ? window.__sitePixelQueue : []),
    ...(Array.isArray(window.__lapsiquePixelQueue) ? window.__lapsiquePixelQueue : []),
];
let advancedMatchingApplied = false;
let lastTrackedStandardEvent = null;

function trackMetaPixel(event, payload, options) {
    if (!pixelConfig.enabled || typeof window.fbq !== 'function') {
        return;
    }

    const {
        event_id,
        eventID,
        client_email,
        client_phone,
        client_name,
        customer_email,
        customer_phone,
        customer_name,
        external_id,
        customer_id,
        booking_id,
        ticket_order_id,
        ...rest
    } = payload || {};
    const deduplicationId = event_id || eventID || options?.eventID;

    applyAdvancedMatchingOnce({
        em: client_email || customer_email,
        ph: client_phone || customer_phone,
        fn: client_name || customer_name,
        external_id: resolvePixelExternalId({
            external_id,
            customer_id,
            booking_id,
            ticket_order_id,
        }, deduplicationId),
    });

    const trackOptions = options || (deduplicationId ? { eventID: deduplicationId } : undefined);

    window.fbq('track', event, rest, trackOptions);
    lastTrackedStandardEvent = {
        name: event,
        trackedAt: performance.now(),
    };
}

window.trackMetaPixel = trackMetaPixel;
window.trackMetaPixelCustom = function trackMetaPixelCustom(event, payload, options) {
    if (!pixelConfig.enabled || typeof window.fbq !== 'function') {
        return;
    }

    const {
        event_id,
        eventID,
        client_email,
        client_phone,
        client_name,
        customer_email,
        customer_phone,
        customer_name,
        external_id,
        customer_id,
        booking_id,
        ticket_order_id,
        ...rest
    } = payload || {};
    const deduplicationId = event_id || eventID || options?.eventID;

    applyAdvancedMatchingOnce({
        em: client_email || customer_email,
        ph: client_phone || customer_phone,
        fn: client_name || customer_name,
        external_id: resolvePixelExternalId({
            external_id,
            customer_id,
            booking_id,
            ticket_order_id,
        }, deduplicationId),
    });

    const trackOptions = options || (deduplicationId ? { eventID: deduplicationId } : undefined);

    window.fbq('trackCustom', event, rest, trackOptions);
};

function resolvePixelExternalId(identifiers, eventId) {
    if (identifiers.external_id != null && identifiers.external_id !== '') {
        return String(identifiers.external_id);
    }

    if (identifiers.customer_id != null && identifiers.customer_id !== '') {
        return withPrefix('customer_', identifiers.customer_id);
    }

    if (identifiers.booking_id) {
        return withPrefix('booking_', identifiers.booking_id);
    }

    if (identifiers.ticket_order_id) {
        return withPrefix('ticket_order_', identifiers.ticket_order_id);
    }

    if (typeof eventId === 'string') {
        const leadCustomerId = eventId.match(/^lead_customer_(.+)$/)?.[1];
        if (leadCustomerId) {
            return withPrefix('customer_', leadCustomerId);
        }

        if (/^ticket_order_.+/.test(eventId) || /^booking_(?!checkout_|payment_|abandoned_).+/.test(eventId)) {
            return eventId;
        }
    }

    return undefined;
}

function withPrefix(prefix, value) {
    const normalized = String(value);

    return normalized.startsWith(prefix) ? normalized : `${prefix}${normalized}`;
}

flushQueuedPixelCalls();

if (pixelConfig.autoTrack) {
    document.addEventListener('click', (event) => {
        const target = event.target?.closest?.('[data-meta-event], a[href]');
        if (!target) {
            return;
        }

        let eventName = target.getAttribute('data-meta-event');
        const href = target.getAttribute('href') || '';
        const isWhatsAppLink = /^https:\/\/(wa\.me|api\.whatsapp\.com)\//i.test(href);
        if (!eventName && (isWhatsAppLink || /^mailto:/i.test(href) || /^tel:/i.test(href))) {
            eventName = 'Contact';
        }
        if (!eventName) {
            return;
        }

        let params = {};
        const rawParams = target.getAttribute('data-meta-params');
        if (rawParams) {
            try {
                params = JSON.parse(rawParams);
            } catch (error) {
                params = {};
            }
        }

        const clickPayload = {
            content_name: target.getAttribute('data-analytics-label') || target.textContent?.trim().slice(0, 120) || undefined,
            content_category: target.getAttribute('data-analytics-category') || (eventName === 'Contact' ? 'contact' : undefined),
            page_path: window.location.pathname,
            ...params,
        };
        const isDuplicateManagedEvent = lastTrackedStandardEvent?.name === eventName
            && performance.now() - lastTrackedStandardEvent.trackedAt < 250;

        if (isDuplicateManagedEvent) {
            return;
        }

        // Un click en un CTA expresa intención, pero todavía no es un lead registrado.
        if (eventName === 'Lead') {
            window.trackMetaPixelCustom('LeadCtaClick', clickPayload);

            return;
        }

        trackMetaPixel(eventName, clickPayload);

        if (isWhatsAppLink) {
            window.trackMetaPixelCustom('WhatsAppClick', {
                ...clickPayload,
                contact_channel: 'whatsapp',
            });
        }
    });
}

function flushQueuedPixelCalls() {
    pixelQueue.forEach((queuedCall) => {
        if (!queuedCall?.method || !queuedCall.eventName) {
            return;
        }

        if (queuedCall.method === 'trackCustom') {
            window.trackMetaPixelCustom(queuedCall.eventName, queuedCall.payload || {}, queuedCall.options);

            return;
        }

        trackMetaPixel(queuedCall.eventName, queuedCall.payload || {}, queuedCall.options);
    });
}

function applyAdvancedMatchingOnce(fields) {
    const advancedMatching = {};

    if (fields.em) {
        advancedMatching.em = fields.em;
    }
    if (fields.ph) {
        advancedMatching.ph = fields.ph;
    }
    if (fields.fn) {
        advancedMatching.fn = fields.fn;
    }
    if (fields.external_id) {
        advancedMatching.external_id = fields.external_id;
    }

    if (!pixelConfig.id || Object.keys(advancedMatching).length === 0) {
        return;
    }

    if (advancedMatchingApplied) {
        return;
    }

    advancedMatchingApplied = true;
    window.fbq('init', pixelConfig.id, advancedMatching);
}
