export type ReelAnalyticsInput = {
    src: string;
    bookingSource: string;
    title?: string | null;
    section?: string;
};

export const REEL_WATCH_MILESTONES = [25, 50, 75, 100] as const;

export function buildReelAnalyticsPayload(input: ReelAnalyticsInput): Record<string, unknown> {
    const section = input.section ?? input.bookingSource;

    return {
        src: input.src,
        section,
        booking_source: input.bookingSource,
        title: input.title ?? undefined,
    };
}
