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
    window.LapsiqueTracker?.track(name, payload);

    if (name === 'newsletter_form_submitted') {
        window.trackMetaPixel?.('Lead', {
            content_name: 'Newsletter signup',
            content_category: 'newsletter_popup',
            ...payload,
        });
    } else if (name === 'newsletter_popup_shown') {
        window.trackMetaPixelCustom?.('newsletter_popup_shown', payload);
    }
}
