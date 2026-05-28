import { Head, Link, router, usePage } from '@inertiajs/react';
import { KeyRound, LogOut, Mail, User } from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { Button } from '@/components/ui/button';
import { PortalEmptyState } from '@/components/lapsique/portal/PortalEmptyState';
import { SessionCard } from '@/components/lapsique/portal/SessionCard';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { ContentBookingData, PageProps } from '@/types';

interface CustomerPortalProps {
    customer: { id: number; name: string; email: string } | null;
    bookings: ContentBookingData[];
}

export default function CustomerPortal({ customer, bookings }: CustomerPortalProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { t, locale } = useTranslations();

    if (!customer) {
        return (
            <SiteLayout>
                <Head title={t('customer.portal.title')} />
                <div className="py-20 text-center">
                    <h1 className="font-display text-2xl font-bold">{t('customer.portal.title')}</h1>
                    <p className="mt-4 text-muted-foreground">{t('customer.portal.login_prompt')}</p>
                    <Button variant="cinematic" className="mt-6" asChild>
                        <Link href={route('customers.login', undefined, false, ziggy)}>
                            {t('customer.portal.login_cta')}
                        </Link>
                    </Button>
                </div>
            </SiteLayout>
        );
    }

    const logout = () => {
        router.post(route('customers.logout', undefined, false, ziggy));
    };

    const sessionCount = bookings.length;

    return (
        <SiteLayout>
            <Head title={t('customer.portal.title')} />
            <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6 md:py-14">
                <header className="glass-panel flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-border p-5 md:p-6">
                    <div className="flex items-center gap-4">
                        <span className="flex h-12 w-12 items-center justify-center rounded-2xl border border-primary/25 bg-primary/10 text-lg font-bold text-primary">
                            {customer.name.charAt(0).toUpperCase()}
                        </span>
                        <div>
                            <h1 className="font-display text-2xl font-bold leading-tight">
                                {t('customer.portal.greeting', { name: customer.name })}
                            </h1>
                            <p className="text-sm text-muted-foreground">{customer.email}</p>
                        </div>
                    </div>
                    <Button variant="outline" size="sm" onClick={logout}>
                        <LogOut className="mr-2 h-4 w-4" />
                        {t('customer.portal.logout')}
                    </Button>
                </header>

                <div className="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_300px]">
                    <section>
                        <div className="mb-4 flex items-baseline justify-between gap-3">
                            <h2 className="font-display text-lg font-semibold">
                                {t('customer.portal.sessions_heading')}
                            </h2>
                            {sessionCount > 0 && (
                                <span className="text-sm text-muted-foreground">
                                    {t('customer.portal.sessions_count', { count: sessionCount })}
                                </span>
                            )}
                        </div>

                        {sessionCount === 0 ? (
                            <PortalEmptyState />
                        ) : (
                            <div className="grid gap-5">
                                {bookings.map((booking) => (
                                    <SessionCard
                                        key={booking.public_id}
                                        booking={booking}
                                        locale={locale}
                                    />
                                ))}
                            </div>
                        )}
                    </section>

                    <aside className="lg:sticky lg:top-24 lg:self-start">
                        <div className="glass-panel rounded-2xl border border-border p-5">
                            <h2 className="font-display text-base font-semibold">
                                {t('customer.portal.tab_profile')}
                            </h2>
                            <dl className="mt-4 space-y-3 text-sm">
                                <div className="flex items-start gap-2">
                                    <User className="mt-0.5 h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <dt className="text-xs text-muted-foreground">
                                            {t('customer.portal.name_label')}
                                        </dt>
                                        <dd className="text-foreground">{customer.name}</dd>
                                    </div>
                                </div>
                                <div className="flex items-start gap-2">
                                    <Mail className="mt-0.5 h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <dt className="text-xs text-muted-foreground">
                                            {t('common.form.email')}
                                        </dt>
                                        <dd className="break-all text-foreground">{customer.email}</dd>
                                    </div>
                                </div>
                            </dl>
                            <Button variant="outline" size="sm" className="mt-5 w-full" asChild>
                                <Link href={route('customers.password.request', undefined, false, ziggy)}>
                                    <KeyRound className="mr-2 h-4 w-4" />
                                    {t('customer.portal.change_password')}
                                </Link>
                            </Button>
                        </div>
                    </aside>
                </div>
            </div>
        </SiteLayout>
    );
}
