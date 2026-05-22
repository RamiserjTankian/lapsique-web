export interface SonyCameraModel {
    id: string;
    name: string;
    pillLabel: string;
    specLine: string;
    imageSrc: string;
    imageAlt: string;
}

export const SONY_BRAND_LOGO = {
    imageSrc: '/images/equipment/sony-logo.svg',
    imageAlt: 'Logo Sony',
} as const;

export const SONY_EQUIPMENT_HEADLINE = 'Look de cine para tu negocio';

export const SONY_EQUIPMENT_EYEBROW = 'Equipo y look';

export const SONY_EQUIPMENT_DESCRIPTION =
    'Sesiones con Sony a7 V, a7 IV y a6700, óptica luminosa y luz de apoyo para imagen cinematográfica en Reels, fotos y campaña de marca. Todo el sistema está pensado para producir imágenes más limpias, más consistentes y más utilizables en venta, pauta y construcción de marca.';

export const SONY_CAMERA_MODELS: SonyCameraModel[] = [
    {
        id: 'a7v',
        name: 'Sony a7 V',
        pillLabel: 'SONY A7 V',
        specLine: 'Full frame · 4K 60p · 61 MP',
        imageSrc: '/images/equipment/sony-a7v.svg',
        imageAlt: 'Cámara Sony a7 V, cuerpo mirrorless full frame',
    },
    {
        id: 'a7iv',
        name: 'Sony a7 IV',
        pillLabel: 'SONY A7 IV',
        specLine: 'Full frame · 4K 60p · 33 MP',
        imageSrc: '/images/equipment/sony-a7iv.svg',
        imageAlt: 'Cámara Sony a7 IV, cuerpo mirrorless full frame',
    },
    {
        id: 'a6700',
        name: 'Sony a6700',
        pillLabel: 'SONY A6700',
        specLine: 'APS-C · 4K 60p · 26 MP',
        imageSrc: '/images/equipment/sony-a6700.svg',
        imageAlt: 'Cámara Sony a6700, cuerpo mirrorless APS-C',
    },
];
