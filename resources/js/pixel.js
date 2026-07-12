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

    const { event_id, eventID, client_email, client_phone, client_name, customer_email, customer_phone, customer_name, ...rest } =
        payload || {};

    applyAdvancedMatchingOnce({
        em: client_email || customer_email,
        ph: client_phone || customer_phone,
        fn: client_name || customer_name,
        external_id: resolvePixelExternalId(rest),
    });

    const trackOptions = options || (event_id || eventID ? { eventID: event_id || eventID } : undefined);

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

    const { event_id, eventID, client_email, client_phone, client_name, customer_email, customer_phone, customer_name, ...rest } =
        payload || {};

    applyAdvancedMatchingOnce({
        em: client_email || customer_email,
        ph: client_phone || customer_phone,
        fn: client_name || customer_name,
        external_id: resolvePixelExternalId(rest),
    });

    const trackOptions = options || (event_id || eventID ? { eventID: event_id || eventID } : undefined);

    window.fbq('trackCustom', event, rest, trackOptions);
};

function resolvePixelExternalId(rest) {
    if (rest.customer_id != null && rest.customer_id !== '') {
        return String(rest.customer_id);
    }

    if (rest.booking_id) {
        return String(rest.booking_id);
    }

    return undefined;
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
        if (!eventName && (/^https:\/\/wa\.me\//i.test(href) || /^mailto:/i.test(href) || /^tel:/i.test(href))) {
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

        const isDuplicateManagedEvent = lastTrackedStandardEvent?.name === eventName
            && performance.now() - lastTrackedStandardEvent.trackedAt < 250;

        if (isDuplicateManagedEvent) {
            return;
        }

        trackMetaPixel(eventName, {
            content_name: target.getAttribute('data-analytics-label') || target.textContent?.trim().slice(0, 120) || undefined,
            content_category: target.getAttribute('data-analytics-category') || (eventName === 'Contact' ? 'contact' : undefined),
            page_path: window.location.pathname,
            ...params,
        });
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
