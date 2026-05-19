const pixelConfig = window.LapsiquePixel || {};
const pixelQueue = Array.isArray(window.__lapsiquePixelQueue) ? window.__lapsiquePixelQueue : [];

function trackMetaPixel(event, payload) {
    if (!pixelConfig.enabled || typeof window.fbq !== 'function') {
        return;
    }

    window.fbq('track', event, payload || {});
}

window.trackMetaPixel = trackMetaPixel;
window.trackMetaPixelCustom = function trackMetaPixelCustom(event, payload) {
    if (!pixelConfig.enabled || typeof window.fbq !== 'function') {
        return;
    }

    window.fbq('trackCustom', event, payload || {});
};

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

        trackMetaPixel(queuedCall.eventName, queuedCall.payload || {});
    });
}
