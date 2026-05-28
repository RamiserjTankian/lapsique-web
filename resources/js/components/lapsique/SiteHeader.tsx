import { useState, type MouseEvent } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { LanguageToggle } from '@/components/lapsique/LanguageToggle';
import { ThemeToggle } from '@/components/lapsique/ThemeToggle';
import { useTranslations } from '@/hooks/useTranslations';
import { markBookingModalPending, openBookingModal } from '@/lib/openBookingModal';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

export function SiteHeader() {
    const { ziggy, site } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);

    return (
        <header className="glass-nav sticky top-0 z-50 border-b border-border/80">
            <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <Link
                    href={route('home', undefined, false, ziggy)}
                    className="font-display text-lg font-bold tracking-tight"
                >
                    {site.name}
                </Link>

                <MotionHeaderActions open={open} setOpen={setOpen} siteName={site.name} />
            </div>
        </header>
    );
}

function MotionHeaderActions({
    open,
    setOpen,
    siteName,
}: {
    open: boolean;
    setOpen: (v: boolean) => void;
    siteName: string;
}) {
    const { ziggy, customer } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const homeAgenda = `${route('home', undefined, false, ziggy)}#agenda`;
    const openBookingPopup = (event: MouseEvent<HTMLAnchorElement>) => {
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
            <Button variant="ghost" size="sm" className="hidden md:inline-flex" asChild>
                <Link href={route('portfolio.index', undefined, false, ziggy)}>{t('common.nav.portfolio')}</Link>
            </Button>
            {customer && (
                <Button variant="ghost" size="sm" className="hidden md:inline-flex" asChild>
                    <Link href={route('customers.portal', undefined, false, ziggy)}>{t('common.nav.my_portal')}</Link>
                </Button>
            )}
            <LanguageToggle className="hidden sm:inline-flex" />
            <ThemeToggle />
            <BookingCtaButton compact className="hidden md:inline-flex" asChild>
                <Link href={homeAgenda} onClick={openBookingPopup}>
                    {t('common.nav.book_session')}
                </Link>
            </BookingCtaButton>

            <Sheet open={open} onOpenChange={setOpen}>
                <SheetTrigger asChild className="md:hidden">
                    <Button variant="ghost" size="icon" className="h-11 w-11">
                        <Menu className="h-5 w-5" />
                    </Button>
                </SheetTrigger>
                <SheetContent side="right" className="glass-panel-elevated">
                    <SheetHeader>
                        <SheetTitle className="font-display">{siteName}</SheetTitle>
                    </SheetHeader>
                    <div className="mt-6 flex items-center justify-between">
                        <span className="text-xs uppercase tracking-wider text-muted-foreground">{t('common.nav.theme')}</span>
                        <div className="flex items-center gap-2">
                            <LanguageToggle />
                            <ThemeToggle />
                        </div>
                    </div>
                    <div className="mt-8 flex flex-col gap-3">
                        <Button variant="outline" asChild className="w-full">
                            <Link
                                href={route('portfolio.index', undefined, false, ziggy)}
                                onClick={() => setOpen(false)}
                            >
                                {t('common.nav.portfolio')}
                            </Link>
                        </Button>
                        <BookingCtaButton asChild className="w-full">
                            <Link
                                href={homeAgenda}
                                onClick={(event) => {
                                    setOpen(false);
                                    openBookingPopup(event);
                                }}
                            >
                                {t('common.nav.book_session')}
                            </Link>
                        </BookingCtaButton>
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    );
}
