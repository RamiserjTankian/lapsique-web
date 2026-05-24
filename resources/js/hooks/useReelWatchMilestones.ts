import { type RefObject, useEffect, useRef } from 'react';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { buildReelAnalyticsPayload, REEL_WATCH_MILESTONES } from '@/lib/reelAnalytics';

type ActiveReel = {
    src: string;
    bookingSource: string;
    title?: string | null;
};

export function useReelWatchMilestones(
    videoRef: RefObject<HTMLVideoElement | null>,
    active: boolean,
    reel: ActiveReel | null,
) {
    const trackedRef = useRef(new Set<number>());

    useEffect(() => {
        trackedRef.current.clear();
    }, [reel?.src, reel?.bookingSource]);

    useEffect(() => {
        if (!active || !reel) {
            return;
        }

        const video = videoRef.current;

        if (!video) {
            return;
        }

        const payload = buildReelAnalyticsPayload({
            src: reel.src,
            bookingSource: reel.bookingSource,
            title: reel.title,
        });

        const onTimeUpdate = () => {
            const duration = video.duration;

            if (!Number.isFinite(duration) || duration <= 0) {
                return;
            }

            const percent = (video.currentTime / duration) * 100;

            for (const milestone of REEL_WATCH_MILESTONES) {
                if (percent < milestone || trackedRef.current.has(milestone)) {
                    continue;
                }

                trackedRef.current.add(milestone);
                trackBookingEvent('reel_watch_milestone', {
                    ...payload,
                    milestone_percent: milestone,
                    watch_seconds: Math.round(video.currentTime),
                    duration_seconds: Math.round(duration),
                    context: 'reel_player_modal',
                });
            }
        };

        video.addEventListener('timeupdate', onTimeUpdate);

        return () => video.removeEventListener('timeupdate', onTimeUpdate);
    }, [active, reel, videoRef]);
}
