import { useCallback, useEffect } from 'react';
import { AnimatePresence } from 'framer-motion';
import { ChevronLeft, ChevronRight, X } from 'lucide-react';
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { PortfolioMediaViewer } from '@/components/lapsique/PortfolioMediaViewer';
import { useTranslations } from '@/hooks/useTranslations';
import { modalShellGallery } from '@/lib/modalLayout';
import { cn } from '@/lib/utils';
import type { PortfolioItemData } from '@/types';

interface PortfolioLightboxProps {
    items: PortfolioItemData[];
    activeIndex: number | null;
    onClose: () => void;
    onNavigate: (index: number) => void;
}

export function PortfolioLightbox({
    items,
    activeIndex,
    onClose,
    onNavigate,
}: PortfolioLightboxProps) {
    const { t } = useTranslations();
    const active = activeIndex !== null ? items[activeIndex] : null;
    const isOpen = activeIndex !== null && active !== null;
    const isMediaFrame = active?.media_type === 'youtube' || active?.media_type === 'video';

    const goPrev = useCallback(() => {
        if (activeIndex === null || items.length === 0) return;
        onNavigate((activeIndex - 1 + items.length) % items.length);
    }, [activeIndex, items.length, onNavigate]);

    const goNext = useCallback(() => {
        if (activeIndex === null || items.length === 0) return;
        onNavigate((activeIndex + 1) % items.length);
    }, [activeIndex, items.length, onNavigate]);

    useEffect(() => {
        if (!isOpen) return;

        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'ArrowLeft') goPrev();
            if (e.key === 'ArrowRight') goNext();
            if (e.key === 'Escape') onClose();
        };

        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [isOpen, goPrev, goNext, onClose]);

    return (
        <Dialog
            open={isOpen}
            onOpenChange={(open) => {
                if (!open) onClose();
            }}
        >
            <DialogContent
                showCloseButton={false}
                className={cn(modalShellGallery, isMediaFrame && 'w-[min(calc(100vw-1.5rem),60rem)]')}
            >
                <DialogTitle className="sr-only">
                    {active?.title ?? t('pages.portfolio.lightbox_title')}
                </DialogTitle>

                <div
                    className={cn(
                        'relative flex items-center justify-center bg-muted/40',
                        isMediaFrame ? 'min-h-0 p-4' : 'min-h-[50vh]',
                    )}
                >
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="absolute top-3 right-3 z-20 rounded-full bg-background/60 backdrop-blur-sm hover:bg-background/80"
                        onClick={onClose}
                        aria-label={t('common.actions.close')}
                    >
                        <X className="h-5 w-5" />
                    </Button>

                    {items.length > 1 && (
                        <>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="absolute left-3 z-20 hidden h-11 w-11 rounded-full bg-background/50 backdrop-blur-sm hover:bg-background/70 sm:flex"
                                onClick={goPrev}
                                aria-label={t('common.actions.previous')}
                            >
                                <ChevronLeft className="h-6 w-6" />
                            </Button>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="absolute right-3 z-20 hidden h-11 w-11 rounded-full bg-background/50 backdrop-blur-sm hover:bg-background/70 sm:flex"
                                onClick={goNext}
                                aria-label={t('common.actions.next')}
                            >
                                <ChevronRight className="h-6 w-6" />
                            </Button>
                        </>
                    )}

                    <AnimatePresence mode="wait">
                        {active && (
                            <div
                                key={active.id}
                                className={cn(
                                    'w-full',
                                    isMediaFrame ? 'max-w-4xl' : 'flex min-h-[50vh] items-center justify-center',
                                )}
                            >
                                <PortfolioMediaViewer item={active} />
                            </div>
                        )}
                    </AnimatePresence>
                </div>
            </DialogContent>
        </Dialog>
    );
}
