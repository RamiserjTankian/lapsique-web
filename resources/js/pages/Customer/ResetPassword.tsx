import { FormEvent } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/hooks/useTranslations';
import { glassCardVariants } from '@/lib/variants';
import { cn } from '@/lib/utils';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';

interface ResetPasswordProps {
    token: string;
    email: string;
}

export default function CustomerResetPassword({ token, email }: ResetPasswordProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('customers.password.update', undefined, false, ziggy));
    };

    return (
        <SiteLayout>
            <Head title={t('customer.reset.title')} />
            <div className="mx-auto max-w-md py-16">
                <h1 className="font-display text-3xl font-bold">{t('customer.reset.title')}</h1>
                <p className="mt-3 text-sm text-muted-foreground">
                    {t('customer.reset.subtitle')}
                </p>

                <div className={cn(glassCardVariants({ elevated: true }), 'mt-8 p-8')}>
                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="email">{t('common.form.email')}</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="password">{t('customer.reset.title')}</Label>
                            <Input
                                id="password"
                                type="password"
                                autoComplete="new-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                required
                            />
                            {errors.password && (
                                <p className="text-sm text-destructive">{errors.password}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="password_confirmation">{t('customer.reset.confirm')}</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                required
                            />
                        </div>
                        <Button type="submit" variant="cinematic" className="w-full" disabled={processing}>
                            {t('customer.reset.submit')}
                        </Button>
                    </form>

                    <p className="mt-6 text-center text-sm">
                        <Link
                            href={route('customers.login', undefined, false, ziggy)}
                            className="text-primary hover:underline"
                        >
                            {t('customer.reset.back')}
                        </Link>
                    </p>
                </div>
            </div>
        </SiteLayout>
    );
}
