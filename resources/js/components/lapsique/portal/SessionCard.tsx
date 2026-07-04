import { format, parseISO } from 'date-fns';
import { Building2, CalendarClock, Camera, Clock, Drone, Music } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { DeliverablePanel } from '@/components/lapsique/portal/DeliverablePanel';
import { SessionStatusTimeline } from '@/components/lapsique/portal/SessionStatusTimeline';
import { useTranslations } from '@/hooks/useTranslations';
import { getDateFnsLocale } from '@/lib/dateLocale';
import { resolveSessionStatus } from '@/lib/sessionStatus';
import { cn } from '@/lib/utils';
import type { ContentBookingData } from '@/types';

const STATUS_BADGE_STYLES: Record<string, string> = {
    payment_pending: 'border-amber-500/40 bg-amber-500/15 text-amber-700 dark:text-amber-300',
    paid: 'border-sky-500/40 bg-sky-500/15 text-sky-700 dark:text-sky-300',
    in_progress: 'border-violet-500/40 bg-violet-500/15 text-violet-700 dark:text-violet-300',
    delivered: 'border-emerald-500/40 bg-emerald-500/15 text-emerald-700 dark:text-emerald-300',
};

export function SessionCard({
    booking,
    locale,
}: {
    booking: ContentBookingData;
    locale: string;
}) {
    const { t } = useTranslations();
    const dateLocale = getDateFnsLocale(locale);
    const status = resolveSessionStatus(booking, t);

    const isDjSet = booking.service_type === 'dj_set';
    const isDroneSession = booking.service_type === 'drone_session';
    const isConstructionProgress = booking.service_type === 'construction_progress';
    const ServiceIcon = isDjSet ? Music : isDroneSession ? Drone : isConstructionProgress ? Building2 : Camera;

    const badgeKey = status.isException ? null : status.currentKey;
    const badgeLabel = status.isException
        ? status.exceptionLabel
        : t(`customer.portal.timeline.${status.currentKey}`);

    return (
        <article className="glass-panel-elevated overflow-hidden rounded-2xl border border-border shadow-[0_20px_50px_var(--glass-panel-shadow)]">
            <header className="flex flex-wrap items-start justify-between gap-3 border-b border-border/60 p-5">
                <div className="flex items-start gap-3">
                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-primary/25 bg-primary/10 text-primary">
                        <ServiceIcon className="h-5 w-5" />
                    </span>
                    <div>
                        <h3 className="font-display text-lg font-semibold text-foreground">
                            {booking.service_name}
                        </h3>
                        {booking.formatted_amount && (
                            <p className="text-sm text-muted-foreground">{booking.formatted_amount}</p>
                        )}
                    </div>
                </div>
                <Badge
                    variant="outline"
                    className={cn(
                        'shrink-0',
                        status.isException
                            ? 'border-destructive/40 bg-destructive/10 text-destructive'
                            : badgeKey
                              ? STATUS_BADGE_STYLES[badgeKey]
                              : undefined,
                    )}
                >
                    {badgeLabel}
                </Badge>
            </header>

            <div className="space-y-5 p-5">
                <div className="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                    {booking.slot ? (
                        <>
                            <span className="inline-flex items-center gap-2 text-foreground">
                                <CalendarClock className="h-4 w-4 text-primary" />
                                {format(parseISO(booking.slot.date), 'EEEE d MMM yyyy', {
                                    locale: dateLocale,
                                })}
                            </span>
                            <span className="inline-flex items-center gap-2 text-foreground">
                                <Clock className="h-4 w-4 text-primary" />
                                {booking.slot.time_label}
                            </span>
                        </>
                    ) : (
                        <span className="text-muted-foreground">
                            {t('customer.portal.no_slot')}
                        </span>
                    )}
                </div>

                <div className="rounded-xl border border-border/60 bg-background/40 p-4">
                    <p className="mb-4 text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                        {t('customer.portal.timeline.heading')}
                    </p>
                    <SessionStatusTimeline booking={booking} />
                </div>

                <DeliverablePanel booking={booking} />
            </div>
        </article>
    );
}
