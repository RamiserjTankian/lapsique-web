export function isIosLikeDevice(): boolean {
    if (typeof navigator === 'undefined') {
        return false;
    }

    const ua = navigator.userAgent;

    return (
        /iPhone|iPad|iPod/i.test(ua)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
    );
}

export function prepareVideoForAutoplay(video: HTMLVideoElement): void {
    video.defaultMuted = true;
    video.muted = true;
    video.playsInline = true;
    video.setAttribute('muted', '');
    video.setAttribute('playsinline', '');
    video.setAttribute('webkit-playsinline', '');
}

export async function attemptVideoAutoplay(video: HTMLVideoElement): Promise<boolean> {
    prepareVideoForAutoplay(video);

    try {
        await video.play();

        return !video.paused;
    } catch {
        return false;
    }
}

export function resolveVideoPreload(
    eager: boolean,
    preload?: 'none' | 'metadata' | 'auto',
): 'none' | 'metadata' | 'auto' {
    if (preload) {
        return preload;
    }

    if (eager) {
        return 'auto';
    }

    if (isIosLikeDevice()) {
        return 'metadata';
    }

    return 'none';
}
