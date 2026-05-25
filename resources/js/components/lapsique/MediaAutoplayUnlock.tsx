import { useEffect } from 'react';
import { mountMediaAutoplayUnlockListeners } from '@/lib/mediaAutoplayRegistry';

/** Unlocks muted inline video playback on iOS after the first user gesture anywhere on the page. */
export function MediaAutoplayUnlock() {
    useEffect(() => mountMediaAutoplayUnlockListeners(), []);

    return null;
}
