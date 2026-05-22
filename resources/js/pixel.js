const pixelConfig = window.LapsiquePixel || {};
const pixelQueue = Array.isArray(window.__lapsiquePixelQueue) ? window.__lapsiquePixelQueue : [];
let advancedMatchingApplied = false;

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
}

window.trackMetaPixel = trackMetaPixel;
window.trackMetaPixelCustom = function trackMetaPixelCustom(event, payload) {
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

    const trackOptions = event_id || eventID ? { eventID: event_id || eventID } : undefined;

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
        const target = event.target?.closest?.('[data-meta-event]');
        if (!target) {
            return;
        }

        const eventName = target.getAttribute('data-meta-event');
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

        trackMetaPixel(eventName, params);
    });
}

function flushQueuedPixelCalls() {
    pixelQueue.forEach((queuedCall) => {
        if (!queuedCall?.method || !queuedCall.eventName) {
            return;
        }

        if (queuedCall.method === 'trackCustom') {
            window.trackMetaPixelCustom(queuedCall.eventName, queuedCall.payload || {});

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
