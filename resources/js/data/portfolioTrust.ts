import type { TranslateFn } from '@/hooks/useTranslations';

export interface PortfolioTrustStat {
    id: string;
    value: string;
    title: string;
}

export function getPortfolioTrustStats(t: TranslateFn): PortfolioTrustStat[] {
    return [
        {
            id: 'productions',
            value: t('funnel.trust.stat_productions_value'),
            title: t('funnel.trust.stat_productions_title'),
        },
        {
            id: 'clients',
            value: t('funnel.trust.stat_clients_value'),
            title: t('funnel.trust.stat_clients_title'),
        },
        {
            id: 'local',
            value: t('funnel.trust.stat_local_value'),
            title: t('funnel.trust.stat_local_title'),
        },
    ];
}
