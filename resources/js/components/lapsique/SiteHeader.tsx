import { useState, type MouseEvent } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Building2, CalendarDays, Drone, Menu, Music2, UtensilsCrossed } from 'lucide-react';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { LapsiqueMediaLogo } from '@/components/lapsique/LapsiqueMediaLogo';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { LanguageToggle } from '@/components/lapsique/LanguageToggle';
import { useTranslations } from '@/hooks/useTranslations';
import { markBookingModalPending, openBookingModal } from '@/lib/openBookingModal';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

export function SiteHeader() {
    const { ziggy } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);

    return (
        <header className="glass-nav sticky top-0 z-50 border-b border-border/80">
            <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <Link
                    href={route('home', undefined, false, ziggy)}
                    className="rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                    aria-label="lapsique media"
                >
                    <LapsiqueMediaLogo />
                </Link>

                <MotionHeaderActions open={open} setOpen={setOpen} />
            </div>
        </header>
    );
}

function MotionHeaderActions({
    open,
    setOpen,
}: {
    open: boolean;
    setOpen: (v: boolean) => void;
}) {
    const { props, url } = usePage<PageProps>();
    const { ziggy, customer } = props;
    const { t } = useTranslations();
    const homeAgenda = `${route('home', undefined, false, ziggy)}#agenda`;
    const foodReelsHref = route('food-reels.show', undefined, false, ziggy);
    const djSetHref = route('djset.show', undefined, false, ziggy);
    const droneSessionsHref = route('drone-sessions.show', undefined, false, ziggy);
    const constructionProgressHref = route('construction-progress.show', undefined, false, ziggy);
    const currentPath = typeof url === 'string' ? url.split('?')[0] : '';
    const isConstructionProgressPage = currentPath === '/avances-de-obra';
    const bookingCtaHref = isConstructionProgressPage ? `${constructionProgressHref}#agenda` : homeAgenda;
    const bookingCtaLabel = isConstructionProgressPage ? t('common.nav.book_progress') : t('common.nav.book_session');
    const navHoverClass = 'hover:bg-primary/10 hover:text-foreground dark:hover:bg-primary/15';
    const openBookingPopup = (event: MouseEvent) => {
        if (document.getElementById('agenda')) {
            event.preventDefault();
            openBookingModal({
                source: 'header',
                analyticsEvent: 'header_cta_clicked',
            });

            return;
        }

        markBookingModalPending();
    };

    return (
        <div className="flex items-center gap-2">
            <Button variant="ghost" size="sm" className={`hidden md:inline-flex ${navHoverClass}`} asChild>
                <Link href={route('portfolio.index', undefined, false, ziggy)}>{t('common.nav.portfolio')}</Link>
            </Button>
            <Button variant="ghost" size="sm" className={`hidden lg:inline-flex ${navHoverClass}`} asChild>
                <Link href={foodReelsHref}>{t('common.nav.food_reels')}</Link>
            </Button>
            <Button variant="ghost" size="sm" className={`hidden md:inline-flex ${navHoverClass}`} asChild>
                <Link href={djSetHref}>{t('common.nav.dj_sets')}</Link>
            </Button>
            <Button variant="ghost" size="sm" className={`hidden md:inline-flex ${navHoverClass}`} asChild>
                <Link href={droneSessionsHref}>{t('common.nav.drone_sessions')}</Link>
            </Button>
            <Button variant="ghost" size="sm" className={`hidden lg:inline-flex ${navHoverClass}`} asChild>
                <Link href={constructionProgressHref}>{t('common.nav.construction_progress')}</Link>
            </Button>
            {customer && (
                <Button variant="ghost" size="sm" className={`hidden md:inline-flex ${navHoverClass}`} asChild>
                    <Link href={route('customers.portal', undefined, false, ziggy)}>{t('common.nav.my_portal')}</Link>
                </Button>
            )}
            <LanguageToggle className="hidden sm:inline-flex" />
            <BookingCtaButton compact className="hidden md:inline-flex" asChild>
                <Link href={bookingCtaHref} onClick={openBookingPopup}>
                    {bookingCtaLabel}
                </Link>
            </BookingCtaButton>

            <Sheet open={open} onOpenChange={setOpen}>
                <SheetTrigger asChild className="md:hidden">
                    <Button variant="ghost" size="icon" className="h-11 w-11">
                        <Menu className="h-5 w-5" />
                    </Button>
                </SheetTrigger>
                <SheetContent
                    side="right"
                    className="glass-panel-elevated !w-[min(92vw,24rem)] !max-w-none overflow-y-auto border-l border-border/80 p-0"
                >
                    <SheetHeader className="border-b border-border/70 px-5 py-5 pr-14 text-left">
                        <SheetTitle asChild>
                            <LapsiqueMediaLogo className="text-left" />
                        </SheetTitle>
                    </SheetHeader>
                    <div className="mx-5 mt-5 flex justify-end">
                        <LanguageToggle />
                    </div>
                    <nav className="mx-5 mt-6 grid gap-2" aria-label="Mobile navigation">
                        <Button variant="ghost" asChild className={`h-auto min-h-12 w-full justify-start gap-3 whitespace-normal rounded-xl px-4 py-3 text-left ${navHoverClass}`}>
                            <Link
                                href={route('portfolio.index', undefined, false, ziggy)}
                                onClick={() => setOpen(false)}
                            >
                                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border/70 bg-background/70">
                                    <Menu className="h-4 w-4 text-primary" />
                                </span>
                                {t('common.nav.portfolio')}
                            </Link>
                        </Button>
                        <Button variant="ghost" asChild className={`h-auto min-h-12 w-full justify-start gap-3 whitespace-normal rounded-xl px-4 py-3 text-left ${navHoverClass}`}>
                            <Link href={foodReelsHref} onClick={() => setOpen(false)}>
                                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-primary/25 bg-primary/10">
                                    <UtensilsCrossed className="h-4 w-4 text-primary" />
                                </span>
                                {t('common.nav.food_reels')}
                            </Link>
                        </Button>
                        <Button variant="ghost" asChild className={`h-auto min-h-12 w-full justify-start gap-3 whitespace-normal rounded-xl px-4 py-3 text-left ${navHoverClass}`}>
                            <Link href={djSetHref} onClick={() => setOpen(false)}>
                                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-primary/25 bg-primary/10">
                                    <Music2 className="h-4 w-4 text-primary" />
                                </span>
                                {t('common.nav.dj_sets')}
                            </Link>
                        </Button>
                        <Button variant="ghost" asChild className={`h-auto min-h-12 w-full justify-start gap-3 whitespace-normal rounded-xl px-4 py-3 text-left ${navHoverClass}`}>
                            <Link href={droneSessionsHref} onClick={() => setOpen(false)}>
                                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-primary/25 bg-primary/10">
                                    <Drone className="h-4 w-4 text-primary" />
                                </span>
                                {t('common.nav.drone_sessions')}
                            </Link>
                        </Button>
                        <Button variant="ghost" asChild className={`h-auto min-h-12 w-full justify-start gap-3 whitespace-normal rounded-xl px-4 py-3 text-left ${navHoverClass}`}>
                            <Link href={constructionProgressHref} onClick={() => setOpen(false)}>
                                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-primary/25 bg-primary/10">
                                    <Building2 className="h-4 w-4 text-primary" />
                                </span>
                                {t('common.nav.construction_progress')}
                            </Link>
                        </Button>
                        {customer && (
                            <Button variant="ghost" asChild className={`h-auto min-h-12 w-full justify-start gap-3 whitespace-normal rounded-xl px-4 py-3 text-left ${navHoverClass}`}>
                                <Link
                                    href={route('customers.portal', undefined, false, ziggy)}
                                    onClick={() => setOpen(false)}
                                >
                                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border/70 bg-background/70">
                                        <Menu className="h-4 w-4 text-primary" />
                                    </span>
                                    {t('common.nav.my_portal')}
                                </Link>
                            </Button>
                        )}
                        <BookingCtaButton asChild className="mt-2 h-auto min-h-12 w-full justify-center whitespace-normal rounded-xl px-4 py-3 text-center">
                            <Link
                                href={bookingCtaHref}
                                onClick={(event) => {
                                    setOpen(false);
                                    openBookingPopup(event);
                                }}
                            >
                                <CalendarDays className="h-5 w-5" />
                                {bookingCtaLabel}
                            </Link>
                        </BookingCtaButton>
                    </nav>
                </SheetContent>
            </Sheet>
        </div>
    );
}
