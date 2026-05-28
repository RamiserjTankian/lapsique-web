import { Download, ExternalLink, FolderOpen, Hourglass } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import type { ContentBookingData } from '@/types';

export function DeliverablePanel({ booking }: { booking: ContentBookingData }) {
    const { t } = useTranslations();

    const links = booking.deliverable_links ?? [];
    const hasLinks = links.length > 0;
    const hasDrive = Boolean(booking.deliverables_drive_url);
    const isReady = hasLinks || hasDrive;

    return (
        <div
            className={cn(
                'rounded-xl border p-4',
                isReady
                    ? 'border-emerald-500/40 bg-emerald-500/[0.06]'
                    : 'border-border bg-muted/30',
            )}
        >
            <div className="flex items-center gap-2">
                <span
                    className={cn(
                        'flex h-8 w-8 items-center justify-center rounded-lg border',
                        isReady
                            ? 'border-emerald-500/40 bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
                            : 'border-border bg-background text-muted-foreground',
                    )}
                >
                    {isReady ? <FolderOpen className="h-4 w-4" /> : <Hourglass className="h-4 w-4" />}
                </span>
                <div>
                    <p className="text-sm font-semibold text-foreground">
                        {isReady
                            ? t('customer.portal.delivery.ready_title')
                            : t('customer.portal.delivery.pending_title')}
                    </p>
                    <p className="text-xs text-muted-foreground">
                        {isReady
                            ? t('customer.portal.delivery.ready_subtitle')
                            : t('customer.portal.delivery.pending_subtitle')}
                    </p>
                </div>
            </div>

            {hasLinks && (
                <div className="mt-4 grid gap-2">
                    {links.map((link) => (
                        <Button key={link.id} variant="cinematic" size="sm" className="justify-between" asChild>
                            <a href={link.url} target="_blank" rel="noopener noreferrer">
                                <span className="flex items-center gap-2">
                                    <Download className="h-4 w-4" />
                                    {link.label}
                                </span>
                                <ExternalLink className="h-3.5 w-3.5 opacity-70" />
                            </a>
                        </Button>
                    ))}
                </div>
            )}

            {!hasLinks && hasDrive && (
                <Button variant="cinematic" size="sm" className="mt-4 w-full justify-center" asChild>
                    <a href={booking.deliverables_drive_url ?? '#'} target="_blank" rel="noopener noreferrer">
                        <FolderOpen className="mr-2 h-4 w-4" />
                        {t('customer.portal.open_drive')}
                        <ExternalLink className="ml-2 h-3.5 w-3.5" />
                    </a>
                </Button>
            )}
        </div>
    );
}
