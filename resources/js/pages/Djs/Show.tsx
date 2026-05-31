import { Head } from '@inertiajs/react';
import { motion } from 'framer-motion';
import SiteLayout from '@/layouts/SiteLayout';
import { DjGallery } from '@/components/lapsique/DjGallery';
import { DjProfileSections } from '@/components/lapsique/DjProfileSections';
import { DjVideoShowcase } from '@/components/lapsique/DjVideoShowcase';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useTranslations } from '@/hooks/useTranslations';
import type { DjItem, VideoItem } from '@/types';

interface DjsShowProps {
    dj: DjItem;
    videos: VideoItem[];
}

export function DjsShow({ dj, videos }: DjsShowProps) {
    const { t } = useTranslations();
    const initials = dj.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();

    const heroImage = dj.cover_url || dj.avatar_url;

    return (
        <SiteLayout>
            <Head title={dj.name} />

            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.5 }}
                className="relative -mx-4 mb-10 overflow-hidden sm:-mx-6 sm:rounded-2xl"
            >
                <div className="relative aspect-[21/9] min-h-[200px] max-h-[360px] w-full">
                    {heroImage ? (
                        <img
                            src={heroImage}
                            alt={dj.name}
                            className="absolute inset-0 h-full w-full object-cover object-top"
                        />
                    ) : (
                        <div className="absolute inset-0 bg-gradient-to-br from-muted/50 to-background" />
                    )}
                    <div className="absolute inset-0 bg-gradient-to-t from-background via-background/60 to-background/20" />
                    <div className="absolute inset-x-0 bottom-0 flex items-end gap-4 p-6 sm:p-8">
                        <Avatar className="h-16 w-16 border-2 border-primary/30 shadow-lg sm:h-20 sm:w-20">
                            <AvatarImage src={dj.avatar_url ?? undefined} alt={dj.name} />
                            <AvatarFallback className="font-display text-lg">{initials}</AvatarFallback>
                        </Avatar>
                        <div className="min-w-0 flex-1 pb-1">
                            {dj.is_highlighted && (
                                <span className="mb-2 inline-block rounded-full bg-primary/20 px-3 py-1 text-xs font-medium uppercase tracking-wider text-primary">
                                    {t('pages.djs.featured_label')}
                                </span>
                            )}
                            <h1 className="font-display text-3xl font-bold tracking-tight md:text-4xl">{dj.name}</h1>
                        </div>
                    </div>
                </div>
            </motion.div>

            <DjProfileSections
                dj={dj}
                afterBio={videos.length > 0 ? <DjVideoShowcase videos={videos} /> : null}
            />
            <DjGallery images={dj.gallery ?? []} djName={dj.name} />
        </SiteLayout>
    );
}

export default DjsShow;
