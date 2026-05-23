export type FunnelModalType = 'booking' | 'newsletter';

export const FUNNEL_MODAL_STATE_EVENT = 'lapsique:funnel-modal-state';
export const NEWSLETTER_OPEN_EVENT = 'lapsique:newsletter-open';
export const BOOKING_AUTO_OPEN_EVENT = 'lapsique:booking-auto-open';

export const BOOKING_POPUP_AUTO_SESSION_KEY = 'lapsique_booking_popup_auto';
export const NEWSLETTER_POPUP_SEEN_KEY = 'lapsique_newsletter_popup_seen';

let activeModal: FunnelModalType | null = null;

export function getActiveFunnelModal(): FunnelModalType | null {
    return activeModal;
}

export function setActiveFunnelModal(type: FunnelModalType | null): void {
    activeModal = type;

    if (typeof window === 'undefined') {
        return;
    }

    window.dispatchEvent(
        new CustomEvent(FUNNEL_MODAL_STATE_EVENT, { detail: { type } }),
    );
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
