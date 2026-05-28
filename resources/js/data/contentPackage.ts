import {
    CONTENT_DRONE_SHOTS,
    CONTENT_PHOTOS_COUNT,
    CONTENT_REEL_DURATION_SECONDS,
} from '@/data/contentOffer';
import type { TranslateFn } from '@/hooks/useTranslations';

export interface ContentPackageItem {
    id: string;
    title: string;
    description: string;
}

export function getContentPackageItems(t: TranslateFn): ContentPackageItem[] {
    const replacements = {
        seconds: CONTENT_REEL_DURATION_SECONDS,
        drone_shots: CONTENT_DRONE_SHOTS,
        photos_count: CONTENT_PHOTOS_COUNT,
    };

    return [
        {
            id: 'session',
            title: t('funnel.package.session_title'),
            description: t('funnel.package.session_description'),
        },
        {
            id: 'reel',
            title: t('funnel.package.reel_title', replacements),
            description: t('funnel.offer.reel_description'),
        },
        {
            id: 'drone',
            title: t('funnel.package.drone_title', replacements),
            description: t('funnel.offer.drone_description'),
        },
        {
            id: 'photos',
            title: t('funnel.package.photos_title', replacements),
            description: t('funnel.package.photos_description'),
        },
        {
            id: 'cloud',
            title: t('funnel.package.cloud_title'),
            description: t('funnel.package.cloud_description'),
        },
    ];
}
