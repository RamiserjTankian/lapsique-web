import { useCallback, useEffect, useState } from 'react';
import { ChevronLeft, ChevronRight, Heart, Sparkles, X, ZoomIn } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { openBookingModal } from '@/lib/openBookingModal';

interface ViewableImage {
    src: string;
    alt: string;
}

const VIEWER_SELECTOR = '.lapsique-site main img, [data-creator-trust-section="true"] img';

export function SiteImageViewer() {
    const { locale } = useTranslations();
    const [images, setImages] = useState<ViewableImage[]>([]);
    const [activeIndex, setActiveIndex] = useState<number | null>(null);
    const [likedImages, setLikedImages] = useState<Set<string>>(() => new Set());

    const close = useCallback(() => setActiveIndex(null), []);
    const go = useCallback((direction: number) => {
        setActiveIndex((current) => {
            if (current === null || images.length === 0) return current;
            return (current + direction + images.length) % images.length;
        });
    }, [images.length]);

    useEffect(() => {
        const collectImages = () => {
            const seen = new Set<string>();

            return Array.from(document.querySelectorAll<HTMLImageElement>(VIEWER_SELECTOR))
                .filter((image) => {
                    const explicitTrigger = image.closest('[data-lightbox-trigger="true"]');

                    if (!explicitTrigger && image.closest('a, button, [data-no-lightbox="true"]')) return false;
                    if (image.closest('[data-no-lightbox="true"]')) return false;
                    if (image.getAttribute('aria-hidden') === 'true') return false;
                    return image.naturalWidth >= 180 || image.width >= 180;
                })
                .map((image) => ({
                    src: image.currentSrc || image.src,
                    alt: image.alt || 'Fotografía de Lapsique Media',
                }))
                .filter((image) => {
                    if (!image.src || seen.has(image.src)) return false;
                    seen.add(image.src);
                    return true;
                });
        };

        const openFromTarget = (target: EventTarget | null) => {
            if (!(target instanceof Element)) return;
            const explicitTrigger = target.closest<HTMLElement>('[data-lightbox-trigger="true"]');
            const image = explicitTrigger?.querySelector<HTMLImageElement>('img')
                ?? target.closest<HTMLImageElement>('img');

            if (!image?.matches(VIEWER_SELECTOR)) return;
            if (!explicitTrigger && image.closest('a, button, [data-no-lightbox="true"]')) return;
            if (image.closest('[data-no-lightbox="true"]')) return;

            const nextImages = collectImages();
            const src = image.currentSrc || image.src;
            const index = nextImages.findIndex((item) => item.src === src);
            if (index < 0) return;

            setImages(nextImages);
            setActiveIndex(index);
            trackBookingEvent('photo_viewer_opened', {
                content_name: image.alt || 'Lapsique Media photo',
                image_path: new URL(src, window.location.origin).pathname,
                image_index: index + 1,
            });
        };

        const handleClick = (event: MouseEvent) => openFromTarget(event.target);
        const handleKey = (event: KeyboardEvent) => {
            if (activeIndex !== null) {
                if (event.key === 'Escape') close();
                if (event.key === 'ArrowLeft') go(-1);
                if (event.key === 'ArrowRight') go(1);
                return;
            }

            if (event.key === 'Enter') openFromTarget(event.target);
        };

        document.addEventListener('click', handleClick);
        document.addEventListener('keydown', handleKey);

        return () => {
            document.removeEventListener('click', handleClick);
            document.removeEventListener('keydown', handleKey);
        };
    }, [activeIndex, close, go]);

    const active = activeIndex === null ? null : images[activeIndex];
    if (!active) return null;
    const liked = likedImages.has(active.src);
    const toggleLike = () => {
        setLikedImages((current) => {
            const next = new Set(current);
            if (next.has(active.src)) next.delete(active.src);
            else next.add(active.src);
            return next;
        });
        trackBookingEvent(liked ? 'photo_unliked' : 'photo_liked', {
            content_name: active.alt,
            image_path: new URL(active.src, window.location.origin).pathname,
        });
    };
    const openContentBooking = () => {
        close();
        openBookingModal({
            source: 'photo_viewer_cta',
            analyticsEvent: 'photo_viewer_booking_clicked',
            analyticsPayload: { content_name: active.alt },
        });
    };

    return (
        <div className="fixed inset-0 z-[120] flex items-center justify-center bg-black/94 p-3 sm:p-8" role="dialog" aria-modal="true" aria-label={active.alt}>
            <button type="button" className="absolute inset-0 cursor-zoom-out" onClick={close} aria-label="Cerrar visor" />

            <div className="pointer-events-none relative z-10 flex h-full w-full max-w-[1600px] items-center justify-center pb-24 sm:pb-20">
                <img src={active.src} alt={active.alt} className="max-h-full max-w-full object-contain" />
                <div className="absolute left-4 top-4 inline-flex items-center gap-2 bg-black/65 px-3 py-2 font-mono text-[10px] uppercase tracking-[0.16em] text-white/75">
                    <ZoomIn className="size-3.5" />
                    {(activeIndex ?? 0) + 1} / {images.length}
                </div>
            </div>

            <div className="absolute inset-x-3 bottom-3 z-20 mx-auto flex max-w-3xl items-center gap-3 border border-white/20 bg-black/82 p-3 text-white backdrop-blur-md sm:bottom-5 sm:px-4">
                <Button
                    type="button"
                    size="icon"
                    variant="outline"
                    className={liked ? 'size-11 shrink-0 rounded-none border-primary bg-primary text-white' : 'size-11 shrink-0 rounded-none border-white/30 bg-transparent text-white hover:border-primary hover:bg-primary hover:text-white'}
                    onClick={toggleLike}
                    aria-label={liked ? 'Quitar Me gusta' : 'Me gusta'}
                    aria-pressed={liked}
                >
                    <Heart className={liked ? 'size-5 fill-current' : 'size-5'} />
                </Button>
                <div className="min-w-0 flex-1">
                    <p className="font-display text-sm font-bold uppercase tracking-[0.04em] sm:text-base">
                        {locale === 'en' ? 'Want content like this for your business?' : '¿Quieres contenido así para tu negocio?'}
                    </p>
                    <p className="hidden text-xs text-white/65 sm:block">
                        {locale === 'en' ? 'Lapsique Media produces photo and video built to sell.' : 'Lapsique Media crea fotografía y video pensado para vender.'}
                    </p>
                </div>
                <Button type="button" className="min-h-11 shrink-0 rounded-none px-4" onClick={openContentBooking}>
                    <Sparkles className="size-4" />
                    <span className="hidden sm:inline">{locale === 'en' ? 'Create content' : 'Crear contenido'}</span>
                    <span className="sm:hidden">{locale === 'en' ? 'Create' : 'Crear'}</span>
                </Button>
            </div>

            <Button type="button" size="icon" variant="outline" className="absolute right-4 top-4 z-20 size-11 rounded-none border-white/30 bg-black/65 text-white hover:bg-white hover:text-black" onClick={close} aria-label="Cerrar visor">
                <X className="size-5" />
            </Button>

            {images.length > 1 ? (
                <>
                    <Button type="button" size="icon" variant="outline" className="absolute left-4 z-20 size-12 rounded-none border-white/30 bg-black/65 text-white hover:bg-white hover:text-black" onClick={() => go(-1)} aria-label="Fotografía anterior">
                        <ChevronLeft className="size-6" />
                    </Button>
                    <Button type="button" size="icon" variant="outline" className="absolute right-4 z-20 size-12 rounded-none border-white/30 bg-black/65 text-white hover:bg-white hover:text-black" onClick={() => go(1)} aria-label="Fotografía siguiente">
                        <ChevronRight className="size-6" />
                    </Button>
                </>
            ) : null}
        </div>
    );
}
