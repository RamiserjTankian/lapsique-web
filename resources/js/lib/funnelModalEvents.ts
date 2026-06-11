export type FunnelModalType = 'booking' | 'newsletter' | 'whatsapp';

export const FUNNEL_MODAL_STATE_EVENT = 'lapsique:funnel-modal-state';
export const NEWSLETTER_OPEN_EVENT = 'lapsique:newsletter-open';
export const BOOKING_AUTO_OPEN_EVENT = 'lapsique:booking-auto-open';
export const BOOKING_MODAL_CLOSED_EVENT = 'lapsique:booking-modal-closed';

export const BOOKING_POPUP_AUTO_SESSION_KEY = 'lapsique_booking_popup_auto';
export const NEWSLETTER_POPUP_SEEN_KEY = 'lapsique_newsletter_popup_seen';
export const WHATSAPP_POPUP_SEEN_KEY = 'lapsique_whatsapp_popup_seen';
export const WHATSAPP_POPUP_SESSION_KEY = 'lapsique_whatsapp_popup_session';
export const FUNNEL_LAST_MODAL_CLOSED_KEY = 'lapsique_funnel_last_modal_closed';
export const FUNNEL_BOOKING_INTENT_KEY = 'lapsique_booking_intent_at';

let activeModal: FunnelModalType | null = null;
let lastModalClosedAt = 0;
let lastBookingIntentAt = 0;

function readStoredTimestamp(key: string): number {
    try {
        const value = sessionStorage.getItem(key);

        return value ? Number.parseInt(value, 10) : 0;
    } catch {
        return 0;
    }
}

function writeStoredTimestamp(key: string, value: number): void {
    try {
        sessionStorage.setItem(key, value.toString());
    } catch {
        // Private browsing / blocked storage
    }
}

export function getActiveFunnelModal(): FunnelModalType | null {
    return activeModal;
}

export function setActiveFunnelModal(type: FunnelModalType | null): void {
    const previous = activeModal;
    activeModal = type;

    if (typeof window === 'undefined') {
        return;
    }

    if (type === null && previous !== null) {
        markFunnelModalClosed();
    }

    window.dispatchEvent(
        new CustomEvent(FUNNEL_MODAL_STATE_EVENT, { detail: { type } }),
    );
}

export function markFunnelModalClosed(): void {
    lastModalClosedAt = Date.now();
    writeStoredTimestamp(FUNNEL_LAST_MODAL_CLOSED_KEY, lastModalClosedAt);
}

export function hasRecentlyClosedFunnelModal(ms = 20_000): boolean {
    const lastClosed = Math.max(
        lastModalClosedAt,
        readStoredTimestamp(FUNNEL_LAST_MODAL_CLOSED_KEY),
    );

    return lastClosed > 0 && Date.now() - lastClosed < ms;
}

export function markBookingIntent(): void {
    lastBookingIntentAt = Date.now();
    writeStoredTimestamp(FUNNEL_BOOKING_INTENT_KEY, lastBookingIntentAt);
}

export function hasRecentBookingIntent(ms = 120_000): boolean {
    const lastIntent = Math.max(
        lastBookingIntentAt,
        readStoredTimestamp(FUNNEL_BOOKING_INTENT_KEY),
    );

    return lastIntent > 0 && Date.now() - lastIntent < ms;
}

export function isSuppressedFunnelPopupContext(): boolean {
    if (typeof window === 'undefined') {
        return true;
    }

    const { pathname, search, hash } = window.location;
    const params = new URLSearchParams(search);

    if (params.get('book') === '1' || params.has('session_id')) {
        return true;
    }

    const target = `${pathname}${hash}`.toLowerCase();

    return [
        '/checkout',
        '/confirm',
        '/confirmation',
        '/failure',
        '/login',
        '/pending',
        '/portal',
    ].some((fragment) => target.includes(fragment));
}

export function canOpenAutomatedFunnelPopup({
    modalQuietMs = 20_000,
    bookingIntentMs = 120_000,
    respectBookingIntent = true,
}: {
    modalQuietMs?: number;
    bookingIntentMs?: number;
    respectBookingIntent?: boolean;
} = {}): boolean {
    if (isSuppressedFunnelPopupContext()) {
        return false;
    }

    if (getActiveFunnelModal() !== null) {
        return false;
    }

    if (hasRecentlyClosedFunnelModal(modalQuietMs)) {
        return false;
    }

    if (respectBookingIntent && hasRecentBookingIntent(bookingIntentMs)) {
        return false;
    }

    return true;
}

export function openNewsletterModal(source = 'manual'): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.dispatchEvent(
        new CustomEvent(NEWSLETTER_OPEN_EVENT, { detail: { source } }),
    );
}

export function requestBookingAutoOpen(): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.dispatchEvent(new CustomEvent(BOOKING_AUTO_OPEN_EVENT));
}

export function markBookingAutoShown(): boolean {
    try {
        if (sessionStorage.getItem(BOOKING_POPUP_AUTO_SESSION_KEY) === '1') {
            return false;
        }

        sessionStorage.setItem(BOOKING_POPUP_AUTO_SESSION_KEY, '1');

        return true;
    } catch {
        return false;
    }
}

export function hasSeenNewsletterPopupWithinDays(days = 7): boolean {
    try {
        const lastSeen = localStorage.getItem(NEWSLETTER_POPUP_SEEN_KEY);

        if (!lastSeen) {
            return false;
        }

        const daysSince = (Date.now() - Number.parseInt(lastSeen, 10)) / (1000 * 60 * 60 * 24);

        return daysSince < days;
    } catch {
        return false;
    }
}

export function markNewsletterPopupSeen(): void {
    try {
        localStorage.setItem(NEWSLETTER_POPUP_SEEN_KEY, Date.now().toString());
    } catch {
        // Private browsing
    }
}

export function hasSeenWhatsAppPopupWithinDays(days = 3): boolean {
    try {
        const lastSeen = localStorage.getItem(WHATSAPP_POPUP_SEEN_KEY);

        if (!lastSeen) {
            return false;
        }

        const daysSince = (Date.now() - Number.parseInt(lastSeen, 10)) / (1000 * 60 * 60 * 24);

        return daysSince < days;
    } catch {
        return false;
    }
}

export function markWhatsAppPopupSeen(): void {
    try {
        localStorage.setItem(WHATSAPP_POPUP_SEEN_KEY, Date.now().toString());
    } catch {
        // Private browsing
    }
}

export function markWhatsAppPopupShownThisSession(): void {
    try {
        sessionStorage.setItem(WHATSAPP_POPUP_SESSION_KEY, '1');
    } catch {
        // Private browsing
    }
}

export function hasWhatsAppPopupShownThisSession(): boolean {
    try {
        return sessionStorage.getItem(WHATSAPP_POPUP_SESSION_KEY) === '1';
    } catch {
        return false;
    }
}
