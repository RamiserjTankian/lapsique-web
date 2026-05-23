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

export const RECORDING_GEAR_GROUPS: RecordingGearGroup[] = [
    {
        id: 'aerial',
        label: 'Aéreo',
        items: [
            {
                quantity: 1,
                name: 'DJI Air 3',
                spec: 'Dron 4K · doble sensor · cobertura aérea en set',
            },
        ],
    },
    {
        id: 'cameras',
        label: 'Cámaras',
        items: [
            {
                quantity: 1,
                name: 'Sony α7 V',
                spec: 'Full frame · 4K · 61 MP',
                imageSrc: '/images/equipment/sony-a7v.svg',
            },
            {
                quantity: 1,
                name: 'Sony α7 IV',
                spec: 'Full frame · 4K 60p · 33 MP',
                imageSrc: '/images/equipment/sony-a7iv.svg',
            },
            {
                quantity: 1,
                name: 'Sony α6700',
                spec: 'APS-C · 4K · cámara B y gimbal',
                imageSrc: '/images/equipment/sony-a6700.svg',
            },
        ],
    },
    {
        id: 'lenses',
        label: 'Óptica',
        items: [
            { quantity: 1, name: '28-70 mm', spec: 'f/2.8 · zoom estándar versátil' },
            { quantity: 1, name: '11 mm', spec: 'f/1.8 · ultrawide · ambientes y locación' },
            { quantity: 1, name: '30 mm', spec: 'f/1.4 · retrato y producto' },
            { quantity: 1, name: '35 mm', spec: 'f/1.8 · documental y entrevistas' },
            { quantity: 1, name: '50 mm', spec: 'f/1.8 · look natural y bokeh' },
        ],
    },
    {
        id: 'light',
        label: 'Luz',
        items: [
            {
                quantity: 1,
                name: 'Godox V1S Pro',
                spec: 'Flash de cámara · luz de relleno y retrato',
            },
            {
                quantity: 1,
                name: 'Disparador remoto Godox',
                spec: 'Control de flash Pro · sincronía en set',
            },
        ],
    },
];
