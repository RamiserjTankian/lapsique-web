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

    return (
        <footer className="mt-8 border-t border-white/15 bg-[#07090b] py-14 text-white">
            <div className="mx-auto max-w-6xl px-4 sm:px-6">
                <div className="grid gap-10 border-b border-white/15 pb-12 lg:grid-cols-[1fr_1.2fr]">
                    <div>
                        <p className="font-display text-4xl font-semibold">{site.name}</p>
                        <p className="mt-3 max-w-md text-sm leading-relaxed text-white/60">
                            {locale === 'en'
                                ? 'Electronic music, visuals, live sets, and commercial audiovisual production in Riviera Maya.'
                                : 'Música electrónica, visuales, sets en vivo y producción audiovisual comercial en Riviera Maya.'}
                        </p>
                        <p className="mt-5 text-sm text-white/60">
                            {t('common.footer.behind_camera')}{' '}
                            <a href={CREATOR_PROFILE.instagramUrl} target="_blank" rel="noopener noreferrer" className="font-medium text-white hover:text-primary">
                                Ramiro Díaz · {CREATOR_PROFILE.instagramHandle}
                            </a>
                        </p>
                    </div>
                    <div className="grid gap-8 sm:grid-cols-3">
                        <FooterGroup label={navigation.portfolio.label} links={[navigation.portfolio]} />
                        {navigation.groups.map((group) => <FooterGroup key={group.id} label={group.label} links={group.links} />)}
                    </div>
                </div>
                <div className="mt-8 flex flex-col gap-4 text-xs uppercase tracking-[0.12em] text-white/45 sm:flex-row sm:items-center sm:justify-between">
                    <p>© {year} {site.name}. {t('common.footer.copyright')}</p>
                    <a href={site.instagramUrl} target="_blank" rel="noopener noreferrer" className="hover:text-white">Instagram</a>
                </div>
            </div>
        </footer>
    );
}

function FooterGroup({ label, links }: { label: string; links: Array<{ id: string; label: string; href: string }> }) {
    return (
        <div>
            <p className="alpha-kicker text-primary">{label}</p>
            <div className="mt-4 grid gap-3">
                {links.map((link) => (
                    <Link key={link.id} href={link.href} className="font-ui-display text-sm font-bold uppercase tracking-[0.06em] text-white/70 hover:text-white">
                        {link.label}
                    </Link>
                ))}
            </div>
        </div>
    );
}
