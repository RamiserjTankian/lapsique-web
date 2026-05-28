import type { TranslateFn } from '@/hooks/useTranslations';

export interface RecordingGearItem {
    quantity: number;
    name: string;
    spec: string;
    imageSrc?: string;
}

export interface RecordingGearGroup {
    id: string;
    label: string;
    items: RecordingGearItem[];
}

export function getRecordingGearGroups(t: TranslateFn): RecordingGearGroup[] {
    return [
        {
            id: 'aerial',
            label: t('funnel.gear.group_aerial'),
            items: [
                {
                    quantity: 3,
                    name: t('funnel.gear.aerial_name'),
                    spec: t('funnel.gear.aerial_spec'),
                },
            ],
        },
        {
            id: 'cameras',
            label: t('funnel.gear.group_cameras'),
            items: [
                {
                    quantity: 1,
                    name: 'Sony α7 V',
                    spec: t('funnel.gear.sony_a7v_spec'),
                    imageSrc: '/images/equipment/sony-a7v.svg',
                },
                {
                    quantity: 1,
                    name: 'Sony α7 IV',
                    spec: t('funnel.gear.sony_a7iv_spec'),
                    imageSrc: '/images/equipment/sony-a7iv.svg',
                },
                {
                    quantity: 1,
                    name: 'Sony α6700',
                    spec: t('funnel.gear.sony_a6700_spec'),
                    imageSrc: '/images/equipment/sony-a6700.svg',
                },
            ],
        },
        {
            id: 'lenses',
            label: t('funnel.gear.group_lenses'),
            items: [
                { quantity: 1, name: '28-70 mm', spec: t('funnel.gear.lens_2870_spec') },
                { quantity: 1, name: '11 mm', spec: t('funnel.gear.lens_11_spec') },
                { quantity: 1, name: '30 mm', spec: t('funnel.gear.lens_30_spec') },
                { quantity: 1, name: '35 mm', spec: t('funnel.gear.lens_35_spec') },
                { quantity: 1, name: '50 mm', spec: t('funnel.gear.lens_50_spec') },
            ],
        },
        {
            id: 'light',
            label: t('funnel.gear.group_light'),
            items: [
                {
                    quantity: 1,
                    name: 'Godox V1S Pro',
                    spec: t('funnel.gear.godox_v1_spec'),
                },
                {
                    quantity: 1,
                    name: t('funnel.gear.godox_trigger_name'),
                    spec: t('funnel.gear.godox_trigger_spec'),
                },
            ],
        },
    ];
}
