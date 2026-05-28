import { useState } from 'react';
import { AtSign, Globe, Music2, Video } from 'lucide-react';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { SpecBadge } from '@/components/lapsique/SpecBadge';
import { useTranslations } from '@/hooks/useTranslations';
import type { DjItem } from '@/types';

const BIO_CLAMP_LENGTH = 280;

interface DjProfileSectionsProps {
    dj: DjItem;
}

export function DjProfileSections({ dj }: DjProfileSectionsProps) {
    const { t } = useTranslations();
    const [bioExpanded, setBioExpanded] = useState(false);
    const bio = dj.bio?.trim() ?? '';
    const showBioToggle = bio.length > BIO_CLAMP_LENGTH;
    const displayBio = showBioToggle && !bioExpanded ? `${bio.slice(0, BIO_CLAMP_LENGTH)}…` : bio;

    const links = [
        dj.instagram_handle && {
            href: `https://instagram.com/${dj.instagram_handle.replace('@', '')}`,
            label: dj.instagram_handle,
            icon: AtSign,
        },
        dj.youtube_url && { href: dj.youtube_url, label: 'YouTube', icon: Video },
        dj.soundcloud_url && { href: dj.soundcloud_url, label: 'SoundCloud', icon: Music2 },
        dj.website_url && { href: dj.website_url, label: t('common.links.website'), icon: Globe },
    ].filter(Boolean) as Array<{ href: string; label: string; icon: typeof AtSign }>;

    const rider = dj.technical_rider ?? [];

    return (
        <div className="space-y-10 pb-8">
            {dj.tags && dj.tags.length > 0 && (
                <div className="flex flex-wrap gap-2">
                    {dj.tags.map((tag) => (
                        <SpecBadge key={tag} highlight={tag === 'star' || tag === 'hot'}>
                            {tag}
                        </SpecBadge>
                    ))}
                </div>
            )}

            {bio && (
                <section className="max-w-2xl">
                    <h2 className="font-display text-lg font-semibold">{t('pages.djs.bio_heading')}</h2>
                    <p className="mt-3 text-sm leading-relaxed text-muted-foreground md:text-base whitespace-pre-line">
                        {displayBio}
                    </p>
                    {showBioToggle && (
                        <Button
                            type="button"
                            variant="link"
                            className="mt-2 h-auto p-0 text-primary"
                            onClick={() => setBioExpanded((v) => !v)}
                        >
                            {bioExpanded ? t('pages.djs.read_less') : t('pages.djs.read_more')}
                        </Button>
                    )}
                </section>
            )}

            {links.length > 0 && (
                <section>
                    <h2 className="font-display mb-4 text-lg font-semibold">{t('pages.djs.links_heading')}</h2>
                    <div className="flex flex-wrap gap-3">
                        {links.map(({ href, label, icon: Icon }) => (
                            <Button key={href} variant="glass" size="sm" asChild>
                                <a href={href} target="_blank" rel="noopener noreferrer">
                                    <Icon className="mr-2 h-4 w-4" />
                                    {label}
                                </a>
                            </Button>
                        ))}
                    </div>
                </section>
            )}

            {rider.length > 0 && (
                <section>
                    <Accordion type="single" collapsible className="rounded-xl border border-border/70 bg-secondary/30">
                        <AccordionItem value="rider" className="border-none px-4">
                            <AccordionTrigger className="font-display text-lg hover:no-underline">
                                {t('pages.djs.rider_heading')}
                            </AccordionTrigger>
                            <AccordionContent>
                                <ul className="space-y-3 pb-2">
                                    {rider.map((item, index) => (
                                        <li
                                            key={`${item.label}-${index}`}
                                            className="rounded-lg border border-border/50 bg-card/50 px-4 py-3"
                                        >
                                            <p className="font-medium text-foreground">{item.label}</p>
                                            {item.value && (
                                                <p className="mt-1 text-sm text-muted-foreground">{item.value}</p>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </section>
            )}
        </div>
    );
}
