type SyncPlaybackFn = () => void | Promise<void>;

const syncCallbacks = new Set<SyncPlaybackFn>();
let unlocked = false;

export function registerMediaAutoplaySync(callback: SyncPlaybackFn): () => void {
    syncCallbacks.add(callback);

    if (unlocked) {
        void callback();
    }

    return () => {
        syncCallbacks.delete(callback);
    };
}

export function unlockMediaAutoplay(): void {
    if (unlocked) {
        syncCallbacks.forEach((callback) => {
            void callback();
        });

        return;
    }

    unlocked = true;

    syncCallbacks.forEach((callback) => {
        void callback();
    });
}

export function isMediaAutoplayUnlocked(): boolean {
    return unlocked;
}

export function mountMediaAutoplayUnlockListeners(): () => void {
    const unlock = () => {
        unlockMediaAutoplay();
    };

    const options: AddEventListenerOptions = { capture: true, passive: true };

    window.addEventListener('touchstart', unlock, options);
    window.addEventListener('touchend', unlock, options);
    window.addEventListener('pointerdown', unlock, options);
    window.addEventListener('scroll', unlock, options);
    window.addEventListener('wheel', unlock, options);
    window.addEventListener('pageshow', unlock);

    return () => {
        window.removeEventListener('touchstart', unlock, options);
        window.removeEventListener('touchend', unlock, options);
        window.removeEventListener('pointerdown', unlock, options);
        window.removeEventListener('scroll', unlock, options);
        window.removeEventListener('wheel', unlock, options);
        window.removeEventListener('pageshow', unlock);
    };
}
