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
                className={cn(
                    'theme-scrollbar glass-panel-elevated max-h-[92vh] w-[min(96vw,1100px)] max-w-none',
                    'border-border/80 p-0 overflow-y-auto gap-0',
                    isMediaFrame && 'w-[min(96vw,960px)]',
                )}
            >
                <DialogTitle className="sr-only">
                    {active?.title ?? 'Galería de portafolio'}
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
                        aria-label="Cerrar"
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
                                aria-label="Anterior"
                            >
                                <ChevronLeft className="h-6 w-6" />
                            </Button>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="absolute right-3 z-20 hidden h-11 w-11 rounded-full bg-background/50 backdrop-blur-sm hover:bg-background/70 sm:flex"
                                onClick={goNext}
                                aria-label="Siguiente"
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

                {(active?.title || active?.caption) && (
                    <div className="border-t border-border/60 px-5 py-4">
                        {active.title && (
                            <p className="font-display text-lg font-semibold">{active.title}</p>
                        )}
                        {active.caption && (
                            <p className="mt-1 text-sm text-muted-foreground">{active.caption}</p>
                        )}
                        {items.length > 1 && activeIndex !== null && (
                            <p className="mt-2 font-mono text-xs text-muted-foreground">
                                {activeIndex + 1} / {items.length}
                            </p>
                        )}
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
