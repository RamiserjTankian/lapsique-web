import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useState,
    type ReactNode,
} from 'react';

export const REEL_PLAYER_STATE_EVENT = 'lapsique:reel-player-state';

export type ReelPlayerPayload = {
    src: string;
    poster?: string | null;
    title?: string | null;
    bookingSource: string;
};

type ReelPlayerContextValue = {
    activeReel: ReelPlayerPayload | null;
    openReelPlayer: (payload: ReelPlayerPayload) => void;
    closeReelPlayer: () => void;
};

const ReelPlayerContext = createContext<ReelPlayerContextValue | null>(null);

function dispatchReelPlayerState(open: boolean): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.dispatchEvent(
        new CustomEvent(REEL_PLAYER_STATE_EVENT, {
            detail: { open },
        }),
    );
}

export function ReelPlayerProvider({ children }: { children: ReactNode }) {
    const [activeReel, setActiveReel] = useState<ReelPlayerPayload | null>(null);

    const openReelPlayer = useCallback((payload: ReelPlayerPayload) => {
        dispatchReelPlayerState(true);
        setActiveReel(payload);
    }, []);

    const closeReelPlayer = useCallback(() => {
        dispatchReelPlayerState(false);
        setActiveReel(null);
    }, []);

    const value = useMemo(
        () => ({ activeReel, openReelPlayer, closeReelPlayer }),
        [activeReel, openReelPlayer, closeReelPlayer],
    );

    return (
        <ReelPlayerContext.Provider value={value}>{children}</ReelPlayerContext.Provider>
    );
}

export function useReelPlayerModal(): ReelPlayerContextValue {
    const context = useContext(ReelPlayerContext);

    if (!context) {
        throw new Error('useReelPlayerModal must be used within ReelPlayerProvider');
    }

    return context;
}

/** Safe hook for components that may render outside Home (no-op when provider absent). */
export function useOptionalReelPlayerModal(): ReelPlayerContextValue | null {
    return useContext(ReelPlayerContext);
}
