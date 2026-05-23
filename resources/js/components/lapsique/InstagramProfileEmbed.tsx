import { useEffect, useId } from 'react';
import { cn } from '@/lib/utils';

declare global {
    interface Window {
        instgrm?: {
            Embeds: {
                process: () => void;
            };
        };
    }
}

interface InstagramProfileEmbedProps {
    username: string;
    profileUrl: string;
    handle: string;
    bio?: string;
    featuredPostUrls?: string[];
    className?: string;
}

const EMBED_SCRIPT_ID = 'instagram-embed-script';

function loadInstagramEmbedScript(onReady: () => void) {
    if (window.instgrm) {
        onReady();

        return;
    }

    const existing = document.getElementById(EMBED_SCRIPT_ID) as HTMLScriptElement | null;

    if (existing) {
        existing.addEventListener('load', onReady, { once: true });

        return;
    }

    const script = document.createElement('script');
    script.id = EMBED_SCRIPT_ID;
    script.async = true;
    script.src = 'https://www.instagram.com/embed.js';
    script.onload = onReady;
    document.body.appendChild(script);
}

export function InstagramProfileEmbed({
    username,
    profileUrl,
    handle,
    bio,
    featuredPostUrls = [],
    className,
}: InstagramProfileEmbedProps) {
    const embedSrc = `https://www.instagram.com/${username}/embed`;
    const titleId = useId();

    useEffect(() => {
        if (featuredPostUrls.length === 0) {
            return;
        }

        loadInstagramEmbedScript(() => {
            window.instgrm?.Embeds.process();
        });
    }, [featuredPostUrls]);

    return (
        <div
            className={cn(
                'overflow-hidden rounded-[1.75rem] border border-border/80 bg-white shadow-[0_20px_60px_rgb(0_0_0/0.12)] dark:border-white/12 dark:bg-zinc-950',
                className,
            )}
            aria-labelledby={titleId}
        >
            <div className="flex items-center justify-between gap-3 border-b border-black/8 px-4 py-3 dark:border-white/10">
                <div className="flex min-w-0 items-center gap-3">
                    <span
                        className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-tr from-[#f9ce34] via-[#ee2a7b] to-[#6228d7] p-[2px]"
                        aria-hidden
                    >
                        <span className="flex h-full w-full items-center justify-center rounded-full bg-white text-xs font-bold text-zinc-900 dark:bg-zinc-950 dark:text-white">
                            {username.slice(0, 1).toUpperCase()}
                        </span>
                    </span>
                    <div className="min-w-0">
                        <p id={titleId} className="truncate text-sm font-semibold text-zinc-900 dark:text-white">
                            {handle}
                        </p>
                        {bio && (
                            <p className="truncate text-xs text-zinc-500 dark:text-zinc-400">{bio}</p>
                        )}
                    </div>
                </div>
                <a
                    href={profileUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="shrink-0 rounded-lg bg-[#0095f6] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#1877f2]"
                >
                    Seguir
                </a>
            </div>

            <div className="relative bg-white dark:bg-zinc-950">
                <iframe
                    title={`Instagram ${handle}`}
                    src={embedSrc}
                    className="block w-full border-0"
                    style={{ height: 'min(520px, 72vh)', minHeight: '380px' }}
                    scrolling="no"
                    loading="lazy"
                    allow="encrypted-media"
                    referrerPolicy="no-referrer-when-downgrade"
                />
            </div>

            {featuredPostUrls.length > 0 && (
                <div className="space-y-3 border-t border-black/8 p-4 dark:border-white/10">
                    {featuredPostUrls.map((permalink) => (
                        <blockquote
                            key={permalink}
                            className="instagram-media"
                            data-instgrm-permalink={`${permalink.replace(/\/$/, '')}/?utm_source=ig_embed&utm_campaign=loading`}
                            data-instgrm-version="14"
                            style={{
                                background: '#FFF',
                                border: 0,
                                borderRadius: '12px',
                                margin: '0 auto',
                                maxWidth: '100%',
                                minWidth: '260px',
                                padding: 0,
                                width: 'calc(100% - 2px)',
                            }}
                        />
                    ))}
                </div>
            )}

            <a
                href={profileUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center justify-center gap-2 border-t border-black/8 px-4 py-3 text-xs font-semibold text-[#0095f6] transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/5"
            >
                Ver perfil completo en Instagram
            </a>
        </div>
    );
}
