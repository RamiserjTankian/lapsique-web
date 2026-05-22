import { Link, usePage } from '@inertiajs/react';
import { openNewsletterModal } from '@/lib/funnelModalEvents';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

function isFunnelNewsletterRoute(url: string): boolean {
    try {
        const path = new URL(url, window.location.origin).pathname;

        return path === '/' || path === '/djset';
    } catch {
        return false;
    }
}

export function SiteFooter() {
    const { ziggy, site } = usePage<PageProps>().props;
    const { url } = usePage<PageProps>();
    const year = new Date().getFullYear();
    const showNewsletterCta = isFunnelNewsletterRoute(url);

    return (
        <footer className="mt-20 border-t border-border py-12">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p className="font-display font-semibold">{site.name}</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Productora audiovisual · Riviera Maya
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-4 text-sm text-muted-foreground">
                        <a
                            href={site.instagramUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="hover:text-foreground"
                        >
                            Instagram
                        </a>
                        <Link
                            href={route('portfolio.index', undefined, false, ziggy)}
                            className="hover:text-foreground"
                        >
                            Portafolio
                        </Link>
                        <Link
                            href={route('customers.portal', undefined, false, ziggy)}
                            className="hover:text-foreground"
                        >
                            Mi portal
                        </Link>
                        {showNewsletterCta && (
                            <button
                                type="button"
                                className="hover:text-foreground"
                                onClick={() => openNewsletterModal('footer')}
                            >
                                Recibir novedades
                            </button>
                        )}
                    </div>
                </div>
                <p className="mt-8 text-xs text-muted-foreground">
                    © {year} {site.name}. Todos los derechos reservados.
                </p>
            </div>
        </footer>
    );
}
