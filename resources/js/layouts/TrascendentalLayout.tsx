import { Link, usePage } from '@inertiajs/react';
import { Camera, Mail, Menu, Music2, Users, X } from 'lucide-react';
import { useState, type ReactNode } from 'react';
import { LanguageToggle } from '@/components/lapsique/LanguageToggle';
import { SeoHead } from '@/components/lapsique/SeoHead';
import { WhatsAppFab } from '@/components/lapsique/WhatsAppFab';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';

interface TrascendentalLayoutProps {
    children: ReactNode;
}

const navRoutes = [
    ['services', 'trascendental.services'],
    ['cases', 'trascendental.cases'],
    ['events', 'trascendental.events'],
    ['tours', 'trascendental.tours'],
    ['about', 'trascendental.about'],
    ['contact', 'trascendental.contact'],
] as const;

export function TrascendentalLayout({ children }: TrascendentalLayoutProps) {
    const { ziggy, site } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const [open, setOpen] = useState(false);
    const isPreview = new URL(ziggy.location).pathname.startsWith('/trascendental');
    const homeHref = isPreview
        ? route('trascendental.home', undefined, false, ziggy)
        : route('home', undefined, false, ziggy);

    return (
        <div className="trascendental-site min-h-screen bg-[#f5f5f2] text-black">
            <SeoHead />
            <header className="sticky top-0 z-40 border-b border-black/15 bg-[#f5f5f2]/95">
                <div className="mx-auto flex h-16 max-w-[1500px] items-center justify-between px-4 sm:px-6 lg:px-8">
                    <Link href={homeHref} className="text-[1.05rem] font-black uppercase tracking-normal">
                        TRASCENDENTAL.
                    </Link>

                    <nav className="hidden items-center gap-7 text-[0.72rem] font-bold uppercase md:flex">
                        {navRoutes.map(([key, name]) => (
                            <Link
                                key={name}
                                href={route(name, undefined, false, ziggy)}
                                className="border-b border-transparent pb-1 hover:border-black"
                            >
                                {t(`trascendental.nav.${key}`)}
                            </Link>
                        ))}
                    </nav>

                    <div className="hidden items-center gap-3 md:flex">
                        <LanguageToggle />
                        <Link
                            href={route('trascendental.contact', undefined, false, ziggy)}
                            className="rounded-full bg-black px-5 py-2 text-[0.72rem] font-bold uppercase text-white"
                        >
                            {t('trascendental.nav.booking')}
                        </Link>
                    </div>

                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="md:hidden"
                        onClick={() => setOpen((value) => !value)}
                        aria-label={open ? 'Close navigation' : 'Open navigation'}
                    >
                        {open ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                    </Button>
                </div>

                <div className={cn('border-t border-black/15 px-4 py-5 md:hidden', open ? 'block' : 'hidden')}>
                    <nav className="grid gap-4 text-2xl font-black uppercase">
                        {navRoutes.map(([key, name]) => (
                            <Link
                                key={name}
                                href={route(name, undefined, false, ziggy)}
                                onClick={() => setOpen(false)}
                            >
                                {t(`trascendental.nav.${key}`)}
                            </Link>
                        ))}
                    </nav>
                    <div className="mt-6">
                        <LanguageToggle />
                    </div>
                </div>
            </header>

            <main>{children}</main>

            <footer className="border-t border-black/15 px-4 py-10 sm:px-6 lg:px-8">
                <div className="mx-auto flex max-w-[1500px] flex-col gap-6 text-sm sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-3xl font-black uppercase">TRASCENDENTAL.</p>
                        <p className="mt-2 max-w-md text-black/60">Artists / Events / Culture</p>
                    </div>
                    <div className="flex flex-wrap items-center gap-3">
                        {site.email ? (
                            <a href={`mailto:${site.email}`} className="inline-flex items-center gap-2 font-bold uppercase">
                                <Mail className="h-4 w-4" />
                                Email
                            </a>
                        ) : null}
                        {site.instagramUrl ? (
                            <a href={site.instagramUrl} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-2 font-bold uppercase">
                                <Camera className="h-4 w-4" />
                                Instagram
                            </a>
                        ) : null}
                        {site.facebookUrl ? (
                            <a href={site.facebookUrl} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-2 font-bold uppercase">
                                <Users className="h-4 w-4" />
                                Facebook
                            </a>
                        ) : null}
                        {site.residentAdvisorUrl ? (
                            <a href={site.residentAdvisorUrl} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-2 font-bold uppercase">
                                <Music2 className="h-4 w-4" />
                                RA
                            </a>
                        ) : null}
                        <Link href={route('trascendental.contact', undefined, false, ziggy)} className="font-bold uppercase">
                            {t('trascendental.hero.produce_cta')}
                        </Link>
                    </div>
                </div>
            </footer>
            <WhatsAppFab />
        </div>
    );
}
