import { FormEvent } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { glassCardVariants } from '@/lib/variants';
import { cn } from '@/lib/utils';
import { useTranslations } from '@/hooks/useTranslations';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

export default function CustomerLogin() {
    const { ziggy, flash } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('customers.login.store', undefined, false, ziggy));
    };

    const formError = errors.email;

    return (
        <SiteLayout>
            <Head title={t('customer.login.head_title')} />
            <div className="mx-auto max-w-md py-16">
                <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">{t('customer.portal.title')}</p>
                <h1 className="mt-2 font-display text-3xl font-bold">{t('customer.login.title')}</h1>
                <p className="mt-3 text-sm text-muted-foreground">
                    {t('customer.login.subtitle')}
                </p>

                <div className={cn(glassCardVariants({ elevated: true }), 'mt-8 p-8')}>
                    {flash?.status && (
                        <div className="mb-4 rounded-lg border border-primary/40 bg-primary/10 px-4 py-3 text-sm">
                            {flash.status}
                        </div>
                    )}
                    {formError && (
                        <div className="mb-4 rounded-lg border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                            {formError}
                        </div>
                    )}

                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="email">{t('common.form.email')}</Label>
                            <Input
                                id="email"
                                type="email"
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="password">{t('customer.login.password')}</Label>
                            <Input
                                id="password"
                                type="password"
                                autoComplete="current-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                required
                            />
                        </div>
                        <div className="flex items-center justify-between gap-4">
                            <label className="flex items-center gap-2 text-sm text-muted-foreground">
                                <Checkbox
                                    checked={data.remember}
                                    onCheckedChange={(checked) => setData('remember', checked === true)}
                                />
                                {t('customer.login.remember')}
                            </label>
                            <Link
                                href={route('customers.password.request', undefined, false, ziggy)}
                                className="text-sm text-primary hover:underline"
                            >
                                {t('customer.login.forgot')}
                            </Link>
                        </div>
                        <Button type="submit" variant="cinematic" className="w-full" disabled={processing}>
                            {processing ? t('customer.login.submitting') : t('customer.login.submit')}
                        </Button>
                    </form>
                </div>
            </div>
        </SiteLayout>
    );
}
