import { useTranslations } from '@/hooks/useTranslations';
import { cn } from '@/lib/utils';
import {
    RESCHEDULED_ICON,
    SESSION_EXCEPTION_ICON,
    resolveSessionStatus,
} from '@/lib/sessionStatus';
import type { SessionStep, SessionStepState } from '@/lib/sessionStatus';
import type { ContentBookingData } from '@/types';

const NODE_STYLES: Record<SessionStepState, string> = {
    complete: 'border-emerald-500/60 bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
    current: 'border-amber-500/70 bg-amber-500/20 text-amber-600 dark:text-amber-300 ring-2 ring-amber-500/25',
    upcoming: 'border-border bg-muted/40 text-muted-foreground',
    skipped: 'border-border bg-muted/20 text-muted-foreground/60',
};

const LABEL_STYLES: Record<SessionStepState, string> = {
    complete: 'text-foreground',
    current: 'text-foreground font-semibold',
    upcoming: 'text-muted-foreground',
    skipped: 'text-muted-foreground/60 line-through',
};

const CONNECTOR_DONE = 'bg-emerald-500/60';
const CONNECTOR_PENDING = 'bg-border';

export function SessionStatusTimeline({ booking }: { booking: ContentBookingData }) {
    const { t } = useTranslations();
    const status = resolveSessionStatus(booking, t);

    if (status.isException && status.exceptionKey) {
        const ExceptionIcon = SESSION_EXCEPTION_ICON[status.exceptionKey];

        return (
            <div className="flex items-center gap-3 rounded-xl border border-destructive/40 bg-destructive/10 px-4 py-3">
                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-destructive/50 bg-destructive/15 text-destructive">
                    <ExceptionIcon className="h-4 w-4" />
                </span>
                <div>
                    <p className="text-sm font-semibold text-foreground">{status.exceptionLabel}</p>
                    <p className="text-xs text-muted-foreground">
                        {t(`customer.portal.timeline.${status.exceptionKey}_hint`)}
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {status.wasRescheduled && <RescheduledNote />}

            {/* Desktop: horizontal stepper */}
            <ol className="hidden items-start gap-0 md:flex">
                {status.steps.map((step, index) => (
                    <li key={step.key} className="flex flex-1 flex-col items-center text-center">
                        <div className="flex w-full items-center">
                            <span
                                className={cn(
                                    'h-0.5 flex-1 rounded-full',
                                    index === 0
                                        ? 'opacity-0'
                                        : step.state === 'complete' || step.state === 'current'
                                          ? CONNECTOR_DONE
                                          : CONNECTOR_PENDING,
                                )}
                            />
                            <StepNode step={step} />
                            <span
                                className={cn(
                                    'h-0.5 flex-1 rounded-full',
                                    index === status.steps.length - 1
                                        ? 'opacity-0'
                                        : step.state === 'complete'
                                          ? CONNECTOR_DONE
                                          : CONNECTOR_PENDING,
                                )}
                            />
                        </div>
                        <p className={cn('mt-2 text-xs', LABEL_STYLES[step.state])}>{step.label}</p>
                        <p className="mt-0.5 text-[11px] leading-snug text-muted-foreground">
                            {step.description}
                        </p>
                    </li>
                ))}
            </ol>

            {/* Mobile: vertical stepper */}
            <ol className="space-y-0 md:hidden">
                {status.steps.map((step, index) => (
                    <li key={step.key} className="flex gap-3">
                        <div className="flex flex-col items-center">
                            <StepNode step={step} />
                            {index < status.steps.length - 1 && (
                                <span
                                    className={cn(
                                        'my-1 w-0.5 flex-1 rounded-full',
                                        step.state === 'complete' ? CONNECTOR_DONE : CONNECTOR_PENDING,
                                    )}
                                />
                            )}
                        </div>
                        <div className="pb-4">
                            <p className={cn('text-sm', LABEL_STYLES[step.state])}>{step.label}</p>
                            <p className="text-xs leading-snug text-muted-foreground">
                                {step.description}
                            </p>
                        </div>
                    </li>
                ))}
            </ol>
        </div>
    );
}

function StepNode({ step }: { step: SessionStep }) {
    const Icon = step.icon;

    return (
        <span
            className={cn(
                'flex h-9 w-9 shrink-0 items-center justify-center rounded-full border transition-colors',
                NODE_STYLES[step.state],
            )}
        >
            <Icon className="h-4 w-4" />
        </span>
    );
}

function RescheduledNote() {
    const { t } = useTranslations();
    const Icon = RESCHEDULED_ICON;

    return (
        <div className="flex items-center gap-2 rounded-lg border border-sky-500/40 bg-sky-500/10 px-3 py-2 text-xs text-sky-700 dark:text-sky-300">
            <Icon className="h-3.5 w-3.5 shrink-0" />
            <span>{t('customer.portal.timeline.rescheduled_note')}</span>
        </div>
    );
}
