import { CONTENT_REEL_DURATION_SECONDS } from '@/data/contentOffer';
import type { TranslateFn } from '@/hooks/useTranslations';

export interface WorkflowStep {
    id: string;
    step: number;
    title: string;
    description: string;
}

export function getWorkflowSteps(t: TranslateFn): WorkflowStep[] {
    const seconds = CONTENT_REEL_DURATION_SECONDS;

    return [
        {
            id: 'meeting',
            step: 1,
            title: t('funnel.workflow.meeting_title'),
            description: t('funnel.workflow.meeting_description'),
        },
        {
            id: 'shoot',
            step: 2,
            title: t('funnel.workflow.shoot_title'),
            description: t('funnel.workflow.shoot_description', { seconds }),
        },
        {
            id: 'drive',
            step: 3,
            title: t('funnel.workflow.drive_title'),
            description: t('funnel.workflow.drive_description'),
        },
        {
            id: 'edit',
            step: 4,
            title: t('funnel.workflow.edit_title'),
            description: t('funnel.workflow.edit_description'),
        },
        {
            id: 'delivery',
            step: 5,
            title: t('funnel.workflow.delivery_title'),
            description: t('funnel.workflow.delivery_description', { seconds }),
        },
    ];
}
