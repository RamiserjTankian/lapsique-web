import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationLinksProps {
    links: PaginationLink[];
    className?: string;
}

function normalizePaginationLinks(links: PaginationLink[] | Record<string, string | null> | undefined): PaginationLink[] {
    if (!links) {
        return [];
    }

    if (Array.isArray(links)) {
        return links;
    }

    return [];
}

export function PaginationLinks({ links = [], className }: PaginationLinksProps) {
    const safeLinks = normalizePaginationLinks(links as PaginationLink[] | Record<string, string | null>);
    const pageLinks = safeLinks.filter(
        (link) => link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;',
    );

    if (pageLinks.length <= 1) {
        return null;
    }

    return (
        <nav className={cn('mt-10 flex flex-wrap justify-center gap-2', className)} aria-label="Paginación">
            {safeLinks.map((link, index) => {
                if (!link.url) {
                    return (
                        <span
                            key={`${link.label}-${index}`}
                            className="rounded-lg border border-border/40 px-3 py-2 text-sm text-muted-foreground/50"
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    );
                }

                return (
                    <Link
                        key={`${link.label}-${index}`}
                        href={link.url}
                        preserveScroll
                        className={cn(
                            'rounded-lg border px-3 py-2 text-sm transition',
                            link.active
                                ? 'border-primary/40 bg-primary/10 text-primary'
                                : 'border-border/60 bg-secondary/50 text-muted-foreground hover:border-primary/30 hover:text-foreground',
                        )}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                );
            })}
        </nav>
    );
}
