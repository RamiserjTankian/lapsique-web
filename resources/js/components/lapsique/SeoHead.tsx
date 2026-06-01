import { Head, usePage } from '@inertiajs/react';
import type { PageProps, SeoMeta } from '@/types';

interface SeoHeadProps {
    /** Override shared SEO (e.g. when page title differs from route default). */
    seo?: Partial<SeoMeta>;
}

export function SeoHead({ seo: override }: SeoHeadProps) {
    const { seo: shared, locale } = usePage<PageProps>().props;
    const ogLocale = locale === 'en' ? 'en_US' : 'es_MX';
    if (!shared && !override) {
        return null;
    }

    const seo: SeoMeta = { ...shared!, ...override };
    const ogImage = seo.ogImage ?? '';
    const siteName = seo.metaTitle.includes('Trascendental') || seo.title === 'Trascendental'
        ? 'Trascendental'
        : 'lapsique.media';

    return (
        <Head title={seo.title}>
            <meta name="description" content={seo.description} />
            {seo.keywords ? <meta name="keywords" content={seo.keywords} /> : null}
            <meta property="og:type" content={seo.ogType} />
            <meta property="og:url" content={seo.canonicalUrl} />
            <meta property="og:title" content={seo.metaTitle} />
            <meta property="og:description" content={seo.description} />
            {ogImage ? <meta property="og:image" content={ogImage} /> : null}
            {ogImage ? <meta property="og:image:secure_url" content={ogImage} /> : null}
            <meta property="og:site_name" content={siteName} />
            <meta property="og:locale" content={ogLocale} />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" content={seo.metaTitle} />
            <meta name="twitter:description" content={seo.description} />
            {ogImage ? <meta name="twitter:image" content={ogImage} /> : null}
            <link rel="canonical" href={seo.canonicalUrl} />
        </Head>
    );
}
