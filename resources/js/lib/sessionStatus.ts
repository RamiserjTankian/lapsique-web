import { CalendarClock, CheckCircle2, Clock, Film, PackageCheck, RotateCcw, XCircle } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { TranslateFn } from '@/hooks/useTranslations';
import type { ContentBookingData } from '@/types';

export type SessionStepKey =
    | 'payment_pending'
    | 'paid'
    | 'in_progress'
    | 'rescheduled'
    | 'delivered';

export type SessionStepState = 'complete' | 'current' | 'upcoming' | 'skipped';

export type SessionExceptionKey = 'cancelled' | 'failed';

export interface SessionStep {
    key: SessionStepKey;
    label: string;
    description: string;
    icon: LucideIcon;
    state: SessionStepState;
}

export interface ResolvedSessionStatus {
    steps: SessionStep[];
    currentKey: SessionStepKey;
    /** Progress 0..1 across the happy-path steps. */
    progress: number;
    isException: boolean;
    exceptionKey: SessionExceptionKey | null;
    exceptionLabel: string | null;
    wasRescheduled: boolean;
}

const HAPPY_PATH: SessionStepKey[] = ['payment_pending', 'paid', 'in_progress', 'delivered'];

const STEP_ICONS: Record<SessionStepKey, LucideIcon> = {
    payment_pending: Clock,
    paid: CheckCircle2,
    in_progress: Film,
    rescheduled: RotateCcw,
    delivered: PackageCheck,
};

function hasDeliverables(booking: ContentBookingData): boolean {
    return (
        (booking.deliverable_links?.length ?? 0) > 0
        || Boolean(booking.deliverables_ready_at)
        || Boolean(booking.deliverables_drive_url)
    );
}

function isPaid(booking: ContentBookingData): boolean {
    return booking.status === 'confirmed' || Boolean(booking.paid_at);
}

function slotIsPast(booking: ContentBookingData): boolean {
    if (!booking.slot?.date) {
        return false;
    }

    const slotDay = new Date(`${booking.slot.date}T23:59:59`);

    return Number.isFinite(slotDay.getTime()) && slotDay.getTime() < Date.now();
}

/**
 * Derive the canonical fulfillment status of a content booking from the data
 * available on the frontend (no dedicated DB column).
 */
export function resolveSessionStatus(
    booking: ContentBookingData,
    t: TranslateFn,
): ResolvedSessionStatus {
    const exceptionKey: SessionExceptionKey | null =
        booking.status === 'cancelled'
            ? 'cancelled'
            : booking.status === 'failed'
              ? 'failed'
              : null;

    let currentKey: SessionStepKey;

    if (hasDeliverables(booking)) {
        currentKey = 'delivered';
    } else if (isPaid(booking)) {
        currentKey = slotIsPast(booking) ? 'in_progress' : 'paid';
    } else {
        currentKey = 'payment_pending';
    }

    const wasRescheduled = Boolean(booking.was_rescheduled);
    const currentIndex = HAPPY_PATH.indexOf(currentKey);
    const isException = exceptionKey !== null;

    const steps: SessionStep[] = HAPPY_PATH.map((key, index) => {
        let state: SessionStepState;

        if (isException) {
            state = index <= currentIndex ? 'complete' : 'skipped';
        } else if (index < currentIndex) {
            state = 'complete';
        } else if (index === currentIndex) {
            state = 'current';
        } else {
            state = 'upcoming';
        }

        return {
            key,
            label: t(`customer.portal.timeline.${key}`),
            description: t(`customer.portal.timeline.${key}_hint`),
            icon: STEP_ICONS[key],
            state,
        };
    });

    return {
        steps,
        currentKey,
        progress: currentIndex / (HAPPY_PATH.length - 1),
        isException,
        exceptionKey,
        exceptionLabel: exceptionKey
            ? t(`customer.portal.timeline.${exceptionKey}`)
            : null,
        wasRescheduled,
    };
}

export const SESSION_EXCEPTION_ICON: Record<SessionExceptionKey, LucideIcon> = {
    cancelled: XCircle,
    failed: XCircle,
};

export const RESCHEDULED_ICON = RotateCcw;
export const CALENDAR_ICON = CalendarClock;
