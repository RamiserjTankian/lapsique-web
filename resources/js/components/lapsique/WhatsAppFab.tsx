import { useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion';
import { MessageCircle, X } from 'lucide-react';
import { REEL_PLAYER_STATE_EVENT } from '@/hooks/useReelPlayerModal';
import { FUNNEL_MODAL_STATE_EVENT, getActiveFunnelModal } from '@/lib/funnelModalEvents';
import { useIsMobileViewport } from '@/hooks/useMediaQuery';
import { useTypingCycle } from '@/hooks/useTypingCycle';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';

const WHATSAPP_PROMPT_DISMISSED_KEY = 'lapsique_whatsapp_prompt_dismissed';
const DISMISS_MS = 1000 * 60 * 60 * 24;

const PROMPT_MESSAGE_KEYS = [
    'common.whatsapp.prompt_1',
    'common.whatsapp.prompt_2',
    'common.whatsapp.prompt_3',
] as const;

export function WhatsAppFab() {
    const { site } = usePage<PageProps>().props;
    const { url } = usePage<PageProps>();
    const { t } = useTranslations();
    const number = site.whatsapp;
    const trascendentalCommunityHref = site.whatsappCommunityUrl;
    const isTrascendental = Boolean(trascendentalCommunityHref);
    const promptMessages = useMemo(
        () => (
            isTrascendental
                ? [
                    t('trascendental.whatsapp.prompt_1'),
                    t('trascendental.whatsapp.prompt_2'),
                    t('trascendental.whatsapp.prompt_3'),
                ]
                : PROMPT_MESSAGE_KEYS.map((key) => t(key))
        ),
        [isTrascendental, t],
    );
    const prefersReducedMotion = useReducedMotion();
    const [isPromptVisible, setIsPromptVisible] = useState(false);
    const [isDismissed, setIsDismissed] = useState(false);
    const isMobile = useIsMobileViewport();
    const [bookingModalOpen, setBookingModalOpen] = useState(getActiveFunnelModal() !== null);
    const [reelPlayerOpen, setReelPlayerOpen] = useState(false);
    const currentPath = useMemo(() => {
        if (typeof url !== 'string' || url === '') {
            return '';
        }

        try {
            return new URL(url, window.location.origin).pathname;
        } catch {
            return '';
        }
    }, [url]);
    const isHomePage = currentPath === '/';
    const isDjSetPage = currentPath === '/dj-set' || currentPath === '/djset';

    const href = useMemo(() => {
        if (trascendentalCommunityHref) {
            return trascendentalCommunityHref;
        }

        if (!number) {
            return '';
        }

        const message = isDjSetPage
            ? t('funnel.whatsapp.prefill_djset')
            : isTrascendental
              ? t('trascendental.whatsapp.default_prefill')
            : t('common.whatsapp.default_prefill');

        return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
    }, [isDjSetPage, isTrascendental, number, t, trascendentalCommunityHref]);

    const { displayed, showCursor } = useTypingCycle({
        texts: promptMessages,
        enabled: isPromptVisible && !prefersReducedMotion,
    });

    const promptText = prefersReducedMotion ? promptMessages[0] : displayed;

    useEffect(() => {
        if (!href) {
            return;
        }

        try {
            const dismissedAt = sessionStorage.getItem(WHATSAPP_PROMPT_DISMISSED_KEY);

            if (dismissedAt && Date.now() - Number(dismissedAt) < DISMISS_MS) {
                setIsDismissed(true);
            }
        } catch {
            // sessionStorage unavailable
        }
    }, [href]);

    useEffect(() => {
        const onModalState = (event: Event) => {
            const type = (event as CustomEvent<{ type: string | null }>).detail?.type;
            setBookingModalOpen(type !== null);
        };

        const onReelPlayerState = (event: Event) => {
            const open = (event as CustomEvent<{ open?: boolean }>).detail?.open;
            setReelPlayerOpen(open === true);
        };

        window.addEventListener(FUNNEL_MODAL_STATE_EVENT, onModalState);
        window.addEventListener(REEL_PLAYER_STATE_EVENT, onReelPlayerState);

        return () => {
            window.removeEventListener(FUNNEL_MODAL_STATE_EVENT, onModalState);
            window.removeEventListener(REEL_PLAYER_STATE_EVENT, onReelPlayerState);
        };
    }, []);

    useEffect(() => {
        if (!href || isDismissed || bookingModalOpen) {
            return;
        }

        const showTimer = window.setTimeout(() => {
            setIsPromptVisible(true);
        }, 1800);

        return () => window.clearTimeout(showTimer);
    }, [href, isDismissed, bookingModalOpen]);

    const dismissPrompt = () => {
        setIsPromptVisible(false);
        setIsDismissed(true);

        try {
            sessionStorage.setItem(WHATSAPP_PROMPT_DISMISSED_KEY, String(Date.now()));
        } catch {
            // ignore
        }
    };

    if (!href || bookingModalOpen || reelPlayerOpen || (isMobile && isHomePage && !isTrascendental)) {
        return null;
    }

    return (
        <div
            className="fixed bottom-[calc(1rem+env(safe-area-inset-bottom,0px))] right-4 z-50 flex flex-col items-end gap-2.5 sm:bottom-6 sm:right-6 sm:gap-3"
            aria-live="polite"
        >
            <AnimatePresence>
                {isPromptVisible && !isDismissed && (
                    <motion.div
                        initial={{ opacity: 0, y: 12, scale: 0.96 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: 8, scale: 0.98 }}
                        transition={{ duration: 0.28, ease: [0.22, 1, 0.36, 1] }}
                        className="relative w-[min(17rem,calc(100vw-5rem))] sm:w-[17rem]"
                    >
                        <button
                            type="button"
                            onClick={dismissPrompt}
                            className="absolute -right-1 -top-1 z-10 flex h-11 w-11 items-center justify-center rounded-full border border-border/80 bg-background text-muted-foreground shadow-md transition hover:bg-secondary hover:text-foreground"
                            aria-label={t('common.whatsapp.close_prompt')}
                        >
                            <X className="h-3.5 w-3.5" />
                        </button>

                        <a
                            href={href}
                            target="_blank"
                            rel="noopener noreferrer"
                            onClick={() => {
                                setIsPromptVisible(false);
                            }}
                            className="group block rounded-2xl border border-[#25D366]/35 bg-background/95 px-4 py-3.5 pr-5 shadow-[0_16px_48px_oklch(0_0_0/0.22)] backdrop-blur-xl transition hover:border-[#25D366]/55 hover:shadow-[0_20px_56px_oklch(0_0_0/0.28)]"
                        >
                            <p className="mb-1 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#25D366]">
                                <MessageCircle className="h-3 w-3" aria-hidden />
                                {isTrascendental ? t('trascendental.whatsapp.community_badge') : 'WhatsApp'}
                            </p>
                            <p className="h-10 overflow-hidden text-left text-sm font-medium leading-snug text-foreground">
                                <span className="line-clamp-2">
                                    {promptText}
                                    {showCursor && (
                                        <span
                                            className={cn(
                                                'ml-0.5 inline-block w-[2px] translate-y-[1px] bg-primary align-middle',
                                                'motion-safe:animate-pulse motion-reduce:hidden',
                                            )}
                                            style={{ height: '0.95em' }}
                                            aria-hidden
                                        />
                                    )}
                                </span>
                            </p>
                            <p className="mt-2 text-xs font-semibold text-[#25D366] transition group-hover:underline">
                                {isTrascendental ? t('trascendental.whatsapp.community_cta') : t('common.whatsapp.write_now')}
                            </p>
                        </a>

                        <span
                            className="absolute -bottom-2 right-6 h-4 w-4 rotate-45 border-b border-r border-[#25D366]/35 bg-background/95"
                            aria-hidden
                        />
                    </motion.div>
                )}
            </AnimatePresence>

            <a
                href={href}
                target="_blank"
                rel="noopener noreferrer"
                className="flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-[0_12px_40px_oklch(0.55_0.18_145/0.45)] transition hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2 focus-visible:ring-offset-background motion-safe:animate-[whatsapp-fab-pulse_2.8s_ease-in-out_infinite] motion-reduce:animate-none"
                aria-label={isTrascendental ? t('trascendental.whatsapp.community_open') : t('common.whatsapp.open')}
            >
                <svg viewBox="0 0 24 24" className="h-7 w-7 fill-current" aria-hidden>
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                </svg>
            </a>
        </div>
    );
}
