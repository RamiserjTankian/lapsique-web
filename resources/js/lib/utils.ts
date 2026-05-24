import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatMxn(amount: number): string {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        maximumFractionDigits: 0,
    }).format(amount);
}

/** Parse YYYY-MM-DD as local calendar date (avoids UTC timezone shifts). */
export function parseSlotDate(dateKey: string): Date {
    const [year, month, day] = dateKey.split('-').map(Number);

    return new Date(year, month - 1, day);
}
