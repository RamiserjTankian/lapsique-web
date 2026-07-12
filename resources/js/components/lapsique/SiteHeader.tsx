import { Link, usePage } from '@inertiajs/react';
import { CalendarDays, Menu } from 'lucide-react';
import { useMemo, useState, type MouseEvent } from 'react';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
} from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { LanguageToggle } from '@/components/lapsique/LanguageToggle';
import { LapsiqueMediaLogo } from '@/components/lapsique/LapsiqueMediaLogo';
import { buildSiteNavigation, type SiteNavigationLink } from '@/data/siteNavigation';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { markBookingModalPending, openBookingModal } from '@/lib/openBookingModal';
import { route } from '@/lib/route';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';

export function SiteHeader() {
    const { ziggy } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);

    return (
        <header className="sticky top-0 z-50 border-b border-foreground/10 bg-background/95 backdrop-blur-xl">
            <div className="mx-auto flex h-[4.5rem] max-w-6xl items-center justify-between px-4 sm:px-6">
                <Link
                    href={route('home', undefined, false, ziggy)}
                    className="rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                    aria-label="Lapsique Media"
                >
                    <LapsiqueMediaLogo />
                </Link>
                <HeaderActions open={open} setOpen={setOpen} />
            </div>
        </header>
    );
}

function HeaderActions({ open, setOpen }: { open: boolean; setOpen: (open: boolean) => void }) {
    const { props, url } = usePage<PageProps>();
    const { ziggy, customer } = props;
    const { t, locale } = useTranslations();
    const navigation = useMemo(() => buildSiteNavigation(ziggy, locale), [ziggy, locale]);
    const homeAgenda = `${route('home', undefined, false, ziggy)}#agenda`;
    const constructionHref = route('construction-progress.show', undefined, false, ziggy);
    const currentPath = normalizePath(url);
    const isConstruction = currentPath === '/avances-de-obra';
    const bookingHref = isConstruction ? `${constructionHref}#agenda` : homeAgenda;
    const bookingLabel = isConstruction ? t('common.nav.book_progress') : t('common.nav.book_session');

    const openBooking = (event: MouseEvent) => {
        if (document.getElementById('agenda')) {
            event.preventDefault();
            openBookingModal({ source: 'header', analyticsEvent: 'header_cta_clicked' });
            return;
        }
        markBookingModalPending();
    };

    const trackNavigation = (link: SiteNavigationLink, group: string) => {
        trackBookingEvent('editorial_navigation_clicked', {
            section: `header_${group}`,
            content_name: link.id,
            destination: link.href,
        });
    };

    return (
        <div className="flex items-center gap-2">
            <NavigationMenu viewport={false} className="hidden md:flex">
                <NavigationMenuList className="gap-0">
                    <NavigationMenuItem>
                        <NavigationMenuLink asChild active={isActive(currentPath, navigation.portfolio.href)}>
                            <Link
                                href={navigation.portfolio.href}
                                onClick={() => trackNavigation(navigation.portfolio, 'portfolio')}
                                className={desktopNavClass}
                            >
                                {navigation.portfolio.label}
                            </Link>
                        </NavigationMenuLink>
                    </NavigationMenuItem>
                    {navigation.groups.map((group) => (
                        <NavigationMenuItem key={group.id}>
                            <NavigationMenuTrigger className={desktopNavClass}>
                                {group.label}
                            </NavigationMenuTrigger>
                            <NavigationMenuContent className="w-[21rem] rounded-none border-foreground/15 bg-background p-0 shadow-2xl md:!left-auto md:!right-0 md:max-h-[calc(100vh-4.5rem)] md:w-[21rem] md:!overflow-y-auto">
                                <div className="border-b border-foreground/15 px-5 py-4">
                                    <p className="alpha-kicker text-primary">Lapsique / {group.label}</p>
                                </div>
                                <div className="divide-y divide-foreground/10">
                                    {group.links.map((link) => (
                                        <NavigationMenuLink key={link.id} asChild active={isActive(currentPath, link.href)}>
                                            <Link
                                                href={link.href}
                                                onClick={() => trackNavigation(link, group.id)}
                                                className="group block rounded-none px-5 py-4 hover:bg-secondary focus:bg-secondary"
                                            >
                                                <span className="font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-foreground group-hover:text-primary">
                                                    {link.label}
                                                </span>
                                                {link.description ? (
                                                    <span className="mt-1 block text-xs leading-relaxed text-muted-foreground">
                                                        {link.description}
                                                    </span>
                                                ) : null}
                                            </Link>
                                        </NavigationMenuLink>
                                    ))}
                                </div>
                            </NavigationMenuContent>
                        </NavigationMenuItem>
                    ))}
                </NavigationMenuList>
            </NavigationMenu>

            {customer ? (
                <Button variant="ghost" size="sm" className={`hidden lg:inline-flex ${desktopNavClass}`} asChild>
                    <Link href={route('customers.portal', undefined, false, ziggy)}>{t('common.nav.my_portal')}</Link>
                </Button>
            ) : null}
            <LanguageToggle className="hidden sm:inline-flex" />
            <BookingCtaButton compact className="hidden rounded-none lg:inline-flex" asChild>
                <Link href={bookingHref} onClick={openBooking}>{bookingLabel}</Link>
            </BookingCtaButton>

            <Sheet open={open} onOpenChange={setOpen}>
                <SheetTrigger asChild className="md:hidden">
                    <Button variant="ghost" size="icon" className="h-11 w-11 rounded-none" aria-label={locale === 'en' ? 'Open menu' : 'Abrir menú'}>
                        <Menu className="h-5 w-5" />
                    </Button>
                </SheetTrigger>
                <SheetContent side="right" className="!w-[min(92vw,24rem)] !max-w-none overflow-y-auto border-l border-border bg-background p-0">
                    <SheetHeader className="border-b border-border/70 px-5 py-5 pr-14 text-left">
                        <SheetTitle className="text-left">
                            <span className="sr-only">Lapsique Media</span>
                            <LapsiqueMediaLogo />
                        </SheetTitle>
                        <SheetDescription className="sr-only">
                            {locale === 'en' ? 'Primary website navigation' : 'Navegación principal del sitio'}
                        </SheetDescription>
                    </SheetHeader>
                    <div className="mx-5 mt-5 flex items-center justify-between border-b border-border/70 pb-5">
                        <p className="alpha-kicker text-muted-foreground">Sony Alpha · Editorial</p>
                        <LanguageToggle />
                    </div>
                    <nav className="mx-5 mt-3" aria-label={locale === 'en' ? 'Mobile navigation' : 'Navegación móvil'}>
                        <MobileLink
                            link={navigation.portfolio}
                            currentPath={currentPath}
                            onNavigate={() => {
                                trackNavigation(navigation.portfolio, 'portfolio');
                                setOpen(false);
                            }}
                        />
                        <Accordion type="multiple" className="border-t border-border/70">
                            {navigation.groups.map((group) => (
                                <AccordionItem key={group.id} value={group.id} className="border-border/70">
                                    <AccordionTrigger className="rounded-none py-5 font-ui-display text-base font-bold uppercase tracking-[0.08em] hover:no-underline">
                                        {group.label}
                                    </AccordionTrigger>
                                    <AccordionContent className="grid gap-1 pb-4">
                                        {group.links.map((link) => (
                                            <MobileLink
                                                key={link.id}
                                                link={link}
                                                currentPath={currentPath}
                                                compact
                                                onNavigate={() => {
                                                    trackNavigation(link, group.id);
                                                    setOpen(false);
                                                }}
                                            />
                                        ))}
                                    </AccordionContent>
                                </AccordionItem>
                            ))}
                        </Accordion>
                        <BookingCtaButton asChild className="mt-6 min-h-13 w-full justify-center rounded-none">
                            <Link
                                href={bookingHref}
                                onClick={(event) => {
                                    setOpen(false);
                                    openBooking(event);
                                }}
                            >
                                <CalendarDays className="h-5 w-5" />
                                {bookingLabel}
                            </Link>
                        </BookingCtaButton>
                    </nav>
                </SheetContent>
            </Sheet>
        </div>
    );
}

function MobileLink({
    link,
    currentPath,
    compact = false,
    onNavigate,
}: {
    link: SiteNavigationLink;
    currentPath: string;
    compact?: boolean;
    onNavigate: () => void;
}) {
    return (
        <Link
            href={link.href}
            onClick={onNavigate}
            className={cn(
                'block border-l-2 px-4 py-4 transition hover:border-primary hover:bg-secondary',
                compact && 'py-3',
                isActive(currentPath, link.href)
                    ? 'border-primary bg-secondary text-primary'
                    : 'border-transparent text-foreground',
            )}
        >
            <span className="font-ui-display text-sm font-bold uppercase tracking-[0.08em]">{link.label}</span>
            {link.description ? <span className="mt-1 block text-xs leading-relaxed text-muted-foreground">{link.description}</span> : null}
        </Link>
    );
}

const desktopNavClass = 'rounded-none bg-transparent px-3 font-ui-display text-[0.68rem] font-bold uppercase tracking-[0.12em] hover:bg-transparent hover:text-primary focus:bg-transparent data-[state=open]:bg-transparent data-[state=open]:text-primary';

function normalizePath(url: string): string {
    try {
        return url.startsWith('http') ? new URL(url).pathname : (url.split('?')[0] || '/');
    } catch {
        return '/';
    }
}

function isActive(currentPath: string, href: string): boolean {
    const path = normalizePath(href);
    return path === '/' ? currentPath === '/' : currentPath === path || currentPath.startsWith(`${path}/`);
}
