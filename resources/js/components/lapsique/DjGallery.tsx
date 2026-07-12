import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ChevronLeft, ChevronRight, X } from 'lucide-react';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { modalShellGallery } from '@/lib/modalLayout';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { useTranslations } from '@/hooks/useTranslations';
import type { DjGalleryImage } from '@/types';

interface DjGalleryProps {
    images: DjGalleryImage[];
    djName: string;
}

export function DjGallery({ images, djName }: DjGalleryProps) {
    const { t } = useTranslations();
    const [activeIndex, setActiveIndex] = useState<number | null>(null);

    if (images.length === 0) {
        return null;
    }

    const active = activeIndex !== null ? images[activeIndex] : null;

    const goPrev = () => {
        if (activeIndex === null) return;
        setActiveIndex((activeIndex - 1 + images.length) % images.length);
    };

    const goNext = () => {
        if (activeIndex === null) return;
        setActiveIndex((activeIndex + 1) % images.length);
    };

    return (
        <section className="border-b border-foreground/20 py-14 md:py-20">
            <p className="alpha-kicker text-primary">Gallery / Photography</p>
            <h2 className="mt-4 mb-8 text-4xl font-semibold md:text-6xl">{t('pages.djs.gallery')}</h2>
            <div className="columns-2 gap-2 sm:columns-3">
                {images.map((image, index) => (
                    <button
                        key={image.id}
                        type="button"
                        onClick={() => setActiveIndex(index)}
                        className="group mb-2 block w-full break-inside-avoid overflow-hidden border border-border/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        <img
                            src={image.thumb_url}
                            alt={`${djName} ${index + 1}`}
                            className="w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                            loading="lazy"
                        />
                    </button>
                ))}
            </div>

            <Dialog open={activeIndex !== null} onOpenChange={(open) => !open && setActiveIndex(null)}>
                <DialogContent showCloseButton={false} className={cn(modalShellGallery, 'w-[min(calc(100vw-1.5rem),62.5rem)]')}>
                    <DialogTitle className="sr-only">{t('pages.djs.gallery_of', { name: djName })}</DialogTitle>
                    <div className="relative flex min-h-[50vh] items-center justify-center bg-black">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            className="absolute top-3 right-3 z-20 rounded-none bg-background/90"
                            onClick={() => setActiveIndex(null)}
                            aria-label={t('common.actions.close')}
                        >
                            <X className="h-5 w-5" />
                        </Button>
                        {images.length > 1 && (
                            <>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    className="absolute left-3 z-20 hidden h-11 w-11 rounded-none bg-background/90 sm:flex"
                                    onClick={goPrev}
                                    aria-label={t('common.actions.previous')}
                                >
                                    <ChevronLeft className="h-6 w-6" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    className="absolute right-3 z-20 hidden h-11 w-11 rounded-none bg-background/90 sm:flex"
                                    onClick={goNext}
                                    aria-label={t('common.actions.next')}
                                >
                                    <ChevronRight className="h-6 w-6" />
                                </Button>
                            </>
                        )}
                        <AnimatePresence mode="wait">
                            {active && (
                                <motion.img
                                    key={active.id}
                                    src={active.url}
                                    alt={djName}
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    exit={{ opacity: 0 }}
                                    className="max-h-[80vh] w-full object-contain"
                                />
                            )}
                        </AnimatePresence>
                    </div>
                </DialogContent>
            </Dialog>
        </section>
    );
}
