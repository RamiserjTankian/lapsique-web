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

export default function CustomerForgotPassword() {
    const { ziggy, flash } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('customers.password.email', undefined, false, ziggy));
    };

    return (
        <SiteLayout>
            <Head title={t('customer.forgot.title')} />
            <div className="mx-auto max-w-md py-16">
                <h1 className="font-display text-3xl font-bold">{t('customer.forgot.title')}</h1>
                <p className="mt-3 text-sm text-muted-foreground">
                    {t('customer.forgot.subtitle')}
                </p>

                <div className={cn(glassCardVariants({ elevated: true }), 'mt-8 p-8')}>
                    {flash?.status && (
                        <div className="mb-4 rounded-lg border border-primary/40 bg-primary/10 px-4 py-3 text-sm">
                            {flash.status}
                        </div>
                    )}

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
                            {errors.email && (
                                <p className="text-sm text-destructive">{errors.email}</p>
                            )}
                        </div>
                        <Button type="submit" variant="cinematic" className="w-full" disabled={processing}>
                            {t('customer.forgot.submit')}
                        </Button>
                    </form>

                    <p className="mt-6 text-center text-sm">
                        <Link
                            href={route('customers.login', undefined, false, ziggy)}
                            className="text-primary hover:underline"
                        >
                            {t('customer.forgot.back')}
                        </Link>
                    </p>
                </div>
            </div>
        </SiteLayout>
    );
}
