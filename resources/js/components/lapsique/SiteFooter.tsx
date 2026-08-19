import { Link, usePage } from '@inertiajs/react';
import { CREATOR_PROFILE } from '@/data/creatorProfile';
import { buildSiteNavigation } from '@/data/siteNavigation';
import { useTranslations } from '@/hooks/useTranslations';
import type { PageProps } from '@/types';

export function SiteFooter() {
    const { ziggy, site } = usePage<PageProps>().props;
    const { t, locale } = useTranslations();
    const navigation = buildSiteNavigation(ziggy, locale);
    const year = new Date().getFullYear();
    const eventLink = navigation.groups.find((group) => group.id === 'scene')?.links.find((link) => link.id === 'events');

    return (
        <footer className="mt-8 border-t border-white/15 bg-[#07090b] py-8 text-white">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <div className="flex flex-col gap-6 border-b border-white/15 pb-7 sm:flex-row sm:items-center sm:justify-between">
                    <p className="font-display text-3xl font-semibold">{site.name}</p>
                    <nav aria-label={locale === 'en' ? 'Footer' : 'Pie de página'} className="flex flex-wrap items-center gap-x-6 gap-y-3 font-ui-display text-xs font-bold uppercase tracking-[0.08em] text-white/70">
                        <Link href={navigation.portfolio.href} className="hover:text-primary">{navigation.portfolio.label}</Link>
                        {eventLink ? <Link href={eventLink.href} className="hover:text-primary">{eventLink.label}</Link> : null}
                        <a href={site.instagramUrl} target="_blank" rel="noopener noreferrer" className="hover:text-primary">Instagram</a>
                    </nav>
                </div>
                <div className="mt-6 flex flex-col gap-3 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
                    <p>© {year} {site.name}. {t('common.footer.copyright')}</p>
                    <a href={CREATOR_PROFILE.instagramUrl} target="_blank" rel="noopener noreferrer" className="hover:text-white">Ramiro Díaz · {CREATOR_PROFILE.instagramHandle}</a>
                </div>
            </div>
        </footer>
    );
}
