export const FUNNEL_CONTENT_ENGAGEMENT_EVENT = 'lapsique:funnel-content-engagement';

const CONTENT_ENGAGEMENT_EVENTS = new Set([
    'proof_section_viewed',
    'gear_section_viewed',
    'workflow_section_viewed',
    'package_includes_viewed',
    'booking_widget_viewed',
]);

let engagementCount = 0;

export function isFunnelContentEngagementEvent(event: string): boolean {
    return CONTENT_ENGAGEMENT_EVENTS.has(event);
}

export function recordFunnelContentEngagement(section?: string): number {
    engagementCount += 1;

    if (typeof window !== 'undefined') {
        window.dispatchEvent(
            new CustomEvent(FUNNEL_CONTENT_ENGAGEMENT_EVENT, {
                detail: { count: engagementCount, section: section ?? null },
            }),
        );
    }

    return engagementCount;
}

export function getFunnelContentEngagementCount(): number {
    return engagementCount;
}
