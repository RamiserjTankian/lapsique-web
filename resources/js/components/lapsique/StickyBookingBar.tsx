import { useEffect, useMemo, useState } from 'react';
import { CalendarDays } from 'lucide-react';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { REEL_PLAYER_STATE_EVENT } from '@/hooks/useReelPlayerModal';
import { useTranslations } from '@/hooks/useTranslations';
import { FUNNEL_MODAL_STATE_EVENT, getActiveFunnelModal } from '@/lib/funnelModalEvents';
import { openBookingModal } from '@/lib/openBookingModal';
import { cn } from '@/lib/utils';

interface StickyBookingBarProps {
    whatsapp?: string;
    className?: string;
}

const DEFAULT_WHATSAPP_TEXT_KEY = 'common.whatsapp.default_prefill';

export function StickyBookingBar({ whatsapp, className }: StickyBookingBarProps) {
    const { t } = useTranslations();
    const [visible, setVisible] = useState(false);
    const [modalOpen, setModalOpen] = useState(getActiveFunnelModal() === 'booking');
    const [reelPlayerOpen, setReelPlayerOpen] = useState(false);

    const whatsappHref = useMemo(() => {
        if (!whatsapp) {
            return '';
        }

        return `https://wa.me/${whatsapp}?text=${encodeURIComponent(t(DEFAULT_WHATSAPP_TEXT_KEY))}`;
    }, [t, whatsapp]);

    useEffect(() => {
        const onScroll = () => setVisible(window.scrollY > 200);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    useEffect(() => {
        const onModalState = (event: Event) => {
            const type = (event as CustomEvent<{ type: string | null }>).detail?.type;
            setModalOpen(type === 'booking');
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

    if (!visible || modalOpen || reelPlayerOpen) {
        return null;
    }

    return (
        <div
            className={cn(
                'fixed bottom-[calc(1rem+env(safe-area-inset-bottom,0px))] right-4 z-50 flex items-center gap-3 md:hidden',
                className,
            )}
            data-mobile-floating-ctas
        >
            <BookingCtaButton
                type="button"
                compact
                className="h-14 shrink-0 rounded-full px-5 shadow-[0_12px_40px_oklch(0.78_0.14_75/0.45)]"
                onClick={() => {
                    openBookingModal({
                        source: 'sticky',
                        analyticsEvent: 'sticky_cta_clicked',
                    });
                }}
            >
                <CalendarDays className="h-5 w-5" />
                {t('common.cta.book')}
            </BookingCtaButton>

            {whatsappHref ? (
                <a
                    href={whatsappHref}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#25D366] text-[#04150a] shadow-[0_12px_40px_oklch(0.55_0.18_145/0.45)] transition-[background-color,color,transform,box-shadow] duration-150 hover:scale-105 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2 focus-visible:ring-offset-background motion-reduce:transition-none"
                    aria-label={t('common.whatsapp.open')}
                >
                    <svg viewBox="0 0 24 24" className="h-7 w-7 fill-current" aria-hidden>
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                </a>
            ) : null}
        </div>
    );
}
