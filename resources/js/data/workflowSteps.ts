import { CONTENT_REEL_DURATION_SECONDS } from '@/data/contentOffer';

export interface WorkflowStep {
    id: string;
    step: number;
    title: string;
    description: string;
}

export const WORKFLOW_STEPS: WorkflowStep[] = [
    {
        id: 'meeting',
        step: 1,
        title: 'Reunión previa a la sesión',
        description:
            'Definimos contigo qué vamos a hacer, el guion visual y la historia que contaremos antes de llegar al set.',
    },
    {
        id: 'shoot',
        step: 2,
        title: 'Grabación de sesión',
        description:
            `Nos apegamos al guion acordado para capturar en tierra y con dron DJI las tomas del reel de ${CONTENT_REEL_DURATION_SECONDS} segundos con cámara Sony.`,
    },
    {
        id: 'drive',
        step: 3,
        title: 'Respaldo en Drive',
        description:
            'Subimos el material en crudo a la nube para que accedas a tu contenido sin esperar la edición.',
    },
    {
        id: 'edit',
        step: 4,
        title: 'Edición en DaVinci Resolve',
        description:
            'Postproducción con color, corte y acabado cinematográfico en un flujo profesional de edición.',
    },
    {
        id: 'delivery',
        step: 5,
        title: 'Entrega en 3 días hábiles',
        description:
            `Recibes tu reel editado de ${CONTENT_REEL_DURATION_SECONDS} segundos y material listo en un máximo de 3 días hábiles, de lunes a viernes.`,
    },
];
