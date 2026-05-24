import { AtSign, ExternalLink, RefreshCw } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { cn } from '@/lib/utils';

interface InstagramProfileEmbedProps {
    username: string;
    profileUrl: string;
    handle: string;
    bio?: string;
    featuredPostUrls?: string[];
    className?: string;
}

function extractPostShortcode(url: string): string | null {
    const match = url.match(/instagram\.com\/(?:p|reel|tv)\/([A-Za-z0-9_-]+)/);

    return match?.[1] ?? null;
}

function postEmbedSrc(shortcode: string): string {
    return `https://www.instagram.com/p/${shortcode}/embed`;
}

export function InstagramProfileEmbed({
    username,
    profileUrl,
    handle,
    featuredPostUrls = [],
    className,
}: InstagramProfileEmbedProps) {
    const containerRef = useRef<HTMLDivElement>(null);
    const [shouldLoadEmbed, setShouldLoadEmbed] = useState(false);
    const [embedLoaded, setEmbedLoaded] = useState(false);
    const [embedFailed, setEmbedFailed] = useState(false);
    const [loadAttempt, setLoadAttempt] = useState(0);

    const postShortcodes = featuredPostUrls
        .map(extractPostShortcode)
        .filter((shortcode): shortcode is string => shortcode !== null)
        .slice(0, 6);
    const usePostGrid = postShortcodes.length >= 3;
    const profileEmbedSrc = `https://www.instagram.com/${username}/embed`;

    useEffect(() => {
        const node = containerRef.current;

        if (!node) {
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setShouldLoadEmbed(true);
                    observer.disconnect();
                }
            },
            { rootMargin: '160px', threshold: 0.08 },
        );

        observer.observe(node);

        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        if (!shouldLoadEmbed || embedLoaded) {
            return;
        }

        const timeoutId = window.setTimeout(() => {
            setEmbedFailed(true);
        }, 12_000);

        return () => window.clearTimeout(timeoutId);
    }, [embedLoaded, loadAttempt, shouldLoadEmbed]);

    const retryEmbed = () => {
        setEmbedFailed(false);
        setEmbedLoaded(false);
        setLoadAttempt((current) => current + 1);
        setShouldLoadEmbed(true);
    };

    const showSkeleton = shouldLoadEmbed && !embedLoaded && !embedFailed;

    return (
        <div
            ref={containerRef}
            className={cn(
                'overflow-hidden rounded-[1.75rem] border border-border/80 bg-white shadow-[0_20px_60px_rgb(0_0_0/0.12)] dark:border-white/12 dark:bg-zinc-950',
                className,
            )}
        >
            <div className="relative bg-white dark:bg-zinc-950">
                {showSkeleton && (
                    <div
                        className="absolute inset-0 z-10 flex flex-col justify-center gap-3 bg-gradient-to-b from-zinc-50 to-white px-4 py-6 dark:from-zinc-900 dark:to-zinc-950"
                        aria-hidden
                    >
                        <div className="grid grid-cols-3 gap-1.5">
                            {Array.from({ length: usePostGrid ? postShortcodes.length : 6 }).map((_, index) => (
                                <div
                                    key={index}
                                    className="aspect-square animate-pulse rounded-md bg-zinc-200 dark:bg-zinc-800"
                                />
                            ))}
                        </div>
                        <p className="text-center text-xs text-zinc-500 dark:text-zinc-400">
                            Cargando publicaciones de Instagram…
                        </p>
                    </div>
                )}

                {embedFailed && !embedLoaded ? (
                    <div className="space-y-4 bg-gradient-to-b from-zinc-50 to-white px-4 py-6 dark:from-zinc-900 dark:to-zinc-950">
                        <p className="text-center text-sm text-zinc-600 dark:text-zinc-300">
                            No pudimos cargar el preview embebido. Abre el perfil directamente en Instagram.
                        </p>
                        <div className="flex flex-col gap-2 sm:flex-row sm:justify-center">
                            <a
                                href={profileUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0095f6] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1877f2]"
                            >
                                <AtSign className="h-4 w-4" aria-hidden />
                                Ver perfil en Instagram
                                <ExternalLink className="h-3.5 w-3.5 opacity-80" aria-hidden />
                            </a>
                            <button
                                type="button"
                                onClick={retryEmbed}
                                className="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-white/15 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                            >
                                <RefreshCw className="h-4 w-4" aria-hidden />
                                Reintentar
                            </button>
                        </div>
                    </div>
                ) : shouldLoadEmbed && usePostGrid ? (
                    <div className="grid grid-cols-3 gap-px bg-zinc-200 dark:bg-zinc-800">
                        {postShortcodes.map((shortcode) => (
                            <div key={`${shortcode}-${loadAttempt}`} className="relative aspect-square bg-black">
                                <iframe
                                    title={`Instagram post ${shortcode}`}
                                    src={postEmbedSrc(shortcode)}
                                    className="absolute inset-0 h-full w-full border-0"
                                    scrolling="no"
                                    loading="lazy"
                                    allow="encrypted-media"
                                    referrerPolicy="no-referrer-when-downgrade"
                                    onLoad={() => setEmbedLoaded(true)}
                                />
                            </div>
                        ))}
                    </div>
                ) : shouldLoadEmbed ? (
                    <iframe
                        key={`profile-${loadAttempt}`}
                        title={`Instagram ${handle}`}
                        src={profileEmbedSrc}
                        className="block w-full border-0"
                        style={{ height: 'min(520px, 72vh)', minHeight: '380px' }}
                        scrolling="no"
                        loading="lazy"
                        allow="encrypted-media"
                        referrerPolicy="no-referrer-when-downgrade"
                        onLoad={() => {
                            setEmbedLoaded(true);
                            setEmbedFailed(false);
                        }}
                    />
                ) : (
                    <div className="space-y-4 bg-gradient-to-b from-zinc-50 to-white px-4 py-6 dark:from-zinc-900 dark:to-zinc-950">
                        <div className="grid grid-cols-3 gap-1.5">
                            {Array.from({ length: 6 }).map((_, index) => (
                                <div
                                    key={index}
                                    className="aspect-square animate-pulse rounded-md bg-zinc-200 dark:bg-zinc-800"
                                />
                            ))}
                        </div>
                        <p className="text-center text-xs text-zinc-500 dark:text-zinc-400">
                            Reels, behind the scenes y piezas recientes en el perfil.
                        </p>
                    </div>
                )}
            </div>

            <a
                href={profileUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center justify-center gap-2 border-t border-black/8 px-4 py-3 text-xs font-semibold text-[#0095f6] transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/5"
            >
                {embedLoaded ? 'Abrir perfil completo en Instagram' : 'Seguir en Instagram'}
            </a>
        </div>
    );
}
