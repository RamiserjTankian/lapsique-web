const analyticsConfig = window.SiteAnalytics || {};
const pageConfig = window.SitePage || {};
const trackerQueue = Array.isArray(window.__siteTrackerQueue) ? window.__siteTrackerQueue : [];
const dntEnabled =
    navigator.doNotTrack === '1' ||
    window.doNotTrack === '1' ||
    navigator.msDoNotTrack === '1';
const sessionTtlMinutes = Number(analyticsConfig.sessionTimeout || 30);
const SESSION_TTL_MS = sessionTtlMinutes * 60 * 1000;
const presenceConfig = analyticsConfig.presence || {};
const HEARTBEAT_INTERVAL_MS = Math.max(Number(presenceConfig.heartbeatIntervalSeconds || 15), 10) * 1000;
const visitorId = dntEnabled ? null : getOrCreateVisitorId();
const utmParams = getPersistedUtmParams();
const analyticsEnabled = Boolean(analyticsConfig.enabled && analyticsConfig.endpoint);
const sampledVisitor = Boolean(
    !dntEnabled &&
        visitorId &&
        shouldSample(visitorId, Number(analyticsConfig.sampleRate || 1))
);
let sessionData = dntEnabled ? null : getOrCreateSession(SESSION_TTL_MS);
let pageStartAt = Date.now();
let exitReported = false;
const trackedSections = new Set();
const trackedScrollDepths = new Set();

const trackerApi = {
    getContext,
    track,
    trackPageview,
    pageview: handleSpaPageview,
    syncForms: syncTrackingForms,
};

window.SiteTracker = trackerApi;
window.SiteTrackingContext = getContext();

syncTrackingForms();
observeTrackingForms();
flushQueuedTrackerCalls();

if (sampledVisitor) {
    trackPageview();
    initClickTracking();
    initFormTracking();
    initEngagementTracking();
    initSectionTracking();
    initPresenceTracking();
    initMediaTracking();
    initFieldIntentTracking();
}

function getContext() {
    sessionData = dntEnabled ? null : getOrCreateSession(SESSION_TTL_MS);
    const pageMetadata = getPageMetadata();

    return {
        visitor_id: visitorId,
        session_id: sessionData?.id || null,
        utm_source: utmParams.source || null,
        utm_medium: utmParams.medium || null,
        utm_campaign: utmParams.campaign || null,
        utm_term: utmParams.term || null,
        utm_content: utmParams.content || null,
        page_type: pageMetadata.page_type,
        page_name: pageMetadata.page_name,
        event_id: pageMetadata.event_id,
        service_type: pageMetadata.service_type,
        landing_page: window.location.pathname,
        landing_url: window.location.href,
        referrer: document.referrer || null,
        fbp: getCookieValue('_fbp'),
        fbc: getFacebookClickId(),
    };
}

function track(name, options = {}) {
    if (!sampledVisitor) {
        return;
    }

    const pageMetadata = getPageMetadata();

    send({
        type: 'event',
        url: window.location.href,
        path: window.location.pathname,
        referrer: document.referrer || null,
        event: {
            name,
            category: options.category || 'custom',
            label: options.label || null,
            value: typeof options.value === 'number' ? options.value : null,
            element: options.element || null,
            metadata: {
                ...pageMetadata,
                ...(options.metadata || {}),
            },
        },
    }, Boolean(options.useBeacon));
}

function trackPageview(overrides = {}) {
    if (!sampledVisitor) {
        return;
    }

    send({
        type: 'pageview',
        url: overrides.url || window.location.href,
        path: overrides.path || window.location.pathname,
        title: overrides.title || document.title,
        referrer: overrides.referrer !== undefined ? overrides.referrer : document.referrer || null,
        utm: utmParams,
        viewport: {
            width: window.innerWidth,
            height: window.innerHeight,
        },
        screen: {
            width: window.screen?.width || null,
            height: window.screen?.height || null,
        },
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        language: navigator.language,
    });
}

function handleSpaPageview(overrides = {}) {
    // Reset per-page engagement counters so SPA navigations report fresh metrics.
    pageStartAt = Date.now();
    exitReported = false;
    trackedScrollDepths.clear();
    trackedSections.clear();

    trackPageview(overrides);
    syncTrackingForms();

    if (sampledVisitor) {
        initSectionTracking();
    }
}

function initClickTracking() {
    if (analyticsConfig.trackClicks === false) {
        return;
    }

    document.addEventListener('click', (event) => {
        const target = event.target?.closest?.('a, button, [data-analytics]');
        if (!target || target.hasAttribute('data-analytics-ignore')) {
            return;
        }

        const elementData = getElementData(target);
        const eventLabel = target.getAttribute('data-analytics-label') || elementData.text || elementData.href;
        const eventCategory = target.getAttribute('data-analytics-category') || elementData.category;
        const section = target.closest('[data-analytics-section]')?.getAttribute('data-analytics-section') || null;

        track('click', {
            category: eventCategory,
            label: eventLabel,
            element: elementData,
            metadata: {
                outbound: elementData.outbound,
                analytics_action: target.getAttribute('data-analytics-action') || null,
                cta: target.getAttribute('data-analytics-cta') || target.getAttribute('data-cta') || null,
                section,
                checkout_stage: target.getAttribute('data-checkout-stage') || null,
            },
        });
    });
}

function initFormTracking() {
    if (analyticsConfig.trackForms === false) {
        return;
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form || form.hasAttribute('data-analytics-ignore')) {
            return;
        }

        const action = form.getAttribute('action') || window.location.href;
        const label = form.getAttribute('data-analytics-label') || form.getAttribute('id') || action;

        track('submit', {
            category: 'form',
            label,
            element: {
                tag: 'form',
                id: form.getAttribute('id'),
                classes: form.getAttribute('class'),
            },
            metadata: {
                form_id: form.getAttribute('id') || null,
                form_name: form.getAttribute('name') || null,
                form_action: action,
                checkout_stage: form.getAttribute('data-checkout-stage') || null,
                service_type: form.getAttribute('data-service-type') || null,
                event_id: form.getAttribute('data-event-id') || null,
            },
        });
    });
}

function initEngagementTracking() {
    if (analyticsConfig.trackEngagement === false) {
        return;
    }

    window.setTimeout(() => {
        track('engaged', {
            category: 'engagement',
            label: '15s',
            value: 15,
        });
    }, 15000);

    window.addEventListener(
        'scroll',
        () => {
            const doc = document.documentElement;
            const scrollable = doc.scrollHeight - window.innerHeight;
            if (scrollable <= 0) {
                return;
            }

            const scrollPercent = Math.round((window.scrollY / scrollable) * 100);
            [25, 50, 75, 90].forEach((depth) => {
                if (scrollPercent >= depth && !trackedScrollDepths.has(depth)) {
                    trackedScrollDepths.add(depth);
                    track('scroll_depth', {
                        category: 'engagement',
                        label: `${depth}%`,
                        value: depth,
                    });
                }
            });
        },
        { passive: true }
    );

    const reportExit = () => {
        if (exitReported) {
            return;
        }

        exitReported = true;

        const seconds = Math.max(1, Math.round((Date.now() - pageStartAt) / 1000));
        track('page_exit', {
            category: 'engagement',
            label: 'time_on_page',
            value: seconds,
            metadata: {
                page_duration_seconds: seconds,
                session_duration_seconds: getSessionAgeSeconds(),
                visibility: document.visibilityState,
            },
            useBeacon: true,
        });
    };

    window.addEventListener('pagehide', reportExit);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            reportExit();
        }
    });

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            pageStartAt = Date.now();
            exitReported = false;
            trackPageview();
        }
    });
}

function initSectionTracking() {
    const sections = Array.from(document.querySelectorAll('[data-analytics-section]'));
    if (sections.length === 0 || typeof window.IntersectionObserver !== 'function') {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const sectionName =
                    entry.target.getAttribute('data-analytics-section') ||
                    entry.target.getAttribute('id') ||
                    'section';

                if (trackedSections.has(sectionName)) {
                    return;
                }

                trackedSections.add(sectionName);
                track('section_view', {
                    category: 'section',
                    label: sectionName,
                    metadata: {
                        section: sectionName,
                        section_id: entry.target.getAttribute('id') || null,
                    },
                });
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.35 }
    );

    sections.forEach((section) => observer.observe(section));
}

function initMediaTracking() {
    const playedVideos = new WeakSet();
    const progressByVideo = new WeakMap();

    document.addEventListener('play', (event) => {
        const video = event.target;
        if (!(video instanceof HTMLVideoElement) || playedVideos.has(video)) {
            return;
        }

        playedVideos.add(video);
        progressByVideo.set(video, new Set());
        const label = getMediaLabel(video);
        track('video_play', {
            category: 'media',
            label,
            metadata: { media_src: sanitizeMediaSrc(video.currentSrc || video.src), autoplay: video.autoplay },
        });
        window.trackMetaPixelCustom?.('VideoPlay', {
            content_name: label,
            content_category: 'video',
            page_path: window.location.pathname,
        });
    }, true);

    document.addEventListener('timeupdate', (event) => {
        const video = event.target;
        if (!(video instanceof HTMLVideoElement) || !Number.isFinite(video.duration) || video.duration <= 0) {
            return;
        }

        const reached = progressByVideo.get(video) || new Set();
        const percent = Math.round((video.currentTime / video.duration) * 100);
        [25, 50, 75].forEach((milestone) => {
            if (percent < milestone || reached.has(milestone)) {
                return;
            }
            reached.add(milestone);
            progressByVideo.set(video, reached);
            track('video_progress', {
                category: 'media',
                label: getMediaLabel(video),
                value: milestone,
                metadata: { progress_percent: milestone, media_src: sanitizeMediaSrc(video.currentSrc || video.src) },
            });
        });
    }, true);

    document.addEventListener('ended', (event) => {
        const video = event.target;
        if (!(video instanceof HTMLVideoElement)) {
            return;
        }
        const label = getMediaLabel(video);
        track('video_complete', {
            category: 'media',
            label,
            value: 100,
            metadata: { progress_percent: 100, media_src: sanitizeMediaSrc(video.currentSrc || video.src) },
        });
        window.trackMetaPixelCustom?.('VideoComplete', {
            content_name: label,
            content_category: 'video',
            page_path: window.location.pathname,
        });
    }, true);
}

function initFieldIntentTracking() {
    const startedForms = new WeakSet();
    document.addEventListener('focusin', (event) => {
        const field = event.target;
        if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) {
            return;
        }
        const form = field.form;
        if (!form || startedForms.has(form) || field.type === 'hidden') {
            return;
        }

        startedForms.add(form);
        const formName = form.getAttribute('data-analytics-label') || form.id || form.getAttribute('name') || 'form';
        track('form_started', {
            category: 'form',
            label: formName,
            metadata: {
                form_id: form.id || null,
                service_type: form.getAttribute('data-service-type') || getPageMetadata().service_type,
            },
        });
        window.trackMetaPixelCustom?.('FormStarted', {
            content_name: formName,
            content_category: 'lead_form',
            page_path: window.location.pathname,
        });
    });
}

function getMediaLabel(video) {
    return video.getAttribute('aria-label')
        || video.getAttribute('title')
        || video.closest('figure, article, section')?.querySelector('h1, h2, h3, figcaption')?.textContent?.trim()
        || 'video';
}

function sanitizeMediaSrc(src) {
    try {
        return new URL(src, window.location.origin).pathname.slice(0, 255);
    } catch {
        return String(src || '').slice(0, 255);
    }
}

function initPresenceTracking() {
    const sendHeartbeat = (reason = 'heartbeat', useBeacon = false) => {
        if (document.visibilityState === 'hidden' && !useBeacon) {
            return;
        }

        send(
            {
                type: 'heartbeat',
                url: window.location.href,
                path: window.location.pathname,
                title: document.title,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                language: navigator.language,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight,
                },
                screen: {
                    width: window.screen?.width || null,
                    height: window.screen?.height || null,
                },
                presence: {
                    reason,
                    visibility: document.visibilityState,
                    session_age_seconds: getSessionAgeSeconds(),
                },
            },
            useBeacon
        );
    };

    window.setInterval(() => {
        sendHeartbeat();
    }, HEARTBEAT_INTERVAL_MS);

    window.addEventListener('focus', () => sendHeartbeat('focus'));
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            sendHeartbeat('visible');
        }
    });
}

function syncTrackingForms(root = document) {
    const forms = root.querySelectorAll?.('form:not([data-track-context="false"])') || [];
    const context = getContext();

    forms.forEach((form) => {
        upsertHiddenInput(form, 'utm_source', context.utm_source);
        upsertHiddenInput(form, 'utm_medium', context.utm_medium);
        upsertHiddenInput(form, 'utm_campaign', context.utm_campaign);
        upsertHiddenInput(form, 'utm_term', context.utm_term);
        upsertHiddenInput(form, 'utm_content', context.utm_content);
        upsertHiddenInput(form, 'analytics_visitor_id', context.visitor_id);
        upsertHiddenInput(form, 'analytics_session_id', context.session_id);
        upsertHiddenInput(form, 'landing_page', context.landing_page);
        upsertHiddenInput(form, 'landing_url', context.landing_url);
        upsertHiddenInput(form, 'page_type', context.page_type);
        upsertHiddenInput(form, 'page_name', context.page_name);
        upsertHiddenInput(form, 'event_id', context.event_id);
        upsertHiddenInput(form, 'service_type', context.service_type);
        upsertHiddenInput(form, 'referrer', context.referrer);
        upsertHiddenInput(form, 'fbp', context.fbp);
        upsertHiddenInput(form, 'fbc', context.fbc);
    });
}

function observeTrackingForms() {
    if (typeof window.MutationObserver !== 'function') {
        return;
    }

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof HTMLElement)) {
                    return;
                }

                if (node.matches?.('form')) {
                    syncTrackingForms(node.parentElement || document);
                    return;
                }

                if (node.querySelector?.('form')) {
                    syncTrackingForms(node);
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

function getPageMetadata() {
    const bodyDataset = document.body?.dataset || {};

    return {
        page_type: pageConfig.type || bodyDataset.pageType || null,
        page_name: pageConfig.name || bodyDataset.routeName || null,
        event_id: pageConfig.eventId || pageConfig.event_id || bodyDataset.eventId || null,
        service_type: pageConfig.serviceType || pageConfig.service_type || bodyDataset.serviceType || null,
        route_name: bodyDataset.routeName || null,
    };
}

function flushQueuedTrackerCalls() {
    while (trackerQueue.length > 0) {
        const queuedCall = trackerQueue.shift();
        if (!queuedCall?.method) {
            continue;
        }

        if (queuedCall.method === 'trackPageview') {
            trackPageview(queuedCall.options || {});
            continue;
        }

        if (queuedCall.method === 'syncForms') {
            syncTrackingForms();
            continue;
        }

        track(queuedCall.name, queuedCall.options || {});
    }
}

function send(payload, useBeacon = false) {
    if (!analyticsEnabled || dntEnabled || !visitorId) {
        return;
    }

    sessionData = getOrCreateSession(SESSION_TTL_MS);
    payload.session_id = sessionData.id;
    payload.visitor_id = visitorId;
    payload.dnt = dntEnabled ? 1 : 0;

    sessionData.lastActivity = Date.now();
    safeSetStorage('lapsique_session', JSON.stringify(sessionData));

    const body = JSON.stringify(payload);

    if (useBeacon && navigator.sendBeacon) {
        const blob = new Blob([body], { type: 'application/json' });
        navigator.sendBeacon(analyticsConfig.endpoint, blob);
        return;
    }

    fetch(analyticsConfig.endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body,
        keepalive: useBeacon,
    }).catch(() => {
        // Ignore network errors to avoid affecting UX.
    });
}

function getOrCreateVisitorId() {
    const key = 'lapsique_visitor_id';
    let value = safeGetStorage(key);
    if (!value) {
        value = generateUuid();
        safeSetStorage(key, value);
    }
    return value;
}

function getOrCreateSession(ttlMs) {
    const key = 'lapsique_session';
    const now = Date.now();
    let session = null;

    try {
        session = JSON.parse(safeGetStorage(key) || 'null');
    } catch (error) {
        session = null;
    }

    if (!session || !session.id || !session.lastActivity || now - session.lastActivity > ttlMs) {
        session = {
            id: generateUuid(),
            startedAt: now,
            lastActivity: now,
        };
        safeSetStorage(key, JSON.stringify(session));
    } else if (!session.startedAt) {
        session.startedAt = session.lastActivity || now;
        safeSetStorage(key, JSON.stringify(session));
    }

    return session;
}

function getSessionAgeSeconds() {
    sessionData = getOrCreateSession(SESSION_TTL_MS);

    if (!sessionData?.startedAt) {
        return null;
    }

    return Math.max(1, Math.round((Date.now() - Number(sessionData.startedAt)) / 1000));
}

function getPersistedUtmParams() {
    const key = 'lapsique_utm';
    const now = Date.now();
    const params = new URLSearchParams(window.location.search);
    const hasUtm = Array.from(params.keys()).some((keyName) => keyName.startsWith('utm_'));

    if (hasUtm) {
        const utm = {
            source: params.get('utm_source'),
            medium: params.get('utm_medium'),
            campaign: params.get('utm_campaign'),
            term: params.get('utm_term'),
            content: params.get('utm_content'),
            expiresAt: now + 1000 * 60 * 60 * 24 * 30,
        };
        safeSetStorage(key, JSON.stringify(utm));
        return utm;
    }

    let stored = null;
    try {
        stored = JSON.parse(safeGetStorage(key) || 'null');
    } catch (error) {
        stored = null;
    }

    if (stored && stored.expiresAt && stored.expiresAt > now) {
        return stored;
    }

    return {
        source: null,
        medium: null,
        campaign: null,
        term: null,
        content: null,
    };
}

function getElementData(target) {
    const tag = target.tagName ? target.tagName.toLowerCase() : null;
    const text = (target.innerText || target.getAttribute('aria-label') || target.getAttribute('title') || '').trim();
    const rawHref = tag === 'a' ? target.getAttribute('href') : null;
    const href = rawHref ? resolveUrl(rawHref) : null;
    const host = href ? getHostFromUrl(href) : null;
    const outbound = host ? host !== window.location.host : false;

    return {
        tag,
        text: text ? text.slice(0, 200) : null,
        href,
        id: target.getAttribute('id'),
        classes: target.getAttribute('class'),
        target: target.getAttribute('target'),
        category: tag === 'a' ? (outbound ? 'outbound' : 'link') : 'button',
        outbound,
    };
}

function getHostFromUrl(url) {
    try {
        return new URL(url, window.location.origin).host;
    } catch (error) {
        return null;
    }
}

function resolveUrl(url) {
    try {
        return new URL(url, window.location.origin).toString();
    } catch (error) {
        return url;
    }
}

function getCookieValue(name) {
    const escaped = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const match = document.cookie.match(new RegExp(`(?:^|; )${escaped}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : null;
}

function getFacebookClickId() {
    const cookieValue = getCookieValue('_fbc');
    if (cookieValue) {
        return cookieValue;
    }

    const fbclid = new URLSearchParams(window.location.search).get('fbclid');
    if (!fbclid) {
        return null;
    }

    return `fb.1.${Date.now()}.${fbclid}`;
}

function upsertHiddenInput(form, name, value) {
    if (value === null || value === undefined || value === '') {
        return;
    }

    const escapedName = window.CSS?.escape ? window.CSS.escape(name) : name.replace(/"/g, '\\"');
    let input = form.querySelector(`input[name="${escapedName}"]`);

    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        form.appendChild(input);
    }

    input.value = String(value);
}

function safeGetStorage(key) {
    try {
        return window.localStorage.getItem(key);
    } catch (error) {
        return null;
    }
}

function safeSetStorage(key, value) {
    try {
        window.localStorage.setItem(key, value);
    } catch (error) {
        // Ignore storage write failures.
    }
}

function generateUuid() {
    if (window.crypto?.randomUUID) {
        return window.crypto.randomUUID();
    }

    let d = new Date().getTime();
    let d2 = (performance && performance.now && performance.now() * 1000) || 0;

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (char) {
        let r = Math.random() * 16;
        if (d > 0) {
            r = (d + r) % 16 | 0;
            d = Math.floor(d / 16);
        } else {
            r = (d2 + r) % 16 | 0;
            d2 = Math.floor(d2 / 16);
        }
        return (char === 'x' ? r : (r & 0x3) | 0x8).toString(16);
    });
}

function shouldSample(currentVisitorId, sampleRate) {
    if (sampleRate >= 1) {
        return true;
    }

    let hash = 0;
    for (let i = 0; i < currentVisitorId.length; i++) {
        hash = (hash << 5) - hash + currentVisitorId.charCodeAt(i);
        hash |= 0;
    }

    const normalized = Math.abs(hash) % 1000 / 1000;
    return normalized <= sampleRate;
}
