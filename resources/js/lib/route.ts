import { route as ziggyRoute } from 'ziggy-js';
import type { PageProps } from '@/types';

type ZiggyPageProps = Pick<PageProps, 'ziggy'>;

export function route(
    name: string,
    params?: Record<string, unknown>,
    absolute?: boolean,
    ziggy?: ZiggyPageProps['ziggy'],
): string {
    if (ziggy) {
        return ziggyRoute(name, params, absolute, ziggy);
    }

    return ziggyRoute(name, params, absolute);
}
