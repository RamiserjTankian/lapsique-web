import { useState, type MouseEvent } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { ThemeToggle } from '@/components/lapsique/ThemeToggle';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

const navLinks = [
    { label: 'Inicio', name: 'home' },
    { label: 'Portafolio', name: 'portfolio.index' },
    { label: 'Videos', name: 'videos.index' },
    { label: 'DJs', name: 'djs.index' },
] as const;

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

                <nav className="hidden items-center gap-6 md:flex">
                    {navLinks.map((link) => (
                        <Link
                            key={link.name}
                            href={route(link.name, undefined, false, ziggy)}
                            className="text-xs uppercase tracking-[0.18em] text-muted-foreground transition hover:text-foreground"
                        >
                            {link.label}
                        </Link>
                    ))}
                </nav>

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
    const homeAgenda = `${route('home', undefined, false, ziggy)}#agenda`;
    const openBookingPopup = (event: MouseEvent<HTMLAnchorElement>) => {
        const target = new URL(homeAgenda, window.location.origin);

        if (target.pathname !== window.location.pathname) {
            return;
        }

        event.preventDefault();
        window.dispatchEvent(new CustomEvent('booking:open'));
    };

    return (
        <div className="flex items-center gap-2">
            {customer && (
                <Button variant="ghost" size="sm" className="hidden md:inline-flex" asChild>
                    <Link href={route('customers.portal', undefined, false, ziggy)}>Mi portal</Link>
                </Button>
            )}
            <ThemeToggle />
            <Button variant="cinematic" size="lg" className="hidden rounded-xl px-6 font-bold md:inline-flex" asChild>
                <Link href={homeAgenda} onClick={openBookingPopup}>Agendar sesión</Link>
            </Button>

            <Sheet open={open} onOpenChange={setOpen}>
                <SheetTrigger asChild className="md:hidden">
                    <Button variant="ghost" size="icon">
                        <Menu className="h-5 w-5" />
                    </Button>
                </SheetTrigger>
                <SheetContent side="right" className="glass-panel-elevated">
                    <SheetHeader>
                        <SheetTitle className="font-display">{siteName}</SheetTitle>
                    </SheetHeader>
                    <div className="mt-6 flex items-center justify-between">
                        <span className="text-xs uppercase tracking-wider text-muted-foreground">Tema</span>
                        <ThemeToggle />
                    </div>
                    <nav className="mt-8 flex flex-col gap-4">
                        {navLinks.map((link) => (
                            <Link
                                key={link.name}
                                href={route(link.name, undefined, false, ziggy)}
                                className="text-sm uppercase tracking-wider"
                                onClick={() => setOpen(false)}
                            >
                                {link.label}
                            </Link>
                        ))}
                        <Button variant="cinematic" size="lg" asChild className="mt-4 rounded-xl font-bold">
                            <Link
                                href={homeAgenda}
                                onClick={(event) => {
                                    setOpen(false);
                                    openBookingPopup(event);
                                }}
                            >
                                Agendar sesión
                            </Link>
                        </Button>
                    </nav>
                </SheetContent>
            </Sheet>
        </div>
    );
}
